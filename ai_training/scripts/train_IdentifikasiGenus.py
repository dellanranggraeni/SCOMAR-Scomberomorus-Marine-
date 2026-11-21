import sys
from pathlib import Path

import torch
from ultralytics import YOLO

# KONFIGURASI
ROOT = Path(__file__).resolve().parents[1]

DATASET_DIR = (
    ROOT / "datasets" / "jenis_ikan" / "IdentifikasiGenus"
)

RUNS_DIR = ROOT / "runs"

EPOCHS = int(sys.argv[1]) if len(sys.argv) > 1 else 50
IMAGE_SIZE = 224
BATCH_SIZE = 16

DEVICE = 0 if torch.cuda.is_available() else "cpu"

EXPECTED_CLASSES = {
    "Scomberomorus",
    "Non_Scomberomorus"
}

EXTENSIONS = {
    ".jpg", ".jpeg", ".png", ".webp", ".bmp"
}

print("ROOT:", ROOT)
print("DATASET_DIR:", DATASET_DIR)
print("DATASET EXISTS:", DATASET_DIR.exists())
print("RUNS_DIR:", RUNS_DIR)

# PEMERIKSAAN DATASET
def check_dataset():

    if not DATASET_DIR.exists():
        raise FileNotFoundError(
            f"Dataset tidak ditemukan: {DATASET_DIR}"
        )

    totals = {}

    for split in ["train", "val", "test"]:

        split_dir = DATASET_DIR / split

        if not split_dir.is_dir():
            raise FileNotFoundError(
                f"Folder {split} tidak ditemukan."
            )

        class_dirs = [
            folder for folder in split_dir.iterdir()
            if folder.is_dir()
            and not folder.name.startswith((".", "__"))
        ]

        names = {folder.name for folder in class_dirs}

        if names != EXPECTED_CLASSES:
            raise ValueError(
                f"Kelas pada {split} harus "
                f"{sorted(EXPECTED_CLASSES)}, "
                f"tetapi ditemukan {sorted(names)}"
            )

        for class_dir in class_dirs:

            count = sum(
                1 for file in class_dir.iterdir()
                if file.is_file()
                and file.suffix.lower() in EXTENSIONS
            )

            if count == 0:
                raise ValueError(
                    f"Tidak ada gambar di {class_dir}"
                )

            totals[(split, class_dir.name)] = count

            print(
                f"{split}/{class_dir.name}: "
                f"{count} gambar"
            )

    print("\nDataset berhasil diperiksa.")

    return totals

# TRAINING MODEL
def train_model():

    check_dataset()

    print("\nMemulai training identifikasi genus ikan...")
    print("Device     :", DEVICE)
    print("Epoch      :", EPOCHS)
    print("Image size :", IMAGE_SIZE)
    print("Batch      :", BATCH_SIZE)

    # Memuat YOLO11 Classification pretrained
    model = YOLO("yolo11n-cls.pt")

    model.train(

        data=str(DATASET_DIR),

        epochs=EPOCHS,
        imgsz=IMAGE_SIZE,
        batch=BATCH_SIZE,

        # AUGMENTASI
        scale=0.2,
        fliplr=0.5,
        flipud=0.0,
        auto_augment="randaugment",
        erasing=0.1,

        workers=0,
        device=DEVICE,

        patience=10,

        project=str(RUNS_DIR),
        name="identifikasi_genus_v1",
        exist_ok=False,

        plots=True,
        save=True,

        amp=(DEVICE != "cpu"),

        seed=42,
        deterministic=True,
        verbose=True,
    )


    # LOKASI MODEL TERBAIK
    save_dir = Path(model.trainer.save_dir)

    best_model = (
        save_dir / "weights" / "best.pt"
    )

    print("\nTRAINING SELESAI")

    print("Model terbaik  :", best_model)
    print("Hasil training :", save_dir)

    if not best_model.exists():
        raise FileNotFoundError(
            f"Model terbaik tidak ditemukan: {best_model}"
        )

    # EVALUASI DATA TEST
    print("\nMengevaluasi pada data test...")

    best = YOLO(str(best_model))

    metrics = best.val(
        data=str(DATASET_DIR),
        split="test",
        imgsz=IMAGE_SIZE,
        batch=BATCH_SIZE,
        device=DEVICE,
        workers=0,
    )

    print("\n===== HASIL EVALUASI MODEL GENUS =====")

    print(
        f"Akurasi Top-1 Test: "
        f"{metrics.top1:.3f}"
    )

# EKSEKUSI
if __name__ == "__main__":

    try:
        train_model()

    except Exception as error:

        print("\nTERJADI KESALAHAN:")
        print(error)

        raise