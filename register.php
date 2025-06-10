<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pdo = new PDO('mysql:host=localhost;dbname=irasuto;charset=utf8', 'root', '');
  $stmt = $pdo->prepare('INSERT INTO users (name, password) VALUES (?, ?)');
  $stmt->execute([
    $_POST['name'],
    password_hash($_POST['password'], PASSWORD_DEFAULT)
  ]);
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>ユーザー登録</title></head>
<body>
  <h2>ユーザー登録</h2>
  <form method="post">
    <input type="text" name="name" placeholder="ユーザー名" required><br>
    <input type="password" name="password" placeholder="パスワード" required><br>
    <button type="submit">登録</button>
  </form>
</body>
</html>
