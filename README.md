# 学園祭売り上げシステム 技術仕様書

## 1. システム概要

本システムは、学園祭における店舗の商品販売を記録・管理するためのWebアプリケーションです。
商品管理（登録・一覧表示）、店舗商品管理、売上入力、売上データ表示、午前午後別売上表示、注文納品管理、予約管理など、包括的な売上管理機能を提供します。

### 主要機能
1. **商品管理**
   - 商品の登録（商品名、価格）
   - 商品一覧の表示

2. **店舗管理**
   - 店舗の登録（クラス、企画名、場所、年度）
   - 店舗一覧の表示

3. **店舗商品管理**
   - 店舗への商品割り当て（複数商品同時登録可能）
   - 店舗商品一覧の表示（店舗ごとにグループ化）
   - 店舗商品の削除

4. **売上管理**
   - 店舗選択（セッション管理、有効期限30分）
   - 売上登録（最大4商品まで同時入力可能、値引き対応）
   - Ajaxによる動的な商品リスト取得
   - 自動単価入力
   - 予約連携機能（予約チェック、自動反映）

5. **売上表示**
   - 店舗別売上一覧表示（総売上金額付き、年度フィルタ対応）
   - 売上詳細表示（店舗別、商品明細付き）
   - 午前・午後別売上集計表示（店舗ごと、年度フィルタ対応）

6. **注文納品管理**
   - 注文納品状況の表示（納品待ち一覧）
   - 納品処理（CSRF対策済み）
   - 納品状況の追跡

7. **予約システム**
   - 学生認証（学生ID + パスコード）
   - 店舗選択と商品予約
   - 予約一覧の閲覧（状態表示付き）
   - 予約検索機能（売上登録画面から学生名で検索）
   - 売上登録時の予約状態自動更新

8. **生徒管理**
   - 生徒一覧表示
   - 生徒検索機能

---

## 2. 画面遷移フロー

### 2.1 商品管理の流れ

#### 商品登録フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「商品登録」を選択
   ↓
2. views/item/inp_item.php（商品登録画面）
   ↓
   - 商品名を入力
   - 価格を入力
   - 「追加」ボタンをクリック
   ↓
3. views/item/add_item.php（登録処理）
   ↓
   - POSTデータを受信
   - ItemControllerで処理
   - データベースに登録
   ↓
4. views/item/dsp_item.php（商品一覧画面）
   ↓
   登録成功時、自動リダイレクトで商品一覧を表示
```

#### 商品一覧閲覧フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「商品一覧」を選択
   ↓
2. views/item/dsp_item.php（商品一覧画面）
   ↓
   - データベースから全商品を取得
   - テーブル形式で表示（商品ID、商品名、価格）
```

### 2.2 店舗管理の流れ

#### 店舗登録フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「店舗登録」を選択
   ↓
2. views/shop/inp_shop.php（店舗登録画面）
   ↓
   - クラスを入力
   - 企画名を入力
   - 場所を入力
   - 年度を入力
   - 「登録」ボタンをクリック
   ↓
3. views/shop/add_shop.php（登録処理）
   ↓
   - POSTデータを受信
   - ShopControllerで処理
   - データベースに登録
   ↓
4. views/shop/dsp_shop.php（店舗一覧画面）
   ↓
   登録成功時、自動リダイレクトで店舗一覧を表示
```

### 2.3 店舗商品管理の流れ

#### 店舗商品登録フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「店舗商品登録」を選択
   ↓
2. views/item/inp_shopitem.php（店舗商品登録画面）
   ↓
   - ShopItemController::getInputData()で店舗一覧と商品一覧を取得
   - 店舗をプルダウンから選択
   - 商品をチェックボックスで複数選択（最低1つ必須）
   - 「登録」ボタンをクリック
   ↓
3. views/item/add_shopitem.php（登録処理）
   ↓
   - POSTデータを受信（shop_id, item_ids[]）
   - ShopItemControllerで処理
   - データベースに登録（shopitemテーブルに複数行INSERT）
   ↓
4. views/item/dsp_shopitem.php（店舗商品一覧画面）
   ↓
   登録成功時、自動リダイレクトで店舗商品一覧を表示
```

### 2.4 売上管理の流れ

#### 売上登録フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「売上登録」を選択
   ↓
2. views/sales/inp_sales.php（売上入力画面）
   ↓
   - セッションから店舗情報を取得
   - セッション未設定の場合はShopController::selectShopId()で店舗選択処理
   - 店舗選択 → Ajax通信で商品一覧を取得（get_items.php）
   - 【オプション】学生名入力 → 予約チェック（check_reservation.php）
   - 予約情報があれば自動入力可能
   - 商品選択（最大4商品）→ 単価自動入力
   - 数量入力、値引き額入力
   - 「登録」ボタンをクリック
   ↓
3. views/sales/add_sales.php（登録処理）
   ↓
   - POSTデータ受信（shop_id, si_id_0〜3, num_0〜3, i_price_0〜3, disc_0〜3, reserve_id）
   - SalesControllerで処理
   - トランザクション開始
   - salesテーブルに販売情報を登録
   - sales_detailテーブルに明細情報を登録（最大4件）
   - reserve_idがある場合は予約状態を「来店(1)」に更新
   - トランザクションコミット
   ↓
4. views/sales/dsp_sales.php（売上一覧画面）
   ↓
   登録成功後、店舗別売上一覧を表示
```

### 2.5 売上表示の流れ

#### 売上一覧表示フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「売上表示」を選択
   ↓
2. views/sales/dsp_sales.php（売上一覧画面）
   ↓
   - SalesController::listSales()で店舗別売上集計を取得
   - 年度フィルタ機能（プルダウン選択）
   - 店舗ごとに売上合計を表示（降順）
   - 全店舗の総売上を表示
   - 各店舗名をクリック可能（sales_detail.phpへ遷移）
```

