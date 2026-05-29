# PHP Error Handling

- **Purpose:** Simple server-side handling and display of authentication errors.

## Snippet
```php
<?php
    $error = '';
    if (isset($_GET['error'])) {
        if ($_GET['error'] === 'invalid_password') {
            $error = 'Incorrect password. Please try again.';
        } elseif ($_GET['error'] === 'user_not_found') {
            $error = 'Username not found. Please check your credentials.';
        }
    }
?>
```

## Display
- If `$error` is non-empty, it is echoed into the `.error-msg` container using `htmlspecialchars($error)` to prevent XSS.

## Notes
- `auth/login.php` should redirect back with `?error=invalid_password` or `?error=user_not_found` on failure.
- Prefer using server-side session flash messages for more robust UX.