<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
// settings.phpはinit.php内で読み込み済み

/* ログインチェック */
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

/* 店舗一覧を取得（現在の年度のみ） */
$sql = "
SELECT
    sh_id,
    class,
    pr_name
FROM shop
WHERE fy = ?
ORDER BY sh_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute([CURRENT_FY]);
$stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>店舗選択 - 学園祭売り上げシステム</title>
<link rel="stylesheet" href="../css/festival.css">
</head>

<body class="store-select-body">
<main class="store-select-container" role="main">
    <p class="store-select-login-info">
        ログイン中: <?= htmlspecialchars($_SESSION['student_id'], ENT_QUOTES, 'UTF-8') ?> |
        <a href="logout.php">ログアウト</a>
    </p>
    <h2>店舗を選択してください</h2>

    <div role="list" aria-label="店舗一覧">
        <?php foreach ($stores as $s): ?>
            <div class="store" role="listitem">
                <form method="post" action="../item/products.php">
                    <input type="hidden" name="store_id"
                           value="<?= htmlspecialchars($s['sh_id'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-outline">
                        <?= htmlspecialchars($s['class'] , ENT_QUOTES, 'UTF-8') ?>
                        <?= htmlspecialchars($s['pr_name'] , ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <nav class="page-nav" aria-label="ページナビゲーション">
        <a href="../common/index.html">メニューに戻る</a>
    </nav>

</main>
</body>
</html>
