<?php

namespace WH\GF\Multicolumn\Classes;

class WH_GF_Multicolumn_Logger {
	const fileLocation = WP_CONTENT_DIR . '/gf-form-multicolumn.log';

	public static function log( $logLevel, $logMessage ) {
		if ( ! file_exists( self::fileLocation ) ) {
			file_put_contents( self::fileLocation,
			                   '/*****************************************************************/' . PHP_EOL .
			                   '/* This is the log file for the Gravity Forms Multicolumn plugin */' . PHP_EOL .
			                   '/*****************************************************************/' . PHP_EOL .
			                   PHP_EOL );
		}
		file_put_contents( self::fileLocation,
		                   date( 'Y-m-d H:i:s' ) . ' ' . $logLevel . ' ' .
		                   $logMessage . PHP_EOL,
		                   FILE_APPEND );
	}
}