#### 売上詳細表示フロー

```
1. views/sales/dsp_sales.php（売上一覧画面）
   ↓
   ユーザーが特定の店舗名をクリック
   ↓
2. views/sales/sales_detail.php?sh_id=XX（売上詳細画面）
   ↓
   - SalesController::showDetail()で店舗の売上明細を取得
   - 売上日時、商品名、単価、数量、小計を一覧表示
   - 売上合計を表示
   - 「注文納品状況」リンク表示
   - 店舗の予約一覧も表示
```

### 2.6 午前午後別売上表示の流れ

```
1. index.html（トップページ）
   ↓
   ユーザーが「売上午前午後」を選択
   ↓
2. views/sales/dsp_shop_sales.php（午前午後別売上画面）
   ↓
   - SalesController::showSalesByPeriod()で午前午後別売上を取得
   - 年度フィルタ機能（プルダウン選択）
   - 店舗ごとに午前売上、午後売上、合計売上を表示
   - 全店舗の午前合計、午後合計、総合計を表示
```

### 2.7 注文納品管理の流れ

#### 注文納品状況確認フロー

```
1. views/sales/sales_detail.php?sh_id=XX（売上詳細画面）
   ↓
   ユーザーが「注文納品状況」リンクをクリック
   ↓
2. views/sales/order_status.php?sh_id=XX（注文納品状況画面）
   ↓
   - SalesController::showPending()で納品待ち注文を取得
   - 納品待ち一覧を表示（売上ID、注文日時、商品情報、金額）
   - 各注文に「納品」ボタンを表示（CSRFトークン付き）
```

#### 納品処理フロー

```
1. views/sales/order_status.php?sh_id=XX（注文納品状況画面）
   ↓
   ユーザーが「納品」ボタンをクリック
   ↓
2. views/sales/deliver_order.php（納品処理）
   ↓
   - POSTデータ受信（s_id, sh_id, csrf_token）
   - CSRFトークン検証
   - SalesController::deliverOrder()で納品処理
   - salesテーブルのsituation列を1に更新
   - トランザクションコミット
   ↓
3. views/sales/order_status.php?sh_id=XX（注文納品状況画面）
   ↓
   納品処理成功後、リダイレクトして更新された一覧を表示
```

### 2.8 予約システムの流れ

#### 学生側：予約登録フロー

```
1. views/auth/login.php（ログイン画面）
   ↓
   - 学生ID入力
   - パスコード入力
   - データベースで認証（studentテーブル）
   - 認証成功でセッションに学生ID保存
   ↓
2. views/auth/store_select.php（店舗選択画面）
   ↓
   - 現在年度の店舗一覧を表示
   - 店舗を選択
   ↓
3. views/item/products.php（商品一覧画面）
   ↓
   - 選択した店舗の商品一覧を取得
   - 商品選択、数量入力
   - 「予約」ボタンをクリック
   ↓
4. views/reserve/reserve.php（予約登録処理）
   ↓
   - ReserveController::create()で処理
   - reserveテーブルに登録（situation = 0: 予約中）
   - トランザクション処理
   ↓
5. views/reserve/my_reservations.php（予約一覧画面）
   ↓
   予約成功時、自動リダイレクトで予約一覧を表示
```

#### 学生側：予約一覧・キャンセルフロー

```
1. views/reserve/my_reservations.php（予約一覧画面）
   ↓
   - ReserveController::getByStudentLabeled()で全予約取得
   - 日時、企画名、場所、商品名、数量、状態を表示
   - 各予約に状態表示（予約中/来店/取消/完売）
   ↓
2. views/reserve/cancel.php（キャンセル処理）※キャンセルボタンクリック時
   ↓
   - ReserveController::cancel()で処理
   - situation を 2（取消）に更新
   ↓
3. views/reserve/my_reservations.php（予約一覧画面）
   ↓
   キャンセル成功後、更新された一覧を表示
```

#### 店舗側：予約確認・売上登録フロー

```
1. views/sales/inp_sales.php（売上登録画面）
   ↓
   - 学生名入力フィールド表示
   - 「予約チェック」ボタンをクリック（オプション）
   ↓
2. views/reserve/check_reservation.php（予約検索API）
   ↓
   - Ajax POST: student_name, shop_id
   - StudentDAO::searchStudentsByName()で学生検索
   - 複数該当時は候補リスト返却
   - 1人該当時は予約情報返却
   - JSON形式でレスポンス
   ↓
3. views/sales/inp_sales.php（売上登録画面）
   ↓
   - 予約情報を画面に表示（あれば）
   - 「この予約内容を入力欄に反映」ボタンで自動入力
   - 予約なしでも売上登録可能（外部来場者対応）
   - 商品選択、数量入力
   - reserve_id をhidden項目で送信（予約ありの場合）
   ↓
4. views/sales/add_sales.php（売上登録処理）
   ↓
   - SalesController::addSales()で売上登録
   - 売上登録成功後
   - reserve_id がある場合:
     ├─ ReserveController::updStatus()実行
     ├─ situation を 1（来店）に更新
     └─ 更新失敗時はロールバック
   ↓
5. views/sales/dsp_sales.php（売上一覧画面）
   ↓
   売上登録完了
```

### 2.9 Ajax通信フロー

#### 商品データ取得

