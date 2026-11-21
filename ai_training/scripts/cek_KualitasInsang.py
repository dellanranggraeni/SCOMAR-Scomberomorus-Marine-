from pathlib import Path
import cv2
import csv

ROOT = Path(__file__).resolve().parents[1]
DATASET_DIR = ROOT / "datasets" / "insang" / "KesegaranInsang"

OUTPUT_DIR = ROOT / "runs" / "audit_kualitas_insang"
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

CSV_PATH = OUTPUT_DIR / "laporan_kualitas_insang.csv"

EXTENSIONS = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}
CLASSES = ["fresh", "non-fresh"]
SPLITS = ["train", "val", "test"]
BATAS_BLUR = 100

hasil = []

for split in SPLITS:
    for kelas in CLASSES:
        folder = DATASET_DIR / split / kelas

        for file in folder.iterdir():
            if not file.is_file():
                continue

            if file.suffix.lower() not in EXTENSIONS:
                continue

            gambar = cv2.imread(str(file))

            if gambar is None:
                nilai = None
                status = "Tidak dapat dibaca"
            else:
                abu_abu = cv2.cvtColor(
                    gambar, cv2.COLOR_BGR2GRAY
                )

                nilai = cv2.Laplacian(
                    abu_abu, cv2.CV_64F
                ).var()

                status = (
                    "Terindikasi blur"
                    if nilai < BATAS_BLUR
                    else "Lolos"
                )

            hasil.append({
                "split": split,
                "kelas": kelas,
                "nama_file": file.name,
                "nilai_ketajaman": (
                    round(nilai, 2) if nilai is not None else ""
                ),
                "status": status
            })

with open(CSV_PATH, "w", newline="", encoding="utf-8-sig") as f:
    kolom = [
        "split",
        "kelas",
        "nama_file",
        "nilai_ketajaman",
        "status"
    ]

    writer = csv.DictWriter(f, fieldnames=kolom)
    writer.writeheader()
    writer.writerows(hasil)

print("\n===== LAPORAN KUALITAS DATASET INSANG =====")

for split in SPLITS:
    for kelas in CLASSES:
        data = [
            item for item in hasil
            if item["split"] == split
            and item["kelas"] == kelas
        ]

        blur = sum(
            item["status"] == "Terindikasi blur"
            for item in data
        )

        lolos = sum(
            item["status"] == "Lolos"
            for item in data
        )

        print(f"\n{split.upper()} - {kelas.upper()}")
        print("Total:", len(data))
        print("Terindikasi blur:", blur)
        print("Lolos:", lolos)

print("\nLaporan CSV tersimpan di:")
print(CSV_PATH)