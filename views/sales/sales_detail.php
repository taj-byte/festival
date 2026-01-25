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

    <div class="sales-detail-container">
        <h4>売上明細</h4>
        <?php if (count($details) > 0): ?>
            <table class="sales-detail-table" border="1">
                <thead>
                    <tr>
                        <th>売上日時</th>
                        <th>商品名</th>
                        <th>単価</th>
                        <th>値引き額</th>
                        <th>数量</th>
                        <th>小計</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($details as $detail): ?>
                        <tr>
                            <td><?= htmlspecialchars($detail['s_date']) ?></td>
                            <td><?= htmlspecialchars($detail['i_name']) ?></td>
                            <td style="text-align: right;"><?= number_format($detail['price']) ?>円</td>
                            <td style="text-align: right;"><?= number_format($detail['disc']) ?>円</td>
                            <td style="text-align: center;"><?= htmlspecialchars($detail['num']) ?></td>
                            <td style="text-align: right;"><?= number_format($detail['subtotal']) ?>円</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="text-align: right; font-size: 1.2em; font-weight: bold; margin-top: 20px; padding-top: 10px; border-top: 2px solid #333;">
                売上合計: <?= number_format($total) ?>円
            </div>

        <?php else: ?>
            <div class="no-data">
                この店舗の売上データは見つかりませんでした。
            </div>
        <?php endif; ?>
    </div>

    <div class="reservation-container" style="margin-top: 40px;">
        <h4>予約情報</h4>
        <?php if (count($reservations) > 0): ?>
            <table class="reservation-table" border="1">
                <thead>
                    <tr>
                        <th>予約ID</th>
                        <th>予約日時</th>
                        <th>学生名</th>
                        <th>商品名</th>
                        <th>数量</th>
                        <th>単価</th>
                        <th>状態</th>
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
                        // ステータスに応じて行の色を変える
                        $rowStyle = '';
                        switch($reservation['situation']) {
                            case 0: $rowStyle = 'background-color: #fff3cd;'; break; // 予約中: 黄色
                            case 1: $rowStyle = 'background-color: #d4edda;'; break; // 来店: 緑
                            case 2: $rowStyle = 'background-color: #f8d7da;'; break; // 取消: 赤
                            case 3: $rowStyle = 'background-color: #d1ecf1;'; break; // 完売: 青
                        }
                    ?>
                        <tr style="<?= $rowStyle ?>">
                            <td style="text-align: center;"><?= htmlspecialchars($reservation['r_id']) ?></td>
                            <td><?= htmlspecialchars($reservation['datetime']) ?></td>
                            <td><?= htmlspecialchars($reservation['st_name']) ?></td>
                            <td><?= htmlspecialchars($reservation['i_name']) ?></td>
                            <td style="text-align: center;"><?= htmlspecialchars($reservation['num']) ?></td>
                            <td style="text-align: right;"><?= number_format($reservation['i_price']) ?>円</td>
                            <td style="text-align: center;"><?= htmlspecialchars($statusLabel) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data" style="background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 12px; border-radius: 5px; margin-top: 10px;">
                この店舗の予約データは見つかりませんでした。
            </div>
        <?php endif; ?>
    </div>

    <p>
        <a href="order_status.php?sh_id=<?= htmlspecialchars($shopId) ?>">注文納品状況</a> |
        <a href="dsp_sales.php">売上一覧に戻る</a> |
        <a href="../common/index.html">メニューに戻る</a>
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../common/footer.php'; ?>
