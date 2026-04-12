from __future__ import annotations

import argparse
import json
import shutil
from dataclasses import asdict, dataclass
from datetime import datetime, timezone
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


PROJECT_ROOT = Path(__file__).resolve().parents[2]
NORMALIZED_ROOT = PROJECT_ROOT / "frontend" / "public" / "avatar-options" / "normalized"
DEFAULT_OUTPUT_ROOT = PROJECT_ROOT / "frontend" / "tmp" / "avatar-review"
LAYER_TYPES = ("body", "face", "hair")
SOURCE_CANVAS = (512, 640)
REVIEW_CANVAS = (256, 320)


@dataclass(frozen=True)
class CompositeRecord:
    body: str
    face: str
    hair: str
    output: str


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Generate full Watercooler avatar composite review renders."
    )
    parser.add_argument(
        "--output-root",
        type=Path,
        default=DEFAULT_OUTPUT_ROOT,
        help="Directory for generated review artifacts.",
    )
    parser.add_argument(
        "--clean",
        action="store_true",
        help="Delete the output directory before regenerating artifacts.",
    )
    return parser.parse_args()


def load_asset_paths(asset_type: str) -> list[Path]:
    return sorted((NORMALIZED_ROOT / asset_type).glob("*.png"))


def load_asset_images(asset_type: str) -> dict[str, Image.Image]:
    return {
        path.stem: Image.open(path).convert("RGBA")
        for path in load_asset_paths(asset_type)
    }


def compose_avatar(body_image: Image.Image, face_image: Image.Image, hair_image: Image.Image) -> Image.Image:
    composite = body_image.copy()
    composite.alpha_composite(face_image)
    composite.alpha_composite(hair_image)
    return composite


def render_review_size(image: Image.Image) -> Image.Image:
    return image.resize(REVIEW_CANVAS, Image.LANCZOS)


def generate_composites(output_root: Path) -> list[CompositeRecord]:
    composites_root = output_root / "composites"
    records: list[CompositeRecord] = []
    body_images = load_asset_images("body")
    face_images = load_asset_images("face")
    hair_images = load_asset_images("hair")

    for body_name, body_image in body_images.items():
        body_output_dir = composites_root / body_name
        body_output_dir.mkdir(parents=True, exist_ok=True)

        for face_name, face_image in face_images.items():
            for hair_name, hair_image in hair_images.items():
                file_name = f"{body_name}__{face_name}__{hair_name}.png"
                output_path = body_output_dir / file_name

                composite = render_review_size(
                    compose_avatar(body_image, face_image, hair_image)
                )
                composite.save(output_path, format="PNG", optimize=True)

                records.append(
                    CompositeRecord(
                        body=body_name,
                        face=face_name,
                        hair=hair_name,
                        output=str(output_path.relative_to(PROJECT_ROOT)).replace("\\", "/"),
                    )
                )

    return records


def draw_text_centered(draw: ImageDraw.ImageDraw, bounds: tuple[int, int, int, int], text: str, font: ImageFont.ImageFont) -> None:
    left, top, right, bottom = bounds
    text_box = draw.textbbox((0, 0), text, font=font)
    text_width = text_box[2] - text_box[0]
    text_height = text_box[3] - text_box[1]
    x = left + (right - left - text_width) / 2
    y = top + (bottom - top - text_height) / 2
    draw.text((x, y), text, fill=(34, 43, 58), font=font)


