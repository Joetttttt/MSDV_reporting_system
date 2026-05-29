# Logo Component

- **Purpose:** Circular logo visually overlapping the login card.

## Markup
- Anchor linking to `csu.html` wrapping the image:
  - `<a href="csu.html"><img src="images/mccLogo.png" class="loginlogo" /></a>`

## Styling & Behavior
- `.loginlogo` — circular image with `border: 5px solid #091a47`, defined dimensions (155×155px).
- `.mcclogo` — dark circular background, absolute positioning and transform to overlap the card (`transform: translate(60%, -55%)`).

## Notes
- Keep image path `images/mccLogo.png` in `uploads` or `images` folder as required.
- Ensure `alt` attribute is added for accessibility when integrating (e.g. `alt="MCC logo"`).