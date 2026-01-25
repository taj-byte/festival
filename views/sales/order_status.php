<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/SalesController.php';
require_once __DIR__ . '/../../utils/CsrfToken.php';

// Controllerのインスタンスを作成
$ctrl = new SalesController($pdo);

// Controller層に処理を委譲してデータを取得
extract($ctrl->showPending());

// CSRFトークンを生成
$csrfToken = CsrfToken::generate();
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>注文納品状況</h2>

<?php if (isset($error) && $error): ?>
    <div class="error-message" style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px;">
        <?= htmlspecialchars($message) ?>
    </div>
    <p>
        <a href="dsp_sales.php">売上一覧に戻る</a>
    </p>
<?php else: ?>
    <?php if (isset($shopInfo) && is_object($shopInfo)): ?>
        <h3><?= htmlspecialchars($shopInfo->class) ?> <?= htmlspecialchars($shopInfo->pr_name) ?></h3>
    <?php else: ?>
        <h3>店舗ID: <?= htmlspecialchars($shopId ?? '') ?></h3>
    <?php endif; ?>

    <div class="order-status-container">
        <h4>納品待ち一覧</h4>

        <?php if (count($pendingOrders) > 0): ?>
            <table class="order-status-table" border="1">
                <thead>
                    <tr>
                        <th>売上ID</th>
                        <th>注文日時</th>
                        <th>商品情報</th>
                        <th>金額</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pendingOrders as $order): ?>
                        <tr>
                            <td style="text-align: center;"><?= htmlspecialchars($order['s_id']) ?></td>
                            <td><?= htmlspecialchars($order['s_date']) ?></td>
                            <td><?= htmlspecialchars($order['items']) ?></td>
                            <td style="text-align: right;"><?= number_format($order['total']) ?>円</td>
                            <td style="text-align: center;">
                                <form method="post" action="deliver_order.php" style="margin: 0;">
                                    <input type="hidden" name="s_id" value="<?= htmlspecialchars($order['s_id']) ?>">
                                    <input type="hidden" name="sh_id" value="<?= htmlspecialchars($shopId) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="submit" value="納品" onclick="return confirm('売上ID <?= htmlspecialchars($order['s_id']) ?> を納品済みにしますか？');">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div class="no-data" style="background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 12px; border-radius: 5px; margin-top: 10px;">
                現在、納品待ちの注文はありません。
            </div>
        <?php endif; ?>
    </div>

    <p style="margin-top: 20px;">
        <a href="sales_detail.php?sh_id=<?= htmlspecialchars($shopId) ?>">売上詳細に戻る</a> |
        <a href="dsp_sales.php">売上一覧に戻る</a> |
        <a href="../common/index.html">メニューに戻る</a>
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../common/footer.php'; ?>
