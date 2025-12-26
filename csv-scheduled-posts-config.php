<?php
// Define standard WordPress fields
// 標準のWordPressフィールドを定義する
define('STANDARD_WP_FIELDS', [
    'Post Date',
    'Title',
    'Content',
    'Category',
    'Tags'
]);

// Define custom fields
// カスタムフィールドを定義する
define('CUSTOM_FIELDS', [
    'custom_fields_01',
    'custom_fields_02',
    'custom_fields_03',
    'custom_fields_04',
    'custom_fields_05',
    'custom_fields_06'
]);

// Combine standard and custom fields to create CSV header fields
// 標準フィールドとカスタムフィールドを組み合わせてCSVヘッダーフィールドを作成する
define('CSV_HEADER_FIELDS', array_merge(STANDARD_WP_FIELDS, CUSTOM_FIELDS));

// Define required CSV header fields
// 必須のCSVヘッダーフィールドを定義する
define('REQUIRED_CSV_HEADER_FIELDS', [
    'Post Date',
    'Title',
    'Content'
]);

// Default menu position
// デフォルトのメニュー位置
define('DEFAULT_MENU_POSITION', 4);

// Default menu icon
// デフォルトのメニューアイコン
define('DEFAULT_MENU_ICON', 'dashicons-carrot');

// Maximum file size (in bytes)
// 最大ファイルサイズ（バイト）
define('MAX_FILE_SIZE', 1024 * 1024); // 1MB

// Default debug mode setting
// デバッグモードのデフォルト設定
define('DEFAULT_DEBUG_MODE', false);

// Log levels
// ログレベル
define('LOG_LEVEL_ERROR', 1);
define('LOG_LEVEL_WARNING', 2);
define('LOG_LEVEL_INFO', 3);
define('LOG_LEVEL_DEBUG', 4);

// Default log level
// デフォルトのログレベル
define('DEFAULT_LOG_LEVEL', LOG_LEVEL_ERROR);

// Default chunk size for processing
// 処理のデフォルトチャンクサイズ
define('DEFAULT_CHUNK_SIZE', 100);

// Error messages
// エラーメッセージ
define('ERROR_SECURITY_CHECK', __('Security check failed. / セキュリティチェックに失敗しました。', 'csv-scheduled-posts'));
define('ERROR_NO_CSV_DATA', __('No CSV data provided. / CSVデータが提供されていません。', 'csv-scheduled-posts'));
define('ERROR_INVALID_CSV', __('Invalid CSV format. Please check your input and try again. / 無効なCSV形式です。入力を確認して再試行してください。', 'csv-scheduled-posts'));
define('ERROR_FILE_UPLOAD', __('File upload error occurred. / ファイルアップロードエラーが発生しました。', 'csv-scheduled-posts'));
define('ERROR_FILE_TYPE', __('Please upload only CSV files. / CSVファイルのみをアップロードしてください。', 'csv-scheduled-posts'));
define('ERROR_FILE_SIZE', __('File size is too large. Maximum allowed size is 1MB. / ファイルサイズが大きすぎます。最大許容サイズは1MBです。', 'csv-scheduled-posts'));