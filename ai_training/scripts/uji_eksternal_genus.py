from pathlib import Path
from ultralytics import YOLO
import csv

ROOT = Path(__file__).resolve().parents[1]

MODEL_PATH = (
    ROOT / "runs" / "identifikasi_genus_v1"
    / "weights" / "best.pt"
)

DATASET_DIR = (
    ROOT / "datasets" / "jenis_ikan"
    / "PengujianEksternal_Genus"
)

OUTPUT_DIR = ROOT / "runs" / "uji_eksternal_genus"
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

CSV_OUTPUT = OUTPUT_DIR / "hasil_prediksi_genus.csv"

EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp", ".bmp"}

LABEL_MAP = {
    "SCOMBEROMORUS": "Scomberomorus",
    "NON-SCOMBEROMORUS": "Non_Scomberomorus",
}


def normalisasi(label):
    return "".join(
        karakter for karakter in str(label).lower()
        if karakter.isalnum()
    )


def main():

    if not MODEL_PATH.exists():
        raise FileNotFoundError(f"Model tidak ditemukan: {MODEL_PATH}")

    if not DATASET_DIR.exists():
        raise FileNotFoundError(f"Dataset tidak ditemukan: {DATASET_DIR}")

    model = YOLO(str(MODEL_PATH))

    print("MODEL:", MODEL_PATH)
    print("KELAS MODEL:", model.names)

    hasil = []

    for folder in sorted(DATASET_DIR.iterdir()):

        if not folder.is_dir():
            continue

        kelas_asli = LABEL_MAP.get(folder.name.upper())

        if kelas_asli is None:
            print("Folder dilewati:", folder.name)
            continue

        files = sorted([
            f for f in folder.rglob("*")
            if f.is_file()
            and f.suffix.lower() in EXTENSIONS
        ])

        print(f"\n===== {kelas_asli.upper()} =====")
        print("Jumlah foto:", len(files))

        for file in files:

            try:
                prediksi = model.predict(
                    source=str(file),
                    imgsz=224,
                    verbose=False
                )[0]

                if prediksi.probs is None:
                    raise ValueError("Hasil prediksi tidak tersedia")

                indeks = int(prediksi.probs.top1)
                prediksi_kelas = model.names[indeks]

                confidence = float(
                    prediksi.probs.top1conf.item()
                )

                benar = (
                    normalisasi(prediksi_kelas)
                    == normalisasi(kelas_asli)
                )

                status = "BENAR" if benar else "SALAH"

                hasil.append({
                    "nama_file": file.name,
                    "label_asli": kelas_asli,
                    "prediksi": prediksi_kelas,
                    "confidence": round(confidence, 4),
                    "hasil": status
                })

                print(
                    f"{file.name} | "
                    f"Prediksi: {prediksi_kelas} | "
                    f"Confidence: {confidence:.4f} | "
                    f"{status}"
                )

            except Exception as e:
                print(f"GAGAL: {file.name} | {e}")

    # Simpan hasil ke CSV
    with open(
        CSV_OUTPUT, "w", newline="", encoding="utf-8-sig"
    ) as f:

        kolom = [
            "nama_file",
            "label_asli",
            "prediksi",
            "confidence",
            "hasil"
        ]

        writer = csv.DictWriter(f, fieldnames=kolom)
        writer.writeheader()
        writer.writerows(hasil)

    print("\n===== REKAP PENGUJIAN EKSTERNAL =====")

    total_benar = 0

    for kelas in ["Scomberomorus", "Non_Scomberomorus"]:

        data = [
            item for item in hasil
            if normalisasi(item["label_asli"]) == normalisasi(kelas)
        ]

        benar = sum(item["hasil"] == "BENAR" for item in data)
        salah = len(data) - benar

        akurasi = benar / len(data) * 100 if data else 0

        total_benar += benar

        print(f"\n{kelas}")
        print("Total:", len(data))
        print("Benar:", benar)
        print("Salah:", salah)
        print(f"Akurasi: {akurasi:.2f}%")

    total = len(hasil)

    print("\n===== TOTAL KESELURUHAN =====")
    print("Total foto:", total)
    print("Prediksi benar:", total_benar)
    print("Prediksi salah:", total - total_benar)

    if total > 0:
        print(
            f"Akurasi eksternal: "
            f"{total_benar / total * 100:.2f}%"
        )
    else:
        print("Tidak ada foto yang berhasil diuji.")

    print("\nCSV tersimpan di:")
    print(CSV_OUTPUT)


if __name__ == "__main__":
    main()