<?php

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export

namespace sirreal;

function dt( ...$data ): void {
	$trace = debug_backtrace( 0, 2 );
	array_shift( $trace );
	foreach ( $trace as $t ) {
		$file     = substr( $t['file'], strlen( \ABSPATH ) );
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

		State::print( "{$call} {$file}:{$line}\n" );
	}
	d( ...$data );
}
function d( ...$data ): void {
	ob_start();
	foreach ( $data as $d ) {
		var_export( $d );
		echo "\n";
	}
	State::print( ob_get_clean() );
}

function dx( ...$args ): void {}

\add_action( 'init', array( State::class, 'init' ) );

\add_action( 'shutdown', array( State::class, 'shutdown' ) );

abstract class State {
	private static $printed_header = false;

	private static $uri    = null;
	private static $time   = null;
	private static $method = null;

	const LOG_LOCATION = \WP_CONTENT_DIR . '/log.txt';

	private static function write_to_log( string $s ) {
		file_put_contents( self::LOG_LOCATION, $s, FILE_APPEND );
	}

	public static function print( $val ) {
		if ( ! self::$printed_header ) {
			self::$printed_header = true;
			$header               = "=======================================\n" .
			'START: ' . self::$time . ' ' . self::$method . ' ' . self::$uri . "\n" .
			"=======================================\n";

			self::write_to_log( $header );
		}
		self::write_to_log( $val );
	}

	public static function init() {
		self::$uri    = $_SERVER['REQUEST_URI'];
		self::$time   = $_SERVER['REQUEST_TIME'];
		self::$method = $_SERVER['REQUEST_METHOD'];
	}

	public static function shutdown() {
		if ( ! self::$printed_header ) {
			return;
		}
		self::write_to_log(
			"=======================================\n" .
			'END: ' . self::$time . ' ' . self::$method . ' ' . self::$uri . "\n" .
			"=======================================\n"
		);
	}
}
