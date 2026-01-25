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
<div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
    <form method="GET" action="dsp_shop_sales.php" style="display: flex; align-items: center; gap: 10px;">
        <label for="fy" style="font-weight: bold;">年度:</label>
        <select name="fy" id="fy" style="padding: 5px 10px; font-size: 14px;" onchange="this.form.submit()">
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
    <div style="background-color: #d1ecf1; padding: 10px; margin-bottom: 15px; border-left: 4px solid #0c5460;">
        <strong>ℹ 表示中:</strong> <?= htmlspecialchars($selectedYear) ?>年度のみ
        <a href="dsp_shop_sales.php" style="margin-left: 10px; color: #0c5460;">[全年度を表示]</a>
    </div>
<?php endif; ?>

<div style="background-color: #d1ecf1; padding: 10px; margin-bottom: 15px; border-left: 4px solid #0c5460;">
    <strong>ℹ 集計基準</strong><br>
    午前: 0:00〜11:59 / 午後: 12:00〜23:59<br>
    <small style="color: #666;">※売上日時（s_date）を基準に集計しています</small>
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
    <table border="1" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
        <thead style="background-color: #f8f9fa;">
            <tr>
                <th style="width: 80px;">店舗ID</th>
                <th>店舗名（クラス）</th>
                <th style="width: 140px; text-align: right;">午前売上</th>
                <th style="width: 140px; text-align: right;">午後売上</th>
                <th style="width: 140px; text-align: right; background-color: #fff3cd;">合計売上</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shopSales as $shop): ?>
                <tr>
                    <td style="text-align: center;">
                        <?= htmlspecialchars($shop['sh_id'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($shop['pr_name'], ENT_QUOTES, 'UTF-8') ?>
                        <span style="color: #666; font-size: 12px;">
                            (<?= htmlspecialchars($shop['class'], ENT_QUOTES, 'UTF-8') ?>)
                        </span>
                    </td>
                    <td style="text-align: right; padding-right: 10px;">
                        ¥<?= number_format($shop['morning_sales'] ?? 0) ?>
                    </td>
                    <td style="text-align: right; padding-right: 10px;">
                        ¥<?= number_format($shop['afternoon_sales'] ?? 0) ?>
                    </td>
                    <td style="text-align: right; padding-right: 10px; font-weight: bold; background-color: #fffbf0;">
                        ¥<?= number_format($shop['total_sales'] ?? 0) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background-color: #e9ecef; font-weight: bold;">
            <tr>
                <td colspan="2" style="text-align: right; padding-right: 10px;">全店舗合計:</td>
                <td style="text-align: right; padding-right: 10px;">
                    ¥<?= number_format($totalMorning) ?>
                </td>
                <td style="text-align: right; padding-right: 10px;">
                    ¥<?= number_format($totalAfternoon) ?>
                </td>
                <td style="text-align: right; padding-right: 10px; background-color: #ffc107; color: #000;">
                    ¥<?= number_format($grandTotal) ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- 売上構成比の表示 -->
    <h3 style="margin-top: 30px;">売上構成比</h3>
    <div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
        <?php if ($grandTotal > 0): ?>
            <div style="margin-bottom: 10px;">
                <strong>午前売上:</strong>
                ¥<?= number_format($totalMorning) ?>
                (<?= number_format(($totalMorning / $grandTotal) * 100, 1) ?>%)
            </div>
            <div style="margin-bottom: 10px;">
                <strong>午後売上:</strong>
                ¥<?= number_format($totalAfternoon) ?>
                (<?= number_format(($totalAfternoon / $grandTotal) * 100, 1) ?>%)
            </div>

            <!-- プログレスバー -->
            <div style="margin-top: 15px;">
                <div style="width: 100%; height: 30px; background-color: #e9ecef; border-radius: 4px; overflow: hidden; display: flex;">
                    <?php
                    $morningPercent = ($totalMorning / $grandTotal) * 100;
                    $afternoonPercent = ($totalAfternoon / $grandTotal) * 100;
                    ?>
                    <div style="width: <?= $morningPercent ?>%; background-color: #17a2b8; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                        <?php if ($morningPercent > 10): ?>
                            午前 <?= number_format($morningPercent, 1) ?>%
                        <?php endif; ?>
                    </div>
                    <div style="width: <?= $afternoonPercent ?>%; background-color: #ffc107; display: flex; align-items: center; justify-content: center; color: #000; font-weight: bold;">
                        <?php if ($afternoonPercent > 10): ?>
                            午後 <?= number_format($afternoonPercent, 1) ?>%
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>売上データがありません</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<p>
    <a href="dsp_sales.php">売上一覧に戻る</a> |
    <a href="../common/index.html">メニューに戻る</a>
</p>

<?php require __DIR__ . '/../common/footer.php'; ?>
