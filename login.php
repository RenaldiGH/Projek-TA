<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/login.css">
<div class="login-container">
  <div class="login-image"></div>
<div class="login-box">
    <h1>Login</h1>
    <br><br>
    <hr>
    <br> <br>
    <form>
        <div class="input-group">
        <input type="text" name="username" placeholder="Username">
         </div>
         <br>
          <div class="input-group">
           <input type="password" name="password" placeholder="Password">
         </div>
         <div class="input-group">
       <button type="submit">Login</button>
      </div>
    </form>
</div>
</div>


