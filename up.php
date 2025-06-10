<?php
session_start();

// ログイン制限
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = 'uploads/';
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $userId = $_SESSION['user']['id'];

            // ★ GitHub（Replicate）APIが使えない環境のため、ベクトルは null に
            $vector = null;

            /*
            // Replicateで画像ベクトルを取得
            $imageData = base64_encode(file_get_contents($targetPath));

            $apiToken ='r8_...'; 
            $replicateUrl = 'https://api.replicate.com/v1/predictions';
            $modelVersion = '...';

            $payload = json_encode([
                'version' => $modelVersion,
                'input' => ['image' => 'data:image/jpeg;base64,' . $imageData]
            ]);

            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Authorization: Token $apiToken\r\nContent-Type: application/json",
                    'content' => $payload
                ]
            ];

            $context = stream_context_create($opts);
            $response = file_get_contents($replicateUrl, false, $context);
            $result = json_decode($response, true);

            if (isset($result['id'])) {
                $predictionId = $result['id'];
                $pollUrl = "$replicateUrl/$predictionId";

                do {
                    sleep(2);
                    $pollRes = file_get_contents($pollUrl, false, stream_context_create([
                        'http' => ['method' => 'GET', 'header' => "Authorization: Token $apiToken"]
                    ]));
                    $pollResult = json_decode($pollRes, true);
                } while (in_array($pollResult['status'], ['starting', 'processing']));

                if ($pollResult['status'] === 'succeeded' && isset($pollResult['output'])) {
                    $vector = json_encode($pollResult['output']);
                }
            }
            */

            // DB保存処理
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=irasuto;charset=utf8', 'root', '');
                $stmt = $pdo->prepare("INSERT INTO illustrations (user_id, title, description, image_path, vector) VALUES (:user_id, :title, :description, :image_path, :vector)");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':title' => $title,
                    ':description' => $description,
                    ':image_path' => $filename,
                    ':vector' => $vector
                ]);

                header("Location: toop.php");
                exit();
            } catch (PDOException $e) {
                echo "DBエラー: " . htmlspecialchars($e->getMessage());
            }

        } else {
            echo "アップロードに失敗しました。";
        }
    } else {
        echo "画像が選択されていないか、エラーがあります。";
    }
}
?>

<!-- 投稿フォーム -->
<form method="POST" enctype="multipart/form-data">
    タイトル：<input type="text" name="title" required><br>
    説明：<textarea name="description" required></textarea><br>
    イラスト画像：<input type="file" name="image" accept="image/*" required><br>
    <input type="submit" value="投稿">
</form>
