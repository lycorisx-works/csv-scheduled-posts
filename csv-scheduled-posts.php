<?php
/*
Plugin Name: CSV Scheduled Posts
Plugin URI: 
Description: Plugin for scheduling posts using CSV files / CSVファイルを使用して記事を予約投稿するためのプラグイン
Version: 3.5
Author: 
Author URI: 
Text Domain: csv-scheduled-posts
*/

if (!defined('ABSPATH')) {
    exit;
}

// Load configuration file
// 設定ファイルを読み込む
require_once plugin_dir_path(__FILE__) . 'csv-scheduled-posts-config.php';

// Load required classes
// 必要なクラスを読み込む
require_once plugin_dir_path(__FILE__) . 'class-csv-scheduled-posts-admin.php';
require_once plugin_dir_path(__FILE__) . 'class-csv-scheduled-posts-processor.php';
require_once plugin_dir_path(__FILE__) . 'class-csv-scheduled-posts-logger.php';
require_once plugin_dir_path(__FILE__) . 'class-csv-scheduled-posts-settings.php';
require_once plugin_dir_path(__FILE__) . 'class-csv-scheduled-posts-csv-handler.php';

class CSV_Scheduled_Posts {
    private $admin;
    private $processor;
    private $csv_handler;

    public function __construct() {
        $this->csv_handler = new CSV_Scheduled_Posts_CSV_Handler();
        $this->processor = new CSV_Scheduled_Posts_Processor($this->csv_handler);
        $this->admin = new CSV_Scheduled_Posts_Admin($this->processor);

        add_action('admin_menu', array($this->admin, 'create_admin_menu'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this->admin, 'add_action_links'));
        add_action('admin_post_csv_file_upload', array($this, 'handle_file_upload'));
        add_action('admin_post_csv_data_input', array($this, 'handle_csv_input'));
    }

    // Handle file upload
    // ファイルアップロードを処理する
    public function handle_file_upload() {
        try {
            $file_path = $_FILES['csv_file']['tmp_name'];
            $csv_data = $this->csv_handler->process_csv_data($file_path, true);
            $this->processor->process_csv_data($csv_data);
            wp_send_json_success(__('CSV file was successfully uploaded and processed. / CSVファイルが正常にアップロードされ、処理されました。', 'csv-scheduled-posts'));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    // Handle CSV input
    // CSV入力を処理する
    public function handle_csv_input() {
        try {
            $csv_data = $_POST['csv_data'];
            $processed_data = $this->csv_handler->process_csv_data($csv_data);
            $this->processor->process_csv_data($processed_data);
            wp_send_json_success(__('CSV data was successfully processed. / CSVデータが正常に処理されました。', 'csv-scheduled-posts'));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
}

// Initialize the plugin
// プラグインを初期化する
new CSV_Scheduled_Posts();