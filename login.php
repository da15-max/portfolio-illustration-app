<?php
session_start();

// DB接続
$pdo = new PDO('mysql:host=localhost;dbname=irasuto;charset=utf8', 'root', '');

// 入力値の取得とバリデーション
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($password)) {
        $error = '名前とパスワードを入力してください。';
    } else {
        // ユーザーの検索
        $stmt = $pdo->prepare("SELECT * FROM users WHERE name = :name");
        $stmt->execute([':name' => $name]);
        $user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // ログイン成功
    $_SESSION['user'] = $user;
    header('Location: toop.php');
    exit;
} else {
    // ログイン失敗
    $error = '名前またはパスワードが間違っています。';
}
    }
}
?>

<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>ログイン</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="container py-5">

  <h1 class="mb-4">ログイン</h1>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label for="name" class="form-label">名前</label>
      <input type="text" name="name" id="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">パスワード</label>
      <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">ログイン</button>
  </form>

</body>
</html>
