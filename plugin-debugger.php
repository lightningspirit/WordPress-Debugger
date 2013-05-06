<?php
/*
Plugin Name: Backtrace Debugger
Plugin URI: http://www.vcarvalho.com/
Version: 1.0
Author: lightningspirit
Author URI: http://profiles.wordpress.org/lightningspirit
Description: A backtrace debugger plugin for WordPress
License: GPLv2
*/



// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}


// Add style to footer
function wp_backtrace_debugger_style() {
?>
<style type="text/css">
.wp-backtrace-debug { display: table; border: 1px solid #ccc; }
.wp-backtrace-debug ol {  }
.wp-backtrace-debug li { border-bottom: 1px solid #ccc; }
</style>
<?php
}

add_action( 'admin_footer', 'wp_backtrace_debugger_style', 99 );
add_action( 'wp_footer', 'wp_backtrace_debugger_style', 99 );


// Forked from: http://stackoverflow.com/questions/1159216/how-can-i-get-php-to-produce-a-backtrace-upon-errors/1159235#1159235

function wp_backtrace_debugger_process( $errno, $errstr, $errfile, $errline ) {
	
	if ( !( error_reporting() & $errno ) )
		return;

	switch( $errno ) {
		case E_WARNING	    :
		case E_USER_WARNING :
		case E_STRICT	    :
		case E_NOTICE	    :
		case E_DEPRECATED   :
		case E_USER_NOTICE  :
			$type = 'warning';
			$fatal = false;
			break;

		default	:
			$type = 'fatal error';
			$fatal = true;
			break;

	}
	
	$trace = debug_backtrace();
	array_shift( $trace );

	if ( php_sapi_name() == 'cli') {
		echo 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
		foreach ( $trace as $item )
			echo '  ' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()' . "\n";

	} else {
		echo '<p class="wp-backtrace-debug">' . "\n";
		echo '  Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
		echo '  <ol>' . "\n";
		foreach($trace as $item)
			echo '	<li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
		echo '  </ol>' . "\n";
		echo '</p>' . "\n";
	
	}

	if ( ini_get( 'log_errors' ) ) {
		$items = array();
		foreach ( $trace as $item )
			$items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()';

		$message = 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ': ' . join(' | ', $items);
		
		error_log( $message );

	}

	flush();

	if ( $fatal )
		exit(1);

}

set_error_handler( 'wp_backtrace_debugger_process' );
