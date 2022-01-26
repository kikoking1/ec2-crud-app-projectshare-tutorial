<?php session_start();

// Set default timezone
date_default_timezone_set('America/Vancouver');

// Create constant variable to hold path to root of project. 
//It's one directory up from this core/ folder.
define( 'APP_ROOT', substr(__DIR__, 0, strrpos(__DIR__, DIRECTORY_SEPARATOR)) );

// Class for database connection/manipulation functions
require_once('db.php');

// when "new" keyword is used to initialize class, auto check and include file with 
//same name as class from /repos/ folder, if exists.
require_once('autoloader.php');

// Restrict logged out access
require_once('auth.php');