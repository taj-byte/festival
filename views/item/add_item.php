<?php
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ItemController.php';

// Controllerのインスタンスを作成
$itemController = new ItemController($pdo);

// Controller層に処理を委譲
$result = $itemController->add();

// エラー表示（成功時はController内でリダイレクト）
?>
<?php require __DIR__ . '/../common/header.php'; ?>

<p style="color: red;"><?= htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') ?></p>
<p><a href="inp_item.php">戻る</a></p>

<?php require __DIR__ . '/../common/footer.php'; ?>
