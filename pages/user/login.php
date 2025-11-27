<?php
// pages/user/login.php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>User Login · Origin-Core</title>
</head>
<body>
    <h1>User Login (Origin-Core)</h1>

    <p>
        This is a demo login screen. In a real application you would
        replace this with your actual TGTRACING login logic.
    </p>

    <form method="post" action="#">
        <label>
            Email:
            <input type="email" name="email" required>
        </label>
        <br><br>
        <label>
            Password:
            <input type="password" name="password" required>
        </label>
        <br><br>
        <button type="submit">Login</button>
    </form>

    <p><a href="?p=home">&larr; Back home</a></p>
</body>
</html>
