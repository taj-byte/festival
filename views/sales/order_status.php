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
    <div class="error-message">
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
            <table class="order-status-table">
                <thead>
                    <tr>
                        <th scope="col">売上ID</th>
                        <th scope="col">注文日時</th>
                        <th scope="col">商品情報</th>
                        <th scope="col">金額</th>
                        <th scope="col">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pendingOrders as $order): ?>
                        <tr>
                            <td class="text-center"><?= htmlspecialchars($order['s_id']) ?></td>
                            <td><?= htmlspecialchars($order['s_date']) ?></td>
                            <td><?= htmlspecialchars($order['items']) ?></td>
                            <td class="text-right"><?= number_format($order['total']) ?>円</td>
                            <td class="text-center">
                                <form method="post" action="deliver_order.php" class="deliver-form" id="deliverForm_<?= htmlspecialchars($order['s_id']) ?>">
                                    <input type="hidden" name="s_id" value="<?= htmlspecialchars($order['s_id']) ?>">
                                    <input type="hidden" name="sh_id" value="<?= htmlspecialchars($shopId) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="showDeliverModal('<?= htmlspecialchars($order['s_id']) ?>')">納品</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div class="no-data">
                現在、納品待ちの注文はありません。
            </div>
        <?php endif; ?>
    </div>

    <!-- 納品確認モーダル -->
    <div class="modal-overlay" id="deliverModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">納品確認</h3>
                <button class="modal-close" onclick="closeDeliverModal()" aria-label="閉じる">&times;</button>
            </div>
            <div class="modal-body">
                <p>売上ID <strong id="modalSalesId"></strong> を納品済みにしますか？</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeDeliverModal()">キャンセル</button>
                <button class="btn btn-primary" id="modalConfirmBtn" onclick="confirmDeliver()">納品する</button>
            </div>
        </div>
    </div>

    <script>
    let currentDeliverSalesId = null;

    function showDeliverModal(salesId) {
        currentDeliverSalesId = salesId;
        document.getElementById('modalSalesId').textContent = salesId;
        document.getElementById('deliverModal').classList.add('active');
        document.getElementById('modalConfirmBtn').focus();
    }

    function closeDeliverModal() {
        document.getElementById('deliverModal').classList.remove('active');
        currentDeliverSalesId = null;
    }

    function confirmDeliver() {
        if (currentDeliverSalesId) {
            document.getElementById('deliverForm_' + currentDeliverSalesId).submit();
        }
    }

    // Escキーでモーダルを閉じる
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeliverModal();
        }
    });

    // オーバーレイクリックでモーダルを閉じる
    document.getElementById('deliverModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeliverModal();
        }
    });
    </script>

    <nav class="page-nav" aria-label="ページナビゲーション">
        <a href="sales_detail.php?sh_id=<?= htmlspecialchars($shopId) ?>">売上詳細に戻る</a>
        <a href="dsp_sales.php">売上一覧に戻る</a>
        <a href="../common/index.html">メニューに戻る</a>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../common/footer.php'; ?>
