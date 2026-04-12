from __future__ import annotations

import json
from dataclasses import asdict, dataclass
from pathlib import Path
from statistics import median

from PIL import Image


PROJECT_ROOT = Path(__file__).resolve().parents[2]
NORMALIZED_ROOT = PROJECT_ROOT / "frontend" / "public" / "avatar-options" / "normalized"
DEFAULT_OUTPUT_PATH = PROJECT_ROOT / "frontend" / "tmp" / "avatar-review" / "audit.json"


@dataclass(frozen=True)
class AssetMetrics:
    asset_type: str
    asset_name: str
    bbox: tuple[int, int, int, int]
    width: int
    height: int
    center_x: float
    center_y: float
    width_delta: float
    height_delta: float
    center_x_delta: float
    center_y_delta: float


def load_alpha_bbox(path: Path) -> tuple[int, int, int, int]:
    image = Image.open(path).convert("RGBA")
    bbox = image.getbbox()
    if bbox is None:
        return (0, 0, 0, 0)
    return bbox


def collect_asset_metrics(asset_type: str) -> list[AssetMetrics]:
    paths = sorted((NORMALIZED_ROOT / asset_type).glob("*.png"))
    raw: list[tuple[str, tuple[int, int, int, int], int, int, float, float]] = []

    for path in paths:
        bbox = load_alpha_bbox(path)
        x0, y0, x1, y1 = bbox
        width = x1 - x0
        height = y1 - y0
        center_x = (x0 + x1) / 2
        center_y = (y0 + y1) / 2
        raw.append((path.stem, bbox, width, height, center_x, center_y))

    width_median = median(item[2] for item in raw)
    height_median = median(item[3] for item in raw)
    center_x_median = median(item[4] for item in raw)
    center_y_median = median(item[5] for item in raw)

    return [
        AssetMetrics(
            asset_type=asset_type,
            asset_name=name,
            bbox=bbox,
            width=width,
            height=height,
            center_x=center_x,
            center_y=center_y,
            width_delta=round(width - width_median, 2),
            height_delta=round(height - height_median, 2),
            center_x_delta=round(center_x - center_x_median, 2),
            center_y_delta=round(center_y - center_y_median, 2),
        )
        for name, bbox, width, height, center_x, center_y in raw
    ]


def collect_face_hair_overlap() -> list[dict[str, object]]:
    face_paths = sorted((NORMALIZED_ROOT / "face").glob("*.png"))
    hair_paths = sorted((NORMALIZED_ROOT / "hair").glob("*.png"))
    overlaps: list[dict[str, object]] = []

    for face_path in face_paths:
        face_alpha = Image.open(face_path).convert("RGBA").getchannel("A")
        face_pixels = sum(1 for value in face_alpha.getdata() if value)
        face_lookup = face_alpha.load()

        for hair_path in hair_paths:
            hair_alpha = Image.open(hair_path).convert("RGBA").getchannel("A")
            hair_lookup = hair_alpha.load()
            overlap_pixels = 0

            for y in range(face_alpha.height):
                for x in range(face_alpha.width):
                    if face_lookup[x, y] and hair_lookup[x, y]:
                        overlap_pixels += 1

            overlaps.append(
                {
                    "face": face_path.stem,
                    "hair": hair_path.stem,
                    "overlapPixels": overlap_pixels,
                    "facePixels": face_pixels,
                    "occlusionRatio": round(overlap_pixels / face_pixels, 4),
                }
            )

    overlaps.sort(key=lambda item: item["occlusionRatio"], reverse=True)
    return overlaps


def category_summary(metrics: list[AssetMetrics]) -> dict[str, object]:
    return {
        "count": len(metrics),
        "medianWidth": median(metric.width for metric in metrics),
        "medianHeight": median(metric.height for metric in metrics),
        "medianCenterX": median(metric.center_x for metric in metrics),
        "medianCenterY": median(metric.center_y for metric in metrics),
        "largestWidthOutliers": [
            asdict(metric)
            for metric in sorted(metrics, key=lambda item: abs(item.width_delta), reverse=True)[:3]
        ],
        "largestHeightOutliers": [
            asdict(metric)
            for metric in sorted(metrics, key=lambda item: abs(item.height_delta), reverse=True)[:3]
        ],
    }


def main() -> None:
    body_metrics = collect_asset_metrics("body")
    face_metrics = collect_asset_metrics("face")
    hair_metrics = collect_asset_metrics("hair")
    overlaps = collect_face_hair_overlap()

    report = {
        "normalizedRoot": str(NORMALIZED_ROOT.relative_to(PROJECT_ROOT)).replace("\\", "/"),
        "categories": {
            "body": category_summary(body_metrics),
            "face": category_summary(face_metrics),
            "hair": category_summary(hair_metrics),
        },
        "assetMetrics": {
            "body": [asdict(metric) for metric in body_metrics],
            "face": [asdict(metric) for metric in face_metrics],
            "hair": [asdict(metric) for metric in hair_metrics],
        },
        "topFaceHairOcclusionPairs": overlaps[:20],
        "occlusionPairsAboveHalfFaceCoverage": [
            item for item in overlaps if item["occlusionRatio"] >= 0.5
        ],
    }

    DEFAULT_OUTPUT_PATH.parent.mkdir(parents=True, exist_ok=True)
    DEFAULT_OUTPUT_PATH.write_text(json.dumps(report, indent=2) + "\n", encoding="utf-8")
    print(f"Wrote avatar audit data to {DEFAULT_OUTPUT_PATH.relative_to(PROJECT_ROOT)}.")


if __name__ == "__main__":
    main()
