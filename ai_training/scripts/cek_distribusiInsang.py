from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DATASET_DIR = ROOT / "datasets" / "insang" / "KesegaranInsang"

EXTENSIONS = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}
CLASSES = ["fresh", "non-fresh"]

grand_total = 0

for split in ["train", "val", "test"]:
    print(f"\n===== {split.upper()} =====")

    split_total = 0

    for kelas in CLASSES:
        folder = DATASET_DIR / split / kelas

        jumlah = sum(
            1 for file in folder.iterdir()
            if file.is_file() and file.suffix.lower() in EXTENSIONS
        )

        split_total += jumlah
        print(f"{kelas}: {jumlah}")

    grand_total += split_total
    print("TOTAL:", split_total)

print("\n===== TOTAL KESELURUHAN =====")
print("Total gambar:", grand_total)