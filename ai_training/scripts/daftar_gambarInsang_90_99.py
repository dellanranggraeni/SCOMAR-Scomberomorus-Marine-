from pathlib import Path
import csv

ROOT = Path(__file__).resolve().parents[1]

AUDIT_DIR = ROOT / "runs" / "audit_kualitas_insang"
CSV_INPUT = AUDIT_DIR / "laporan_kualitas_insang.csv"
CSV_OUTPUT = AUDIT_DIR / "daftar_train_ketajaman_90_99.csv"

gambar_terpilih = []

with open(CSV_INPUT, "r", encoding="utf-8-sig") as file:
    reader = csv.DictReader(file)

    for row in reader:
        if row["split"] != "train":
            continue

        if row["status"] != "Terindikasi blur":
            continue

        nilai = float(row["nilai_ketajaman"])

        if 90 <= nilai < 100:
            gambar_terpilih.append(row)

with open(CSV_OUTPUT, "w", newline="", encoding="utf-8-sig") as file:
    kolom = [
        "split",
        "kelas",
        "nama_file",
        "nilai_ketajaman",
        "status"
    ]

    writer = csv.DictWriter(file, fieldnames=kolom)
    writer.writeheader()
    writer.writerows(gambar_terpilih)

print("\n===== GAMBAR KETAJAMAN 90-99 =====")

for kelas in ["fresh", "non-fresh"]:
    data = [
        row for row in gambar_terpilih
        if row["kelas"] == kelas
    ]

    print(f"{kelas}: {len(data)} gambar")

print("Total gambar:", len(gambar_terpilih))
print("Daftar tersimpan di:", CSV_OUTPUT)