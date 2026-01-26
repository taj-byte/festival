<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/SalesController.php';

// Controllerのインスタンスを作成
$ctrl = new SalesController($pdo);

// Controller層に処理を委譲してデータを取得
extract($ctrl->showDetail());
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>売上詳細</h2>

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

    <div class="sales-detail-container">
        <h4>売上明細</h4>
        <?php if (count($details) > 0): ?>
            <table class="sales-detail-table">
                <thead>
                    <tr>
                        <th scope="col">売上日時</th>
                        <th scope="col">商品名</th>
                        <th scope="col">単価</th>
                        <th scope="col">値引き額</th>
                        <th scope="col">数量</th>
                        <th scope="col">小計</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($details as $detail): ?>
                        <tr>
                            <td><?= htmlspecialchars($detail['s_date']) ?></td>
                            <td><?= htmlspecialchars($detail['i_name']) ?></td>
                            <td class="text-right"><?= number_format($detail['price']) ?>円</td>
                            <td class="text-right"><?= number_format($detail['disc']) ?>円</td>
                            <td class="text-center"><?= htmlspecialchars($detail['num']) ?></td>
                            <td class="text-right"><?= number_format($detail['subtotal']) ?>円</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="grand-total">
                売上合計: <?= number_format($total) ?>円
            </div>

        <?php else: ?>
            <div class="no-data">
                この店舗の売上データは見つかりませんでした。
            </div>
        <?php endif; ?>
    </div>

    <div class="reservation-container">
        <h4>予約情報</h4>
        <?php if (count($reservations) > 0): ?>
            <table class="reservation-table">
                <thead>
                    <tr>
                        <th scope="col">予約ID</th>
                        <th scope="col">予約日時</th>
                        <th scope="col">学生名</th>
                        <th scope="col">商品名</th>
                        <th scope="col">数量</th>
                        <th scope="col">単価</th>
                        <th scope="col">状態</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require_once __DIR__ . '/../../models/ReserveModel.php';
                    require_once __DIR__ . '/../../dao/ReserveDAO.php';
                    $reserveDAO = new ReserveDAO($pdo);
                    $reserveModel = new ReserveModel($reserveDAO);

                    foreach($reservations as $reservation):
                        $statusLabel = $reserveModel->getSituationLabel($reservation['situation']);
                        // ステータスに応じて行とバッジのクラスを設定
                        $rowClass = '';
                        $badgeClass = '';
                        switch($reservation['situation']) {
                            case 0: $rowClass = 'reservation-row-pending';   $badgeClass = 'status-pending';   break;
                            case 1: $rowClass = 'reservation-row-visited';   $badgeClass = 'status-visited';   break;
                            case 2: $rowClass = 'reservation-row-cancelled'; $badgeClass = 'status-cancelled'; break;
                            case 3: $rowClass = 'reservation-row-soldout';   $badgeClass = 'status-soldout';   break;
                        }
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="text-center"><?= htmlspecialchars($reservation['r_id']) ?></td>
                            <td><?= htmlspecialchars($reservation['datetime']) ?></td>
                            <td><?= htmlspecialchars($reservation['st_name']) ?></td>
                            <td><?= htmlspecialchars($reservation['i_name']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($reservation['num']) ?></td>
                            <td class="text-right"><?= number_format($reservation['i_price']) ?>円</td>
                            <td class="text-center"><span class="status-badge <?= $badgeClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                この店舗の予約データは見つかりませんでした。
            </div>
        <?php endif; ?>
    </div>

    <nav class="page-nav" aria-label="ページナビゲーション">
        <a href="order_status.php?sh_id=<?= htmlspecialchars($shopId) ?>">注文納品状況</a>
        <a href="dsp_sales.php">売上一覧に戻る</a>
        <a href="../common/index.html">メニューに戻る</a>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../common/footer.php'; ?>
