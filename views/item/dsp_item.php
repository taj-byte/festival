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
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>商品一覧</h2>

<!-- 年度選択フォーム -->
<div style="margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">
    <form method="GET" action="dsp_item.php" style="display: inline-block;">
        <label style="font-weight: bold; margin-right: 10px;">表示年度:</label>
        <select name="year" onchange="this.form.submit()" style="padding: 5px 10px; font-size: 14px;">
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

    <?php if ($selectedYear !== 'all'): ?>
        <a href="dsp_item.php" style="margin-left: 10px; color: #007bff; text-decoration: none;">
            ✕ フィルタ解除
        </a>
    <?php endif; ?>
</div>

<?php if (empty($byYear)): ?>
    <p>選択された年度の商品が登録されていません。</p>
<?php else: ?>
    <?php foreach ($byYear as $year => $yearItems): ?>
        <h3 style="background-color: #e9ecef; padding: 8px; margin-top: 20px; border-left: 4px solid #007bff;">
            <?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?>年度の商品
            <span style="font-size: 14px; font-weight: normal; color: #666;">
                (<?= count($yearItems) ?>件)
            </span>
        </h3>

        <table border="1" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
            <thead>
                <tr>
                    <th>商品ID</th>
                    <th>商品名</th>
                    <th>価格</th>
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

<p><a href="inp_item.php">商品追加</a> | <a href="../common/index.html">メニューに戻る</a></p>

<?php require __DIR__ . '/../common/footer.php'; ?>