```
views/sales/inp_sales.php（売上入力画面）
   ↓
   JavaScript: 店舗IDを取得
   ↓
   Fetch API: GET views/item/get_items.php?shop_id=XX
   ↓
views/item/get_items.php（商品データ取得API）
   ↓
   - ItemController::getItemsApi()でデータベースから商品一覧を取得
   - JSON形式でレスポンス
   ↓
views/sales/inp_sales.php（売上入力画面）
   ↓
   商品セレクトボックスに動的に選択肢を追加（4つすべてのプルダウンに反映）
```

#### 予約検索

```
views/sales/inp_sales.php（売上入力画面）
   ↓
   JavaScript: 学生名と店舗IDを取得
   ↓
   Fetch API: POST views/reserve/check_reservation.php
   ↓
views/reserve/check_reservation.php（予約検索API）
   ↓
   - StudentDAO::searchStudentsByName()で学生検索
   - ReserveController::getByStudentShop()で予約取得
   - JSON形式でレスポンス
   ↓
views/sales/inp_sales.php（売上入力画面）
   ↓
   - 複数学生候補の場合は選択UI表示
   - 予約情報表示、「入力欄に反映」ボタン表示
```

---

## 3. ページ構成と機能

### 3.1 views/common/index.html（トップページ）

**目的:** システムのエントリーポイント

**提供機能:**
- 商品登録画面への遷移（views/item/inp_item.php）
- 商品一覧画面への遷移（views/item/dsp_item.php）
- 売上登録画面への遷移（views/sales/inp_sales.php）
- 売上表示画面への遷移（views/sales/dsp_sales.php）
- 午前午後別売上画面への遷移（views/sales/dsp_shop_sales.php）
- 店舗商品登録画面への遷移（views/item/inp_shopitem.php）
- 店舗商品表示画面への遷移（views/item/dsp_shopitem.php）
- 店舗登録画面への遷移（views/shop/inp_shop.php）
- 店舗一覧画面への遷移（views/shop/dsp_shop.php）
- 予約画面への遷移（views/item/products.php）
- 予約確認画面への遷移（views/reserve/my_reservations.php）
- 生徒一覧画面への遷移（views/student/dsp_student.php）
- 生徒検索画面への遷移（views/student/st_search.php）

**技術仕様:**
- 静的HTMLページ
- 12のメインナビゲーションリンクを提供

---

### 3.2 views/item/inp_item.php（商品登録画面）

**目的:** 新規商品の登録

**機能詳細:**
- 商品名の入力フォーム
- 価格の入力フォーム
- 入力値のバリデーション（required属性）
- 登録完了後は商品一覧画面へ自動遷移

**入力項目:**
- 商品名（必須）
- 価格（必須）

**POST先:**
- views/item/add_item.php（商品追加処理）

**遷移先:**
- 成功時: views/item/dsp_item.php（商品一覧画面）
- エラー時: エラーメッセージを表示

---

### 3.3 views/item/add_item.php（商品追加処理）

**目的:** 商品データのデータベース登録

**処理フロー:**
1. POSTデータの受信と検証
2. ItemControllerに処理を委譲
3. データベースへの登録
4. 成功時は商品一覧画面へリダイレクト
5. 失敗時はエラーメッセージを表示

**データ登録仕様:**

#### itemテーブル
- 商品ID: 自動採番
- 商品名: POSTデータから取得
- 価格: POSTデータから取得

---

### 3.4 views/item/dsp_item.php（商品一覧画面）

**目的:** 登録済み商品の一覧表示

**機能詳細:**
- データベースから全商品を取得
- テーブル形式で商品情報を表示
- 商品ID、商品名、価格の3列表示

**表示データ:**
- 商品ID（i_id）
- 商品名（i_name）
- 価格（i_price）

**技術仕様:**
- ItemControllerを使用してデータを取得
- XSS対策としてhtmlspecialchars()でエスケープ処理
- header.phpとfooter.phpを使用した共通レイアウト

---

### 3.5 views/shop/inp_shop.php（店舗登録画面）

**目的:** 新規店舗の登録

**機能詳細:**
- クラス、企画名、場所、年度の入力フォーム
- 入力値のバリデーション
- 登録完了後は店舗一覧画面へ自動遷移

**入力項目:**
- クラス（必須）
- 企画名（必須）
- 場所（必須）
- 年度（必須、デフォルトは現在年度）

**POST先:**
- views/shop/add_shop.php（店舗追加処理）

---

### 3.6 views/shop/dsp_shop.php（店舗一覧画面）

**目的:** 登録済み店舗の一覧表示

**機能詳細:**
- データベースから全店舗を取得
- テーブル形式で店舗情報を表示

**表示データ:**
- 店舗ID（sh_id）
- 年度（fy）
- クラス（class）
- 企画名（pr_name）
- 場所（place）

---

### 3.7 views/item/inp_shopitem.php（店舗商品登録画面）

**目的:** 店舗への商品割り当て

**機能詳細:**
- 店舗をプルダウンから選択
- 商品をチェックボックスで複数選択可能
- 最低1つの商品選択が必須
- ShopItemController::getInputData()で店舗一覧と商品一覧を取得

**入力項目:**
- 店舗ID（必須、プルダウン選択）
- 商品ID（複数選択可能、チェックボックス）

**POST先:**
- views/item/add_shopitem.php（店舗商品追加処理）

---

### 3.8 views/item/dsp_shopitem.php（店舗商品一覧画面）

**目的:** 店舗商品の一覧表示

**機能詳細:**
- データベースから全店舗商品を取得
- 店舗ごとにグループ化して表示
- 各店舗の商品リストをテーブル形式で表示
- 各商品に削除ボタンを表示

**表示データ:**
- 店舗ID（sh_id）
- 店舗名（class + pr_name）
- 商品ID（i_id）
- 商品名（i_name）
- 価格（i_price）
- 削除ボタン

---

### 3.9 views/sales/inp_sales.php（売上登録画面）

