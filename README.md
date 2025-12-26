# CSV Scheduled Posts

[English](#english) | [日本語](#japanese)

---

<a name="english"></a>
## English

A WordPress plugin for scheduling posts using CSV files.

### Description

This plugin allows you to upload CSV files or input CSV data directly to schedule WordPress posts with custom fields support.

### Features

- Upload CSV files to schedule posts
- Direct CSV data input
- Custom fields support
- Automatic post scheduling
- Bilingual interface (English/Japanese)

### Installation

1. Download the plugin files
2. Upload to your WordPress `wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Access the plugin via 'CSV Posts' in the admin menu

### Usage

#### CSV File Format

```csv
Post Date,Title,Content,Category,Tags,custom_field_1,custom_field_2,...
2024-06-01 10:00:00,Sample Post,This is the content,News,"tag1, tag2",Value1,Value2
```

#### Field Description

- **Post Date**: Publication date and time (Format: YYYY-MM-DD HH:MM:SS)
- **Title**: Post title
- **Content**: Post content (can be empty)
- **Category**: Post category (can be empty)
- **Tags**: Post tags (can be empty)
- **Custom Fields**: Any number of custom fields

#### Configuration (Optional)

You can pre-configure custom field headers in `csv-scheduled-posts-config.php`:

```php
define('CUSTOM_FIELDS', [
    'custom_field_1',
    'custom_field_2',
    // Add more fields as needed
]);
```

### Technical Stack

- PHP
- WordPress Plugin API
- CSV Processing
- Custom Fields API

### Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher

### License

MIT License

### Links

---

<a name="japanese"></a>
## 日本語

CSVファイルを使用して記事を予約投稿するWordPressプラグインです。

### 説明

このプラグインはCSVファイルをアップロードするか、CSVデータを直接入力することで、カスタムフィールドに対応したWordPress記事を予約投稿できます。

### 機能

- CSVファイルのアップロードによる予約投稿
- CSVデータの直接入力
- カスタムフィールド対応
- 自動予約投稿
- 日英バイリンガルインターフェース

### インストール方法

1. プラグインファイルをダウンロード
2. WordPressの `wp-content/plugins/` ディレクトリにアップロード
3. 管理画面の「プラグイン」メニューから有効化
4. 管理画面の「CSV Posts」からアクセス

### 使い方

#### CSVファイルの形式

```csv
Post Date,Title,Content,Category,Tags,custom_field_1,custom_field_2,...
2024-06-01 10:00:00,サンプル投稿,これは本文です,ニュース,"タグ1, タグ2",値1,値2
```

#### 各フィールドの説明

- **Post Date**: 記事の公開日時（形式: YYYY-MM-DD HH:MM:SS）
- **Title**: 記事のタイトル
- **Content**: 記事の本文（空欄でも可）
- **Category**: カテゴリ（空欄でも可）
- **Tags**: タグ（空欄でも可）
- **Custom Fields**: 任意の数のカスタムフィールド

#### 設定（オプション）

`csv-scheduled-posts-config.php` でカスタムフィールドのヘッダー情報を事前設定できます：

```php
define('CUSTOM_FIELDS', [
    'custom_field_1',
    'custom_field_2',
    // 必要に応じて追加
]);
```

この設定により、ヘッダー行が存在しないCSVデータでもアップロードが可能になります。

#### アップロード方法

管理画面の「CSV Posts」ページから、以下の2つの方法でデータをアップロードできます：

1. **CSVファイルのアップロード**
   - 「ファイルを選択」からCSVファイルを選択
   - 「CSVをアップロード」ボタンをクリック

2. **CSVデータの入力**
   - フォームにCSV形式のデータを入力
   - 「CSVデータを処理」ボタンをクリック

### 技術スタック

- PHP
- WordPress Plugin API
- CSV処理
- Custom Fields API

### 必要要件

- WordPress 5.0 以上
- PHP 7.2 以上

### ライセンス

MIT License

### 作者

lycorisx-works
