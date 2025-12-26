# CSV Scheduled Posts

A WordPress plugin for scheduling posts using CSV files.

## Description

This plugin allows you to upload CSV files or input CSV data directly to schedule WordPress posts with custom fields support.

## Features

- Upload CSV files to schedule posts
- Direct CSV data input
- Custom fields support
- Automatic post scheduling
- Bilingual interface (English/Japanese)

## Installation

1. Download the plugin files
2. Upload to your WordPress `wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Access the plugin via 'CSV Posts' in the admin menu

## Usage

### CSV File Format

```csv
Post Date,Title,Content,Category,Tags,custom_field_1,custom_field_2,...
2024-06-01 10:00:00,Sample Post,This is the content,News,"tag1, tag2",Value1,Value2
```

### Field Description

- **Post Date**: Publication date and time (Format: YYYY-MM-DD HH:MM:SS)
- **Title**: Post title
- **Content**: Post content (can be empty)
- **Category**: Post category (can be empty)
- **Tags**: Post tags (can be empty)
- **Custom Fields**: Any number of custom fields

### Configuration

You can pre-configure custom field headers in `csv-scheduled-posts-config.php`:

```php
define('CUSTOM_FIELDS', [
    'custom_field_1',
    'custom_field_2',
    // Add more fields as needed
]);
```

## Technical Stack

- PHP
- WordPress Plugin API
- CSV Processing
- Custom Fields API

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher

## License

MIT License

## Author

lycorisx-works

## Links

- [Detailed Usage Guide (Japanese)](https://note.com/ne_gy/n/na8c0aa8f101a)
- [Portfolio](https://lycorisx.com/portfolio_wd)
