
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DATASET_DIR = ROOT / "datasets" / "mata" / "KesegaranMata_Revisi"

for split in ["train", "val", "test"]:
    print(f"\n===== {split.upper()} =====")

    total = 0

    for kelas in ["Fresh", "Non_Fresh"]:
        folder = DATASET_DIR / split / kelas

        jumlah = sum(
            1 for file in folder.iterdir()
            if file.is_file() and file.suffix.lower()
            in {".jpg", ".jpeg", ".png", ".bmp", ".webp"}
        )

        total += jumlah
        print(f"{kelas}: {jumlah}")

    print("TOTAL:", total)