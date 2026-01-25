<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/SalesController.php';

// Controllerのインスタンスを作成
$ctrl = new SalesController($pdo);

// Controller層に処理を委譲してデータを取得
extract($ctrl->listSales());
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>売上一覧</h2>

<!-- 年度フィルター -->
<div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
    <form method="GET" action="dsp_sales.php" style="display: flex; align-items: center; gap: 10px;">
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
        <a href="dsp_sales.php" style="margin-left: 10px; color: #0c5460;">[全年度を表示]</a>
    </div>
<?php endif; ?>

<div class="sales-container">
    <?php if (count($salesSummary) > 0): ?>
        <table class="sales-table" border="1">
            <thead>
                <tr>
                    <th>店舗名</th>
                    <th>売上合計</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($salesSummary as $shop): ?>
                    <tr style="cursor: pointer;" onclick="location.href='sales_detail.php?sh_id=<?= htmlspecialchars($shop['sh_id']) ?>'">
                        <td><?= htmlspecialchars($shop['class']) ?> <?= htmlspecialchars($shop['pr_name']) ?></td>
                        <td style="text-align: right;"><?= number_format($shop['total_sales']) ?>円</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="text-align: right; font-size: 1.2em; font-weight: bold; margin-top: 20px; padding-top: 10px; border-top: 2px solid #333;">
            総売上: <?= number_format($grandTotal) ?>円
        </div>

    <?php else: ?>
        <div class="no-data">
            <?php if ($selectedYear !== null): ?>
                <?= htmlspecialchars($selectedYear) ?>年度の売上データは見つかりませんでした。
            <?php else: ?>
                売上データは見つかりませんでした。
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<p>
    <a href="inp_sales.php">売上新規登録</a> |
    <a href="../common/index.html">メニューに戻る</a>
</p>

<?php require __DIR__ . '/../common/footer.php'; ?>
