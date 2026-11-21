from pathlib import Path
from io import BytesIO
from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from PIL import Image, ImageOps
from ultralytics import YOLO

# 1. KONFIGURASI DIREKTORI
ROOT = Path(__file__).resolve().parents[1]

MODEL_PATHS = {
    "validator": ROOT / "ai_service" / "models" / "validator" / "best.pt",
    "genus": ROOT / "ai_service" / "models" / "genus" / "best.pt",
    "mata": ROOT / "ai_service" / "models" / "mata" / "best.pt",
    "insang": ROOT / "ai_service" / "models" / "insang" / "best.pt",
}

# Kelas yang wajib dikenali oleh model validator
EXPECTED_CLASSES = {
    "bukan_ikan",
    "insang_ikan",
    "mata_ikan",
    "tubuh_ikan"
}

# Ambang confidence awal untuk pemeriksaan gambar
# Nilai ini dapat dievaluasi kembali setelah pengujian lanjutan.
VALIDATION_MIN_CONFIDENCE = 0.70

IMAGE_SIZE = 224
DEVICE = "cpu"

# 2. INISIALISASI FASTAPI
app = FastAPI(
    title="SCOMAR AI Service",
    description=(
        "API validasi gambar, identifikasi genus, "
        "dan kesegaran ikan SCOMAR"
    ),
    version="2.0.0"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=False,
    allow_methods=["*"],
    allow_headers=["*"],
)

# 3. MEMUAT EMPAT MODEL AI
models = {}

for nama, path in MODEL_PATHS.items():

    if not path.exists():
        raise FileNotFoundError(
            f"Model {nama} tidak ditemukan: {path}"
        )

    print(f"Memuat model {nama}: {path}")

    models[nama] = YOLO(str(path))

    print(f"Model {nama} berhasil dimuat.")

# Pastikan kelas model validator sesuai
validator_names = models["validator"].names

if isinstance(validator_names, dict):
    validator_classes = {
        str(name) for name in validator_names.values()
    }
else:
    validator_classes = {
        str(name) for name in validator_names
    }

if validator_classes != EXPECTED_CLASSES:
    raise ValueError(
        "Kelas model validator tidak sesuai.\n"
        f"Ditemukan: {sorted(validator_classes)}\n"
        f"Diharapkan: {sorted(EXPECTED_CLASSES)}"
    )

print("\nSeluruh model AI SCOMAR berhasil dimuat.")

# 4. MEMBACA GAMBAR
def baca_gambar(image_file: UploadFile):

    if (
        image_file.content_type
        and not image_file.content_type.startswith("image/")
    ):
        raise HTTPException(
            status_code=400,
            detail="File yang diunggah harus berupa gambar."
        )

    contents = image_file.file.read()

    if not contents:
        raise HTTPException(
            status_code=400,
            detail="File gambar kosong."
        )

    try:
        with Image.open(BytesIO(contents)) as img:

            image = (
                ImageOps.exif_transpose(img)
                .convert("RGB")
            )

            image.load()

            return image.copy()

    except Exception:
        raise HTTPException(
            status_code=400,
            detail="Gambar tidak valid atau tidak dapat dibaca."
        )

# 5. FUNGSI KLASIFIKASI MODEL
def klasifikasi_gambar(model_name: str, image: Image.Image):

    model = models[model_name]

    result = model.predict(
        source=image,
        imgsz=IMAGE_SIZE,
        device=DEVICE,
        verbose=False
    )[0]

    if result.probs is None:
        raise ValueError(
            "Hasil klasifikasi tidak tersedia."
        )

    index = int(result.probs.top1)

    if isinstance(model.names, dict):
        label = model.names[index]
    else:
        label = model.names[index]

    confidence = float(
        result.probs.top1conf.item()
    )

    return {
        "prediksi": str(label),
        "confidence": round(confidence, 4),
        "confidence_persen": round(confidence * 100, 2)
    }

# 6. VALIDASI KESESUAIAN GAMBAR
def validasi_gambar(
    image: Image.Image,
    nama_file: str,
    expected_class: str
):

    hasil = klasifikasi_gambar(
        "validator",
        image
    )

    predicted_class = hasil["prediksi"]
    confidence = hasil["confidence"]

    label_sesuai = (
        predicted_class == expected_class
    )

    confidence_sesuai = (
        confidence >= VALIDATION_MIN_CONFIDENCE
    )

    status_sesuai = (
        label_sesuai and confidence_sesuai
    )

    if not label_sesuai:

        alasan = (
            f"Gambar terdeteksi sebagai "
            f"{predicted_class}, bukan {expected_class}."
        )

    elif not confidence_sesuai:

        alasan = (
            "Confidence gambar belum mencapai "
            f"batas minimum "
            f"{VALIDATION_MIN_CONFIDENCE * 100:.0f}%."
        )

    else:
        alasan = "Gambar sesuai dengan bagian yang diminta."

    return {
        "nama_file": nama_file,
        "kelas_yang_diharapkan": expected_class,
        "prediksi": predicted_class,
        "confidence": hasil["confidence"],
        "confidence_persen": hasil["confidence_persen"],
        "status": (
            "sesuai" if status_sesuai
            else "tidak_sesuai"
        ),
        "alasan": alasan
    }

