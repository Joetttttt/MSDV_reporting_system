# Error Component

- **Purpose:** Visual error banner shown when authentication fails.

## Markup
```html
<?php if ($error): ?>
<div class="error-msg">
  <!-- SVG icon -->
  <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>
```

## Styling cues
- `.error-msg` uses a light pink background, red left border, and an SVG icon for emphasis.
- `animation: fadeInDown` provides a subtle entrance.

## Notes
- Keep the echoed text sanitized with `htmlspecialchars()` to prevent XSS.