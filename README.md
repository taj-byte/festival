# 学園祭売り上げシステム 技術仕様書

## 1. システム概要

本システムは、学園祭における店舗の商品販売を記録・管理するためのWebアプリケーションです。
商品管理（登録・一覧表示）、店舗商品管理、売上入力、売上データ表示、午前午後別売上表示、注文納品管理など、包括的な売上管理機能を提供します。

### 主要機能
1. **商品管理**
   - 商品の登録（商品名、価格）
   - 商品一覧の表示

2. **店舗商品管理**
   - 店舗への商品割り当て（複数商品同時登録可能）
   - 店舗商品一覧の表示（店舗ごとにグループ化）
   - 店舗商品の削除

3. **売上管理**
   - 店舗選択（セッション管理、有効期限30分）
   - 売上登録（最大4商品まで同時入力可能）
   - Ajaxによる動的な商品リスト取得
   - 自動単価入力・合計金額計算

4. **売上表示**
   - 店舗別売上一覧表示（総売上金額付き）
   - 売上詳細表示（店舗別、商品明細付き）
   - 午前・午後別売上集計表示（店舗ごと）

5. **注文納品管理**
   - 注文納品状況の表示（納品待ち一覧）
   - 納品処理（CSRF対策済み）
   - 納品状況の追跡

---

## 2. 画面遷移フロー

### 2.1 商品管理の流れ

#### 商品登録フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「商品登録」を選択
   ↓
2. inp_item.php（商品登録画面）
   ↓
   - 商品名を入力
   - 価格を入力
   - 「追加」ボタンをクリック
   ↓
3. add_item.php（登録処理）
   ↓
   - POSTデータを受信
   - ItemControllerで処理
   - データベースに登録
   ↓
4. dsp_item.php（商品一覧画面）
   ↓
   登録成功時、自動リダイレクトで商品一覧を表示
```

#### 商品一覧閲覧フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「商品一覧」を選択
   ↓
2. dsp_item.php（商品一覧画面）
   ↓
   - データベースから全商品を取得
   - テーブル形式で表示（商品ID、商品名、価格）
```

### 2.2 店舗商品管理の流れ

#### 店舗商品登録フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「店舗商品登録」を選択
   ↓
2. inp_shopitem.php（店舗商品登録画面）
   ↓
   - ShopItemController::getInputData()で店舗一覧と商品一覧を取得
   - 店舗をプルダウンから選択
   - 商品をチェックボックスで複数選択（最低1つ必須）
   - 「登録」ボタンをクリック
   ↓
3. add_shopitem.php（登録処理）
   ↓
   - POSTデータを受信（shop_id, item_ids[]）
   - ShopItemControllerで処理
   - データベースに登録（shop_itemテーブルに複数行INSERT）
   ↓
4. dsp_shopitem.php（店舗商品一覧画面）
   ↓
   登録成功時、自動リダイレクトで店舗商品一覧を表示
```

#### 店舗商品一覧閲覧フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「店舗商品表示」を選択
   ↓
2. dsp_shopitem.php（店舗商品一覧画面）
   ↓
   - ShopItemController::displayShopItems()で全店舗商品を取得
   - 店舗ごとにグループ化して表示
   - 各店舗の商品リストを一覧表示
   - 削除ボタン表示（各商品に対して）
```

### 2.3 売上管理の流れ

#### 売上登録フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「売上登録」を選択
   ↓
2. inp_sales.php（売上入力画面）
   ↓
   - セッションから店舗IDリストを取得（未設定の場合はSelectShId.phpへリダイレクト）
   - 店舗選択 → Ajax通信で商品一覧を取得（get_items.php）
   - 商品選択（最大4商品）→ 単価自動入力
   - 数量入力 → 合計金額自動計算
   - 「登録」ボタンをクリック
   ↓
3. add_sales.php（登録処理）
   ↓
   - POSTデータ受信（shop_id, item_id_0～3, num_0～3, i_price_0～3）
   - SalesControllerで処理
   - トランザクション開始
   - salesテーブルに販売情報を登録
   - sales_detailテーブルに明細情報を登録（最大4件）
   - トランザクションコミット
   ↓
4. dsp_sales.php（売上一覧画面）
   ↓
   登録成功後、店舗別売上一覧を表示
```

### 2.4 売上表示の流れ

#### 売上一覧表示フロー

```
1. index.html（トップページ）
   ↓
   ユーザーが「売上表示」を選択
   ↓
2. dsp_sales.php（売上一覧画面）
   ↓
   - SalesController::displaySales()で店舗別売上集計を取得
   - 店舗ごとに売上合計を表示（降順）
   - 全店舗の総売上を表示
   - 各店舗名をクリック可能（sales_detail.phpへ遷移）
```

#### 売上詳細表示フロー

```
1. dsp_sales.php（売上一覧画面）
   ↓
   ユーザーが特定の店舗名をクリック
   ↓
2. sales_detail.php?sh_id=XX（売上詳細画面）
   ↓
   - SalesController::displaySalesDetail()で店舗の売上明細を取得
   - 売上日時、商品名、単価、数量、小計を一覧表示
   - 売上合計を表示
   - 「注文納品状況」リンク表示
```

### 2.5 午前午後別売上表示の流れ

```
1. index.html（トップページ）
   ↓
   ユーザーが「売上午前午後」を選択
   ↓
