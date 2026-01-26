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
<div class="filter-box">
    <form method="GET" action="dsp_sales.php" class="filter-form">
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
        <a href="dsp_sales.php" class="year-info-link">[全年度を表示]</a>
    </div>
<?php endif; ?>

<div class="sales-container">
    <?php if (count($salesSummary) > 0): ?>
        <table class="sales-summary-table">
            <thead>
                <tr>
                    <th scope="col">店舗名</th>
                    <th scope="col">売上合計</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($salesSummary as $shop): ?>
                    <tr class="clickable-row" onclick="location.href='sales_detail.php?sh_id=<?= htmlspecialchars($shop['sh_id']) ?>'" role="link" tabindex="0" onkeydown="if(event.key==='Enter')this.click()">
                        <td>
                            <a href="sales_detail.php?sh_id=<?= htmlspecialchars($shop['sh_id']) ?>" class="row-link">
                                <?= htmlspecialchars($shop['class']) ?> <?= htmlspecialchars($shop['pr_name']) ?>
                            </a>
                        </td>
                        <td class="text-right"><?= number_format($shop['total_sales']) ?>円</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="grand-total">
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

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="inp_sales.php">売上新規登録</a>
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