**目的:** 売上データの入力

**前提条件:**
- セッションに店舗情報が保存されている必要がある
- セッション未設定またはタイムアウト時は、ShopController::selectShopId()で店舗選択処理が実行される
- セッション有効期限は30分間（ShopController::checkSessionExpiry()でチェック）

**機能詳細:**
1. セッションから店舗データを取得し、プルダウンメニューに表示
2. 店舗ID選択時、Ajaxで該当店舗の商品一覧を動的取得（get_items.php経由）
3. 【オプション】学生名入力して予約チェック（check_reservation.php経由）
4. 予約情報があれば「入力欄に反映」ボタンで自動入力
5. 最大4行の売上明細を同時入力可能
6. 商品選択時に単価を自動入力
7. 各行に値引き額入力欄あり
8. 入力完了後、データベースに登録

**JavaScript処理:**
- `updatePrice(index)`: 商品選択時の単価自動入力
- `checkReservation()`: 予約検索API呼び出し
- `displayReservation(data)`: 予約情報表示
- `applyReservation()`: 予約内容を入力欄に反映
- `clearReservation()`: 予約情報クリア
- 店舗ID変更時のイベントリスナー: Fetch APIで商品データを取得し、4つすべての商品プルダウンに反映

**データ通信:**
- Ajax GET: views/item/get_items.php?shop_id=XX（商品一覧取得、JSON形式）
- Ajax POST: views/reserve/check_reservation.php（予約検索、JSON形式）
- POST: views/sales/add_sales.php（売上データ登録）

**入力項目:**
- 店舗ID: セッションから取得したリストより選択
- 商品 × 4行: 各行に商品名、数量、単価、値引き額（最大4件まで同時入力可能）
- 商品名: プルダウン選択（店舗IDに紐づく商品が動的に表示、si_idを使用）
- 数量: 数値入力（1以上）
- 単価: 読み取り専用（商品選択時に自動入力）
- 値引き額: 数値入力（デフォルト0）
- reserve_id: hidden項目（予約ありの場合に設定）
- student_id: hidden項目（予約ありの場合に設定）

---

### 3.10 views/item/get_items.php（商品データ取得API）

**目的:** Ajax通信による商品データ提供

**機能詳細:**
- GETパラメータで店舗IDを受け取る
- 指定店舗IDに紐づく商品一覧をItemControllerから取得
- JSON形式でデータを返却

**リクエスト:**
- メソッド: GET
- パラメータ: shop_id（店舗ID）

**レスポンス形式（JSON）:**
```json
{
  "success": true,
  "items": [
    {
      "si_id": "店舗商品ID",
      "i_name": "商品名",
      "i_price": "価格"
    }
  ],
  "count": 商品件数
}
```

**エラー時:**
```json
{
  "success": false,
  "message": "エラーメッセージ"
}
```

**技術仕様:**
- Content-Type: application/json
- ItemControllerのgetItemsByShopId()メソッドを使用

---

### 3.11 views/sales/add_sales.php（売上データ登録処理）

**目的:** 売上情報のデータベース登録

**処理フロー:**
1. SalesControllerのインスタンスを作成
2. SalesController::addSales()に処理を委譲
3. トランザクション内で以下を実行:
   - salesテーブルへの売上ヘッダー情報登録
   - sales_detailテーブルへの明細情報登録（最大4件、空欄は除外）
   - reserve_idがある場合は予約状態を「来店(1)」に更新
4. 成功時はコミット、失敗時はロールバック

**POSTデータ仕様:**
- shop_id: 店舗ID
- si_id_0 〜 si_id_3: 店舗商品ID（4行分）
- num_0 〜 num_3: 数量（4行分）
- i_price_0 〜 i_price_3: 単価（4行分）
- disc_0 〜 disc_3: 値引き額（4行分）
- reserve_id: 予約ID（オプション）
- student_id: 学生ID（オプション）

---

### 3.12 views/sales/dsp_sales.php（売上一覧画面）

**目的:** 店舗別売上一覧の表示

**機能詳細:**
- SalesController::listSales()で店舗別売上集計を取得
- 年度フィルタ機能（プルダウン選択）
- 店舗ごとの売上合計を降順で表示
- 全店舗の総売上金額を表示
- 各店舗名をクリックするとsales_detail.phpへ遷移

**表示データ:**
- 店舗ID（sh_id）
- 店舗名（class + pr_name）
- 売上合計金額
- 総売上金額
- 年度選択ドロップダウン

---

### 3.13 views/sales/sales_detail.php（売上詳細画面）

**目的:** 店舗別の売上明細表示

**機能詳細:**
- GETパラメータで店舗ID（sh_id）を受け取る
- SalesController::showDetail()で店舗の売上明細を取得
- 売上日時、商品名、単価、数量、小計を一覧表示
- 売上合計金額を表示
- 店舗の予約一覧も表示

**表示データ:**
- 店舗ID（sh_id）
- 店舗名（class + pr_name）
- 売上明細:
  - 売上日時（s_date）
  - 商品名（i_name）
  - 単価（price）
  - 数量（num）
  - 小計（subtotal = 単価 × 数量）
- 売上合計金額
- 予約一覧

---

### 3.14 views/sales/dsp_shop_sales.php（午前午後別売上画面）

**目的:** 店舗別の午前・午後売上集計表示

**機能詳細:**
- SalesController::showSalesByPeriod()で午前午後別売上を取得
- 年度フィルタ機能（プルダウン選択）
- 店舗ごとに午前売上、午後売上、合計売上を表示
- 全店舗の午前合計、午後合計、総合計を表示

