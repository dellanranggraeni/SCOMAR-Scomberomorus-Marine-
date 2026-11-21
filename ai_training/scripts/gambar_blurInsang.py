from pathlib import Path
import csv
import shutil

ROOT = Path(__file__).resolve().parents[1]

DATASET_DIR = ROOT / "datasets" / "insang" / "KesegaranInsang"
CSV_PATH = ROOT / "runs" / "audit_kualitas_insang" / "laporan_kualitas_insang.csv"

# Folder cadangan di luar dataset aktif
BACKUP_DIR = ROOT / "datasets" / "insang" / "gambar_dikeluarkan_blur"

BATAS_BLUR = 20

dipindahkan = 0
dilewati = 0

with open(CSV_PATH, "r", encoding="utf-8-sig") as f:
    data = list(csv.DictReader(f))

for item in data:
    # Hanya mengeluarkan gambar training
    if item["split"] != "train":
        continue

    nilai = float(item["nilai_ketajaman"])

    if nilai >= BATAS_BLUR:
        continue

    kelas = item["kelas"]
    sumber = DATASET_DIR / "train" / kelas / item["nama_file"]

    if not sumber.exists():
        dilewati += 1
        continue

    tujuan_folder = BACKUP_DIR / "train" / kelas
    tujuan_folder.mkdir(parents=True, exist_ok=True)

    tujuan = tujuan_folder / sumber.name

    if tujuan.exists():
        dilewati += 1
        continue

    shutil.move(str(sumber), str(tujuan))
    dipindahkan += 1

print("\n===== HASIL PENYARINGAN BLUR =====")
print("Gambar dikeluarkan dari training:", dipindahkan)
print("File dilewati:", dilewati)
print("Folder cadangan:", BACKUP_DIR)