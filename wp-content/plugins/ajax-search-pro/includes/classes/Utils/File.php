<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

class File {
	/**
	 * Delete files in directory according to a pattern
	 *
	 * @param $dir string
	 * @param $file_arg string
	 * @return int files and directories deleted
	 */
	public static function delete(string $dir, string $file_arg = '*.*'): int {
		if ( $dir != '' && $dir != '/' ) {
			$count = 0;
			$files = @glob($dir . $file_arg, GLOB_MARK);
			// Glob can return FALSE on error
			if ( is_array($files) ) {
				foreach ($files as $file) {
					wpd_del_file($file);
					$count++;
				}
			}
			return $count;
		}
		return 0;
	}
}