**表示データ:**
- 店舗ID（sh_id）
- 店舗名（class + pr_name）
- 午前売上（morning_sales）
- 午後売上（afternoon_sales）
- 合計売上（total_sales）
- 全店舗の午前合計
- 全店舗の午後合計
- 総合計

---

### 3.15 views/sales/order_status.php（注文納品状況画面）

**目的:** 納品待ち注文の一覧表示

**機能詳細:**
- GETパラメータで店舗ID（sh_id）を受け取る
- SalesController::showPending()で納品待ち注文を取得
- 納品待ち一覧を表示（situation = 0の注文のみ）
- 各注文に「納品」ボタンを表示（CSRFトークン付き）

---

### 3.16 views/sales/deliver_order.php（納品処理）

**目的:** 注文の納品処理

**処理フロー:**
1. POSTデータの受信（s_id, sh_id, csrf_token）
2. CSRFトークンの検証
3. SalesController::deliverOrder()に処理を委譲
4. 売上IDと店舗IDの整合性チェック
5. 既に納品済みかチェック
6. salesテーブルのsituation列を1（納品済み）に更新
7. 成功時は注文納品状況画面へリダイレクト

---

### 3.17 views/auth/login.php（学生ログイン画面）

**目的:** 学生の認証

**機能詳細:**
- 学生IDとパスコードの入力フォーム
- データベースで認証（studentテーブル）
- 認証成功でセッションにstudent_idを保存
- 店舗選択画面へリダイレクト

**入力項目:**
- 学生ID（必須）
- パスコード（必須）

**遷移先:**
- 成功時: views/auth/store_select.php
- 失敗時: エラーメッセージを表示して同画面に留まる

---

### 3.18 views/auth/store_select.php（店舗選択画面）

**目的:** 予約対象の店舗選択

**前提条件:**
- 学生がログイン済みであること（セッションにstudent_idが存在）

**機能詳細:**
- 現在年度の店舗一覧を表示
- 各店舗をボタンで表示
- 選択するとproducts.phpへPOST送信

**表示データ:**
- 店舗一覧（class + pr_name）

---

### 3.19 views/item/products.php（商品一覧・予約画面）

**目的:** 店舗の商品一覧表示と予約入力

**前提条件:**
- 学生がログイン済みであること
- POSTで店舗ID（store_id）が送信されていること

**機能詳細:**
- 選択した店舗の情報を表示（クラス、企画名、場所）
- 店舗の商品一覧を取得して表示
- 各商品に数量入力欄と「予約」ボタンを表示

**表示データ:**
- 店舗情報（class, pr_name, place）
- 商品一覧（i_name, i_price）

**POST先:**
- views/reserve/reserve.php（各商品の予約ボタンから）

---

### 3.20 views/reserve/reserve.php（予約登録処理）

**目的:** 予約データのデータベース登録

**前提条件:**
- 学生がログイン済みであること

**処理フロー:**
1. セッションから学生IDを取得
2. POSTデータから店舗商品ID（store_product_id）と数量（quantity）を取得
3. ReserveController::create()に処理を委譲
4. 成功時は予約一覧画面へリダイレクト
5. 失敗時はエラーメッセージを表示

---

### 3.21 views/reserve/my_reservations.php（予約一覧画面）

**目的:** 学生の予約一覧表示

**前提条件:**
- 学生がログイン済みであること

**機能詳細:**
- ReserveController::getByStudentLabeled()で学生の全予約を取得
- 予約一覧をテーブル形式で表示
- 状態ラベル（予約中/来店/取消/完売）を表示

**表示データ:**
- 日時（datetime）
- 企画名（pr_name）
- 場所（place）
- 商品名（i_name）
- 数量（num）
- 状態（situation_label）

---

### 3.22 views/reserve/cancel.php（予約キャンセル処理）

**目的:** 予約のキャンセル

**前提条件:**
- 学生がログイン済みであること

**処理フロー:**
1. セッションから学生IDを取得
2. POSTデータから予約ID（reservation_id）を取得
3. ReserveController::cancel()に処理を委譲
4. 成功時は予約一覧画面へリダイレクト
5. 失敗時はエラーメッセージを表示

---

### 3.23 views/reserve/check_reservation.php（予約検索API）

**目的:** 学生名から予約を検索するJSON API

**リクエスト:**
- メソッド: POST
- パラメータ:
  - `student_name`: 学生名（部分一致検索）
  - `shop_id`: 店舗ID

**レスポンス形式（JSON）:**

**学生が見つからない場合:**
```json
{
  "success": true,
  "has_reservation": false,
  "message": "該当する学生が見つかりません"
}
```

**複数の学生が見つかった場合:**
```json
{
  "success": true,
  "multiple_students": true,
  "message": "複数の学生が見つかりました。学生を選択してください",
  "students": [
    {
      "st_id": 1001,
      "name": "山田太郎",
      "class": "3-A"
    }
  ]
}
```

**学生が1人見つかり、予約がない場合:**
```json
{
  "success": true,
  "has_reservation": false,
  "student_found": true,
  "student_name": "山田太郎",
  "student_id": 1001,
  "message": "山田太郎 さんの予約はありません"
}
```

**学生が1人見つかり、予約がある場合:**
```json
{
  "success": true,
  "has_reservation": true,
  "student_name": "山田太郎",
  "student_id": 1001,
  "message": "山田太郎 さんの予約が見つかりました",
  "reservation": {
    "r_id": 501,
    "si_id": 12,
    "i_name": "焼きそば",
    "i_price": 300,
    "num": 2,
    "datetime": "2025-10-15 10:30:00"
  }
}
```

---

### 3.24 views/reserve/get_reservation_by_student.php（学生ID指定予約検索API）

**目的:** 学生IDから予約を検索するJSON API

