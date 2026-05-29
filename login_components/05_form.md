# Form Markup

- **Purpose:** Login form fields and submit action.

## Markup summary
- `<form action="auth/login.php" method="POST">` — server-side POST to authentication script.
- Username field:
  - `<label class="field-label" for="username">Username</label>`
  - `<input type="text" id="username" name="username" class="form-control-mcc" placeholder="Enter your username" required>`
- Password field:
  - `<label class="field-label" for="password">Password</label>`
  - `<input type="password" id="password" name="password" class="form-control-mcc" placeholder="Enter your password" required>`
- Submit button:
  - `<button type="submit" class="btn-signin">Sign In</button>`

## Validation & UX
- Uses `required` attributes for basic client-side enforcement.
- Placeholder text provides guidance; labels remain visible for clarity and accessibility.

## Notes
- Consider adding `autocomplete` attributes (`username`, `current-password`) for improved UX.