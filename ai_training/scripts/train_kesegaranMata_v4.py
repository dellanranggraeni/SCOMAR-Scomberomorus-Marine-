import sys
from pathlib import Path
import torch
from ultralytics import YOLO

# KONFIGURASI
ROOT = Path(__file__).resolve().parents[1]   # folder proyek (skrip ada di scripts/)
DATASET_DIR = ROOT / "datasets" / "mata" / "KesegaranMata_Revisi"
RUNS_DIR = ROOT / "runs"

print("ROOT:", ROOT)
print("DATASET_DIR:", DATASET_DIR)
print("DATASET EXISTS:", DATASET_DIR.exists())
print("RUNS_DIR:", RUNS_DIR)

# Jumlah epoch bisa diubah dari terminal: python scripts/train.py 2
EPOCHS = int(sys.argv[1]) if len(sys.argv) > 1 else 50
IMAGE_SIZE = 224
BATCH_SIZE = 16
DEVICE = 0 if torch.cuda.is_available() else "cpu"

EXPECTED_CLASSES = {"Fresh", "Non_Fresh"}
EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp", ".bmp"}

# PEMERIKSAAN DATASET
def check_dataset():
    if not DATASET_DIR.exists():
        raise FileNotFoundError(f"Dataset tidak ditemukan: {DATASET_DIR}")

    totals = {}
    for split in ["train", "val", "test"]:
        split_dir = DATASET_DIR / split
        if not split_dir.is_dir():
            raise FileNotFoundError(f"Folder '{split}' tidak ditemukan di {DATASET_DIR}")

        # Abaikan folder tersembunyi (.ipynb_checkpoints, __MACOSX, dll.)
        class_dirs = [
            f for f in split_dir.iterdir()
            if f.is_dir() and not f.name.startswith((".", "__"))
        ]
        names = {f.name for f in class_dirs}
        if names != EXPECTED_CLASSES:
            raise ValueError(
                f"Kelas pada '{split}' harus persis {sorted(EXPECTED_CLASSES)}, "
                f"tetapi ditemukan {sorted(names)}"
            )

        for class_dir in class_dirs:
            count = sum(
                1 for f in class_dir.iterdir()
                if f.is_file() and f.suffix.lower() in EXTENSIONS
            )
            if count == 0:
                raise ValueError(f"Tidak ada gambar di {class_dir}")
            totals[(split, class_dir.name)] = count
            print(f"{split}/{class_dir.name}: {count} gambar")

    # Peringatan jika jumlah kelas timpang pada data train
    fresh, non_fresh = totals[("train", "Fresh")], totals[("train", "Non_Fresh")]
    if max(fresh, non_fresh) > 2 * min(fresh, non_fresh):
        print("\nPERINGATAN: jumlah gambar fresh dan non_fresh pada train timpang (lebih dari 2x).")
    print("\nDataset berhasil diperiksa.")

# TRAINING MODEL
def train_model():
    check_dataset()
    print("\nMemulai training model kesegaran mata...")
    print(f"Device     : {DEVICE}")
    print(f"Epoch      : {EPOCHS}")
    print(f"Image size : {IMAGE_SIZE}")
    print(f"Batch      : {BATCH_SIZE}")

    # Model klasifikasi YOLO11 (diunduh otomatis saat pertama kali, butuh internet)
    model = YOLO("yolo11n-cls.pt")

    model.train(
        data=str(DATASET_DIR),
        epochs=EPOCHS,
        imgsz=IMAGE_SIZE,
        batch=BATCH_SIZE,
        scale=0.2,              # augmentasi untuk klasifikasi
        fliplr=0.5,
        flipud=0.0,
        auto_augment=None,
        hsv_h=0.015,
        hsv_s=0.15,
        hsv_v=0.15,
        erasing=0.1,
        workers=0,              # aman untuk Windows
        device=DEVICE,
        patience=10,
        project=str(RUNS_DIR),
        name="kesegaran_mata_v4",
        exist_ok=False,         # tiap training masuk folder baru
        plots=True,
        save=True,
        amp=(DEVICE != "cpu"),
        seed=42,
        deterministic=True,
        verbose=True,
    )

    save_dir = Path(model.trainer.save_dir)
    best_model = save_dir / "weights" / "best.pt"

    print("TRAINING SELESAI")
    print(f"Model terbaik  : {best_model}")
    print(f"Hasil training : {save_dir}")

    # Evaluasi akhir pada data test (data yang tidak dipakai saat training)
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
    print(f"Akurasi top-1 pada data test: {metrics.top1:.3f}")


if __name__ == "__main__":
    try:
        train_model()
    except Exception as error:
        print("\nTERJADI KESALAHAN:")
        print(error)
        raise