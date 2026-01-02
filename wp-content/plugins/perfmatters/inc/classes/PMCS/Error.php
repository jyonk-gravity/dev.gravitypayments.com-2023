<?php
namespace Perfmatters\PMCS;

class Error
{
	private static $previous_exception_handler = null;

	public static function init()
	{
		//fatal error handling
		add_filter('wp_php_error_args', array('Perfmatters\PMCS\Error', 'handle_fatal_error'), 1, 2);

		self::$previous_exception_handler = set_exception_handler(array('Perfmatters\PMCS\Error', 'exception_handler'));

		add_action('shutdown', function() {
	        $error = error_get_last();
	        if($error && $error['type'] === 1) {
	            self::handle_fatal_error([
	                'response' => 500
	            ], $error);
	        }
	    });
	}

	//uncaught exception/error handler
	public static function exception_handler($e) {

		$error = self::handle_fatal_error(
			[
            	'response' => 500
            ], 
            [
				'message' => 'Uncaught ' . (($e instanceof Exception) ? 'Exception' : 'Error') . ': ' . $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'trace' => $e->getTrace()
			]
		);

		//restore original exception handler
		if(self::$previous_exception_handler) {
	        
	        //remove our handler from the stack
	        restore_exception_handler();
	        
	        //temporarily set the handler to null in case the previous handler throws a new exception
	        set_exception_handler(null);
	        
	        //call original handler to continue error reporting
	        call_user_func(self::$previous_exception_handler, $e);
	    }

		exit(1);
	}

	//handle fatal error
	public static function handle_fatal_error($args, $error)
    {
        if(empty($args['response']) || $args['response'] != 500) {
            return $args;
        }

        if(empty($error['file'])) {
            return $args;
        }

        if(PMCS::get_storage_dir() !== dirname($error['file'])) {
            return $args;
        }

        $config = PMCS::get_snippet_config();

        if(empty($config)) {
        	return $args;
        }

        $file_name = basename($error['file']);

        if(isset($config['error_files'][$file_name])) {
            return $args;
        }

        //error from our snippet
        if(!empty($error['message'])) {
            $message = $error['message'];
            $message = explode("\n", $message)[0];
            $message = str_replace($error['file'], 'SNIPPET', $message);
        }
        else {
        	$message = 'Unknown Error';
        }

        //add error message for snippet in config
        $config['error_files'][$file_name] = $message;
        PMCS::update_snippet_config($config);

        //deactivate the snippet
        Snippet::deactivate($file_name);

        return $args;
    }
}