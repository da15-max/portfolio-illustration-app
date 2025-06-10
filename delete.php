<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=irasuto;charset=utf8', 'root', '');

$id = $_POST['id'] ?? null;

// 投稿が存在し、自分の投稿なら削除
$stmt = $pdo->prepare("SELECT * FROM illustrations WHERE id = :id");
$stmt->execute([':id' => $id]);
$illustration = $stmt->fetch();

if ($illustration && $_SESSION['user']['id'] == $illustration['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM illustrations WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // 画像ファイルも削除（オプション）
    $filepath = 'uploads/' . $illustration['image_path'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

header('Location: toop.php');
exit;