2. dsp_shop_sales.php（午前午後別売上画面）
   ↓
   - SalesController::displayShopSalesByTime()で午前午後別売上を取得
   - 店舗ごとに午前売上、午後売上、合計売上を表示
   - 全店舗の午前合計、午後合計、総合計を表示
```

### 2.6 注文納品管理の流れ

#### 注文納品状況確認フロー

```
1. sales_detail.php?sh_id=XX（売上詳細画面）
   ↓
   ユーザーが「注文納品状況」リンクをクリック
   ↓
2. order_status.php?sh_id=XX（注文納品状況画面）
   ↓
   - SalesController::displayPendingOrders()で納品待ち注文を取得
   - 納品待ち一覧を表示（売上ID、注文日時、商品情報、金額）
   - 各注文に「納品」ボタンを表示（CSRFトークン付き）
```

#### 納品処理フロー

```
1. order_status.php?sh_id=XX（注文納品状況画面）
   ↓
   ユーザーが「納品」ボタンをクリック
   ↓
2. deliver_order.php（納品処理）
   ↓
   - POSTデータ受信（s_id, sh_id, csrf_token）
   - CSRFトークン検証
   - SalesController::deliverOrder()で納品処理
   - salesテーブルのsituation列を1に更新
   - トランザクションコミット
   ↓
3. order_status.php?sh_id=XX（注文納品状況画面）
   ↓
   納品処理成功後、リダイレクトして更新された一覧を表示
```

### 2.7 Ajax通信フロー

```
inp_sales.php（売上入力画面）
   ↓
   JavaScript: 店舗IDを取得
   ↓
   Fetch API: GET get_items.php?shop_id=XX
   ↓
get_items.php（商品データ取得API）
   ↓
   - ItemController::getItemsApi()でデータベースから商品一覧を取得
   - JSON形式でレスポンス
   ↓
inp_sales.php（売上入力画面）
   ↓
   商品セレクトボックスに動的に選択肢を追加（4つすべてのプルダウンに反映）
