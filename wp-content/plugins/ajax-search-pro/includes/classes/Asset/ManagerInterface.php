<?php
namespace WPDRMS\ASP\Asset;

if ( !defined('ABSPATH') ) {
	die('-1');
}

interface ManagerInterface {
	/**
	 * To be called before (or within) wp_print_footer_scripts|admin_print_footer_scripts
	 *
	 * @param bool $force
	 * @return void
	 */
	public function enqueue( bool $force = false ): void;

	/**
	 * To be called on shutdown - backup print scripts for panic mode
	 *
	 * @param array<string, mixed> $instances
	 * @return void
	 */
	public function printInline( array $instances = array() ): void;

	/**
	 * Injection handler for the output buffer
	 *
	 * @param string                    $buffer
	 * @param bool|array<string, mixed> $instances
	 * @return string
	 */
	public function injectToBuffer( string $buffer, $instances ): string;
}
