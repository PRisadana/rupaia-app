from __future__ import annotations

import uuid
from pathlib import Path

from flask import Flask, jsonify, request, send_file
from werkzeug.utils import secure_filename

from image_processor import apply_lut_to_image

BASE_DIR = Path(__file__).resolve().parent
UPLOAD_DIR = BASE_DIR / "uploads"
OUTPUT_DIR = BASE_DIR / "outputs"
LUT_DIR = BASE_DIR / "luts"

UPLOAD_DIR.mkdir(exist_ok=True)
OUTPUT_DIR.mkdir(exist_ok=True)
LUT_DIR.mkdir(exist_ok=True)

ALLOWED_EXTENSIONS = {"jpg", "jpeg", "png"}

app = Flask(__name__)
app.config["MAX_CONTENT_LENGTH"] = 16 * 1024 * 1024


def allowed_file(filename: str) -> bool:
    return "." in filename and filename.rsplit(".", 1)[1].lower() in ALLOWED_EXTENSIONS


@app.get("/health")
def health():
    return jsonify({"status": "ok"}), 200


@app.post("/apply-lut-preview")
def apply_lut_preview():
    if "image" not in request.files:
        return jsonify({"error": "image is required"}), 400

    image_file = request.files["image"]
    lut_filename = request.form.get("lut_filename", "").strip()

    if not image_file or image_file.filename == "":
        return jsonify({"error": "empty image file"}), 400

    if not allowed_file(image_file.filename):
        return jsonify({"error": "unsupported image type"}), 400

    if not lut_filename:
        return jsonify({"error": "lut_filename is required"}), 400

    lut_path = LUT_DIR / secure_filename(lut_filename)
    if not lut_path.exists():
        return jsonify({"error": f"LUT not found: {lut_filename}"}), 404

    input_name = f"{uuid.uuid4().hex}_{secure_filename(image_file.filename)}"
    input_path = UPLOAD_DIR / input_name
    image_file.save(input_path)

    output_name = f"preview_{uuid.uuid4().hex}.jpg"
    output_path = OUTPUT_DIR / output_name

    try:
        apply_lut_to_image(
            input_path=input_path,
            lut_path=lut_path,
            output_path=output_path,
            resize_width=1080,
        )

        return send_file(output_path, mimetype="image/jpeg")
    except Exception as e:
        return jsonify({"error": str(e)}), 500
    finally:
        if input_path.exists():
            input_path.unlink(missing_ok=True)


if __name__ == "__main__":
    app.run(debug=True, host="127.0.0.1", port=5001)