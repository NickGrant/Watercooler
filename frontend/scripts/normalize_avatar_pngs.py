from __future__ import annotations

import json
from dataclasses import asdict, dataclass
from pathlib import Path
from typing import Dict, Iterable

from PIL import Image


PROJECT_ROOT = Path(__file__).resolve().parents[2]
AVATAR_ROOT = PROJECT_ROOT / "frontend" / "public" / "avatar-options"
RAW_TYPES = ("body", "face", "hair")
CANVAS_SIZE = (512, 640)
PLACEMENT_BOXES = {
    "body": (56, 70, 456, 640),
    "face": (162, 138, 350, 310),
    "hair": (112, 16, 400, 248),
}


@dataclass(frozen=True)
class NormalizedAsset:
    asset_type: str
    source: str
    output: str
    width: int
    height: int


def fit_to_box(image: Image.Image, box: tuple[int, int, int, int]) -> Image.Image:
    x0, y0, x1, y1 = box
    box_width = x1 - x0
    box_height = y1 - y0
    scale = min(box_width / image.width, box_height / image.height)
    resized_width = max(1, round(image.width * scale))
    resized_height = max(1, round(image.height * scale))
    resized = image.resize((resized_width, resized_height), Image.LANCZOS)

    layer = Image.new("RGBA", CANVAS_SIZE, (255, 255, 255, 0))
    paste_x = x0 + (box_width - resized_width) // 2
    paste_y = y0
    layer.alpha_composite(resized, (paste_x, paste_y))
    return layer


def iter_source_images(asset_type: str) -> Iterable[Path]:
    return sorted((AVATAR_ROOT / asset_type).glob("*.png"))


def normalize_asset(asset_type: str, source_path: Path, output_path: Path) -> NormalizedAsset:
    image = Image.open(source_path).convert("RGBA")
    normalized = fit_to_box(image, PLACEMENT_BOXES[asset_type])
    output_path.parent.mkdir(parents=True, exist_ok=True)
    normalized.save(output_path)

    return NormalizedAsset(
        asset_type=asset_type,
        source=str(source_path.relative_to(PROJECT_ROOT)).replace("\\", "/"),
        output=str(output_path.relative_to(PROJECT_ROOT)).replace("\\", "/"),
        width=CANVAS_SIZE[0],
        height=CANVAS_SIZE[1],
    )


def write_metadata(assets: list[NormalizedAsset]) -> None:
    metadata_path = AVATAR_ROOT / "normalized" / "metadata.json"
    metadata_path.parent.mkdir(parents=True, exist_ok=True)

    grouped_assets: Dict[str, list[dict[str, object]]] = {asset_type: [] for asset_type in RAW_TYPES}
    for asset in assets:
        grouped_assets[asset.asset_type].append(asdict(asset))

    metadata = {
        "canvas": {"width": CANVAS_SIZE[0], "height": CANVAS_SIZE[1]},
        "placementBoxes": {
            asset_type: {
                "x0": box[0],
                "y0": box[1],
                "x1": box[2],
                "y1": box[3],
            }
            for asset_type, box in PLACEMENT_BOXES.items()
        },
        "assets": grouped_assets,
    }

    metadata_path.write_text(json.dumps(metadata, indent=2) + "\n", encoding="utf-8")


def main() -> None:
    normalized_assets: list[NormalizedAsset] = []

    for asset_type in RAW_TYPES:
        for source_path in iter_source_images(asset_type):
            output_path = AVATAR_ROOT / "normalized" / asset_type / source_path.name
            normalized_assets.append(normalize_asset(asset_type, source_path, output_path))

    write_metadata(normalized_assets)
    print(f"Normalized {len(normalized_assets)} avatar PNG assets.")


if __name__ == "__main__":
    main()
