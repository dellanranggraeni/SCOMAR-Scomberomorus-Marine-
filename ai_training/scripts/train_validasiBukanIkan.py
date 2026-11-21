from pathlib import Path
from ultralytics import YOLO
import torch

# 1. KONFIGURASI LOKASI

# Folder utama ai_training
BASE_DIR = Path(__file__).resolve().parent.parent

# Lokasi dataset validasi
DATASET_DIR = (
    BASE_DIR / "datasets" / "dataset_validasi_ikan"
)

# Folder penyimpanan hasil training
PROJECT_DIR = BASE_DIR / "runs_validasi_ikan"

# Nama eksperimen
RUN_NAME = "validasi_gambar_v1"

# 2. KONFIGURASI KELAS
CLASS_NAMES = [
    "bukan_ikan",
    "insang_ikan",
    "mata_ikan",
    "tubuh_ikan"
]

SPLITS = ["train", "val", "test"]

IMAGE_EXTENSIONS = {
    ".jpg",
    ".jpeg",
    ".png",
    ".webp",
    ".bmp"
}

# 3. INFORMASI LINGKUNGAN
print("=" * 55)
print("TRAINING MODEL VALIDASI GAMBAR SCOMAR")
print("=" * 55)

print(f"PyTorch       : {torch.__version__}")
print(f"Ultralytics   : {YOLO.__module__}")
print(f"CUDA tersedia : {torch.cuda.is_available()}")
print(f"Dataset       : {DATASET_DIR}")

if not torch.cuda.is_available():
    print("Perangkat     : CPU")

# 4. MEMERIKSA LOKASI DATASET
if not DATASET_DIR.exists():
    raise FileNotFoundError(
        f"Dataset tidak ditemukan: {DATASET_DIR}"
    )

# 5. MEMERIKSA STRUKTUR DAN JUMLAH DATASET
dataset_counts = {}

print("\nMEMERIKSA DATASET...")

for split in SPLITS:

    split_dir = DATASET_DIR / split

    if not split_dir.exists():
        raise FileNotFoundError(
            f"Folder {split} tidak ditemukan: {split_dir}"
        )

    actual_classes = {
        folder.name
        for folder in split_dir.iterdir()
        if folder.is_dir()
    }

    expected_classes = set(CLASS_NAMES)

    if actual_classes != expected_classes:
        raise ValueError(
            f"Nama kelas pada folder {split} tidak sesuai.\n"
            f"Ditemukan: {sorted(actual_classes)}\n"
            f"Seharusnya: {sorted(expected_classes)}"
        )

    dataset_counts[split] = {}

    for class_name in CLASS_NAMES:

        class_dir = split_dir / class_name

        images = [
            file
            for file in class_dir.rglob("*")
            if file.is_file()
            and file.suffix.lower() in IMAGE_EXTENSIONS
        ]

        if len(images) == 0:
            raise ValueError(
                f"Tidak ada gambar pada folder: {class_dir}"
            )

        dataset_counts[split][class_name] = len(images)

# 6. MENAMPILKAN REKAP DATASET
print("\nREKAPITULASI DATASET")

for split in SPLITS:

    print(f"\n===== {split.upper()} =====")

    total = 0

    for class_name in CLASS_NAMES:

        count = dataset_counts[split][class_name]
        total += count

        print(f"{class_name:15s}: {count:4d} foto")

    print(f"Total {split}: {total} foto")

# 7. MEMUAT PRETRAINED MODEL
print("\nMemuat model YOLO26 Nano Classification...")

model = YOLO("yolo26n-cls.pt")

# 8. PROSES TRAINING
print("\nMemulai training model validasi SCOMAR...")

results = model.train(

    # Dataset klasifikasi
    data=str(DATASET_DIR),

    # Maksimum epoch
    epochs=50,

    # Ukuran gambar
    imgsz=224,

    # Batch untuk menyesuaikan RAM
    batch=8,

    # Menggunakan CPU
    device="cpu",

    # Workers
    workers=0,

    # Early stopping
    patience=12,

    # Tidak menyimpan dataset ke RAM
    cache=False,

    # Nonaktifkan mixed precision pada CPU
    amp=False,

    # Reproduksibilitas
    seed=42,
    deterministic=True,

    # Lokasi hasil training khusus
    project=str(PROJECT_DIR),
    name=RUN_NAME,

    # Tidak menimpa eksperimen sebelumnya
    exist_ok=False,

    # Menyimpan grafik evaluasi
    plots=True,

    # Menampilkan informasi training
    verbose=True
)

# 9. MENYIMPAN LOKASI HASIL TRAINING
save_dir = Path(model.trainer.save_dir)

best_model_path = (
    save_dir / "weights" / "best.pt"
)

last_model_path = (
    save_dir / "weights" / "last.pt"
)

print("\n" + "=" * 55)
print("TRAINING SELESAI")
print("=" * 55)

print(f"Folder hasil : {save_dir}")
print(f"Best model   : {best_model_path}")
print(f"Last model   : {last_model_path}")

if not best_model_path.exists():
    raise FileNotFoundError(
        "best.pt tidak ditemukan. Periksa hasil training."
    )

# 10. EVALUASI MENGGUNAKAN DATA TEST
print("\nMemuat model terbaik untuk pengujian...")

best_model = YOLO(str(best_model_path))

print("\nMemulai evaluasi menggunakan dataset TEST...")

test_metrics = best_model.val(

    # Dataset validasi
    data=str(DATASET_DIR),

    # Menggunakan split test
    split="test",

    # Ukuran gambar
    imgsz=224,

    # Batch
    batch=8,

    # CPU
    device="cpu",

    # Workers
    workers=0,

    # Simpan grafik evaluasi
    plots=True,

    # Folder evaluasi terpisah
    project=str(PROJECT_DIR),
    name=f"{RUN_NAME}_test",

    exist_ok=False

)

# 11. MENAMPILKAN HASIL EVALUASI
print("\n" + "=" * 55)
print("HASIL EVALUASI MODEL")
print("=" * 55)

print(
    f"Top-1 Accuracy : "
    f"{test_metrics.top1:.4f}"
)

print(
    f"Top-5 Accuracy : "
    f"{test_metrics.top5:.4f}"
)

print("\nLokasi model terbaik:")
print(best_model_path)

print("\nModel validasi gambar SCOMAR selesai dilatih.")
print("Dataset asli dan model sebelumnya tetap aman.")

print("=" * 55)