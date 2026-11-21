from pathlib import Path
from ultralytics import YOLO
import csv
import shutil
import cv2

def cek_kualitas_gambar(image_path, batas_blur=100):
    gambar = cv2.imread(str(image_path))

    if gambar is None:
        return False, 0, "Gambar tidak dapat dibaca"

    abu_abu = cv2.cvtColor(gambar, cv2.COLOR_BGR2GRAY)

    nilai_ketajaman = cv2.Laplacian(
        abu_abu,
        cv2.CV_64F
    ).var()

    if nilai_ketajaman < batas_blur:
        return False, nilai_ketajaman, "Gambar buram"

    return True, nilai_ketajaman, "Gambar jelas"

# Lokasi folder utama
BASE_DIR = Path(__file__).resolve().parents[1]

# Lokasi model terbaik
MODEL_PATH = BASE_DIR / "runs" / "kesegaran_mata-3" / "weights" / "best.pt"

# Lokasi dataset pengujian
TEST_DIR = BASE_DIR / "datasets" / "mata" / "KesegaranMata_Bersih" / "test"

# Lokasi penyimpanan hasil audit
OUTPUT_DIR = BASE_DIR / "runs" / "audit_kesegaran_mata_bersih_3"
WRONG_DIR = OUTPUT_DIR / "gambar_salah"

# Memuat model
model = YOLO(str(MODEL_PATH))

# Membuat folder hasil audit
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

# Mengambil seluruh gambar pengujian
image_paths = sorted([
    p for p in TEST_DIR.rglob("*")
    if p.suffix.lower() in [".jpg", ".jpeg", ".png", ".webp"]
])

hasil_audit = []

print(f"Jumlah gambar pengujian: {len(image_paths)}")

# Memproses gambar satu per satu
for image_path in image_paths:

    # Label asli berasal dari nama folder
    label_asli = image_path.parent.name

    # Periksa kualitas gambar terlebih dahulu
    gambar_jelas, nilai_ketajaman, keterangan = cek_kualitas_gambar(
        image_path,
        batas_blur=100
    )

    # Jika gambar buram, lewati prediksi AI
    if not gambar_jelas:
        print(
            f"{image_path.name} | "
            f"GAGAL: {keterangan} | "
            f"Ketajaman: {nilai_ketajaman:.2f}"
        )

        hasil_audit.append({
            "nama_gambar": image_path.name,
            "label_asli": label_asli,
            "prediksi_ai": "Tidak Diprediksi",
            "confidence": 0,
            "status": "Gagal - Gambar Buram"
        })
        continue

    # Jika gambar jelas, lanjutkan prediksi AI
    result = model.predict(
        source=str(image_path),
        imgsz=224,
        device="cpu",
        verbose=False
    )[0]

    indeks_prediksi = result.probs.top1
    label_prediksi = model.names[indeks_prediksi]
    confidence = float(result.probs.top1conf)

    status = "Benar" if label_asli == label_prediksi else "Salah"

    print(
        f"{image_path.name} | "
        f"Asli: {label_asli} | "
        f"Prediksi: {label_prediksi} | "
        f"{status}"
    )

    # Simpan gambar yang salah diprediksi
    if status == "Salah":

        nama_folder = f"{label_asli}_diprediksi_{label_prediksi}"
        folder_tujuan = WRONG_DIR / nama_folder
        folder_tujuan.mkdir(parents=True, exist_ok=True)

        shutil.copy2(
            image_path,
            folder_tujuan / image_path.name
        )

    hasil_audit.append({
        "nama_gambar": image_path.name,
        "label_asli": label_asli,
        "prediksi_ai": label_prediksi,
        "confidence": round(confidence, 4),
        "status": status
    })

# Menyimpan hasil ke CSV
csv_path = OUTPUT_DIR / "hasil_audit.csv"

with open(csv_path, "w", newline="", encoding="utf-8-sig") as file:
    kolom = [
        "nama_gambar",
        "label_asli",
        "prediksi_ai",
        "confidence",
        "status"
    ]

    writer = csv.DictWriter(file, fieldnames=kolom)
    writer.writeheader()
    writer.writerows(hasil_audit)

print("\nAUDIT SELESAI!")
print(f"Hasil audit: {OUTPUT_DIR}")
print(f"CSV: {csv_path}")