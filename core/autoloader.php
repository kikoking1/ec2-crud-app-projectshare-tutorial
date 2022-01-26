<?php 

// Automatically include a relevant /repos class if it is instantiated, for project scalability purposes.
spl_autoload_register(function ($class) {
    // add .php file extension and lower case class name to match with corresponding filename
	$filename = strtolower($class) . '.php';

    // Check if instantiated class is in the repos folder before trying to include it
	if( file_exists(APP_ROOT . '/repos/' . $filename)){
		require_once APP_ROOT . '/repos/' . $filename;
	}
});