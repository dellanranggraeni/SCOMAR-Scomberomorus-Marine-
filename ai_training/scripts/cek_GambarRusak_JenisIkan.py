
from pathlib import Path
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]

DATASET_DIR = (
    ROOT / "datasets" / "jenis_ikan" / "IdentifikasiGenus"
)

SPLITS = ["train", "val", "test"]

CLASSES = [
    "Scomberomorus",
    "Non_Scomberomorus"
]

EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp", ".bmp"}

total = 0
rusak = []

for split in SPLITS:
    for kelas in CLASSES:

        folder = DATASET_DIR / split / kelas

        if not folder.exists():
            raise FileNotFoundError(
                f"Folder tidak ditemukan: {folder}"
            )

        for file in folder.iterdir():

            if not file.is_file():
                continue

            if file.suffix.lower() not in EXTENSIONS:
                continue

            total += 1

            try:
                with Image.open(file) as img:
                    img.verify()

            except Exception as e:
                rusak.append((file, str(e)))

print("\n===== HASIL PEMERIKSAAN DATASET GENUS =====")
print("Total gambar diperiksa:", total)
print("Jumlah gambar rusak:", len(rusak))

for file, error in rusak:
    print("\nFile:", file)
    print("Error:", error)