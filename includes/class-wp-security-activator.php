<?php 
class WP_Security_Activator {
	public static function activate() {
		$log_dir = plugin_dir_path(__FILE__) . '../logs';
		if (!file_exists($log_dir)) {
			mkdir($log_dir, 0755, true);
		}
	}
}

?>
