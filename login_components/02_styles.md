# Styles

- **Purpose:** Central CSS rules that define appearance, layout, and interactions for the login card.

## Key selectors & intent
- `body` — full-screen centered layout, background colors and radial gradients.
- `.login-wrapper` — width constraint and horizontal padding for the card.
- `.login-card` — white panel, rounded corners, shadow, relative positioning.
- `.login-card::before` — small red top bar visual accent.
- `.card-body-inner` — inner padding for content spacing.
- `.field-label` — small uppercase label style.
- `.form-control-mcc` — custom input styling (rounded, neutral background, focus shadow).
- `.btn-signin` — full-width primary action with hover/active states.
- `.error-msg` — styled error banner with left colored border and fade-in animation.
- `.loginlogo` & `.mcclogo` — circular logo and positioning styles.

## Accessibility / UX
- Focus state uses an outline-like box-shadow for keyboard users.
- Error banner uses color contrast and an icon for quick recognition.