```

---

## 3. ページ構成と機能

### 3.1 index.html（トップページ）

**目的:** システムのエントリーポイント

**提供機能:**
- 商品登録画面への遷移（inp_item.php）
- 商品一覧画面への遷移（dsp_item.php）
- 売上登録画面への遷移（inp_sales.php）
- 売上表示画面への遷移（dsp_sales.php）
- 午前午後別売上画面への遷移（dsp_shop_sales.php）
- 店舗商品登録画面への遷移（inp_shopitem.php）
- 店舗商品表示画面への遷移（dsp_shopitem.php）

**技術仕様:**
- 静的HTMLページ
- 7つのメインナビゲーションリンクを提供

---

### 3.2 inp_item.php（商品登録画面）

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
- add_item.php（商品追加処理）

**遷移先:**
- 成功時: dsp_item.php（商品一覧画面）
- エラー時: エラーメッセージを表示し、inp_item.phpへの「戻る」リンクを提供

**技術仕様:**
- header.phpとfooter.phpを使用した共通レイアウト
- フォームのmethod属性はPOST

---

### 3.3 add_item.php（商品追加処理）

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

**エラー処理:**
- 入力値エラー時はエラーメッセージを表示
- 「戻る」リンクでinp_item.phpへ遷移可能

**遷移先:**
- 成功時: dsp_item.php（自動リダイレクト）
- 失敗時: エラーメッセージ表示画面（戻るリンク付き）

---

### 3.4 dsp_item.php（商品一覧画面）

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

**画面内リンク:**
- 「メニューに戻る」ボタン: index.htmlへ遷移

---

### 3.5 inp_shopitem.php（店舗商品登録画面）

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
- add_shopitem.php（店舗商品追加処理）

**遷移先:**
- 成功時: dsp_shopitem.php（店舗商品一覧画面）
- エラー時: エラーメッセージを表示

**技術仕様:**
- header.phpとfooter.phpを使用した共通レイアウト
- フォームのmethod属性はPOST
- 商品はcheckbox配列（item_ids[]）として送信

---

### 3.6 add_shopitem.php（店舗商品追加処理）

**目的:** 店舗商品データのデータベース登録

**処理フロー:**
1. POSTデータの受信と検証（shop_id, item_ids[]）
2. ShopItemControllerに処理を委譲
3. データベースへの登録（shop_itemテーブルに複数行INSERT）
4. 成功時は店舗商品一覧画面へリダイレクト
5. 失敗時はエラーメッセージを表示

**データ登録仕様:**

#### shop_itemテーブル
- si_id: 自動採番（主キー）
- sh_id: POSTデータから取得
- i_id: POSTデータから取得（複数件）

**エラー処理:**
- 商品未選択時はエラーメッセージを表示
- データベースエラー時はエラーメッセージを表示
- 「戻る」リンクでinp_shopitem.phpへ遷移可能

**遷移先:**
- 成功時: dsp_shopitem.php（自動リダイレクト）
- 失敗時: エラーメッセージ表示画面（戻るリンク付き）

---

### 3.7 dsp_shopitem.php（店舗商品一覧画面）

**目的:** 店舗商品の一覧表示

**機能詳細:**
- データベースから全店舗商品を取得
- 店舗ごとにグループ化して表示
- 各店舗の商品リストをテーブル形式で表示
- 各商品に削除ボタンを表示

**表示データ:**
- 店舗ID（sh_id）
- 店舗名（sh_name）
- 商品ID（i_id）
- 商品名（i_name）
- 価格（i_price）
- 削除ボタン

**技術仕様:**
- ShopItemControllerを使用してデータを取得
- XSS対策としてhtmlspecialchars()でエスケープ処理
- 店舗ごとにセクション分けして表示
- header.phpとfooter.phpを使用した共通レイアウト

**画面内リンク:**
- 「店舗商品登録」ボタン: inp_shopitem.phpへ遷移
- 「メニューに戻る」ボタン: index.htmlへ遷移

---

### 3.8 SelectShId.php（店舗選択画面）

**目的:** 店舗の選択とセッション管理

**機能詳細:**
- データベースから店舗一覧を取得し表示
- 選択された店舗IDリストをセッションに保存
- セッション有効期限は30分間（1800秒）
- 選択後、売上入力画面へ自動遷移

**セッション管理:**
- `$_SESSION['sh_id']`: 店舗IDリストの配列
- `$_SESSION['sh_id_expires']`: 有効期限（Unixタイムスタンプ）

**遷移先:**
- inp_sales.php（売上登録画面）

---

### 3.6 inp_sales.php（売上登録画面）

**目的:** 売上データの入力

**前提条件:**
- セッションに店舗情報が保存されている必要がある
- セッション未設定またはタイムアウト時は、SelectShId.phpで店舗選択が必要
- セッション有効期限は10分間（ShopController::checkSessionExpiry()でチェック）

**機能詳細:**
1. セッションから店舗IDリストを取得し、プルダウンメニューに表示
2. 店舗ID選択時、Ajaxで該当店舗の商品一覧を動的取得（get_items.php経由）
3. 最大4行の売上明細を同時入力可能
4. 商品選択時に単価を自動入力（基本単価を保持）
5. 数量入力時に合計金額を自動計算（数量 × 基本単価）
6. 入力完了後、データベースに登録

**JavaScript処理:**
- `updatePrice(index)`: 商品選択時の単価自動入力と基本単価の保存
- `calculateTotal(index)`: 数量変更時の合計金額計算（数量 × 基本単価）
- 店舗ID変更時のイベントリスナー: Fetch APIで商品データを取得し、4つすべての商品プルダウンに反映

**データ通信:**
- Ajax GET: get_items.php?shop_id=XX（商品一覧取得、JSON形式）
- POST: add_sales.php（売上データ登録）

**入力項目:**
- 店舗ID: セッションから取得したリストより選択
- 商品 × 4行: 各行に商品名、数量、単価（最大4件まで同時入力可能）
- 商品名: プルダウン選択（店舗IDに紐づく商品が動的に表示）
- 数量: 数値入力（1以上、入力時に合計金額を自動計算）
- 単価: 読み取り専用（商品選択時に自動入力、数量入力時に合計金額へ自動変換）

**UI仕様:**
- テーブル風レイアウト（display:table使用）
- 店舗未選択時は商品プルダウンが無効化
- 商品読み込み中はローディングメッセージを表示
- 読み込み成功時は件数を表示（3秒後に自動で消える）

**遷移先:**
- 成功時: add_sales.php経由でdsp_sales.php?s_id=XX
- エラー時: add_sales.phpでエラーメッセージ表示

---

### 3.7 get_items.php（商品データ取得API）

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
      "i_id": "商品ID",
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

### 3.8 add_sales.php（売上データ登録処理）

**目的:** 売上情報のデータベース登録

**処理フロー:**
1. POSTデータの受信（店舗ID、商品ID × 4、数量 × 4、単価 × 4）
2. SalesControllerに処理を委譲
3. トランザクション開始
4. salesテーブルへの売上ヘッダー情報登録
5. sales_detailテーブルへの明細情報登録（最大4件、空欄は除外）
6. トランザクションコミット
7. 登録完了画面へリダイレクト

**POSTデータ仕様:**
- shop_id: 店舗ID
- item_id_0 〜 item_id_3: 商品ID（4行分）
- num_0 〜 num_3: 数量（4行分）
- i_price_0 〜 i_price_3: 合計金額（4行分）

**データ登録仕様:**

#### salesテーブル
- 売上ID（s_id）: 自動採番
- 売上日時（s_date）: 登録時のタイムスタンプ
- 店舗ID（sh_id）: POSTデータから取得

#### sales_detailテーブル（複数件）
- 明細ID（sd_id）: 自動採番
- 売上ID（s_id）: 外部キー（salesテーブルのs_id）
- 商品ID（i_id）: POSTデータから取得
- 数量（num）: POSTデータから取得
- 単価（price）: POSTデータから取得（合計金額 ÷ 数量で逆算も可）

**エラー処理:**
- 入力値検証エラー時はエラーメッセージを表示
- データベースエラー時はロールバック
- エラー時は「戻る」リンクでinp_sales.phpへ遷移可能

**遷移先:**
- 成功時: dsp_sales.php?s_id=登録した売上ID（自動リダイレクト）
- 失敗時: エラーメッセージ表示画面（戻るリンク付き）

---

### 3.11 dsp_sales.php（売上一覧画面）

**目的:** 店舗別売上一覧の表示

**機能詳細:**
- SalesController::displaySales()で店舗別売上集計を取得
- 店舗ごとの売上合計を降順で表示
- 全店舗の総売上金額を表示
- 各店舗名をクリックするとsales_detail.phpへ遷移

**表示データ:**
- 店舗ID（sh_id）
- 店舗名（class + pr_name）
- 売上合計金額
- 総売上金額

**画面内リンク:**
- 店舗名をクリック: sales_detail.php?sh_id=XXへ遷移
- 「売上新規登録」リンク: inp_sales.phpへ遷移
- 「メニューに戻る」リンク: index.htmlへ遷移

**技術仕様:**
- SalesControllerのdisplaySales()メソッドを使用
- extract()関数でコントローラーから返されたデータを展開
- number_format()で金額を3桁カンマ区切りで表示
- XSS対策としてhtmlspecialchars()でエスケープ処理

---

### 3.12 sales_detail.php（売上詳細画面）

**目的:** 店舗別の売上明細表示

**機能詳細:**
- GETパラメータで店舗ID（sh_id）を受け取る
- SalesController::displaySalesDetail()で店舗の売上明細を取得
- 売上日時、商品名、単価、数量、小計を一覧表示
- 売上合計金額を表示

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

**画面内リンク:**
- 「注文納品状況」リンク: order_status.php?sh_id=XXへ遷移
- 「売上一覧に戻る」リンク: dsp_sales.phpへ遷移
- 「メニューに戻る」リンク: index.htmlへ遷移

**技術仕様:**
- SalesControllerのdisplaySalesDetail()メソッドを使用
- extract()関数でコントローラーから返されたデータを展開
- number_format()で金額を3桁カンマ区切りで表示
- XSS対策としてhtmlspecialchars()でエスケープ処理

---

### 3.13 dsp_shop_sales.php（午前午後別売上画面）

**目的:** 店舗別の午前・午後売上集計表示

**機能詳細:**
- SalesController::displayShopSalesByTime()で午前午後別売上を取得
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

**画面内リンク:**
- 「メニューに戻る」リンク: index.htmlへ遷移

**技術仕様:**
- SalesControllerのdisplaySalesByTime()メソッドを使用
- extract()関数でコントローラーから返されたデータを展開
- number_format()で金額を3桁カンマ区切りで表示
- 午前/午後の判定は売上日時（s_date）の時刻で判定（12:00以前が午前、12:00以降が午後）

---

### 3.14 order_status.php（注文納品状況画面）

**目的:** 納品待ち注文の一覧表示

**機能詳細:**
- GETパラメータで店舗ID（sh_id）を受け取る
- SalesController::displayPendingOrders()で納品待ち注文を取得
- 納品待ち一覧を表示（situation = 0の注文のみ）
- 各注文に「納品」ボタンを表示（CSRFトークン付き）

**表示データ:**
- 店舗ID（sh_id）
- 店舗名（class + pr_name）
- 納品待ち注文:
  - 売上ID（s_id）
  - 注文日時（s_date）
  - 商品情報（items、カンマ区切りの商品名リスト）
  - 金額（total）
  - 納品ボタン

**フォーム:**
- 納品ボタン: deliver_order.phpへPOST送信
  - s_id（売上ID）
  - sh_id（店舗ID）
  - csrf_token（CSRFトークン）
  - 確認ダイアログ付き（JavaScript confirm）

**画面内リンク:**
- 「売上詳細に戻る」リンク: sales_detail.php?sh_id=XXへ遷移
- 「売上一覧に戻る」リンク: dsp_sales.phpへ遷移
- 「メニューに戻る」リンク: index.htmlへ遷移

**技術仕様:**
- SalesControllerのdisplayPendingOrders()メソッドを使用
- CsrfToken::generate()でCSRFトークンを生成
- extract()関数でコントローラーから返されたデータを展開
- number_format()で金額を3桁カンマ区切りで表示
- XSS対策としてhtmlspecialchars()でエスケープ処理

---

### 3.15 deliver_order.php（納品処理）

**目的:** 注文の納品処理

**処理フロー:**
1. POSTデータの受信（s_id, sh_id, csrf_token）
2. CSRFトークンの検証
3. SalesControllerに処理を委譲
4. 売上IDと店舗IDの整合性チェック
5. 既に納品済みかチェック
6. salesテーブルのsituation列を1（納品済み）に更新
7. 成功時は注文納品状況画面へリダイレクト

**エラー処理:**
- CSRFトークンが無効な場合はエラーメッセージを表示
- パラメータ不足時はエラーメッセージを表示
- 売上IDが存在しない場合はエラーメッセージを表示
- 店舗IDが一致しない場合はエラーメッセージを表示
- 既に納品済みの場合はエラーメッセージを表示
- データベースエラー時はエラーメッセージを表示

**遷移先:**
- 成功時: order_status.php?sh_id=XX（自動リダイレクト）
- 失敗時: エラーメッセージ表示画面（戻るリンク付き）

**セキュリティ:**
- CSRF対策としてCsrfToken::validate()でトークン検証
- 売上IDと店舗IDの整合性チェック
- 既に納品済みの場合は処理を拒否

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
| i_id | INT | 商品ID（外部キー） |
| num | INT | 数量 |
| price | DECIMAL | 単価 |

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
| class | VARCHAR | クラス名 |
| pr_name | VARCHAR | 企画名 |

### 5.5 shop_itemテーブル（店舗商品関連）

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| si_id | INT | 店舗商品ID（主キー、自動採番） |
| sh_id | INT | 店舗ID（外部キー） |
| i_id | INT | 商品ID（外部キー） |

**テーブル関連:**
- shop_item.sh_id → shop.sh_id（多対一）
- shop_item.i_id → item.i_id（多対一）
- sales.sh_id → shop.sh_id（多対一）
- sales_detail.s_id → sales.s_id（多対一）
- sales_detail.i_id → item.i_id（多対一）

---

## 6. ファイル構成

### 6.1 画面ファイル（views/）
- `index.html` - トップページ（メニュー画面）
- `inp_item.php` - 商品登録画面
- `dsp_item.php` - 商品一覧画面
- `inp_shopitem.php` - 店舗商品登録画面
- `dsp_shopitem.php` - 店舗商品一覧画面
- `SelectShId.php` - 店舗選択画面
- `inp_sales.php` - 売上登録画面
- `dsp_sales.php` - 売上一覧画面（店舗別集計）
- `sales_detail.php` - 売上詳細画面（店舗別明細）
- `dsp_shop_sales.php` - 午前午後別売上画面
- `order_status.php` - 注文納品状況画面

### 6.2 処理ファイル（views/）
- `add_item.php` - 商品データ登録処理
- `add_shopitem.php` - 店舗商品データ登録処理
- `add_sales.php` - 売上データ登録処理
- `deliver_order.php` - 納品処理
- `get_items.php` - 商品データ取得API（Ajax用）

### 6.3 コントローラー（controllers/）
- `ItemController.php` - 商品管理のコントローラー層
- `ShopItemController.php` - 店舗商品管理のコントローラー層
- `SalesController.php` - 売上管理のコントローラー層
- `ShopController.php` - 店舗管理・セッション管理のコントローラー層

### 6.4 モデル（models/）
- `ItemModel.php` - 商品データのビジネスロジック
- `ShopItemModel.php` - 店舗商品データのビジネスロジック
- `SalesModel.php` - 売上データのビジネスロジック
- `ShopModel.php` - 店舗データのビジネスロジック

### 6.5 DAO（dao/）
- `BaseDAO.php` - 基底DAOクラス（データベース接続管理）
- `ItemDAO.php` - 商品データアクセス
- `ShopItemDAO.php` - 店舗商品データアクセス
- `SalesDAO.php` - 売上データアクセス
- `ShopDAO.php` - 店舗データアクセス
- `StudentDAO.php` - 生徒データアクセス
- `ReserveDAO.php` - 予約データアクセス

### 6.6 DTO（dto/）
- `ItemDTO.php` - 商品データ転送オブジェクト
- `ShopItemDTO.php` - 店舗商品データ転送オブジェクト
- `SalesDTO.php` - 売上データ転送オブジェクト
- `SalesDetailDTO.php` - 売上明細データ転送オブジェクト
- `ShopDTO.php` - 店舗データ転送オブジェクト
- `StudentDTO.php` - 生徒データ転送オブジェクト
- `ReserveDTO.php` - 予約データ転送オブジェクト

### 6.7 ユーティリティ（utils/）
- `CsrfToken.php` - CSRF対策トークン生成・検証クラス

### 6.8 共通ファイル（views/）
- `header.php` - HTMLヘッダー部分
- `footer.php` - HTMLフッター部分
- `dbConnection.php` - データベース接続処理

### 6.9 ドキュメント
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
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  Model（models/）                   │  ← ビジネスロジック
│  - ItemModel                        │
│  - SalesModel                       │
│  - ShopModel                        │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  DAO（dao/）                        │  ← データアクセス抽象化
│  - BaseDAO                          │
│  - ItemDAO                          │
│  - SalesDAO                         │
│  - ShopDAO                          │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  Database（MySQL）                  │  ← データ永続化
│  - item, sales, sales_detail, shop  │
└─────────────────────────────────────┘

        ↕ データ転送

┌─────────────────────────────────────┐
│  DTO（dto/）                        │  ← レイヤー間データ転送
│  - ItemDTO                          │
│  - SalesDTO                         │
│  - SalesDetailDTO                   │
│  - ShopDTO                          │
└─────────────────────────────────────┘
```

