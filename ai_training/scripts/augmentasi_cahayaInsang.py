from pathlib import Path
import csv
from PIL import Image, ImageEnhance

ROOT = Path(__file__).resolve().parents[1]

DATASET_DIR = ROOT / "datasets" / "insang" / "KesegaranInsang"
CSV_PATH = (
    ROOT / "runs" / "audit_kualitas_insang"
    / "daftar_train_ketajaman_90_99.csv"
)

# Variasi pencahayaan
AUGMENTASI = {
    "bright10": 1.10,
    "dark10": 0.90,
}

berhasil = 0
dilewati = 0

with open(CSV_PATH, "r", encoding="utf-8-sig") as f:
    gambar_terpilih = list(csv.DictReader(f))

print("Jumlah gambar sumber:", len(gambar_terpilih))

for item in gambar_terpilih:
    split = item["split"]
    kelas = item["kelas"]
    nama_file = item["nama_file"]

    # Pastikan hanya data training yang diproses
    if split != "train":
        continue

    sumber = DATASET_DIR / split / kelas / nama_file

    if not sumber.exists():
        print("File tidak ditemukan:", sumber)
        dilewati += 1
        continue

    try:
        with Image.open(sumber) as img:
            gambar_asli = img.convert("RGB")

            for jenis, faktor in AUGMENTASI.items():
                nama_baru = (
                    f"{sumber.stem}__{jenis}.jpg"
                )

                tujuan = sumber.parent / nama_baru

                # Hindari membuat duplikat ketika script dijalankan ulang
                if tujuan.exists():
                    dilewati += 1
                    continue

                variasi = ImageEnhance.Brightness(
                    gambar_asli
                ).enhance(faktor)

                variasi.save(
                    tujuan,
                    format="JPEG",
                    quality=95
                )

                berhasil += 1

    except Exception as e:
        print("Gagal memproses:", nama_file, "|", e)
        dilewati += 1

print("\n===== HASIL AUGMENTASI INSANG =====")
print("Gambar sumber:", len(gambar_terpilih))
print("Variasi berhasil dibuat:", berhasil)
print("File dilewati:", dilewati)
print("Faktor brightness: +10% dan -10%")