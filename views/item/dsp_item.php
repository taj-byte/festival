<?php
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ItemController.php';

// Controllerのインスタンスを作成
$itemController = new ItemController($pdo);

// Controller層に処理を委譲
$data = $itemController->listByYear();
$byYear = $data['byYear'];
$years = $data['years'];
$selectedYear = $data['year'];
$currentFy = $data['currentFy'];
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>商品一覧</h2>

<!-- 年度選択フォーム -->
<div class="filter-box">
    <form method="GET" action="dsp_item.php" class="filter-form">
        <label class="filter-label">表示年度:</label>
        <select name="year" onchange="this.form.submit()" class="filter-select">
            <option value="all" <?= $selectedYear === 'all' ? 'selected' : '' ?>>全年度</option>
            <?php foreach ($years as $y): ?>
                <?php if ($y !== '未分類'): ?>
                    <option value="<?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $selectedYear == $y ? 'selected' : '' ?>>
                        <?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?>年度
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (in_array('未分類', $years)): ?>
                <option value="未分類" <?= $selectedYear === '未分類' ? 'selected' : '' ?>>未分類</option>
            <?php endif; ?>
        </select>
        <noscript><button type="submit">表示</button></noscript>
    </form>

    <?php if ($selectedYear !== 'all' && $selectedYear != $currentFy): ?>
        <a href="dsp_item.php" class="filter-reset-link">
            ✕ フィルタ解除
        </a>
    <?php endif; ?>
</div>

<?php if (empty($byYear)): ?>
    <p>選択された年度の商品が登録されていません。</p>
<?php else: ?>
    <?php foreach ($byYear as $year => $yearItems): ?>
        <h3 class="item-year-header">
            <?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?>年度の商品
            <span class="item-year-header-count">
                (<?= count($yearItems) ?>件)
            </span>
        </h3>

        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col">商品ID</th>
                    <th scope="col">商品名</th>
                    <th scope="col">価格</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yearItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->i_id, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item->i_name, ENT_QUOTES, 'UTF-8') ?></td>
                        <td>¥<?= number_format($item->i_price) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
<?php endif; ?>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="inp_item.php">商品追加</a>
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