#### 各層の責務

**View（ビュー層）:**
- ユーザーインターフェースの表示
- ユーザー入力の受付
- Controllerへのリクエスト送信
- Ajaxによる非同期通信（get_items.php経由）

**Controller（コントローラー層）:**
- HTTPリクエストの受信
- 入力値の検証
- Modelの呼び出し
- View（画面遷移）の制御
- セッション管理

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
1. ユーザーがinp_sales.phpで売上を入力
   ↓
2. フォーム送信（POST） → add_sales.php
   ↓
3. add_sales.php → SalesController::addSales()
   ↓
4. SalesController → SalesModel::registerSales()
   ↓
5. SalesModel → SalesDAO::insert() / SalesDAO::insertDetail()
   ↓
6. SalesDAO → データベースへINSERT（トランザクション内）
   ↓
7. SalesDAO → SalesDTO / SalesDetailDTOを生成
   ↓
8. DTO → Model → Controller → View
   ↓
9. リダイレクト → dsp_sales.php?s_id=XX
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
- Apache 2.4以上
- PHP 7.x以上
- MySQL 5.7以上
- セッション機能有効化

---

## 9. 注意事項

### 9.1 セッションタイムアウト
- 店舗選択後30分間で自動的にセッションが切れます
- セッション切れ後は再度店舗選択が必要です
- ShopController::checkSessionExpiry()で有効期限チェックを実施

