# Document Structure

- **Purpose:** HTML structure and DOM hierarchy of the login page.

## High-level layout
- `body` (centered flex container)
  - `.login-wrapper` (max-width container)
    - container `div` (position: relative)
      - `.mcclogo` (absolute-positioned logo)
      - `.login-card` (visual card)
        - `.card-body-inner` (padding and content)
          - PHP error handling block
          - `<form action="auth/login.php" method="POST">`
            - `.form-group` — Username label + input
            - `.form-group` — Password label + input
            - Submit button `Sign In`
            - Conditional `.error-msg` block (rendered if `$error` is set)

## Notes
- The structure separates visual decoration (logo, top bar) from the form content.
- The form posts to `auth/login.php` for server-side authentication.