def generate_contact_sheets(output_root: Path, records: list[CompositeRecord]) -> None:
    contact_root = output_root / "contact-sheets"
    contact_root.mkdir(parents=True, exist_ok=True)
    font = ImageFont.load_default()
    faces = sorted({record.face for record in records})
    hairs = sorted({record.hair for record in records})
    tile_width, tile_height = REVIEW_CANVAS
    scale = 0.375
    tile_width = round(tile_width * scale)
    tile_height = round(tile_height * scale)
    label_band = 24
    margin = 16
    gutter = 8
    row_label_width = 72

    by_body: dict[str, dict[tuple[str, str], Path]] = {}
    for record in records:
        by_body.setdefault(record.body, {})[(record.face, record.hair)] = PROJECT_ROOT / record.output

    for body, combo_paths in by_body.items():
        width = (
            margin * 2
            + row_label_width
            + (tile_width + gutter) * len(hairs)
            - gutter
        )
        height = (
            margin * 2
            + label_band
            + (tile_height + gutter) * len(faces)
            - gutter
        )
        sheet = Image.new("RGBA", (width, height), (247, 249, 252, 255))
        draw = ImageDraw.Draw(sheet)

        draw_text_centered(
            draw,
            (margin + row_label_width, margin, width - margin, margin + label_band),
            body,
            font,
        )

        for column, hair in enumerate(hairs):
            x = margin + row_label_width + column * (tile_width + gutter)
            draw_text_centered(draw, (x, margin, x + tile_width, margin + label_band), hair, font)

        for row, face in enumerate(faces):
            y = margin + label_band + row * (tile_height + gutter)
            draw_text_centered(draw, (margin, y, margin + row_label_width - gutter, y + tile_height), face, font)

            for column, hair in enumerate(hairs):
                x = margin + row_label_width + column * (tile_width + gutter)
                combo_path = combo_paths[(face, hair)]
                tile = Image.open(combo_path).convert("RGBA").resize((tile_width, tile_height), Image.LANCZOS)
                sheet.alpha_composite(tile, (x, y))
                draw.rounded_rectangle(
                    (x, y, x + tile_width, y + tile_height),
                    radius=8,
                    outline=(200, 208, 219),
                    width=1,
                )

        sheet.save(contact_root / f"{body}.png")


def write_manifest(output_root: Path, records: list[CompositeRecord]) -> None:
    manifest = {
        "generatedAtUtc": datetime.now(timezone.utc).isoformat(),
        "normalizedRoot": str(NORMALIZED_ROOT.relative_to(PROJECT_ROOT)).replace("\\", "/"),
        "outputRoot": str(output_root.relative_to(PROJECT_ROOT)).replace("\\", "/"),
        "sourceCanvas": {"width": SOURCE_CANVAS[0], "height": SOURCE_CANVAS[1]},
        "reviewCanvas": {"width": REVIEW_CANVAS[0], "height": REVIEW_CANVAS[1]},
        "counts": {
            "body": len(load_asset_paths("body")),
            "face": len(load_asset_paths("face")),
            "hair": len(load_asset_paths("hair")),
            "composites": len(records),
        },
        "records": [asdict(record) for record in records],
    }
    (output_root / "manifest.json").write_text(json.dumps(manifest, indent=2) + "\n", encoding="utf-8")


def write_summary(output_root: Path, records: list[CompositeRecord]) -> None:
    bodies = sorted({record.body for record in records})
    faces = sorted({record.face for record in records})
    hairs = sorted({record.hair for record in records})
    lines = [
        "# Avatar Review Output",
        "",
        f"- Generated at: `{datetime.now(timezone.utc).isoformat()}`",
        f"- Bodies: `{len(bodies)}`",
        f"- Faces: `{len(faces)}`",
        f"- Hairs: `{len(hairs)}`",
        f"- Total composites: `{len(records)}`",
        "",
        "## Locations",
        "",
        "- `composites/`: one review-sized image for every body/face/hair combination",
        "- `contact-sheets/`: one matrix per body with faces as rows and hairs as columns",
        "- `manifest.json`: machine-readable index of every generated composite",
        "",
        "## Naming",
        "",
        "- Composite format: `body-x__face-y__hair-z.png`",
        "- Contact-sheet format: `body-x.png`",
    ]
    (output_root / "README.md").write_text("\n".join(lines) + "\n", encoding="utf-8")


def main() -> None:
    args = parse_args()
    output_root = args.output_root

    if args.clean and output_root.exists():
        shutil.rmtree(output_root)

    output_root.mkdir(parents=True, exist_ok=True)
    records = generate_composites(output_root)
    generate_contact_sheets(output_root, records)
    write_manifest(output_root, records)
    write_summary(output_root, records)

    print(
        f"Generated {len(records)} avatar composites under "
        f"{output_root.relative_to(PROJECT_ROOT)}."
    )


if __name__ == "__main__":
    main()
