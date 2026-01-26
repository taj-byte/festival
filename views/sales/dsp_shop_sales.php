<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/SalesController.php';

// Controllerのインスタンスを作成
$ctrl = new SalesController($pdo);

// Controller層に処理を委譲
$data = $ctrl->showSalesByPeriod();
$shopSales = $data['shopSales'];
$totalMorning = $data['totalMorning'];
$totalAfternoon = $data['totalAfternoon'];
$grandTotal = $data['grandTotal'];
$availableYears = $data['availableYears'];
$selectedYear = $data['selectedYear'];
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>店舗別売上集計（午前・午後）</h2>

<!-- 年度フィルター -->
<div class="filter-box">
    <form method="GET" action="dsp_shop_sales.php" class="filter-form">
        <label for="fy" class="filter-label">年度:</label>
        <select name="fy" id="fy" class="filter-select" onchange="this.form.submit()">
            <option value="">全年度</option>
            <?php foreach ($availableYears as $year): ?>
                <option value="<?= htmlspecialchars($year) ?>"
                    <?= ($selectedYear === $year) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($year) ?>年度
                </option>
            <?php endforeach; ?>
        </select>
        <noscript>
            <button type="submit">絞り込み</button>
        </noscript>
    </form>
</div>

<?php if ($selectedYear !== null): ?>
    <div class="year-info-box">
        <strong>ℹ 表示中:</strong> <?= htmlspecialchars($selectedYear) ?>年度のみ
        <a href="dsp_shop_sales.php" class="year-info-link">[全年度を表示]</a>
    </div>
<?php endif; ?>

<div class="period-info-box">
    <strong>ℹ 集計基準</strong><br>
    午前: 0:00〜11:59 / 午後: 12:00〜23:59<br>
    <small class="hint-text">※売上日時（s_date）を基準に集計しています</small>
</div>

<?php if (empty($shopSales)): ?>
    <p>
        <?php if ($selectedYear !== null): ?>
            <?= htmlspecialchars($selectedYear) ?>年度の売上データがありません。
        <?php else: ?>
            売上データがありません。
        <?php endif; ?>
    </p>
<?php else: ?>
    <table class="shop-sales-table">
        <thead>
            <tr>
                <th scope="col" class="col-shop-id">店舗ID</th>
                <th scope="col">店舗名（クラス）</th>
                <th scope="col" class="col-sales">午前売上</th>
                <th scope="col" class="col-sales">午後売上</th>
                <th scope="col" class="col-total-header">合計売上</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shopSales as $shop): ?>
                <tr>
                    <td class="text-center">
                        <?= htmlspecialchars($shop['sh_id'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($shop['pr_name'], ENT_QUOTES, 'UTF-8') ?>
                        <span class="shop-class-text">
                            (<?= htmlspecialchars($shop['class'], ENT_QUOTES, 'UTF-8') ?>)
                        </span>
                    </td>
                    <td class="cell-sales">
                        ¥<?= number_format($shop['morning_sales'] ?? 0) ?>
                    </td>
                    <td class="cell-sales">
                        ¥<?= number_format($shop['afternoon_sales'] ?? 0) ?>
                    </td>
                    <td class="cell-total">
                        ¥<?= number_format($shop['total_sales'] ?? 0) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="footer-label">全店舗合計:</td>
                <td class="cell-sales">
                    ¥<?= number_format($totalMorning) ?>
                </td>
                <td class="cell-sales">
                    ¥<?= number_format($totalAfternoon) ?>
                </td>
                <td class="footer-grand-total">
                    ¥<?= number_format($grandTotal) ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- 売上構成比の表示 -->
    <h3 class="composition-title">売上構成比</h3>
    <div class="composition-box">
        <?php if ($grandTotal > 0): ?>
            <div class="composition-item">
                <strong>午前売上:</strong>
                ¥<?= number_format($totalMorning) ?>
                (<?= number_format(($totalMorning / $grandTotal) * 100, 1) ?>%)
            </div>
            <div class="composition-item">
                <strong>午後売上:</strong>
                ¥<?= number_format($totalAfternoon) ?>
                (<?= number_format(($totalAfternoon / $grandTotal) * 100, 1) ?>%)
            </div>

            <!-- プログレスバー -->
            <div class="progress-bar-container">
                <div class="progress-bar">
                    <?php
                    $morningPercent = ($totalMorning / $grandTotal) * 100;
                    $afternoonPercent = ($totalAfternoon / $grandTotal) * 100;
                    ?>
                    <div class="progress-morning tooltip-wrapper" style="width: <?= $morningPercent ?>%;" aria-label="午前 <?= number_format($morningPercent, 1) ?>%">
                        <?php if ($morningPercent > 10): ?>
                            午前 <?= number_format($morningPercent, 1) ?>%
                        <?php endif; ?>
                        <span class="tooltip-text">午前: ¥<?= number_format($totalMorning) ?> (<?= number_format($morningPercent, 1) ?>%)</span>
                    </div>
                    <div class="progress-afternoon tooltip-wrapper" style="width: <?= $afternoonPercent ?>%;" aria-label="午後 <?= number_format($afternoonPercent, 1) ?>%">
                        <?php if ($afternoonPercent > 10): ?>
                            午後 <?= number_format($afternoonPercent, 1) ?>%
                        <?php endif; ?>
                        <span class="tooltip-text">午後: ¥<?= number_format($totalAfternoon) ?> (<?= number_format($afternoonPercent, 1) ?>%)</span>
                    </div>
                </div>
                <!-- 凡例 -->
                <div class="progress-legend">
                    <span class="progress-legend-item">
                        <span class="progress-legend-color progress-legend-morning"></span>
                        午前 (0:00〜11:59)
                    </span>
                    <span class="progress-legend-item">
                        <span class="progress-legend-color progress-legend-afternoon"></span>
                        午後 (12:00〜23:59)
                    </span>
                </div>
            </div>
        <?php else: ?>
            <p>売上データがありません</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="dsp_sales.php">売上一覧に戻る</a>
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
