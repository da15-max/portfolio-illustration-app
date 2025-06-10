<?php
// DB接続
$conn = new PDO('mysql:host=localhost;dbname=irasuto', 'root', '');
$stmt = $conn->query("SELECT * FROM illustrations ORDER BY created_at DESC");

while ($row = $stmt->fetch()) {
    echo '<div>';
    echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
    echo '<img src="uploads/' . htmlspecialchars($row['image_path']) .'" width="150">';
    echo '<p>' . nl2br(htmlspecialchars($row['description'])) .'</p>';
    echo '</div><hr>';
}
?>
