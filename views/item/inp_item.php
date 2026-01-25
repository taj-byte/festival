<?php require __DIR__ . '/../common/header.php'; ?>

<h2>商品登録</h2>

<div style="background-color: #fff3cd; padding: 10px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
    <strong>⚠ 商品名の命名規則</strong><br>
    商品名には必ず年度を含めてください。<br>
    例: ベースドリンク(2025)、焼きそば (2026)<br>
    <small style="color: #666;">※全角・半角どちらでも入力可能です（自動で半角に変換されます）</small>
</div>

<form action="add_item.php" method="post">
    <div style="margin-bottom: 10px;">
        <label for="i_name">商品名:</label><br>
        <input type="text" name="i_name" id="i_name"
               placeholder="例: タピオカミルクティー (2025)"
               required
               style="width: 300px;">
    </div>

    <div style="margin-bottom: 10px;">
        <label for="i_price">価格:</label><br>
        <input type="number" name="i_price" id="i_price" min="0" required>
    </div>

    <input type="submit" value="追加">
</form>

<p><a href="dsp_item.php">商品一覧</a> | <a href="../common/index.html">メニューに戻る</a></p>

<?php require __DIR__ . '/../common/footer.php'; ?>
