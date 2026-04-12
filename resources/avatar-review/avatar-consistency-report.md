# Avatar Consistency Audit

## Scope

This report audits the normalized layered PNG avatar library after generating the full review set at:

- `frontend/tmp/avatar-review/composites/`
- `frontend/tmp/avatar-review/contact-sheets/`
- `frontend/tmp/avatar-review/manifest.json`

The goal is to identify:

- category-level consistency issues
- individual asset outliers
- body/face/hair combinations that are likely to read poorly in play

The supporting machine-readable audit output lives at:

- `frontend/tmp/avatar-review/audit.json`

## Category-Level Findings

### Body

- The body set is mostly stable, but `body-6` and `body-7` are materially shorter than the rest after normalization.
- Median normalized body height is `570px`.
- `body-6` lands `56px` below that median.
- `body-7` lands `104px` below that median.
- Those two assets still align horizontally, but they read smaller and leave more empty vertical space beneath the torso than the rest of the library.

### Face

- Faces are the most consistent category overall.
- The notable variance is vertical height rather than position.
- `face-7` is `11px` shorter than the median face height.
- `face-3` is `9px` shorter than the median face height.
- `face-8` is `7px` shorter than the median face height.
- These are mild outliers and do not currently look like the highest-priority fix lane.

### Hair

- Hair is the most variable category by a wide margin.
- Width spread is large enough to change the perceived head size from option to option.
- `hair-5` is `81px` narrower than the median normalized hair width.
- `hair-6` is `61px` narrower than the median normalized hair width.
- `hair-11` is `48px` narrower than the median normalized hair width.
- `hair-7` is the opposite extreme, landing `63px` wider than the median and also `14px` shorter.
- Several hair assets also cover a very high percentage of the face layer, which produces the most noticeable combination issues in the current review set.

## Asset-Level Findings

- `body-7` is the strongest body outlier and should be treated as a primary repair candidate.
- `body-6` is the secondary body outlier and likely needs the same normalization strategy as `body-7`.
- `hair-11` consistently covers too much of the face area and is involved in the highest-occlusion combinations.
- `hair-3` is also involved in repeated high-occlusion combinations, though less aggressively than `hair-11`.
- `hair-5` and `hair-6` are unusually narrow and can make the head read compressed compared with the wider hair options.

## Combination-Level Findings

The most repeatable risky combinations are driven by face/hair overlap rather than by the body layer. The following face/hair pairs each hide at least half of the face pixels in the normalized stack:

- `face-8 + hair-11` at `57.0%` face occlusion
- `face-6 + hair-11` at `55.1%` face occlusion
- `face-2 + hair-11` at `52.9%` face occlusion
- `face-4 + hair-11` at `52.9%` face occlusion
- `face-6 + hair-3` at `52.8%` face occlusion
- `face-7 + hair-11` at `52.6%` face occlusion
- `face-8 + hair-3` at `52.5%` face occlusion
- `face-1 + hair-11` at `52.4%` face occlusion
- `face-3 + hair-11` at `51.8%` face occlusion
- `face-8 + hair-6` at `51.1%` face occlusion

These combinations are not necessarily unusable, but they are the clearest place where the current library risks making the face selection feel irrelevant.

## Recommended Fix Order

1. Repair `body-6` and `body-7` with a shared scripted normalization strategy.
2. Repair `hair-11` and likely `hair-3` with a shared scripted position adjustment aimed at reducing face occlusion.
3. Re-run the composite generator and audit after each loop to confirm the fixes improve the measurable outliers without creating worse regressions.
