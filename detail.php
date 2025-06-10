<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=irasuto;charset=utf8', 'root', '');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "無効なアクセスです。";
    exit;
}

// イラスト取得
$stmt = $pdo->prepare("SELECT * FROM illustrations WHERE id = :id");
$stmt->execute([':id' => $id]);
$illustration = $stmt->fetch();

if (!$illustration) {
    echo "該当のイラストが見つかりません。";
    exit;
}

// 閲覧記録を保存
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM views WHERE user_id = ? AND illustration_id = ?");
    $check->execute([$user_id, $id]);
    if ($check->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO views (user_id, illustration_id) VALUES (?, ?)");
        $insert->execute([$user_id, $id]);
    }
}

//　観覧済の除外つきおすすめ取得処理
$recommend = [];
$keywords = explode(' ', $illustration['title'] . ' ' . $illustration['description']);
$likeClauses = [];
$keywordParams = [];

foreach ($keywords as $word) {
    $likeClauses[] = "(title LIKE ? OR description LIKE ?)";
    $keywordParams[] = '%' . $word . '%';
    $keywordParams[] = '%' . $word . '%';
}

$where = implode(" OR ", $likeClauses);

// 閲覧済みのイラストIDを取得
$viewedIds = [];
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $viewStmt = $pdo->prepare("SELECT illustration_id FROM views WHERE user_id = ?");
    $viewStmt->execute([$user_id]);
    $viewedIds = array_column($viewStmt->fetchAll(), 'illustration_id');
}

// 現在のイラストIDも除外
$excludeIds = array_merge([$id], $viewedIds);
$placeholders = implode(',', array_fill(0, count($excludeIds), '?'));

$sql = "SELECT * FROM illustrations WHERE id NOT IN ($placeholders) AND ($where) ORDER BY views_count DESC LIMIT 6";
$stmt = $pdo->prepare($sql);

// パラメータ結合（ID除外 + キーワード検索）
$params = array_merge($excludeIds, $keywordParams);
$stmt->execute($params);
$recommend = $stmt->fetchAll();

?>

<!doctype html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($illustration['title']) ?> - 詳細</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <style>
    .object-fit-cover { object-fit: cover; }
  </style>
</head>
<body>
<div class="container mt-4">
  <h1><?= htmlspecialchars($illustration['title']) ?></h1>
  <img src="uploads/<?= htmlspecialchars($illustration['image_path']) ?>" class="img-fluid mb-3" style="max-height: 500px;" alt="イラスト画像">
  <p><?= nl2br(htmlspecialchars($illustration['description'])) ?></p>

  <!-- 削除ボタン -->
  <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $illustration['user_id']): ?>
    <form method="POST" action="delete.php" onsubmit="return confirm('本当に削除しますか？');">
      <input type="hidden" name="id" value="<?= $illustration['id'] ?>">
      <button type="submit" class="btn btn-danger">削除</button>
    </form>
  <?php endif; ?>

  <a href="toop.php" class="btn btn-primary mt-3">戻る</a>

  <?php if (!empty($recommend)): ?>
    <h3 class="mt-5">おすすめのイラスト</h3>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
      <?php foreach ($recommend as $rec): ?>
        <div class="col">
          <div class="card h-100">
            <a href="detail.php?id=<?= $rec['id'] ?>">
              <img src="uploads/<?= htmlspecialchars($rec['image_path']) ?>" class="card-img-top object-fit-cover" style="height: 200px;" alt="<?= htmlspecialchars($rec['title']) ?>">
            </a>
            <div class="card-body">
              <h6><?= htmlspecialchars($rec['title']) ?></h6>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
