from pathlib import Path
from collections import Counter
from PIL import Image, UnidentifiedImageError
from ultralytics import YOLO
import csv

# 1. LOKASI MODEL DAN DATASET
BASE_DIR = Path(__file__).resolve().parent.parent

MODEL_PATH = (
    BASE_DIR
    / "runs_validasi_ikan"
    / "validasi_gambar_v1"
    / "weights"
    / "best.pt"
)

EXTERNAL_DIR = (
    BASE_DIR
    / "datasets"
    / "pengujian_eksternalValidasi"
)

OUTPUT_DIR = (
    BASE_DIR
    / "runs_validasi_ikan"
    / "pengujian_eksternal_v1"
)

CSV_PATH = OUTPUT_DIR / "hasil_pengujian_eksternal.csv"

# 2. KONFIGURASI KELAS DAN FORMAT GAMBAR
CLASS_NAMES = [
    "bukan_ikan",
    "insang_ikan",
    "mata_ikan",
    "tubuh_ikan"
]

IMAGE_EXTENSIONS = {
    ".jpg",
    ".jpeg",
    ".png",
    ".webp",
    ".bmp",
    ".tif",
    ".tiff"
}

IMAGE_SIZE = 224
DEVICE = "cpu"

# 3. PEMERIKSAAN FILE
print("=" * 60)
print("PENGUJIAN EKSTERNAL MODEL VALIDASI SCOMAR")
print("=" * 60)

if not MODEL_PATH.exists():
    raise FileNotFoundError(
        f"Model tidak ditemukan: {MODEL_PATH}"
    )

if not EXTERNAL_DIR.exists():
    raise FileNotFoundError(
        f"Dataset eksternal tidak ditemukan: {EXTERNAL_DIR}"
    )

OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

# 4. MEMUAT MODEL TERBAIK
print("\nMemuat model best.pt...")

model = YOLO(str(MODEL_PATH))

model_names = model.names

if isinstance(model_names, dict):
    names_by_index = {
        int(index): str(name)
        for index, name in model_names.items()
    }
else:
    names_by_index = {
        index: str(name)
        for index, name in enumerate(model_names)
    }

print("Kelas model:")

for index, name in names_by_index.items():
    print(f"{index}: {name}")

if set(names_by_index.values()) != set(CLASS_NAMES):
    raise ValueError(
        "Nama kelas pada model tidak sesuai dengan "
        "kelas pengujian eksternal."
    )

# 5. MEMERIKSA JUMLAH GAMBAR EKSTERNAL
print("\nMemeriksa dataset eksternal...")

test_images = []

for class_name in CLASS_NAMES:

    class_dir = EXTERNAL_DIR / class_name

    if not class_dir.exists():
        raise FileNotFoundError(
            f"Folder kelas tidak ditemukan: {class_dir}"
        )

    images = sorted([
        file
        for file in class_dir.rglob("*")
        if file.is_file()
        and file.suffix.lower() in IMAGE_EXTENSIONS
    ])

    print(f"{class_name:15s}: {len(images)} foto")

    if not images:
        raise ValueError(
            f"Tidak ada gambar dalam {class_dir}"
        )

    for image_path in images:
        test_images.append((class_name, image_path))

print(f"\nTotal gambar: {len(test_images)}")

# 6. PROSES PREDIKSI
print("\nMemulai pengujian gambar...")

results_data = []
correct_counts = Counter()
total_counts = Counter()
prediction_counts = Counter()

for number, (true_class, image_path) in enumerate(
    test_images, start=1
):

    try:
        # Membaca gambar, termasuk TIFF,
        # lalu mengubahnya menjadi RGB.
        with Image.open(image_path) as original:
            image = original.convert("RGB")

        # Melakukan prediksi
        predictions = model.predict(
            source=image,
            imgsz=IMAGE_SIZE,
            device=DEVICE,
            verbose=False
        )

        result = predictions[0]

        predicted_index = int(result.probs.top1)
        confidence = float(
            result.probs.top1conf.item()
        )

        predicted_class = names_by_index[predicted_index]

        is_correct = predicted_class == true_class

        status = "BENAR" if is_correct else "SALAH"

        total_counts[true_class] += 1
        prediction_counts[predicted_class] += 1

        if is_correct:
            correct_counts[true_class] += 1

        results_data.append({
            "nama_file": image_path.name,
            "kelas_sebenarnya": true_class,
            "prediksi_model": predicted_class,
            "confidence": round(confidence, 6),
            "status": status
        })

        print(
            f"[{number:02d}/{len(test_images)}] "
            f"{image_path.name}\n"
            f"  Asli       : {true_class}\n"
            f"  Prediksi   : {predicted_class}\n"
            f"  Confidence : {confidence * 100:.2f}%\n"
            f"  Status     : {status}\n"
        )

    except (OSError, UnidentifiedImageError) as error:

        print(f"GAGAL MEMBACA: {image_path.name}")
        print(f"Alasan: {error}")

# 7. MENYIMPAN HASIL KE CSV
if not results_data:
    raise RuntimeError(
        "Tidak ada gambar yang berhasil diuji."
    )

with open(
    CSV_PATH,
    "w",
    newline="",
    encoding="utf-8-sig"
) as file:

    fieldnames = [
        "nama_file",
        "kelas_sebenarnya",
        "prediksi_model",
        "confidence",
        "status"
    ]

    writer = csv.DictWriter(
        file,
        fieldnames=fieldnames
    )

    writer.writeheader()
    writer.writerows(results_data)

# 8. REKAPITULASI HASIL
total_correct = sum(correct_counts.values())
total_tested = len(results_data)

accuracy = total_correct / total_tested * 100

print("\n" + "=" * 60)
print("REKAPITULASI PENGUJIAN EKSTERNAL")
print("=" * 60)

for class_name in CLASS_NAMES:

    total = total_counts[class_name]
    correct = correct_counts[class_name]

    accuracy_class = (
        correct / total * 100 if total else 0
    )

    print(
        f"{class_name:15s}: "
        f"{correct}/{total} benar "
        f"({accuracy_class:.2f}%)"
    )

print("-" * 60)

print(f"Total gambar diuji : {total_tested}")
print(f"Prediksi benar     : {total_correct}")
print(f"Prediksi salah     : {total_tested - total_correct}")
print(f"Akurasi eksternal  : {accuracy:.2f}%")

print("\nHasil CSV:")
print(CSV_PATH)

print("\nPengujian eksternal selesai.")
print("Model dan dataset asli tidak diubah.")

print("=" * 60)