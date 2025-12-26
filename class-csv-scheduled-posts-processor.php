<?php
class CSV_Scheduled_Posts_Processor {
    private $logger;
    private $csv_handler;

    public function __construct($csv_handler) {
        $this->logger = CSV_Scheduled_Posts_Logger::get_instance();
        $this->csv_handler = $csv_handler;
    }

    /**
     * Process CSV data and create posts
     * CSVデータを処理し、投稿を作成する
     */
    public function process_csv_data(array $csv_data): void {
        $header = array_shift($csv_data);
        foreach ($csv_data as $row) {
            try {
                $post_data = array_combine($header, $row);
                $post_id = $this->create_post_from_csv_data($post_data);
                $this->logger->log(sprintf(__('Post created successfully. ID: %d / 投稿が正常に作成されました。ID: %d', 'csv-scheduled-posts'), $post_id, $post_id), LOG_LEVEL_INFO);
            } catch (Exception $e) {
                $this->logger->log($e->getMessage(), LOG_LEVEL_ERROR);
            }
        }
    }

    /**
     * Create a post from CSV data
     * CSVデータから投稿を作成する
     */
    private function create_post_from_csv_data(array $post_data): int {
        try {
            // Convert and validate the post date
            // 投稿日を変換し、検証する
            $post_date = $this->convert_to_wp_timezone($post_data['Post Date']);

            $post_args = [
                'post_title'    => sanitize_text_field($post_data['Title']),
                'post_content'  => wp_kses_post($post_data['Content'] ?? ''),
                'post_status'   => 'future',
                'post_date'     => $post_date,
                'post_author'   => get_current_user_id(),
            ];

            // Handle category
            // カテゴリを処理する
            if (!empty($post_data['Category'])) {
                $this->logger->log("Processing category: " . $post_data['Category'], LOG_LEVEL_DEBUG);
                $category_id = $this->get_or_create_category($post_data['Category']);
                if ($category_id) {
                    $post_args['post_category'] = [$category_id];
                }
            }

            // Handle tags
            // タグを処理する
            if (!empty($post_data['Tags'])) {
                $post_args['tags_input'] = explode(',', $post_data['Tags']);
            }

            $post_id = wp_insert_post($post_args, true);

            if (is_wp_error($post_id)) {
                throw new Exception($post_id->get_error_message());
            }

            // Handle custom fields
            // カスタムフィールドを処理する
            foreach (CUSTOM_FIELDS as $field) {
                if (!empty($post_data[$field])) {
                    $value = $post_data[$field];
                    $this->logger->log("Processing custom field: {$field}, Original value: {$value}", LOG_LEVEL_DEBUG);
                    
                    if ($this->contains_iframe($value)) {
                        $sanitized_value = $this->sanitize_iframe($value);
                    } elseif ($this->is_url($value)) {
                        $sanitized_value = esc_url_raw($value);
                    } else {
                        $sanitized_value = sanitize_text_field($value);
                    }
                    
                    update_post_meta($post_id, $field, $sanitized_value);
                    $this->logger->log("Processed custom field: {$field}, Sanitized value: {$sanitized_value}", LOG_LEVEL_DEBUG);
                }
            }

            // Handle additional custom fields
            // 追加のカスタムフィールドを処理する
            if (isset($post_data['custom_fields']) && is_array($post_data['custom_fields'])) {
                foreach ($post_data['custom_fields'] as $key => $value) {
                    update_post_meta($post_id, $key, sanitize_text_field($value));
                    $this->logger->log("Processed additional custom field: {$key}, Value: {$value}", LOG_LEVEL_DEBUG);
                }
            }

            return $post_id;

        } catch (Exception $e) {
            $this->logger->log("Error creating post: " . $e->getMessage(), LOG_LEVEL_ERROR);
            throw $e;
        }
    }

    /**
     * Convert date string to WordPress timezone
     * 日付文字列をWordPressのタイムゾーンに変換する
     */
    private function convert_to_wp_timezone(string $date_string): string {
        $timezone = new DateTimeZone(get_option('timezone_string') ?: 'UTC');
        
        // Try different date formats
        // 異なる日付フォーマットを試す
        $formats = ['Y-m-d H:i', 'Y/m/d H:i', 'd-m-Y H:i', 'm/d/Y H:i'];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $date_string, $timezone);
            if ($date) {
                return $date->format('Y-m-d H:i:s');
            }
        }
        
        throw new Exception(__('Invalid date format. Expected one of: ' . implode(', ', $formats), 'csv-scheduled-posts'));
    }

    /**
     * Get or create category by slug
     * スラッグでカテゴリを取得または作成する
     */
    private function get_or_create_category(string $category_slug): ?int {
        $category = get_term_by('slug', $category_slug, 'category');
        if ($category) {
            return $category->term_id;
        }

        // Create a new category if it doesn't exist
        // カテゴリが存在しない場合、新しく作成する
        $new_category = wp_insert_term($category_slug, 'category', array('slug' => $category_slug));
        if (is_wp_error($new_category)) {
            $this->logger->log(sprintf(__('Failed to create category: %s', 'csv-scheduled-posts'), $new_category->get_error_message()), LOG_LEVEL_ERROR);
            return null;
        }

        return $new_category['term_id'];
    }

    /**
     * Check if a string contains an iframe tag
     * 文字列にiframeタグが含まれているかチェックする
     */
    private function contains_iframe(string $string): bool {
        return strpos(strtolower($string), '<iframe') !== false;
    }

    /**
     * Sanitize a string containing an iframe
     * iframeを含む文字列をサニタイズする
     */
    private function sanitize_iframe(string $value): string {
        // 既に処理されたiframeタグかどうかをチェック
        if (strpos($value, '<iframe') === 0 && strpos($value, '</iframe>') === strlen($value) - 9) {
            // 既に処理済みの場合はそのまま返す
            return $value;
        }

        $decoded_value = rawurldecode($value);
        
        $dom = new DOMDocument();
        @$dom->loadHTML('<div>' . $decoded_value . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $iframe = $dom->getElementsByTagName('iframe')->item(0);
        
        if ($iframe) {
            $allowed_attributes = ['width', 'height', 'src', 'scrolling', 'frameborder', 'allowfullscreen'];
            $new_iframe = $dom->createElement('iframe');
            
            foreach ($allowed_attributes as $attr) {
                if ($iframe->hasAttribute($attr)) {
                    $value = $iframe->getAttribute($attr);
                    $value = str_replace(["'", '"', '\\'], '', $value);
                    if ($attr === 'frameborder') {
                        $value = ($value === '0' || $value === 'no') ? '0' : '1';
                    }
                    $new_iframe->setAttribute($attr, $value);
                }
            }
            
            if ($iframe->hasAttribute('allowfullscreen')) {
                $new_iframe->setAttribute('allowfullscreen', '');
            }
            
            $dom->appendChild($new_iframe);
            $result = $dom->saveHTML($new_iframe);
            
            return $result;
        }
        
        return '';
    }

    /**
     * Check if a string is a URL
     * 文字列がURLかどうかをチェックする
     */
    private function is_url(string $string): bool {
        return filter_var($string, FILTER_VALIDATE_URL) !== false;
    }
}