### 9.2 データ入力
- 数量と単価は正の数値のみ入力可能
- 売上登録時は商品を最低1件以上選択が必要
- 店舗商品登録時は商品を最低1つ選択が必要

### 9.3 トランザクション
- 登録処理中はブラウザの「戻る」ボタンを使用しないでください
- エラー発生時は自動的にロールバックされます
- 複数テーブルへの登録は必ずトランザクション内で実行

### 9.4 納品処理
- 納品処理はCSRFトークンで保護されています
- 他店舗の注文を誤って納品できないように整合性チェックを実施
- 既に納品済みの注文は再度納品できません

---

## 10. 今後の拡張可能性

### 10.1 機能追加案
- ユーザー認証機能
- 売上集計・分析機能（日別、週別、月別）
- CSV/Excel出出力機能
- 販売データの編集・削除機能
- 在庫管理機能（在庫数追跡、在庫アラート）
- QRコードによる商品管理
- モバイルアプリ対応

---

## 11. 予約システム

### 11.1 予約システム概要

学園祭の商品予約機能を提供します。学生は事前に商品を予約でき、店舗側は来店時に予約を確認して売上登録できます。予約システムと売上システムは連携しており、売上登録時に予約状態が自動更新されます。

#### 主要機能
1. **学生側機能**
   - 店舗選択と商品予約
   - 予約一覧の閲覧
   - 予約のキャンセル
   - 学生認証（学生ID + パスコード）

