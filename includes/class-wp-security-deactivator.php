// includes/class-wp-security-deactivator.php
<?php
class WP_Security_Deactivator {
    public static function deactivate() {
        $log_file = plugin_dir_path(__FILE__) . '../logs/bot_log.txt';
        file_put_contents($log_file, date('Y-m-d H:i:s') . " | Plugin deactivated\n", FILE_APPEND);
    }
}