**リクエスト:**
- メソッド: POST
- パラメータ:
  - `student_id`: 学生ID
  - `shop_id`: 店舗ID

**レスポンス形式（JSON）:**
- check_reservation.phpと同様の形式

---

### 3.25 views/auth/logout.php（ログアウト処理）

**目的:** 学生のログアウト

**処理フロー:**
1. セッションを破棄
2. ログイン画面へリダイレクト

---

### 3.26 views/student/dsp_student.php（生徒一覧画面）

**目的:** 登録済み生徒の一覧表示

**機能詳細:**
- データベースから全生徒を取得
- テーブル形式で生徒情報を表示

---

### 3.27 views/student/st_search.php（生徒検索画面）

**目的:** 生徒の検索

**機能詳細:**
- 生徒名、クラスなどで検索
- 検索結果を表示

---

## 4. セキュリティ対策

### 4.1 SQLインジェクション対策
- プリペアドステートメントを使用
- ユーザー入力値を直接SQL文に埋め込まない
- DAOレイヤーでパラメータバインディングを徹底

### 4.2 XSS対策
- 出力時に`htmlspecialchars()`で特殊文字をエスケープ
- ENT_QUOTES フラグを使用
- すべてのユーザー入力データを表示前にエスケープ

### 4.3 CSRF対策
- utils/CsrfToken.phpでトークン生成・検証
- セッションベースのトークン管理
- 納品処理などの重要な操作でトークン検証を実施
- トークンはフォーム送信時にhidden inputで送信

### 4.4 トランザクション管理
- データ整合性を保つためトランザクションを使用
- エラー時は必ずロールバック
- 複数テーブルへの登録は必ずトランザクション内で実行

### 4.5 入力値検証
- 数値項目は負の値を許可しない
- 必須項目のチェック
- データ型の検証
- 商品未選択時のエラー処理

### 4.6 セッション管理
- セッション有効期限を30分に設定
- 期限切れ時は再選択を要求
- ShopController::checkSessionExpiry()で有効期限チェック

### 4.7 アクセス制御
- 売上IDと店舗IDの整合性チェック
- 他店舗の注文を誤って納品できないように検証
- 納品済み注文の重複処理を防止
- 学生は自分の予約のみ操作可能

---

## 5. データベース構造

### 5.1 salesテーブル（販売情報）

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| s_id | INT | 販売ID（主キー、自動採番） |
| s_date | DATETIME | 販売日時 |
| sh_id | INT | 店舗ID（外部キー） |
| situation | TINYINT | 納品状況（0: 未納品、1: 納品済み） |

### 5.2 sales_detailテーブル（販売明細）

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| sd_id | INT | 明細ID（主キー、自動採番） |
| s_id | INT | 販売ID（外部キー） |
| si_id | INT | 店舗商品ID（外部キー） |
| num | INT | 数量 |
| price | DECIMAL | 単価 |
| disc | DECIMAL | 値引き額 |

### 5.3 itemテーブル（商品情報）

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| i_id | INT | 商品ID（主キー、自動採番） |
| i_name | VARCHAR | 商品名 |
| i_price | DECIMAL | 標準価格 |

### 5.4 shopテーブル（店舗情報）

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| sh_id | INT | 店舗ID（主キー） |
| fy | INT | 年度 |
| class | VARCHAR | クラス名 |
| pr_name | VARCHAR | 企画名 |
| place | VARCHAR | 場所 |

### 5.5 shopitemテーブル（店舗商品関連）

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| si_id | INT | 店舗商品ID（主キー、自動採番） |
| sh_id | INT | 店舗ID（外部キー） |
| i_id | INT | 商品ID（外部キー） |

### 5.6 reserveテーブル（予約情報）

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| r_id | INT | 予約ID（主キー、自動採番） |
| datetime | DATETIME | 予約日時 |
| st_id | INT | 学生ID（外部キー） |
| si_id | INT | 店舗商品ID（外部キー） |
| num | INT | 数量 |
| situation | TINYINT | 予約状態（0:予約中, 1:来店, 2:取消, 3:完売） |

### 5.7 studentテーブル（学生情報）

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| st_id | INT | 学生ID（主キー） |
| class | VARCHAR | クラス |
| name | VARCHAR | 学生名 |
| kana | VARCHAR | 学生名かな |
| pasc | VARCHAR | パスコード |

**テーブル関連:**
- shopitem.sh_id → shop.sh_id（多対一）
- shopitem.i_id → item.i_id（多対一）
- sales.sh_id → shop.sh_id（多対一）
- sales_detail.s_id → sales.s_id（多対一）
- sales_detail.si_id → shopitem.si_id（多対一）
- reserve.st_id → student.st_id（多対一）
- reserve.si_id → shopitem.si_id（多対一）

---

## 6. ファイル構成

### 6.1 設定ファイル（config/）
- `dbConnection.php` - データベース接続処理
- `init.php` - 初期化処理（セッション開始等）
- `settings.php` - アプリケーション設定（現在年度 CURRENT_FY 等）

### 6.2 画面ファイル（views/）

#### views/common/
- `index.html` - トップページ（メニュー画面）
- `header.php` - HTMLヘッダー部分
- `footer.php` - HTMLフッター部分
- `css/sales.css` - スタイルシート

#### views/auth/
- `login.php` - 学生ログイン画面
- `logout.php` - ログアウト処理
- `store_select.php` - 店舗選択画面

#### views/item/
- `inp_item.php` - 商品登録画面
- `add_item.php` - 商品データ登録処理
- `dsp_item.php` - 商品一覧画面
- `inp_shopitem.php` - 店舗商品登録画面
- `add_shopitem.php` - 店舗商品データ登録処理
- `dsp_shopitem.php` - 店舗商品一覧画面
- `get_items.php` - 商品データ取得API（Ajax用）
- `products.php` - 商品一覧・予約画面

