<?php
include __DIR__ . '/auth/authentication/auth.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = new User();

  if (isset($_POST['login_as_employee'])) {
    $user->loginAsEmployee();
    exit;
  }

  if (isset($_POST['book_meeting'])) {
    $user->loginAsMeetingGuest();
    exit;
  }

  if (isset($_POST['username']) && isset($_POST['password'])) {
    if (!$user->login($_POST['username'], $_POST['password'])) {
      $message = "Invalid username/email or password.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>STLAF | WIP SUPPLIES LEDGER</title>
  <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="icon" type="image/png" href="assets/images/sub_logo_light.png">
</head>

<body>

  <nav class="navbar px-5 bg-light">
    <div class="d-flex align-items-center gap-5">
      <a class="navbar-brand m-0 p-0" href="index.php">
        <img src="assets/images/official_logo.png" alt="Logo" width="80" height="80">
      </a>
      <h3 class="supply mb-0">WIP Supplies Ledger</h3>
    </div>
  </nav>

  <div class="login-container">
    <div class="login-box">
      <h2>Login</h2>
      <?php if ($message): ?>
        <div class="error"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form method="post">
        <input type="text" name="username" placeholder="Username or Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="login-btn">Login</button>
      </form>

      <form method="post">
        <button type="submit" name="login_as_employee" class="guest-btn">Login as Guest</button>
        <!-- <button type="submit" name="book_meeting" class="guest-btn">Book a meeting!</button> -->
      </form>
    </div>
  </div>

  <script src="assets/bootstrap/all.min.js"></script>
  <script src="assets/bootstrap/bootstrap.bundle.min.js"></script>

</body>

</html>