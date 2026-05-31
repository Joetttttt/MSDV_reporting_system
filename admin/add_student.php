mysqli_query($conn,"
    INSERT INTO users
    (
        fullname,
        username,
        email,
        password,
        role,
        student_id,
        first_login
    )
    VALUES
    (
        '$fullname',
        '$username',
        '',
        '$encrypted_password',
        'student',
        '$student_id',
        1
    )
");

echo "
<script>
alert(
'Student Added Successfully\n\nUsername: $username\nPassword: $default_password'
);
window.location='students.php';
</script>";
exit();