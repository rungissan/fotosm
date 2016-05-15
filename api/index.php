<?php

/**
 * The directory in which your application specific resources are located.
 * The application directory must contain the bootstrap.php file.
 *
 * @link http://kohanaframework.org/guide/about.install#application
 */
/**
 * The directory in which your codes are located.
 */
$apppath = './app';
$usrpath = './usr';

/**
 * The directory in which the Kohana resources are located. The system
 * directory must contain the classes/kohana.php file.
 *
 * @link http://kohanaframework.org/guide/about.install#system
 */
$system = './sys';

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 * @link http://kohanaframework.org/guide/about.install#ext
 */
define('EXT', '.php');

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 *
 * When using a legacy application with PHP >= 5.3, it is recommended to disable
 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting(E_ALL | E_STRICT);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Kohana internals.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 */
// Set the full path to the docroot
define('DOCROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);


if (!is_dir($usrpath) AND is_dir(DOCROOT . $usrpath))
    $usrpath = DOCROOT . $usrpath;

// Make the system relative to the docroot, for symlink'd index.php
if (!is_dir($system) AND is_dir(DOCROOT . $system))
    $system = DOCROOT . $system;


$system = realpath($system) . DIRECTORY_SEPARATOR;
define('SYSPATH', $system . 'src' . DIRECTORY_SEPARATOR);
define('MODPATH', $system . 'lib' . DIRECTORY_SEPARATOR);

$usrpath = realpath($usrpath) . DIRECTORY_SEPARATOR;
define('USRMODPATH', $usrpath . 'lib' . DIRECTORY_SEPARATOR);
define('USRSRCPATH', $usrpath . 'src' . DIRECTORY_SEPARATOR);

$apppath = realpath($apppath) . DIRECTORY_SEPARATOR;
define('APPPATH', $apppath);

define('TMPPATH', DOCROOT . 'tmp' . DIRECTORY_SEPARATOR);



// Clean up the configuration vars
unset($system, $usrpath, $apppath);

if (file_exists('install' . EXT)) {
    // Load the installation check
    return include 'install' . EXT;
}

/**
 * Define the start time of the application, used for profiling.
 */
if (!defined('KOHANA_START_TIME')) {
    define('KOHANA_START_TIME', microtime(TRUE));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if (!defined('KOHANA_START_MEMORY')) {
    define('KOHANA_START_MEMORY', memory_get_usage());
}

// Bootstrap the application
require APPPATH . 'bootstrap' . EXT;


if (PHP_SAPI == 'cli') { // Try and load minion

    echo Request::factory($argv[1])
        ->execute()
        ->send_headers(TRUE)
        ->body();

} else {
    
    /**
     * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
     * If no source is specified, the URI will be automatically detected.
     */
    echo Request::factory(TRUE, array(), FALSE)
            ->execute()
            ->send_headers(TRUE)
            ->body();
}

