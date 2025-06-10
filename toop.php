<?php
require 'session.php'; // ログイン判定などを関数化している場合
$pdo = new PDO('mysql:host=localhost;dbname=irasuto;charset=utf8', 'root', '');

// 検索処理
$keyword = $_GET['q'] ?? '';
if (!empty($keyword)) {
    $stmt = $pdo->prepare("SELECT * FROM illustrations WHERE title LIKE :kw OR description LIKE :kw ORDER BY created_at DESC");
    $stmt->execute([':kw' => '%' . $keyword . '%']);
} else {
    $stmt = $pdo->query("SELECT * FROM illustrations ORDER BY created_at DESC");
}
?>

<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <style>
    .object-fit-cover {
      object-fit: cover;
    }
  </style>
  <title>イラスト投稿サイト</title>
</head>
<body>

<!-- ナビゲーション -->
<ul class="nav">
  <li class="nav-item"><a href="toop.php" class="nav-link">Home</a></li>
  <li class="nav-item"><a href="up.php" class="nav-link">イラスト投稿</a></li>
  <?php if (is_logged_in()): ?>
    <li class="nav-item ms-auto">
      <a href="logout.php" class="nav-link">ログアウト (<?= htmlspecialchars($_SESSION['user']['name']) ?>)</a>
    </li>
  <?php else: ?>
    <li class="nav-item ms-auto"><a href="login.php" class="nav-link">ログイン</a></li>
    <li class="nav-item"><a href="register.php" class="nav-link">登録</a></li>
  <?php endif; ?>
</ul>

<div class="container">
  <!-- 検索フォーム -->
  <form method="GET" class="my-4">
    <div class="input-group">
      <input type="text" name="q" class="form-control" placeholder="タイトルや説明で検索" value="<?= htmlspecialchars($keyword) ?>">
      <button class="btn btn-outline-secondary" type="submit">検索</button>
    </div>
  </form>

  <!-- 投稿作品一覧 -->
  <h2 class="my-4">投稿作品一覧</h2>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
    <?php while ($row = $stmt->fetch()): ?>
      <div class="col">
        <div class="card h-100 border-0">
          <a href="detail.php?id=<?= $row['id'] ?>">
            <div class="ratio ratio-1x1 overflow-hidden rounded">
              <img src="uploads/<?= htmlspecialchars($row['image_path']) ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($row['title']) ?>">
            </div>
          </a>
          <div class="card-body text-center p-2">
            <h6 class="mb-1"><?= htmlspecialchars($row['title']) ?: '無題' ?></h6>
            <small class="text-muted"><?= htmlspecialchars($row['author'] ?? '投稿者不明') ?></small>

          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<footer class="bg-primary bg-opacity-25 text-center p-3 mt-4">&copy; 2024 情報スペシャリスト科1-4A 城間 太吾</footer>

</body>
</html>
