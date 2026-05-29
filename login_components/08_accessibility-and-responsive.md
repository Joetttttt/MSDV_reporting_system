# Accessibility & Responsive Notes

- **Purpose:** Summarize accessibility and responsive considerations used in the page.

## Responsive
- `meta name="viewport" content="width=device-width, initial-scale=1.0"` enables responsive scaling.
- Layout uses a centered flexbox that adapts to viewport height.

## Accessibility
- Visible `<label>` elements associated with inputs via `for`/`id`.
- Focus styles (`box-shadow`) on inputs support keyboard navigation.
- Error messages include both an icon and text; ensure color contrast is sufficient.

## Suggestions
- Add `alt` text to the logo image.
- Use `aria-live="polite"` on the error container for screen-reader announcement.
- Provide server-side rate-limiting and generic error messages to avoid username enumeration.