#### views/shop/
- `inp_shop.php` - 店舗登録画面
- `add_shop.php` - 店舗データ登録処理
- `dsp_shop.php` - 店舗一覧画面

#### views/sales/
- `inp_sales.php` - 売上登録画面
- `add_sales.php` - 売上データ登録処理
- `dsp_sales.php` - 売上一覧画面（店舗別集計）
- `sales_detail.php` - 売上詳細画面（店舗別明細）
- `dsp_shop_sales.php` - 午前午後別売上画面
- `order_status.php` - 注文納品状況画面
- `deliver_order.php` - 納品処理

#### views/reserve/
- `reserve.php` - 予約登録処理
- `my_reservations.php` - 予約一覧画面
- `cancel.php` - 予約キャンセル処理
- `check_reservation.php` - 予約検索API
- `get_reservation_by_student.php` - 学生ID指定予約検索API

#### views/student/
- `dsp_student.php` - 生徒一覧画面
- `st_search.php` - 生徒検索画面
- `st_search_output.php` - 生徒検索結果表示

### 6.3 コントローラー（controllers/）
- `ItemController.php` - 商品管理のコントローラー層
- `ShopItemController.php` - 店舗商品管理のコントローラー層
- `SalesController.php` - 売上管理のコントローラー層
- `ShopController.php` - 店舗管理・セッション管理のコントローラー層
- `ReserveController.php` - 予約管理のコントローラー層
- `StudentController.php` - 学生管理のコントローラー層

### 6.4 モデル（models/）
- `ItemModel.php` - 商品データのビジネスロジック
- `ShopItemModel.php` - 店舗商品データのビジネスロジック
- `SalesModel.php` - 売上データのビジネスロジック
- `ShopModel.php` - 店舗データのビジネスロジック
- `ReserveModel.php` - 予約データのビジネスロジック
- `StudentModel.php` - 学生データのビジネスロジック

### 6.5 DAO（dao/）
- `BaseDAO.php` - 基底DAOクラス（データベース接続管理）
- `ItemDAO.php` - 商品データアクセス
- `ShopItemDAO.php` - 店舗商品データアクセス
- `SalesDAO.php` - 売上データアクセス
- `ShopDAO.php` - 店舗データアクセス
- `ReserveDAO.php` - 予約データアクセス
- `StudentDAO.php` - 学生データアクセス

### 6.6 DTO（dto/）
- `ItemDTO.php` - 商品データ転送オブジェクト
- `ShopItemDTO.php` - 店舗商品データ転送オブジェクト
- `SalesDTO.php` - 売上データ転送オブジェクト
- `SalesDetailDTO.php` - 売上明細データ転送オブジェクト
- `ShopDTO.php` - 店舗データ転送オブジェクト
- `ReserveDTO.php` - 予約データ転送オブジェクト
- `StudentDTO.php` - 学生データ転送オブジェクト

### 6.7 ユーティリティ（utils/）
- `CsrfToken.php` - CSRF対策トークン生成・検証クラス

### 6.8 ドキュメント
- `README.md` - 本仕様書

---

## 7. アーキテクチャ

### 7.1 設計パターン

本システムは**MVCアーキテクチャ**に**DTO（Data Transfer Object）パターン**と**DAO（Data Access Object）パターン**を組み合わせた多層アーキテクチャで設計されています。

#### レイヤー構成

```
┌─────────────────────────────────────┐
│  View（views/）                     │  ← ユーザーインターフェース
│  - HTML/PHP画面                     │
│  - JavaScriptによるクライアント処理 │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  Controller（controllers/）         │  ← リクエスト処理・制御
│  - ItemController                   │
│  - SalesController                  │
│  - ShopController                   │
│  - ReserveController                │
│  - StudentController                │
│  - ShopItemController               │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  Model（models/）                   │  ← ビジネスロジック
│  - ItemModel                        │
│  - SalesModel                       │
│  - ShopModel                        │
│  - ReserveModel                     │
│  - StudentModel                     │
│  - ShopItemModel                    │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  DAO（dao/）                        │  ← データアクセス抽象化
│  - BaseDAO                          │
│  - ItemDAO                          │
│  - SalesDAO                         │
│  - ShopDAO                          │
│  - ReserveDAO                       │
│  - StudentDAO                       │
│  - ShopItemDAO                      │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  Database（MySQL）                  │  ← データ永続化
│  - item, sales, sales_detail, shop  │
│  - shopitem, reserve, student       │
└─────────────────────────────────────┘

        ↕ データ転送

┌─────────────────────────────────────┐
│  DTO（dto/）                        │  ← レイヤー間データ転送
│  - ItemDTO                          │
│  - SalesDTO                         │
│  - SalesDetailDTO                   │
│  - ShopDTO                          │
│  - ReserveDTO                       │
│  - StudentDTO                       │
│  - ShopItemDTO                      │
└─────────────────────────────────────┘
```

#### 各層の責務

**View（ビュー層）:**
- ユーザーインターフェースの表示
- ユーザー入力の受付
- Controllerへのリクエスト送信
- Ajaxによる非同期通信（get_items.php、check_reservation.php経由）

**Controller（コントローラー層）:**
- HTTPリクエストの受信
- 入力値の検証
- Modelの呼び出し
- View（画面遷移）の制御
- セッション管理
- トランザクション制御

**Model（モデル層）:**
- ビジネスロジックの実装
- DAOの呼び出し
- DTOの生成・操作
- データの変換・加工

**DAO（データアクセスオブジェクト層）:**
- データベースCRUD操作
- SQLクエリの実行
- プリペアドステートメントの使用
- トランザクション管理