2. **店舗側機能**
   - 予約検索（学生名で検索）
   - 売上登録時の予約自動更新
   - 予約状態の管理

3. **システム連携**
   - 売上登録と予約状態の自動連携
   - 外部来場者への対応（予約なしでも売上登録可能）
   - 二重登録の防止

### 11.2 予約状態の遷移

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

### 11.3 予約機能の画面遷移

#### 学生側：予約登録フロー
```
1. login.php（ログイン画面）
   ↓
   - 学生ID入力
   - パスコード入力
   - StudentDAO::authenticateStudent()で認証
   ↓
2. store_select.php（店舗選択画面）
   ↓
   - 店舗一覧から選択
   - セッションに店舗ID保存
   ↓
3. reserve.php（予約画面）
   ↓
   - ReserveController::getReserveData()で商品一覧取得
   - 商品選択、数量入力
   - 「予約する」ボタンをクリック
   ↓
4. add_reserve.php（予約登録処理）
   ↓
   - ReserveController::addReserve()で処理
   - reserveテーブルに登録（situation = 0: 予約中）
   - トランザクション処理
   ↓
5. dsp_reserve.php（予約確認画面）
   ↓
   予約成功時、登録内容を表示
```

#### 学生側：予約一覧・キャンセルフロー
```
1. login.php（ログイン画面）
   ↓
2. my_reservations.php（予約一覧画面）
   ↓
   - ReserveController::getStudentReservations()で全予約取得
   - 日時、企画名、場所、商品名、数量、状態を表示
   - 各予約に状態表示（予約中/来店/取消/完売）
   ↓
3. cancel.php（キャンセル処理）※キャンセルボタンクリック時
   ↓
   - ReserveController::cancelReservation()で処理
   - situation を 2（取消）に更新
   ↓
4. my_reservations.php（予約一覧画面）
   ↓
   キャンセル成功後、更新された一覧を表示
```

#### 店舗側：予約確認・売上登録フロー
```
1. inp_sales.php（売上登録画面）
   ↓
   - 学生名入力フィールド表示
   - 「予約確認」ボタンをクリック（オプション）
   ↓
2. check_reservation.php（予約検索API）
   ↓
   - Ajax POST: student_name, shop_id
   - StudentDAO::searchStudentsByName()で学生検索
   - 複数該当時は候補リスト返却
   - 1人該当時は予約情報返却
   - JSON形式でレスポンス
   ↓
3. inp_sales.php（売上登録画面）
   ↓
   - 予約情報を画面に表示（あれば）
   - 予約なしでも売上登録可能（外部来場者対応）
   - 商品選択、数量入力
   - reserve_id をhidden項目で送信（予約ありの場合）
   ↓
4. add_sales.php（売上登録処理）
   ↓
   - SalesController::addSales()で売上登録
   - 売上登録成功後
   - reserve_id がある場合:
     ├─ ReserveController::updateReservationStatus()実行
     ├─ situation を 1（来店）に更新
     └─ 更新失敗してもエラーにしない（ログ記録のみ）
   ↓
5. dsp_sales.php（売上一覧画面）
   ↓
   売上登録完了
```

### 11.4 予約システムのAPI

