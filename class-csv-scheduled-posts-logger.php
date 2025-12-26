<?php
class CSV_Scheduled_Posts_Logger {
    private static $instance = null;
    private $debug_mode;
    private $log_level;
    private $log_file;

    private function __construct() {
        $this->debug_mode = get_option('csv_scheduled_posts_debug_mode', DEFAULT_DEBUG_MODE);
        $this->log_level = get_option('csv_scheduled_posts_log_level', DEFAULT_LOG_LEVEL);
        $this->set_log_file();
    }

    /**
     * Get the singleton instance of the logger
     * ロガーのシングルトンインスタンスを取得する
     */
    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set the log file path
     * ログファイルのパスを設定する
     */
    private function set_log_file(): void {
        $debug_log_in_plugin_dir = get_option('csv_scheduled_posts_debug_log_in_plugin_dir', false);
        if ($debug_log_in_plugin_dir) {
            $this->log_file = plugin_dir_path(dirname(__FILE__)) . 'csv-scheduled-posts/debug_csv-scheduled-posts.log';
        } else {
            $this->log_file = WP_CONTENT_DIR . '/debug_csv-scheduled-posts.log';
        }
    }

    /**
     * Log a message
     * メッセージをログに記録する
     */
    public function log(string $message, int $level = LOG_LEVEL_ERROR): void {
        if ($this->debug_mode && $level <= $this->log_level) {
            $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $this->get_level_name($level) . ': ' . $message . PHP_EOL;
            error_log($log_message, 3, $this->log_file);
        }
    }

    /**
     * Get the name of the log level
     * ログレベルの名前を取得する
     */
    private function get_level_name(int $level): string {
        switch ($level) {
            case LOG_LEVEL_ERROR:
                return 'ERROR';
            case LOG_LEVEL_WARNING:
                return 'WARNING';
            case LOG_LEVEL_INFO:
                return 'INFO';
            case LOG_LEVEL_DEBUG:
                return 'DEBUG';
            default:
                return 'UNKNOWN';
        }
    }

    /**
     * Set the debug mode
     * デバッグモードを設定する
     */
    public function set_debug_mode(bool $mode): void {
        $this->debug_mode = $mode;
        update_option('csv_scheduled_posts_debug_mode', $mode);
    }

    /**
     * Set the log level
     * ログレベルを設定する
     */
    public function set_log_level(int $level): void {
        $this->log_level = $level;
        update_option('csv_scheduled_posts_log_level', $level);
    }

    /**
     * Get the path of the log file
     * ログファイルのパスを取得する
     */
    public function get_log_file_path(): string {
        return $this->log_file;
    }

    /**
     * Clear the log file
     * ログファイルをクリアする
     */
    public function clear_log(): void {
        if (file_exists($this->log_file)) {
            file_put_contents($this->log_file, '');
        }
    }
}