from __future__ import annotations

import json
import shutil
import subprocess
import sys
from pathlib import Path


PROJECT_ROOT = Path(__file__).resolve().parents[2]
REVIEW_ROOT = PROJECT_ROOT / "frontend" / "tmp" / "avatar-review"


def run(script: str, *args: str) -> None:
    subprocess.run(
        [sys.executable, str(PROJECT_ROOT / "frontend" / "scripts" / script), *args],
        check=True,
        cwd=PROJECT_ROOT,
    )


def main() -> None:
    before_path = REVIEW_ROOT / "audit-before-fixes.json"
    after_path = REVIEW_ROOT / "audit-after-fixes.json"
    previous_audit_path = REVIEW_ROOT / "audit.json"
    before: dict[str, object] = {}

    if previous_audit_path.exists():
        before = json.loads(previous_audit_path.read_text(encoding="utf-8"))

    run("normalize_avatar_pngs.py")
    run("generate_avatar_composites.py", "--clean")
    run("audit_avatar_composites.py")

    if before != {}:
        before_path.write_text(json.dumps(before, indent=2) + "\n", encoding="utf-8")

    after = json.loads(previous_audit_path.read_text(encoding="utf-8"))
    after_path.write_text(json.dumps(after, indent=2) + "\n", encoding="utf-8")

    summary = {
        "bodyHeightOutliersBefore": before.get("categories", {}).get("body", {}).get("largestHeightOutliers", []),
        "bodyHeightOutliersAfter": after["categories"]["body"]["largestHeightOutliers"],
        "occlusionPairsAboveHalfFaceCoverageBefore": before.get("occlusionPairsAboveHalfFaceCoverage", []),
        "occlusionPairsAboveHalfFaceCoverageAfter": after["occlusionPairsAboveHalfFaceCoverage"],
    }

    (REVIEW_ROOT / "fix-summary.json").write_text(json.dumps(summary, indent=2) + "\n", encoding="utf-8")
    print(f"Wrote repair summary to {(REVIEW_ROOT / 'fix-summary.json').relative_to(PROJECT_ROOT)}.")


if __name__ == "__main__":
    main()
