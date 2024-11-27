<?php

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export

namespace sirreal;

function dt( ...$data ): void {
	d( ...$data );
	$trace = debug_backtrace( 0, 2 );
	array_shift( $trace );
	foreach ( $trace as $t ) {
		$file     = defined( 'ABSPATH' ) ? substr( $t['file'], strlen( \ABSPATH ) ) : $t['file'];
		$line     = $t['line'];
		$function = $t['function'];
		$class    = $t['class'] ?? '';
		$type     = $t['type'] ?? '';

		$args = $t['args'] ? implode(
			', ',
			array_map(
				function ( $a ) {
					return var_export( $a, true );
				},
				$t['args']
			)
		) : '';

		$call  = $class === '' ? '' : "{$class}{$type}";
		$call .= $function;
		$call .= $args === '' ? '()' : "( {$args} )";

		$j = $i;
		State::print( "\e[32;2m" );
		while ( $j-- ) {
			State::print( '  ' );
		}
		State::print( "↳ {$call} {$file}:{$line}\e[0m\n" );
		++$i;
	}
}

function d( ...$data ): void {
	ob_start();
	foreach ( $data as $d ) {
		var_export( $d );
		echo "\n";
	}
	State::print( ob_get_clean() );
}

/**
 * Gated debug function
 *
 * Use \sirreal\gate(true|false) to enable or disable debug output
 */
function dg( ...$data ): void {
	if ( State::$log_enabled ) {
		d( ...$data );
	}
}

function gate( bool $gate ): void {
	State::$log_enabled = $gate;
}

function dx( ...$args ): void {}

if ( function_exists( 'add_action' ) ) {
	\add_action( 'init', array( State::class, 'init' ) );
	\add_action( 'shutdown', array( State::class, 'shutdown' ) );
}

abstract class State {
	private static $printed_header = false;
	private static $log_location   = '/tmp/sireal-debug-log.txt';
	private static $uri            = null;
	private static $time           = null;
	private static $method         = null;

	public static $log_enabled = false;

	private static function write_to_log( string $s ) {
		file_put_contents( self::$log_location, $s, FILE_APPEND );
	}

	public static function print( $val ) {
		if ( ! self::$printed_header ) {
			self::$printed_header = true;

			$header = "\e[32;2m=======================================\n" .
			'START: ' . self::$time . ' ' . self::$method . ' ' . self::$uri . "\n" .
			"=======================================\e[0m\n";

			self::write_to_log( $header );
		}
		self::write_to_log( $val );
	}

	public static function init() {
		self::$uri    = $_SERVER['REQUEST_URI'];
		self::$time   = $_SERVER['REQUEST_TIME'];
		self::$method = $_SERVER['REQUEST_METHOD'];

		if ( defined( 'WP_CONTENT_DIR' ) ) {
			self::$log_location = \WP_CONTENT_DIR . '/uploads/log.txt';
		}
	}

	public static function shutdown() {
		if ( ! self::$printed_header ) {
			return;
		}
		self::write_to_log(
			"\e[32;2m=======================================\n" .
			'END: ' . self::$time . ' ' . self::$method . ' ' . self::$uri . "\n" .
			"=======================================\e[0m\n"
		);
	}
}
