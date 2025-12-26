<?php
class CSV_Scheduled_Posts_Admin {
    private $menu_position;
    private $processor;
    private $logger;
    private $settings;
    private $current_tab;

    public function __construct($processor = null) {
        $this->menu_position = DEFAULT_MENU_POSITION;
        $this->processor = $processor ?: new CSV_Scheduled_Posts_Processor();
        $this->logger = CSV_Scheduled_Posts_Logger::get_instance();
        $this->settings = new CSV_Scheduled_Posts_Settings();
        $this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'csv_posts';
    }

    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=csv-scheduled-posts&tab=settings') . '">' . __('Settings / 設定', 'csv-scheduled-posts') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function create_admin_menu() {
        $icon = get_option('csv_scheduled_posts_menu_icon', DEFAULT_MENU_ICON);
        add_menu_page(
            __('CSV Scheduled Posts / CSV予約投稿', 'csv-scheduled-posts'),
            __('CSV Posts / CSV投稿', 'csv-scheduled-posts'),
            'manage_options',
            'csv-scheduled-posts',
            array($this, 'admin_page'),
            $icon,
            $this->get_menu_position()
        );
    }

    public function admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page. / このページにアクセスする十分な権限がありません。', 'csv-scheduled-posts'));
        }

        $this->display_tabs();

        switch ($this->current_tab) {
            case 'settings':
                $this->settings->display_settings_page();
                break;
            case 'log':
                $this->log_page();
                break;
            default:
                $this->csv_posts_page();
                break;
        }
    }

    private function display_tabs() {
        $tabs = array(
            'csv_posts' => __('CSV Posts / CSV投稿', 'csv-scheduled-posts'),
            'settings' => __('Settings / 設定', 'csv-scheduled-posts'),
            'log' => __('Log / ログ', 'csv-scheduled-posts')
        );

        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $this->current_tab) ? ' nav-tab-active' : '';
            echo '<a class="nav-tab' . $class . '" href="?page=csv-scheduled-posts&tab=' . $tab . '">' . $name . '</a>';
        }
        echo '</h2>';
    }

    private function csv_posts_page() {
        $csv_upload_nonce = wp_create_nonce('csv_upload');
        $csv_input_nonce = wp_create_nonce('csv_input');

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('CSV Scheduled Posts / CSV予約投稿', 'csv-scheduled-posts'); ?></h1>
            
            <?php settings_errors('csv_scheduled_posts'); ?>

            <div id="csv-upload-message" class="notice" style="display: none;"></div>

            <h2><?php echo esc_html__('CSV Header Information / CSVヘッダー情報', 'csv-scheduled-posts'); ?></h2>
            <p><?php echo esc_html__('Required fields: / 必須フィールド:', 'csv-scheduled-posts'); ?></p>
            <pre><?php echo esc_html(implode(', ', REQUIRED_CSV_HEADER_FIELDS)); ?></pre>
            
            <p><?php echo esc_html__('All available fields: / 利用可能な全フィールド:', 'csv-scheduled-posts'); ?></p>
            <pre><?php echo esc_html(implode(', ', CSV_HEADER_FIELDS)); ?></pre>
            
            <p><?php echo esc_html__('Note: If your CSV file does not include a header row, the above fields will be used. Any additional fields will be treated as custom fields. / 注意：CSVファイルにヘッダー行が含まれていない場合、上記のフィールドが使用されます。追加のフィールドはカスタムフィールドとして扱われます。', 'csv-scheduled-posts'); ?></p>

            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="csv-upload-form">
                <input type="hidden" name="action" value="csv_file_upload">
                <?php wp_nonce_field('csv_upload', 'csv_upload_nonce'); ?>
                <h2><?php echo esc_html__('Upload CSV File / CSVファイルのアップロード', 'csv-scheduled-posts'); ?></h2>
                <input type="file" name="csv_file" accept=".csv">
                <?php submit_button(__('Upload CSV / CSVをアップロード', 'csv-scheduled-posts'), 'primary', 'submit_csv_file'); ?>
            </form>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="csv-input-form">
                <input type="hidden" name="action" value="csv_data_input">
                <?php wp_nonce_field('csv_input', 'csv_input_nonce'); ?>
                <h2><?php echo esc_html__('Input CSV Data / CSVデータの入力', 'csv-scheduled-posts'); ?></h2>
                <p><?php echo esc_html__('Enter your CSV data below, one row per line: / CSVデータを以下に入力してください。1行に1レコードです：', 'csv-scheduled-posts'); ?></p>
                <textarea name="csv_data" rows="10" cols="50" class="large-text"></textarea>
                <?php submit_button(__('Process CSV Data / CSVデータを処理', 'csv-scheduled-posts'), 'primary', 'submit_csv_data'); ?>
            </form>
        </div>

        <style>
        .csv-message {
            padding: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #00a0d2;
            background-color: #fff;
        }
        .csv-message.success {
            border-left-color: #46b450;
        }
        .csv-message.error {
            border-left-color: #dc3232;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $('#csv-upload-form, #csv-input-form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $submitButton = $form.find('input[type="submit"]');
                var originalButtonText = $submitButton.val();
                
                $submitButton.val('<?php echo esc_js(__('Processing... / 処理中...', 'csv-scheduled-posts')); ?>').prop('disabled', true);
                
                $('#csv-upload-message').removeClass('success error').addClass('csv-message').html('<?php echo esc_js(__('Processing CSV data. Please wait... / CSVデータを処理しています。お待ちください...', 'csv-scheduled-posts')); ?>').show();

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#csv-upload-message').removeClass('error').addClass('success').html(response.data).show();
                        } else {
                            $('#csv-upload-message').removeClass('success').addClass('error').html(response.data).show();
                        }
                    },
                    error: function() {
                        $('#csv-upload-message').removeClass('success').addClass('error').html('<?php echo esc_js(__('An error occurred. Please try again. / エラーが発生しました。もう一度お試しください。', 'csv-scheduled-posts')); ?>').show();
                    },
                    complete: function() {
                        $submitButton.val(originalButtonText).prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function log_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page. / このページにアクセスする十分な権限がありません。', 'csv-scheduled-posts'));
        }

        $log_file = $this->logger->get_log_file_path();
        $log_content = '';

        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('CSV Scheduled Posts Log / CSV予約投稿ログ', 'csv-scheduled-posts'); ?></h1>
            <div class="log-content">
                <h2><?php echo esc_html__('Log File Content / ログファイルの内容', 'csv-scheduled-posts'); ?></h2>
                <?php if (empty($log_content)): ?>
                    <p><?php echo esc_html__('The log file is empty or does not exist. / ログファイルが空であるか、存在しません。', 'csv-scheduled-posts'); ?></p>
                <?php else: ?>
                    <pre><?php echo esc_html($log_content); ?></pre>
                <?php endif; ?>
            </div>
            <form method="post" action="">
                <?php wp_nonce_field('clear_log_action', 'clear_log_nonce'); ?>
                <input type="submit" name="clear_log" class="button button-primary" value="<?php echo esc_attr__('Clear Log / ログをクリア', 'csv-scheduled-posts'); ?>">
            </form>
        </div>
        <style>
            .log-content pre {
                background-color: #f0f0f0;
                padding: 10px;
                white-space: pre-wrap;
                word-wrap: break-word;
                max-height: 500px;
                overflow-y: auto;
            }
        </style>
        <?php

        if (isset($_POST['clear_log']) && check_admin_referer('clear_log_action', 'clear_log_nonce')) {
            $this->logger->clear_log();
            echo '<div class="updated"><p>' . esc_html__('Log has been cleared. / ログがクリアされました。', 'csv-scheduled-posts') . '</p></div>';
        }
    }

    private function get_menu_position() {
        return get_option('csv_scheduled_posts_menu_position', $this->menu_position);
    }
}