# 7. MENOLAK GAMBAR YANG TIDAK SESUAI
def periksa_hasil_validasi(hasil_validasi: dict):

    gambar_tidak_sesuai = [
        hasil
        for hasil in hasil_validasi.values()
        if hasil["status"] != "sesuai"
    ]

    if gambar_tidak_sesuai:

        raise HTTPException(
            status_code=422,
            detail={
                "kode": "GAMBAR_TIDAK_SESUAI",
                "status": "tidak_sesuai",
                "pesan": (
                    "Satu atau lebih gambar tidak sesuai "
                    "dengan bagian ikan yang diminta. "
                    "Silakan periksa dan unggah ulang gambar."
                ),
                "gambar_tidak_sesuai": gambar_tidak_sesuai,
                "validasi": hasil_validasi
            }
        )

# 8. PREDIKSI MODEL UTAMA
def prediksi_model_utama(
    model_name: str,
    image: Image.Image,
    filename: str
):

    try:

        hasil = klasifikasi_gambar(
            model_name,
            image
        )

        return {
            "nama_file": filename,
            **hasil
        }

    except Exception as error:

        raise HTTPException(
            status_code=500,
            detail=f"Prediksi {model_name} gagal: {str(error)}"
        )

# 9. ENDPOINT UTAMA
@app.get("/")
def home():

    return {
        "aplikasi": "SCOMAR AI Service",
        "status": "Aktif",
        "model": list(models.keys()),
        "dokumentasi": "/docs"
    }

# 10. HEALTH CHECK
@app.get("/health")
def health_check():

    return {
        "status": "healthy",
        "models": {
            nama: {
                "loaded": True,
                "classes": model.names
            }
            for nama, model in models.items()
        },
        "validation": {
            "minimum_confidence": VALIDATION_MIN_CONFIDENCE,
            "minimum_confidence_persen": (
                VALIDATION_MIN_CONFIDENCE * 100
            )
        }
    }

# 11. PREDIKSI GENUS IKAN
@app.post("/predict/genus")
def predict_genus(image: UploadFile = File(...)):

    gambar = baca_gambar(image)

    validasi = validasi_gambar(
        gambar,
        image.filename,
        "tubuh_ikan"
    )

    periksa_hasil_validasi({
        "tubuh_ikan": validasi
    })

    hasil = prediksi_model_utama(
        "genus",
        gambar,
        image.filename
    )

    return {
        "fitur": "Identifikasi Genus Ikan",
        "validasi": validasi,
        **hasil
    }

# 12. PREDIKSI KESEGARAN MATA
@app.post("/predict/mata")
def predict_mata(image: UploadFile = File(...)):

    gambar = baca_gambar(image)

    validasi = validasi_gambar(
        gambar,
        image.filename,
        "mata_ikan"
    )

    periksa_hasil_validasi({
        "mata_ikan": validasi
    })

    hasil = prediksi_model_utama(
        "mata",
        gambar,
        image.filename
    )

    return {
        "fitur": "Kesegaran Mata Ikan",
        "validasi": validasi,
        **hasil
    }

# 13. PREDIKSI KESEGARAN INSANG
@app.post("/predict/insang")
def predict_insang(image: UploadFile = File(...)):

    gambar = baca_gambar(image)

    validasi = validasi_gambar(
        gambar,
        image.filename,
        "insang_ikan"
    )

    periksa_hasil_validasi({
        "insang_ikan": validasi
    })

    hasil = prediksi_model_utama(
        "insang",
        gambar,
        image.filename
    )

    return {
        "fitur": "Kesegaran Insang Ikan",
        "validasi": validasi,
        **hasil
    }

# 14. PEMINDAIAN GABUNGAN SCOMAR
@app.post("/predict/pemindaian")
def predict_all(
    genus_image: UploadFile = File(...),
    mata_image: UploadFile = File(...),
    insang_image: UploadFile = File(...)
):

    # A. MEMBACA SEMUA GAMBAR
    gambar_genus = baca_gambar(genus_image)
    gambar_mata = baca_gambar(mata_image)
    gambar_insang = baca_gambar(insang_image)

    # B. VALIDASI GAMBAR SEBELUM MODEL UTAMA
    hasil_validasi = {

        "tubuh_ikan": validasi_gambar(
            gambar_genus,
            genus_image.filename,
            "tubuh_ikan"
        ),

        "mata_ikan": validasi_gambar(
            gambar_mata,
            mata_image.filename,
            "mata_ikan"
        ),

        "insang_ikan": validasi_gambar(
            gambar_insang,
            insang_image.filename,
            "insang_ikan"
        )

    }

    # Jika ada gambar tidak sesuai,
    # hentikan pemindaian sebelum menjalankan
    # ketiga model utama.

    periksa_hasil_validasi(hasil_validasi)

    # C. SEMUA GAMBAR LOLOS VALIDASI
    hasil_genus = prediksi_model_utama(
        "genus",
        gambar_genus,
        genus_image.filename
    )

    hasil_mata = prediksi_model_utama(
        "mata",
        gambar_mata,
        mata_image.filename
    )

    hasil_insang = prediksi_model_utama(
        "insang",
        gambar_insang,
        insang_image.filename
    )

    # D. MENGEMBALIKAN HASIL PEMINDAIAN
    return {
        "fitur": "Pemindaian Ikan SCOMAR",

        "status_validasi": "sesuai",

        "validasi": {
            "status": "seluruh_gambar_sesuai",
            "minimum_confidence_persen": (
                VALIDATION_MIN_CONFIDENCE * 100
            ),
            "detail": hasil_validasi
        },

        "hasil": {
            "identifikasi_genus": hasil_genus,
            "kesegaran_mata": hasil_mata,
            "kesegaran_insang": hasil_insang
        },

        "catatan": (
            "Hasil merupakan indikasi eksperimental berdasarkan "
            "analisis citra AI, bukan keputusan final."
        )
    }