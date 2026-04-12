# Avatar Consistency Fix Report

## Summary

This report captures the scripted repair pass that followed the initial avatar audit.

The repair work targeted only issues that were both:

- repeated across many generated composites
- safe to adjust through the asset normalization pipeline without adding runtime positioning logic

## Repair Loops

### Loop 1

Applied overrides for:

- `body-6`
- `body-7`
- `hair-3`
- `hair-6`
- `hair-11`

Result:

- `body-6` and `body-7` improved immediately.
- `hair-11` improved.
- `hair-6` improved.
- `hair-3` became worse according to the occlusion audit, so that adjustment was rejected.

### Loop 2

Removed the `hair-3` override and re-ran:

- `python frontend/scripts/normalize_avatar_pngs.py`
- `python frontend/scripts/generate_avatar_composites.py --clean`
- `python frontend/scripts/audit_avatar_composites.py`

This became the accepted final state.

## Verified Improvements

### Body Layer

- `body-6` height delta improved from `-56px` to `-1px`.
- `body-7` height delta improved from `-104px` to `0px`.
- Both assets now read much closer to the rest of the body library in the contact-sheet review.

### Hair / Face Occlusion

- Face/hair pairs with at least `50%` face occlusion dropped from `10` to `6`.
- `hair-11` severe-occlusion pairs dropped from `7` to `4`.
- `hair-6` no longer appears in the `>= 50%` occlusion list.

## Assets Fixed

- `body-6`
- `body-7`
- `hair-6`
- `hair-11`

## Attempted But Rejected

- `hair-3`

The scripted lift for `hair-3` increased occlusion rather than reducing it, so it was removed from the final override set.

## Remaining Follow-Up Candidates

- `hair-11` still produces the strongest remaining high-occlusion combinations.
- `hair-3` still creates a smaller cluster of heavy-overlap combinations, but it likely needs either a more nuanced art edit or a different scripted strategy than simple vertical repositioning.
- `hair-5` remains visually narrow compared with the broader styles, though it was not severe enough in the current audit to justify an automatic transform.

## Scripts Used

- `frontend/scripts/normalize_avatar_pngs.py`
- `frontend/scripts/generate_avatar_composites.py`
- `frontend/scripts/audit_avatar_composites.py`
- `frontend/scripts/repair_avatar_layers.py`
- `frontend/scripts/avatar_normalization_overrides.json`