#### check_reservation.php（予約検索API）

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
    },
    {
      "st_id": 1002,
      "name": "山田花子",
      "class": "2-B"
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

**技術仕様:**
- Content-Type: application/json
- StudentDAO::searchStudentsByName()で学生検索
- ReserveController::getStudentReservationsByShop()で予約取得
- 部分一致検索（LIKE '%name%'）

### 11.5 売上システムとの連携

#### 予約と売上の関連付け

売上登録時に予約IDを指定することで、予約と売上を自動的に連携します。

**add_sales.php の処理フロー:**
```php
1. 売上データを登録（salesテーブル、detailテーブル）
2. 売上登録成功時:
   if (reserve_id が指定されている) {
       // 予約状態を「来店(1)」に更新
       ReserveController::updateReservationStatus(reserve_id, shop_id, 1)

       // 更新失敗してもエラーにしない（売上は既に登録済み）
       if (更新失敗) {
           error_log("予約状態の更新に失敗")
           // 処理は継続
       }
   }
3. 売上一覧画面へリダイレクト
```

**重要な設計思想:**
- **売上登録が最優先**: 予約更新に失敗しても売上登録は成功とする
- **外部来場者対応**: reserve_id なしでも売上登録可能
- **データ整合性**: 予約更新失敗はエラーログに記録

#### 二重登録の防止

同じ予約に対して複数回売上登録されるのを防ぐため、以下の仕組みを実装：

1. **予約状態チェック**
   - 既に situation = 1（来店）の予約は再更新しない
   - ReserveController::updateReservationStatus()内で状態チェック

2. **時間ベースの重複検出**
   - SalesDAO::hasDuplicateSales()で5分以内の同一売上を検出
   - 同じ店舗・商品・時間帯の売上があれば警告

### 11.6 予約システムのMVCアーキテクチャ

予約システムは完全なMVC構造に準拠しています。

#### レイヤー構成

```
┌─────────────────────────────────────┐
│  View（views/）                     │
│  - login.php（ログイン画面）        │
│  - store_select.php（店舗選択）     │
│  - reserve.php（予約画面）          │
│  - my_reservations.php（予約一覧）  │
│  - check_reservation.php（API）     │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  Controller（controllers/）         │
│  - ReserveController                │
│    ├─ getReserveData()              │
│    ├─ addReserve()                  │
│    ├─ getStudentReservations()      │
│    ├─ getStudentReservationsByShop()│
│    ├─ cancelReservation()           │
│    └─ updateReservationStatus()     │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  Model（models/）                   │
│  - ReserveModel                     │
│    ├─ createReservation()           │
│    ├─ getStudentReservations()      │
│    ├─ cancelReservation()           │
│    └─ updateReservationStatus()     │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  DAO（dao/）                        │
│  - ReserveDAO                       │
│    ├─ insert()                      │
│    ├─ findByStudentId()             │
│    ├─ findByStudentAndShop()        │
│    ├─ updateStatus()                │
│    ├─ findById()                    │
│    ├─ delete()                      │
│    └─ findByShopId()                │
│  - StudentDAO                       │
│    ├─ searchStudentsByName()        │
│    └─ searchStudentsByKana()        │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│  Database（MySQL）                  │
│  - reserve, student                 │
└─────────────────────────────────────┘

        ↕ データ転送

┌─────────────────────────────────────┐
│  DTO（dto/）                        │
│  - ReserveDTO                       │
│  - StudentDTO                       │
└─────────────────────────────────────┘
```

#### 各層の責務

**ReserveController:**
- 予約関連のリクエスト処理
- 入力値の検証
- ReserveModelの呼び出し
- 画面遷移の制御
- セッション管理（学生認証）

**ReserveModel:**
- 予約のビジネスロジック
- ReserveDAOの呼び出し
- 予約データの変換・加工
- 予約状態の管理

**ReserveDAO:**
- reserveテーブルへのCRUD操作
- SQLクエリの実行
- 予約データの取得・更新・削除

**StudentDAO（拡張機能）:**
- 学生名検索機能（部分一致）
- 学生かな検索機能（部分一致）
- 学生認証機能

### 11.7 データベース構造（予約関連）

#### reserveテーブル

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| r_id | INT | 予約ID（主キー、自動採番） |
| st_id | INT | 学生ID（外部キー） |
| si_id | INT | 店舗商品ID（外部キー） |
| num | INT | 数量 |
| datetime | DATETIME | 予約日時 |
| situation | TINYINT | 予約状態（0:予約中, 1:来店, 2:取消, 3:完売） |

#### studentテーブル

| カラム名 | データ型 | 説明 |
|---------|---------|------|
| st_id | INT | 学生ID（主キー） |
| class | VARCHAR | クラス |
| name | VARCHAR | 学生名 |
| kana | VARCHAR | 学生名かな |
| pasc | VARCHAR | パスコード |

**テーブル関連:**
- reserve.st_id → student.st_id（多対一）
- reserve.si_id → shop_item.si_id（多対一）

**インデックス:**
- name列: 部分一致検索の高速化
- kana列: かな検索の高速化
- situation列: 状態別検索の高速化

### 11.8 予約システムの設計思想

#### 外部来場者への対応

学園祭には学生以外の外部来場者も多数訪れます。このため、予約システムは**オプション機能**として設計されています。

**設計原則:**
1. **予約なしでも売上登録可能**
   - reserve_id は任意項目
   - 外部来場者は予約なしで商品購入できる

2. **学生の利便性向上**
   - 予約があれば自動で状態更新
   - 予約検索で迅速な対応

3. **売上データの優先**
   - 予約更新失敗でも売上は成功
   - データ整合性よりも運用の柔軟性を重視

#### 学生名検索の実装

学生IDではなく学生名で検索できる理由：

1. **ユーザビリティ**
   - 店舗スタッフは学生IDを知らない
   - 学生名を聞いて検索する方が自然

2. **部分一致対応**
   - 「山田」で検索すると複数の山田さんが表示
   - クラスで絞り込み可能

3. **かな検索サポート**
   - 漢字が分からない場合はひらがなで検索
   - searchStudentsByKana()で対応

### 11.9 予約システムのセキュリティ

#### 学生認証
- 学生ID + パスコードで認証
- StudentDAO::authenticateStudent()で検証
- セッションで認証状態を管理

#### アクセス制御
- 自分の予約のみ閲覧・キャンセル可能
- セッションの学生IDで制限
- 他の学生の予約は操作不可

#### SQLインジェクション対策
- プリペアドステートメント使用
- パラメータバインディング徹底

#### XSS対策
- htmlspecialchars()でエスケープ
- JSON出力時も適切にエンコード

### 11.10 予約システムの使用方法

#### 学生の予約手順

1. **ログイン**
   - login.phpで学生ID・パスコード入力
   - 認証成功でstore_select.phpへ

2. **店舗選択**
   - 一覧から店舗を選択
   - reserve.phpへ遷移

3. **商品予約**
   - 商品を選択
   - 数量を入力
   - 「予約する」ボタンをクリック

4. **予約確認**
   - dsp_reserve.phpで予約内容確認
   - 予約IDが発行される

5. **予約一覧**
   - my_reservations.phpで全予約を確認
   - 状態（予約中/来店/取消）を表示

6. **キャンセル（必要時）**
   - cancel.phpでキャンセル処理
   - 状態が「取消」に変更

#### 店舗側の予約確認手順

1. **売上登録画面を開く**
   - inp_sales.phpへアクセス

2. **予約確認（オプション）**
   - 学生名を入力
   - 「予約確認」ボタンをクリック
   - 予約情報が表示される（あれば）

3. **売上登録**
   - 予約情報があれば自動入力される
   - 予約なしでも手動で商品選択可能
   - 数量を入力して登録

4. **自動更新**
   - 売上登録成功時、予約状態が「来店」に自動更新
   - 学生の予約一覧でも状態が変わる

### 11.11 予約システムのエラーハンドリング

#### 予約登録時のエラー

1. **学生未認証**
   - セッションなし → login.phpへリダイレクト

2. **店舗未選択**
   - 店舗ID未設定 → store_select.phpへリダイレクト

3. **商品未選択**
   - エラーメッセージ表示
   - reserve.phpへ戻るリンク提供

4. **データベースエラー**
   - トランザクションロールバック
   - エラーメッセージ表示

#### 予約検索時のエラー

1. **該当なし**
   - JSONで `has_reservation: false` 返却
   - メッセージ表示

2. **複数該当**
   - 候補リストを返却
   - ユーザーに選択を促す

3. **リクエストエラー**
   - JSONで `success: false` 返却
   - エラーメッセージ表示

#### 予約更新時のエラー

1. **予約更新失敗**
   - エラーログに記録
   - 売上登録は成功とする
   - ユーザーには通知しない（運用上の判断）

2. **予約状態不正**
   - 既に来店済み → 更新スキップ
   - 既にキャンセル済み → エラー

### 11.12 予約システムの拡張性

#### 将来実装予定の機能

1. **完売機能**
   - 在庫管理との連携
   - situation = 3（完売）への自動更新
   - 在庫切れ時の予約自動キャンセル

2. **予約時間枠管理**
   - 時間帯ごとの予約上限
   - 混雑緩和のための予約枠管理

3. **予約通知機能**
   - メール通知
   - LINE通知
   - 予約確認QRコード

4. **統計・分析**
   - 予約率の集計
   - キャンセル率の分析
   - 人気商品の分析

5. **店舗側管理画面**
   - 予約一覧の表示
   - 予約状態の一括変更
   - 予約統計の表示

---

## 12. バージョン情報

- **作成日:** 2025-12-12
- **更新日:** 2026-01-22
- **バージョン:** 4.0
- **文書形式:** 技術仕様書
- **変更履歴:**
  - v1.0 (2025-12-12): 初版作成
  - v2.0 (2026-01-14): 商品管理機能の遷移フロー追加、アーキテクチャ図追加、ファイル構成の詳細化、各画面の仕様を実装に合わせて更新
  - v3.0 (2026-01-17): 全機能の完全な記載、店舗商品管理機能追加、注文納品管理機能追加、午前午後別売上表示機能追加、売上詳細画面追加、CSRF対策追加、セッション有効期限を30分に変更、画面遷移フローの全面更新、データベース構造の更新（shop_itemテーブル追加、situation列追加）、ファイル構成の全面更新
  - v4.0 (2026-01-22): 予約システム追加、予約と売上の連携機能実装、学生名検索機能追加、予約状態管理機能追加、予約システムのMVCアーキテクチャ実装、check_reservation.php API追加、StudentDAOに検索機能追加、ReserveController/Model/DAO実装、外部来場者対応設計、予約システムの完全なドキュメント化
