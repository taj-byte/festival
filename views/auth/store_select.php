<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';

/* ログインチェック */
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

/* 店舗一覧を取得 */
$sql = "
SELECT
    sh_id,
    class,
    pr_name
FROM shop
ORDER BY sh_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>店舗選択</title>
<style>
body {
    font-family: sans-serif;
    background: #f5f5f5;
}
.container {
    width: 400px;
    margin: 50px auto;
    background: #fff;
    padding: 20px;
    border-radius: 6px;
}
h2 {
    text-align: center;
}
.store {
    margin: 10px 0;
}
button {
    width: 100%;
    padding: 10px;
    font-size: 16px;
}
</style>
</head>

<body>
<div class="container">
    <p style="text-align: right; margin: 0; padding: 10px 0; font-size: 0.9em;">
        ログイン中: <?= htmlspecialchars($_SESSION['student_id'], ENT_QUOTES, 'UTF-8') ?> |
        <a href="logout.php">ログアウト</a>
    </p>
    <h2>店舗を選択してください</h2>

    <?php foreach ($stores as $s): ?>
        <div class="store">
            <form method="post" action="../item/products.php">
                <input type="hidden" name="store_id"
                       value="<?= htmlspecialchars($s['sh_id'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">
                    <?= htmlspecialchars($s['class'] , ENT_QUOTES, 'UTF-8') ?>
                    <?= htmlspecialchars($s['pr_name'] , ENT_QUOTES, 'UTF-8') ?>
                </button>
            </form>
        </div>
    <?php endforeach; ?>

    <p style="text-align: center; margin-top: 20px;">
        <a href="../common/index.html">メニューに戻る</a>
    </p>

</div>
</body>
</html>
