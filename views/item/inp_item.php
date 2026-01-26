<?php require __DIR__ . '/../common/header.php'; ?>

<h2>商品登録</h2>

<div class="naming-rule-box">
    <strong>⚠ 商品名の命名規則</strong><br>
    商品名には必ず年度を含めてください。<br>
    例: ベースドリンク(2025)、焼きそば (2026)<br>
    <small class="hint-text">※全角・半角どちらでも入力可能です（自動で半角に変換されます）</small>
</div>

<form action="add_item.php" method="post">
    <div class="form-group">
        <label for="i_name">商品名:</label><br>
        <input type="text" name="i_name" id="i_name"
               placeholder="例: タピオカミルクティー (2025)"
               required
               class="input-wide">
    </div>

    <div class="form-group">
        <label for="i_price">価格:</label><br>
        <input type="number" name="i_price" id="i_price" min="0" required>
    </div>

    <input type="submit" value="追加">
</form>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="dsp_item.php">商品一覧</a>
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
