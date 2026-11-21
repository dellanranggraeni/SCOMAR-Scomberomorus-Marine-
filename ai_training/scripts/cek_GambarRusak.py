
from pathlib import Path
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
DATASET_DIR = ROOT / "datasets" / "mata" / "KesegaranMata_Revisi"

EXTENSIONS = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}

total = 0
rusak = []

for split in ["train", "val", "test"]:
    for kelas in ["Fresh", "Non_Fresh"]:
        folder = DATASET_DIR / split / kelas

        for file in folder.iterdir():
            if file.suffix.lower() not in EXTENSIONS:
                continue

            total += 1

            try:
                with Image.open(file) as img:
                    img.verify()
            except Exception as e:
                rusak.append((file, str(e)))

print("\n===== HASIL PEMERIKSAAN =====")
print("Total gambar diperiksa:", total)
print("Jumlah gambar rusak:", len(rusak))

for file, error in rusak:
    print("\nFile:", file)
    print("Error:", error)