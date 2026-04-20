from __future__ import annotations

from pathlib import Path
from typing import List
from PIL import ImageFilter


def parse_cube_file(cube_path: str | Path) -> ImageFilter.Color3DLUT:
    cube_path = Path(cube_path)

    if not cube_path.exists():
        raise FileNotFoundError(f"LUT file not found: {cube_path}")

    size = None
    table: List[float] = []

    with cube_path.open("r", encoding="utf-8") as f:
        for raw_line in f:
            line = raw_line.strip()

            if not line or line.startswith("#"):
                continue

            upper = line.upper()

            if upper.startswith("TITLE"):
                continue

            if upper.startswith("DOMAIN_MIN") or upper.startswith("DOMAIN_MAX"):
                continue

            if upper.startswith("LUT_3D_SIZE"):
                parts = line.split()
                if len(parts) != 2:
                    raise ValueError(f"Invalid LUT_3D_SIZE line: {line}")
                size = int(parts[1])
                continue

            parts = line.split()
            if len(parts) == 3:
                r, g, b = map(float, parts)
                table.extend([r, g, b])

    if size is None:
        raise ValueError("LUT_3D_SIZE not found in .cube file")

    expected = size * size * size * 3
    if len(table) != expected:
        raise ValueError(f"Invalid LUT data. Expected {expected}, got {len(table)}")

    return ImageFilter.Color3DLUT(size=size, table=table, channels=3, target_mode="RGB")