from __future__ import annotations

from pathlib import Path
from typing import Optional

from PIL import Image

from luts_util import parse_cube_file


def apply_lut_to_image(
    input_path: str | Path,
    lut_path: str | Path,
    output_path: str | Path,
    resize_width: Optional[int] = None,
) -> str:
    input_path = Path(input_path)
    output_path = Path(output_path)

    if not input_path.exists():
        raise FileNotFoundError(f"Input image not found: {input_path}")

    # open image and convert to RGB
    image = Image.open(input_path).convert("RGB")

    if resize_width and resize_width > 0:
        width, height = image.size
        new_height = int((resize_width / width) * height)
        image = image.resize((resize_width, new_height))

    lut = parse_cube_file(lut_path)
    output = image.filter(lut)

    output_path.parent.mkdir(parents=True, exist_ok=True)
    output.save(output_path, format="JPEG", quality=100)

    return str(output_path)