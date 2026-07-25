<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="login-box">
    <h2>Login</h2>
    <form>
        <input type="text" name="username" placeholder="Username" style="width:100%; margin-bottom:10px; padding:8px;">
        <input type="password" name="password" placeholder="Password" style="width:100%; margin-bottom:10px; padding:8px;">
        <button type="submit">Login</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
