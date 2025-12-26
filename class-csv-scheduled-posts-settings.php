<?php
class CSV_Scheduled_Posts_Settings {
    private $logger;

    public function __construct() {
        $this->logger = CSV_Scheduled_Posts_Logger::get_instance();
    }

    /**
     * Display the settings page
     * 設定ページを表示する
     */
    public function display_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page. / このページにアクセスする十分な権限がありません。', 'csv-scheduled-posts'));
        }

        $this->handle_form_submission();

        // Set default values
        // デフォルト値を設定
        $menu_position = get_option('csv_scheduled_posts_menu_position', DEFAULT_MENU_POSITION);
        $debug_mode = get_option('csv_scheduled_posts_debug_mode', false);
        $log_level = get_option('csv_scheduled_posts_log_level', DEFAULT_LOG_LEVEL);
        $debug_log_in_plugin_dir = get_option('csv_scheduled_posts_debug_log_in_plugin_dir', false);

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('CSV Scheduled Posts Settings / CSV予約投稿の設定', 'csv-scheduled-posts'); ?></h1>
            <?php settings_errors('csv_scheduled_posts'); ?>
            <form method="post" action="">
                <?php wp_nonce_field('csv_scheduled_posts_settings', 'csv_scheduled_posts_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="csv_scheduled_posts_menu_position"><?php echo esc_html__('Menu Position / メニュー位置', 'csv-scheduled-posts'); ?></label></th>
                        <td>
                            <input type="number" id="csv_scheduled_posts_menu_position" name="csv_scheduled_posts_menu_position" value="<?php echo esc_attr($menu_position); ?>" class="small-text">
                            <p class="description">
                                <?php echo esc_html__('Set the position where the menu should appear. Default is 4. / メニューの表示位置を設定します。デフォルトは4です。', 'csv-scheduled-posts'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="csv_scheduled_posts_debug_mode"><?php echo esc_html__('Debug Mode / デバッグモード', 'csv-scheduled-posts'); ?></label></th>
                        <td>
                            <input type="checkbox" id="csv_scheduled_posts_debug_mode" name="csv_scheduled_posts_debug_mode" value="1" <?php checked($debug_mode, true); ?>>
                            <p class="description">
                                <?php echo esc_html__('Enable debug mode to log detailed information. / デバッグモードを有効にして詳細な情報をログに記録します。', 'csv-scheduled-posts'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="csv_scheduled_posts_log_level"><?php echo esc_html__('Log Level / ログレベル', 'csv-scheduled-posts'); ?></label></th>
                        <td>
                            <select id="csv_scheduled_posts_log_level" name="csv_scheduled_posts_log_level">
                                <option value="<?php echo LOG_LEVEL_ERROR; ?>" <?php selected($log_level, LOG_LEVEL_ERROR); ?>><?php echo esc_html__('Error / エラー', 'csv-scheduled-posts'); ?></option>
                                <option value="<?php echo LOG_LEVEL_WARNING; ?>" <?php selected($log_level, LOG_LEVEL_WARNING); ?>><?php echo esc_html__('Warning / 警告', 'csv-scheduled-posts'); ?></option>
                                <option value="<?php echo LOG_LEVEL_INFO; ?>" <?php selected($log_level, LOG_LEVEL_INFO); ?>><?php echo esc_html__('Info / 情報', 'csv-scheduled-posts'); ?></option>
                                <option value="<?php echo LOG_LEVEL_DEBUG; ?>" <?php selected($log_level, LOG_LEVEL_DEBUG); ?>><?php echo esc_html__('Debug / デバッグ', 'csv-scheduled-posts'); ?></option>
                            </select>
                            <p class="description">
                                <?php echo esc_html__('Set the level of detail for logging. / ログの詳細レベルを設定します。', 'csv-scheduled-posts'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="csv_scheduled_posts_debug_log_in_plugin_dir"><?php echo esc_html__('Save debug.log in plugin folder / debug.logをプラグインフォルダに保存', 'csv-scheduled-posts'); ?></label></th>
                        <td>
                            <input type="checkbox" id="csv_scheduled_posts_debug_log_in_plugin_dir" name="csv_scheduled_posts_debug_log_in_plugin_dir" value="1" <?php checked($debug_log_in_plugin_dir, true); ?>>
                            <p class="description">
                                <?php echo esc_html__('If checked, debug.log will be saved in the plugin folder instead of wp-content/ / チェックを入れると、debug.logはwp-content/ではなくプラグインフォルダに保存されます', 'csv-scheduled-posts'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(esc_html__('Save Settings / 設定を保存', 'csv-scheduled-posts')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Handle form submission
     * フォーム送信を処理する
     */
    private function handle_form_submission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csv_scheduled_posts_settings_nonce'])) {
            if (!wp_verify_nonce($_POST['csv_scheduled_posts_settings_nonce'], 'csv_scheduled_posts_settings')) {
                wp_die(esc_html__('Security check failed. / セキュリティチェックに失敗しました。', 'csv-scheduled-posts'));
            }

            $menu_position = isset($_POST['csv_scheduled_posts_menu_position']) ? intval($_POST['csv_scheduled_posts_menu_position']) : DEFAULT_MENU_POSITION;
            update_option('csv_scheduled_posts_menu_position', $menu_position);

            $debug_mode = isset($_POST['csv_scheduled_posts_debug_mode']);
            update_option('csv_scheduled_posts_debug_mode', $debug_mode);
            $this->logger->set_debug_mode($debug_mode);

            $log_level = isset($_POST['csv_scheduled_posts_log_level']) ? intval($_POST['csv_scheduled_posts_log_level']) : DEFAULT_LOG_LEVEL;
            update_option('csv_scheduled_posts_log_level', $log_level);
            $this->logger->set_log_level($log_level);

            $debug_log_in_plugin_dir = isset($_POST['csv_scheduled_posts_debug_log_in_plugin_dir']);
            update_option('csv_scheduled_posts_debug_log_in_plugin_dir', $debug_log_in_plugin_dir);

            add_settings_error('csv_scheduled_posts', 'settings_updated', esc_html__('Settings saved. / 設定が保存されました。', 'csv-scheduled-posts'), 'updated');
        }
    }
}