<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WP_Security {

    private $log_file;

    public function __construct() {
        $this->log_file = plugin_dir_path(__FILE__) . '../logs/bot_log.txt';

        // Make sure logs folder exists
        if (!file_exists(plugin_dir_path(__FILE__) . '../logs')) {
            mkdir(plugin_dir_path(__FILE__) . '../logs', 0755, true);
        }

        // Hooks
        add_action('init', [$this, 'log_admin_access']);
        add_action('wp_login_failed', [$this, 'log_failed_login']);
    }

    // Log access to /wp-admin or /wp-login.php
    public function log_admin_access() {
        $uri = $_SERVER['REQUEST_URI'];
        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = $_SERVER['HTTP_USER_AGENT'];

        if (strpos($uri, '/wp-admin') !== false || strpos($uri, '/wp-login.php') !== false) {
            $this->write_log($ip, $uri, $ua);
        }
    }

    // Log failed login attempts specifically
    public function log_failed_login($username) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $uri = '/wp-login.php (failed login: ' . $username . ')';

        $this->write_log($ip, $uri, $ua);
    }

    // Function to write logs and check for repeated hits
    private function write_log($ip, $uri, $ua) {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "$timestamp | IP: $ip | URI: $uri | UA: $ua\n";

        // Append log entry
        file_put_contents($this->log_file, $entry, FILE_APPEND);

        // Check for repeated hits (simple detection)
        $recent_hits = $this->count_recent_hits($ip);
        if ($recent_hits >= 5) { // example threshold
            file_put_contents($this->log_file, "$timestamp | ALERT: IP $ip has $recent_hits hits recently!\n", FILE_APPEND);
        }
    }

    // Count hits from this IP in the last 10 minutes
    private function count_recent_hits($ip) {
        if (!file_exists($this->log_file)) return 0;

        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $count = 0;
        $now = time();

        foreach ($lines as $line) {
            if (strpos($line, "IP: $ip") !== false) {
                preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches);
                if ($matches) {
                    $line_time = strtotime($matches[1]);
                    if ($now - $line_time <= 600) { // 10 minutes
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    public function run() {
        // Additional hooks can be added here
    }
}

