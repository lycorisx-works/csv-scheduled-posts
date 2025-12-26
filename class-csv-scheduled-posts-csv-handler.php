<?php
class CSV_Scheduled_Posts_CSV_Handler {
    private $logger;

    public function __construct() {
        $this->logger = CSV_Scheduled_Posts_Logger::get_instance();
    }

    /**
     * Process CSV data from either a string or a file
     * 文字列またはファイルからCSVデータを処理する
     *
     * @param mixed $input Either a CSV string or a file path
     * @param bool $is_file Whether the input is a file path
     * @return array Processed and validated CSV data
     * @throws Exception If processing fails
     */
    public function process_csv_data($input, $is_file = false): array {
        try {
            $csv_data = $is_file ? $this->read_csv_file($input) : $this->parse_csv_string($input);
            
            // Check if the CSV has a header
            // CSVにヘッダーがあるかチェックする
            $has_header = $this->check_for_header($csv_data[0]);
            
            if ($has_header) {
                $header = $this->validate_and_get_header($csv_data[0]);
                $start_index = 1; // Skip the first row (header) when processing data
            } else {
                $header = CSV_HEADER_FIELDS;
                $start_index = 0; // Process all rows as data
            }
            
            $processed_data = array($header);
            for ($i = $start_index; $i < count($csv_data); $i++) {
                $row = $csv_data[$i];
                foreach ($row as &$cell) {
                    $cell = $this->preprocess_csv_data($cell);
                }
                if (count($row) !== count($header)) {
                    throw new Exception(sprintf(__('Error on line %d: Incorrect number of columns. Expected %d, got %d. / %d行目でエラー：列数が不正です。期待値：%d、実際：%d', 'csv-scheduled-posts'), $i + 1, count($header), count($row)));
                }
                $processed_row = $this->map_csv_data_to_internal_fields(array_combine($header, $row));
                $processed_data[] = $processed_row;
            }
            
            return $processed_data;
        } catch (Exception $e) {
            $this->logger->log('Error processing CSV data: ' . $e->getMessage(), LOG_LEVEL_ERROR);
            throw $e;
        }
    }

    /**
     * Parse CSV string
     * CSV文字列を解析する
     *
     * @param string $csv_string The CSV string to parse
     * @return array Parsed CSV data
     */
    private function parse_csv_string(string $csv_string): array {
        $csv_data = array();
        $rows = str_getcsv($csv_string, "\n");
        foreach($rows as $row) {
            $csv_data[] = $this->parse_csv_row($row);
        }
        return $csv_data;
    }

    /**
     * Parse a single CSV row
     * CSV の1行を解析する
     *
     * @param string $row The CSV row to parse
     * @return array Parsed row data
     */
    private function parse_csv_row(string $row): array {
        return array_map('trim', str_getcsv($row));
    }

    /**
     * Read CSV file
     * CSVファイルを読み込む
     *
     * @param string $file_path Path to the CSV file
     * @return array CSV data
     * @throws Exception If file cannot be opened
     */
    private function read_csv_file(string $file_path): array {
        if (($handle = fopen($file_path, 'r')) === false) {
            throw new Exception(__('Could not open the file. / ファイルを開けませんでした。', 'csv-scheduled-posts'));
        }

        $csv_data = array();
        while (($data = fgetcsv($handle)) !== false) {
            $csv_data[] = $data;
        }
        fclose($handle);

        return $csv_data;
    }

    /**
     * Check if the first row is a header
     * 最初の行がヘッダーかどうかチェックする
     *
     * @param array $first_row The first row of CSV data
     * @return bool True if the first row is likely a header, false otherwise
     */
    private function check_for_header($first_row): bool {
        // Method 1: Check if the first row contains expected header fields
        // 方法1: 最初の行に期待されるヘッダーフィールドが含まれているかチェックする
        $expected_headers = array('Post Date', 'Title');
        $matches = array_intersect($first_row, $expected_headers);
        if (count($matches) === count($expected_headers)) {
            return true;
        }

        // Method 2: Check data types
        // 方法2: データ型をチェックする
        $second_row = isset($this->csv_data[1]) ? $this->csv_data[1] : null;
        if ($second_row) {
            $first_row_types = array_map('gettype', $first_row);
            $second_row_types = array_map('gettype', $second_row);
            if ($first_row_types !== $second_row_types) {
                return true;
            }
        }

        // If none of the above methods indicate a header, assume no header
        // 上記の方法でヘッダーが検出されなかった場合、ヘッダーなしと判断する
        return false;
    }

