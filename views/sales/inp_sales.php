<?php
require __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ShopController.php';

// Controllerのインスタンスを作成
$shopController = new ShopController($pdo);

// セッション有効期限チェック
$shopController->checkSessionExpiry();

// セッションから店舗データを取得
$shops = $shopController->getShopsFromSession();

// セッションが空の場合、店舗選択処理を実行
if (empty($shops)) {
    $result = $shopController->selectShopId();
    // エラーがなければリダイレクトされるので、ここには到達しない
    // エラーがあれば$resultに['error']が入る
    if (isset($result['error'])) {
        $error = $result['error'];
    }
}
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>売上登録</h2>

<?php if (isset($error)): ?>
    <p class="error-text">エラー: <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <p><a href="../shop/dsp_shop.php">店舗一覧に戻る</a></p>
<?php elseif (empty($shops)): ?>
    <p>店舗情報を読み込んでいます...</p>
<?php else: ?>
    <!-- 売上登録フォーム -->
    <form action="add_sales.php" method="post" id="salesForm">
        <p>
            店舗ID:
            <select name="shop_id" id="shop_id" required>
                <option value="">店舗を選択</option>
                <?php foreach ($shops as $shop): ?>
                    <option value="<?= htmlspecialchars($shop['sh_id'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($shop['sh_id'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span id="shopStatus" class="item-status"></span>
        </p>

        <!-- 予約チェック機能 -->
        <div class="reservation-check-box">
            <h3>予約確認</h3>
            <p>
                学生名:
                <input type="text" id="student_name" placeholder="学生の名前を入力">
                <button type="button" id="checkReservationBtn" onclick="checkReservation()" disabled>予約チェック</button>
                <span id="reservationStatus" class="item-status"></span>
            </p>
            <div id="reservationInfo" class="reservation-info">
                <!-- 予約情報がここに表示される -->
            </div>
            <div id="multipleStudents" class="multiple-students">
                <!-- 複数の学生候補がここに表示される -->
            </div>
        </div>

        <!-- 予約IDを保持する隠しフィールド -->
        <input type="hidden" name="reserve_id" id="reserve_id" value="">
        <input type="hidden" name="student_id" id="student_id" value="">

        <div class="sales-input-table">
            <!-- テーブルヘッダー -->
            <div class="sales-header">
                <div>商品名</div>
                <div>数量</div>
                <div>単価</div>
                <div>値引き額</div>
            </div>

            <!-- データ行 -->
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="sales-row">
                <div class="sales-cell">
                    <select name="si_id_<?= $i ?>" id="si_id_<?= $i ?>" onchange="updatePrice(<?= $i ?>)" disabled>
                        <option value="">まず店舗を選択してください</option>
                    </select>
                </div>
                <div class="sales-cell">
                    <input type="number" name="num_<?= $i ?>" id="num_<?= $i ?>" min="1">
                </div>
                <div class="sales-cell">
                    <input type="number" name="i_price_<?= $i ?>" id="i_price_<?= $i ?>" min="0" step="1" readonly>
                </div>
                <div class="sales-cell">
                    <input type="number" name="disc_<?= $i ?>" id="disc_<?= $i ?>" min="0" step="1" value="0">
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <p>
            <input type="submit" value="売上を登録">
        </p>
    </form>

    <!-- トースト通知コンテナ -->
    <div id="toastContainer" class="toast-container" aria-live="polite"></div>

    <script>
    // 商品価格データを保持するグローバル変数
    let itemPrices = {};
    // 予約情報を保持するグローバル変数
    let currentReservation = null;

    // トースト通知を表示する関数
    function showToast(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;

        const icons = { success: '\u2714', error: '\u2718', warning: '\u26A0', info: '\u2139' };
        toast.innerHTML =
            '<span class="toast-icon">' + (icons[type] || icons.info) + '</span>' +
            '<span class="toast-message">' + escapeHtml(message) + '</span>' +
            '<button class="toast-close" onclick="this.parentElement.remove()">&times;</button>';

        container.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'toast-out 0.3s ease-in forwards';
            toast.addEventListener('animationend', () => toast.remove());
        }, duration);
    }

    // 商品選択時に単価を自動入力する関数
    function updatePrice(index) {
        const itemSelect = document.getElementById('si_id_' + index);
        const priceInput = document.getElementById('i_price_' + index);
        const numInput = document.getElementById('num_' + index);
        const discInput = document.getElementById('disc_' + index);
        const itemId = itemSelect.value;

        if (itemId && itemPrices[itemId]) {
            priceInput.value = itemPrices[itemId];
            numInput.value = ''; // 数量をリセット
            discInput.value = '0'; // 値引き額をリセット
        } else {
            priceInput.value = '';
            numInput.value = '';
            discInput.value = '0';
        }
    }

    // 予約チェック機能
    function checkReservation() {
        const studentName = document.getElementById('student_name').value.trim();
        const shopId = document.getElementById('shop_id').value;
        const reservationStatus = document.getElementById('reservationStatus');
        const reservationInfo = document.getElementById('reservationInfo');
        const multipleStudents = document.getElementById('multipleStudents');
        const btn = document.getElementById('checkReservationBtn');

        if (!studentName) {
            showToast('学生名を入力してください', 'warning');
            return;
        }

        if (!shopId) {
            showToast('先に店舗を選択してください', 'warning');
            return;
        }

        // ローディング表示（スピナー付き）
        btn.disabled = true;
        reservationStatus.innerHTML = '<span class="spinner"></span> 予約を確認中...';
        reservationStatus.className = 'loading';
        reservationInfo.style.display = 'none';
        multipleStudents.style.display = 'none';

        // Ajaxで予約情報を取得
        fetch('../reserve/check_reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'student_name=' + encodeURIComponent(studentName) + '&shop_id=' + encodeURIComponent(shopId)
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            reservationStatus.innerHTML = '';
            if (data.success) {
                if (data.multiple_students) {
                    // 複数の学生が見つかった場合
                    displayMultipleStudents(data.students, shopId);
                    showToast(data.message, 'warning');
                } else if (data.has_reservation) {
                    // 予約が見つかった場合
                    displayReservation(data);
                    showToast('予約情報を取得しました', 'success');
                } else {
                    // 予約が見つからなかった場合
                    clearReservation();
                    showToast(data.message, 'warning');
                }
            } else {
                showToast('エラー: ' + data.message, 'error');
            }
        })
        .catch(error => {
            btn.disabled = false;
            reservationStatus.innerHTML = '';
            console.error('Error:', error);
            showToast('通信エラーが発生しました', 'error');
        });
    }

    // 複数の学生候補を表示
    function displayMultipleStudents(students, shopId) {
        const multipleStudents = document.getElementById('multipleStudents');
        let html = '<h4>該当する学生を選択してください:</h4><ul class="multiple-students-list">';

        students.forEach(student => {
            html += '<li>' +
                    '<button type="button" onclick="selectStudent(\'' + student.st_id + '\', \'' +
                    escapeHtml(student.name) + '\', \'' + shopId + '\')">' +
                    escapeHtml(student.name) + ' (' + escapeHtml(student.class) + ')' +
                    '</button></li>';
        });

        html += '</ul>';
        multipleStudents.innerHTML = html;
        multipleStudents.style.display = 'block';
    }

    // 学生を選択したときの処理
    function selectStudent(studentId, studentName, shopId) {
        const reservationStatus = document.getElementById('reservationStatus');

        // ローディング表示（スピナー付き）
        reservationStatus.innerHTML = '<span class="spinner"></span> 予約を確認中...';
        reservationStatus.className = 'loading';
        document.getElementById('multipleStudents').style.display = 'none';

        // 学生IDで直接予約を検索
        fetch('../reserve/get_reservation_by_student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'student_id=' + encodeURIComponent(studentId) + '&shop_id=' + encodeURIComponent(shopId)
        })
        .then(response => response.json())
        .then(data => {
            reservationStatus.innerHTML = '';
            if (data.success && data.has_reservation) {
                displayReservation(data);
                showToast('予約情報を取得しました', 'success');
            } else {
                clearReservation();
                showToast(studentName + ' さんの予約はありません', 'warning');
            }
        })
        .catch(error => {
            reservationStatus.innerHTML = '';
            console.error('Error:', error);
            showToast('通信エラーが発生しました', 'error');
        });
    }

    // 予約情報を表示して自動入力
    function displayReservation(data) {
        currentReservation = data.reservation;

        // 予約情報を表示
        const reservationInfo = document.getElementById('reservationInfo');
        reservationInfo.innerHTML =
            '<strong>予約情報:</strong><br>' +
            '学生名: ' + escapeHtml(data.student_name) + '<br>' +
            '商品: ' + escapeHtml(data.reservation.i_name) + '<br>' +
            '数量: ' + data.reservation.num + '<br>' +
            '単価: ' + parseFloat(data.reservation.i_price).toLocaleString() + '円<br>' +
            '予約日時: ' + escapeHtml(data.reservation.datetime) + '<br>' +
            '<button type="button" onclick="applyReservation()" class="reservation-apply-btn">この予約内容を入力欄に反映</button> ' +
            '<button type="button" onclick="clearReservation()" class="reservation-clear-btn">クリア</button>';
        reservationInfo.style.display = 'block';

        // 予約IDと学生IDを隠しフィールドに設定
        document.getElementById('reserve_id').value = data.reservation.r_id;
        document.getElementById('student_id').value = data.student_id;
    }

    // 予約内容を入力欄に反映
    function applyReservation() {
        if (!currentReservation) return;

        // 最初の行に予約商品を設定
        const itemSelect = document.getElementById('si_id_0');
        const numInput = document.getElementById('num_0');
        const priceInput = document.getElementById('i_price_0');
        const discInput = document.getElementById('disc_0');

        // 商品を選択
        itemSelect.value = currentReservation.si_id;
        priceInput.value = currentReservation.i_price;
        numInput.value = currentReservation.num;
        discInput.value = '0';

        showToast('予約内容を1行目に反映しました', 'success');
    }

    // 予約情報をクリア
    function clearReservation() {
        currentReservation = null;
        document.getElementById('reservationInfo').style.display = 'none';
        document.getElementById('multipleStudents').style.display = 'none';
        document.getElementById('reserve_id').value = '';
        document.getElementById('student_id').value = '';
    }

    // HTMLエスケープ関数
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // 店舗IDのプルダウンが変更されたときの処理
    document.getElementById('shop_id').addEventListener('change', function() {
        const shopId = this.value;
        const shopStatus = document.getElementById('shopStatus');
        const checkReservationBtn = document.getElementById('checkReservationBtn');

        // 予約チェックボタンの有効/無効を切り替え
        if (shopId) {
            checkReservationBtn.disabled = false;
        } else {
            checkReservationBtn.disabled = true;
            clearReservation();
        }

        // 4つすべての商品セレクトボックスを取得
        const itemSelects = [];
        for (let i = 0; i < 4; i++) {
            itemSelects.push(document.getElementById('si_id_' + i));
        }

        // すべての商品プルダウンと入力欄をリセット
        itemSelects.forEach((itemSelect, i) => {
            itemSelect.innerHTML = '<option value="">読み込み中...</option>';
            itemSelect.disabled = true;
            document.getElementById('num_' + i).value = '';
            document.getElementById('i_price_' + i).value = '';
            document.getElementById('disc_' + i).value = '0';
        });
        shopStatus.textContent = '';
        itemPrices = {}; // 価格データをクリア

        if (!shopId) {
            itemSelects.forEach(itemSelect => {
                itemSelect.innerHTML = '<option value="">まず店舗を選択してください</option>';
            });
            return;
        }

        // ローディング表示（スピナー付き）
        shopStatus.innerHTML = '<span class="spinner"></span> 商品を読み込んでいます...';
        shopStatus.className = 'loading';

        // Ajaxで商品データを取得
        fetch('../item/get_items.php?shop_id=' + encodeURIComponent(shopId))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.items.length > 0) {
                        // 商品価格データを保存（si_idをキーにする）
                        data.items.forEach(item => {
                            itemPrices[item.si_id] = item.i_price;
                        });

                        // すべての商品プルダウンに同じ商品リストを設定
                        itemSelects.forEach(itemSelect => {
                            // 商品プルダウンをクリア
                            itemSelect.innerHTML = '<option value="">商品を選択</option>';

                            // 商品をプルダウンに追加
                            data.items.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.si_id;  // si_id（店舗商品ID）を使用
                                option.textContent = item.i_name + ' (' + parseFloat(item.i_price).toLocaleString() + '円)';
                                itemSelect.appendChild(option);
                            });

                            itemSelect.disabled = false;
                        });

                        showToast('商品が読み込まれました (' + data.count + '件)', 'success');
                        shopStatus.innerHTML = '';
                    } else {
                        itemSelects.forEach(itemSelect => {
                            itemSelect.innerHTML = '<option value="">この店舗に商品がありません</option>';
                        });
                        showToast('この店舗に登録されている商品がありません', 'warning');
                        shopStatus.innerHTML = '';
                    }
                } else {
                    itemSelects.forEach(itemSelect => {
                        itemSelect.innerHTML = '<option value="">エラーが発生しました</option>';
                    });
                    showToast('エラー: ' + data.message, 'error');
                    shopStatus.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                itemSelects.forEach(itemSelect => {
                    itemSelect.innerHTML = '<option value="">エラーが発生しました</option>';
                });
                showToast('通信エラーが発生しました', 'error');
                shopStatus.innerHTML = '';
            });
    });
    </script>

    <nav class="page-nav" aria-label="ページナビゲーション">
        <a href="../common/index.html">メニューに戻る</a>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../common/footer.php'; ?>