**DTO（データ転送オブジェクト）:**
- レイヤー間のデータ転送
- データの型安全性を保証
- ドメインオブジェクトとしての役割

### 7.2 データフロー例（売上登録）

```
1. ユーザーがviews/sales/inp_sales.phpで売上を入力
   ↓
2. フォーム送信（POST） → views/sales/add_sales.php
   ↓
3. add_sales.php → SalesController::addSales()
   ↓
4. SalesController → SalesModel::createSales()
   ↓
5. SalesModel → SalesDAO::insert() / SalesDAO::insertDetail()
   ↓
6. SalesDAO → データベースへINSERT（トランザクション内）
   ↓
7. 予約連携：ReserveController::updStatus()で予約状態更新
   ↓
8. トランザクションコミット
   ↓
9. リダイレクト → views/sales/dsp_sales.php?s_id=XX
```

---

## 8. 技術仕様

### 8.1 使用技術
- **サーバーサイド:** PHP 7.x以上
- **データベース:** MySQL 5.7以上
- **フロントエンド:** HTML5, CSS3, JavaScript (ES6)
- **通信:** Fetch API（Ajax通信）
- **データ形式:** JSON
- **アーキテクチャ:** MVC + DTO + DAO

### 8.2 ブラウザ要件
- モダンブラウザ（Chrome, Firefox, Edge, Safari最新版）
- JavaScript有効化必須

### 8.3 サーバー要件
- Apache 2.4以上（XAMPP推奨）
- PHP 7.x以上
- MySQL 5.7以上
- セッション機能有効化

---

## 9. 予約状態の遷移

予約には4つの状態（situation）があります。

| 状態値 | 状態名 | 説明 | 遷移元 |
|-------|--------|------|--------|
| 0 | 予約中 | 予約完了、来店待ち | 初期状態 |
| 1 | 来店 | 来店確認済み、商品受け取り完了 | 売上登録時に自動更新 |
| 2 | 取消 | 学生によるキャンセル | 学生がキャンセル操作 |
| 3 | 完売 | 在庫切れにより予約無効 | （将来実装予定） |

#### 状態遷移図
```
[予約中:0] ─────売上登録────→ [来店:1]
    │
    └─────キャンセル────→ [取消:2]

（将来実装）
[予約中:0] ─────完売処理────→ [完売:3]
```

---

## 10. 注意事項

### 10.1 セッションタイムアウト
- 店舗選択後30分間で自動的にセッションが切れます
- セッション切れ後は再度店舗選択が必要です
- ShopController::checkSessionExpiry()で有効期限チェックを実施

### 10.2 データ入力
- 数量は正の数値のみ入力可能
- 売上登録時は商品を最低1件以上選択が必要
- 店舗商品登録時は商品を最低1つ選択が必要
- 値引き額は0以上の数値

### 10.3 トランザクション
- 登録処理中はブラウザの「戻る」ボタンを使用しないでください
- エラー発生時は自動的にロールバックされます
- 複数テーブルへの登録は必ずトランザクション内で実行
- 売上登録と予約状態更新は同一トランザクション内で実行

### 10.4 納品処理
- 納品処理はCSRFトークンで保護されています
- 他店舗の注文を誤って納品できないように整合性チェックを実施
- 既に納品済みの注文は再度納品できません

### 10.5 予約と売上の連携
- 売上登録時に予約IDを指定すると、予約状態が自動的に「来店」に更新されます
- 予約更新に失敗した場合は売上登録もロールバックされます
- 外部来場者（予約なし）でも売上登録は可能です

### 10.6 年度管理
- config/settings.phpでCURRENT_FYを設定
- 店舗選択や売上表示で年度フィルタが適用されます
- 毎年4月に年度を更新してください

---

## 11. 今後の拡張可能性

### 11.1 機能追加案
- ユーザー認証機能（店舗スタッフ向け）
- 売上集計・分析機能（日別、週別、月別）
- CSV/Excel出力機能
- 販売データの編集・削除機能
- 在庫管理機能（在庫数追跡、在庫アラート）
- QRコードによる商品管理
- モバイルアプリ対応
- 完売機能（在庫連携）
- 予約通知機能（メール、LINE）
- 予約時間枠管理

---

## 12. バージョン情報

- **作成日:** 2025-12-12
- **更新日:** 2026-01-26
- **バージョン:** 5.0
- **文書形式:** 技術仕様書
- **変更履歴:**
  - v1.0 (2025-12-12): 初版作成
  - v2.0 (2026-01-14): 商品管理機能の遷移フロー追加、アーキテクチャ図追加、ファイル構成の詳細化
  - v3.0 (2026-01-17): 全機能の完全な記載、店舗商品管理機能追加、注文納品管理機能追加
  - v4.0 (2026-01-22): 予約システム追加、予約と売上の連携機能実装
  - v5.0 (2026-01-26): 実際のコードベースに合わせて全面改訂
    - ディレクトリ構造の更新（views内のサブディレクトリ整理）
    - SelectShId.php廃止、ShopControllerへの機能統合を反映
    - shopテーブルにfy列、place列追加を反映
    - shopitemテーブル名の修正（shop_item → shopitem）
    - sales_detailテーブルにsi_id、disc列追加を反映
    - 店舗登録機能の追加（inp_shop.php、dsp_shop.php、add_shop.php）
    - 生徒検索機能の追加（st_search.php、st_search_output.php）
    - 年度フィルタ機能の追加（settings.phpのCURRENT_FY）
    - 値引き機能の追加
    - SalesControllerメソッド名の更新
    - get_reservation_by_student.phpの追加
    - ファイル構成の全面更新