    /**
     * Validate and get header
     * ヘッダーを検証して取得する
     *
     * @param array $first_row The first row of CSV data
     * @return array Validated header or default header if invalid
     */
    private function validate_and_get_header(array $first_row): array {
        $required_fields = REQUIRED_CSV_HEADER_FIELDS;

        // Check if all required fields are present
        // 必須フィールドがすべて存在するかチェックする
        $missing_required_fields = array_diff($required_fields, $first_row);
        if (!empty($missing_required_fields)) {
            $this->logger->log('Missing required fields in header: ' . implode(', ', $missing_required_fields), LOG_LEVEL_WARNING);
            // Instead of returning null, we'll use the default header
            // nullを返す代わりに、デフォルトのヘッダーを使用する
            return CSV_HEADER_FIELDS;
        }

        // Check for unknown fields
        // 未知のフィールドをチェックする
        $unknown_fields = array_diff($first_row, CSV_HEADER_FIELDS);
        if (!empty($unknown_fields)) {
            $this->logger->log('Unknown fields in header: ' . implode(', ', $unknown_fields), LOG_LEVEL_WARNING);
        }

        return $first_row;
    }

    /**
     * Map CSV data to internal fields
     * CSVデータを内部フィールドにマッピングする
     *
     * @param array $csv_row CSV row data
     * @return array Mapped data
     */
    private function map_csv_data_to_internal_fields(array $csv_row): array {
        $mapped_data = array();
        foreach ($csv_row as $key => $value) {
            if (in_array($key, CSV_HEADER_FIELDS)) {
                $mapped_data[$key] = $value;
            } else {
                // Handle unknown fields as custom fields
                // 未知のフィールドはカスタムフィールドとして処理する
                $mapped_data['custom_fields'][$key] = $value;
            }
        }
        return $mapped_data;
    }

    /**
     * Preprocess CSV data
     * CSVデータの前処理を行う
     *
     * @param string $input Input data
     * @return string Processed data
     */
    private function preprocess_csv_data($input) {
        $this->logger->log("Preprocessing CSV data. Original input: " . $input, LOG_LEVEL_DEBUG);

        try {
            // Detect iframe tag
            // iframeタグを検出
            if (strpos($input, '<iframe') !== false) {
                $this->logger->log("iframe tag detected. Processing...", LOG_LEVEL_DEBUG);

                // Use DOMDocument to parse iframe
                // DOMDocumentを使用してiframeを解析
                $dom = new DOMDocument();
                @$dom->loadHTML('<div>' . $input . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $iframes = $dom->getElementsByTagName('iframe');

                if ($iframes->length > 0) {
                    $iframe = $iframes->item(0);
                    $new_iframe = $dom->createElement('iframe');

                    // Process attributes
                    // 属性を処理
                    $attributes = ['width', 'height', 'src', 'scrolling', 'frameborder', 'allowfullscreen'];
                    foreach ($attributes as $attr) {
                        if ($iframe->hasAttribute($attr)) {
                            $value = $iframe->getAttribute($attr);
                            // Remove quotes completely and re-wrap with double quotes
                            // クォートを完全に削除し、ダブルクォートで囲み直す
                            $value = str_replace(["'", '"', '\\'], '', $value);
                            if ($attr === 'src') {
                                $value = rawurldecode($value);
                            }
                            // Special processing for frameborder attribute
                            // frameborder属性の特別処理
                            if ($attr === 'frameborder') {
                                $value = ($value === '0' || $value === 'no') ? '0' : '1';
                            }
                            $new_iframe->setAttribute($attr, $value);
                        }
                    }

                    // Special processing for allowfullscreen attribute
                    // allowfullscreen属性の特別処理
                    if ($iframe->hasAttribute('allowfullscreen')) {
                        $new_iframe->setAttribute('allowfullscreen', '');
                    }

                    // Replace old iframe with new iframe
                    // 新しいiframeで古いiframeを置換
                    $iframe->parentNode->replaceChild($new_iframe, $iframe);

                    // Get processed HTML
                    // 処理されたHTMLを取得
                    $input = $dom->saveHTML($new_iframe);
                    $this->logger->log("Processed iframe: " . $input, LOG_LEVEL_DEBUG);
                }
            }
        } catch (Exception $e) {
            $this->logger->log("Error in preprocessing CSV data: " . $e->getMessage(), LOG_LEVEL_ERROR);
        }

        $this->logger->log("Preprocessed CSV data: " . $input, LOG_LEVEL_DEBUG);
        return $input;
    }
}