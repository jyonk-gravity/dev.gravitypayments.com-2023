<?php

namespace WPDRMS\ASP\Utils;

use InvalidArgumentException;
use WPDRMS\ASP\Patterns\SingletonTrait;

/**
 * File manager/wrapper
 */
class FileManager {
	use SingletonTrait;

	/**
	 * @var string[]
	 */
	private array $allowed_directories;

	private function __construct() {
		$this->allowed_directories = array( wd_asp()->upload_path, wd_asp()->cache_path );
	}

	public function initialized( bool $init = false, string $check_method = '' ): bool {
		global $wp_filesystem;
		if ( $init && empty($wp_filesystem) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}
		if ( function_exists('WP_Filesystem') && WP_Filesystem() === true && is_object($wp_filesystem) ) {
			if ( $check_method !== '' ) {
				return method_exists($wp_filesystem, $check_method);
			}
			return true;
		}
		// Did not init
		return false;
	}

	/**
	 * @param callable-string $func
	 * @return mixed
	 */
	public function wrapper( string $func ) {
		global $wp_filesystem;
		$args = func_get_args();
		array_shift($args);
		return $this->initialized(false, $func) ?
			call_user_func_array(array( $wp_filesystem, $func ), $args) : // @phpstan-ignore-line
			call_user_func_array($func, $args);
	}

	/**
	 * @param string $file
	 * @return false|int
	 */
	public function mtime( string $file ) {
		global $wp_filesystem;
		// Did it fail?
		if ( $this->initialized(false, 'mtime') ) {
			return $wp_filesystem->mtime($file);
		}
		return filemtime( $file );
	}

	public function isFile( string $path ): bool {
		return $this->wrapper('is_file', $path);
	}

	public function isDir( string $path ): bool {
		return $this->wrapper('is_dir', $path);
	}

	/**
	 * @param string $filename
	 * @return false|string
	 */
	public function read( string $filename ) {
		global $wp_filesystem;
		// Replace double
		$filename = str_replace(array( '\\\\', '//' ), array( '\\', '/' ), $filename);

		if ( !file_exists($filename) ) {
			return '';
		}

		if ( $this->initialized(false, 'get_contents') ) {
			// All went well, return
			return $wp_filesystem->get_contents( $filename );
		}

		return @file_get_contents($filename); //@phpcs:ignore
	}

	public function write( string $filename, string $contents ): bool {
		global $wp_filesystem;
		// Replace double
		$filename = str_replace(array( '\\\\', '//' ), array( '\\', '/' ), $filename);

		if ( $this->inAllowedDirectory($filename) ) {
			// Make sure that the directory exists
			$this->createRequiredDirectories();

			// Did it fail?
			if ( !$this->initialized(false, 'put_contents') ) {
				/* any problems and we exit */
				return !( @file_put_contents($filename, $contents) === false ); //@phpcs:ignore
			}

			// It worked, use it!
			if ( defined('FS_CHMOD_FILE') ) {
				if ( !$wp_filesystem->put_contents($filename, $contents, FS_CHMOD_FILE) ) {
					return !( @file_put_contents($filename, $contents) === false ); //@phpcs:ignore
				}
			} elseif ( !$wp_filesystem->put_contents($filename, $contents) ) {
				return !( @file_put_contents($filename, $contents) === false ); //@phpcs:ignore
			}
		}

		return true;
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public function delFile( string $filename ): bool {
		global $wp_filesystem;
		if ( $this->inAllowedDirectory($filename) ) {
			// Did it fail?
			if ( !$this->initialized(false, 'delete') ) {
				/* any problems and we exit */
				return @unlink($filename); //@phpcs:ignore
			}
			return $wp_filesystem->delete($filename);
		} else {
			return false;
		}
	}

	/**
	 * Delete files in directory according to a pattern
	 *
	 * @param string $dir
	 * @param string $file_arg
	 * @return int files and directories deleted
	 */
	public function deleteByPattern( string $dir, string $file_arg = '*.*' ): int {
		if ( $dir !== '' && $dir !== '/' ) {
			$count = 0;
			$files = @glob($dir . $file_arg, GLOB_MARK); // @phpcs:ignore
			// Glob can return FALSE on error
			if ( is_array($files) ) {
				foreach ( $files as $file ) {
					$this->delFile($file);
					++$count;
				}
			}
			return $count;
		}
		return 0;
	}

	public function rmdir( string $dir, bool $recursive = false, bool $force = false ): bool {
		global $wp_filesystem;
		if ( $this->inAllowedDirectory($dir) ) {
			if ( $force ) {
				$this->recursiveRmdir($dir);
				return true;
			}

			// Did it fail?
			if ( !$this->initialized(false, 'rmdir') ) {
				// $recursive is not supported in the default php rmdir function
				return rmdir($dir); // @phpcs:ignore
			}

			$wp_filesystem->rmdir($dir, $recursive);
		} else {
			return false;
		}

		return false;
	}

	/**
	 * @param string $path
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function recursiveRmdir( string $path ): void {
		if ( !is_dir($path) ) {
			return;
		}
		if ( !str_ends_with($path, '/') ) {
			$path .= '/';
		}
		$files = glob($path . '*', GLOB_MARK);
		if ( $files === false ) {
			return;
		}
		foreach ( $files as $file ) {
			if ( is_dir($file) ) {
				$this->recursiveRmdir($file);
			} else {
				unlink($file); // @phpcs:ignore
			}
		}
		rmdir($path); // @phpcs:ignore
	}

	/**
	 * @return bool
	 */
	public function createRequiredDirectories(): bool {
		foreach ( $this->allowed_directories as $directory ) {
			if ( !is_dir($directory) ) {
				if ( !wp_mkdir_p($directory) ) {
					@mkdir($directory, 0755, true); // @phpcs:ignore
				}
			}
		}

		return true;
	}

	/**
	 * @return void
	 */
	public function removeRequiredDirectories(): void {
		foreach ( $this->allowed_directories as $directory ) {
			if ( $this->pathSafetyCheck($directory) && $this->isDir($directory) ) {
				$this->rmdir( $directory  );
				if ( $this->isDir( $directory ) ) { // @phpstan-ignore-line
					$this->rmdir( $directory, true);
					if ( $this->isDir( $directory ) ) { // @phpstan-ignore-line
						// Last attempt, with force
						$this->rmdir( $directory, true, true);
					}
				}
			}
		}
	}

	private function pathSafetyCheck( string $path ): bool {
		if (
			$path !== '' &&
			$path !== '/' &&
			$path !== './' &&
			str_replace('/', '', get_home_path()) !== str_replace('/', '', $path) &&
			strpos($path, 'wp-content') > 5 &&
			strpos($path, 'plugins') === false &&
			strpos($path, 'wp-includes') === false &&
			strpos($path, 'wp-admin') === false &&
			is_dir( $path )
		) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	private function inAllowedDirectory( string $path ): bool {
		foreach ( $this->allowed_directories as $directory ) {
			if ( strpos($path, $directory) !== false ) {
				return true;
			}
		}
		return false;
	}
}
