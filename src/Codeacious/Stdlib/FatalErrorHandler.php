<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @version $Id: FatalErrorHandler.php 2522 2017-02-26 13:04:35Z glenn $
 */

namespace Codeacious\Stdlib;

/**
 * Catches fatal errors and makes sure that an appropriate HTTP response is sent.
 */
abstract class FatalErrorHandler
{
    /**
     * @var boolean Whether startErrorHandling() has been called
     */
    protected static $isRegistered = false;
    
    /**
     * @var string The filesystem path to an HTML document
     */
    protected static $errorPage;
    
    /**
     * @var array List of error types which are considered fatal by PHP
     */
    protected static $fatalErrorTypes = array(
        E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR, E_USER_ERROR
    );
    
    
	/**
     * Activates this class's error handling features.
     *
     * @return void
     */
    public static function start()
    {
        if (static::$isRegistered)
            return;
        
        register_shutdown_function(array(get_called_class(), 'checkForFatalErrors'));
        static::$isRegistered = true;
    }
    
    /**
     * Determines whether start() has been called previously.
     * 
     * @return boolean
     */
    public static function started()
    {
        return static::$isRegistered;
    }
    
    /**
     * Returns the document that will be rendered if an error occurs, and headers have not yet been
     * sent.
     * 
     * @return string The filesystem path to an HTML document
     */
    public static function getErrorPage()
    {
        if (!empty(static::$errorPage))
            return static::$errorPage;
        
        return __DIR__.DIRECTORY_SEPARATOR.'FatalErrorHandler'.DIRECTORY_SEPARATOR.'errorPage.html';
    }
    
    /**
     * Override the standard document that is rendered if an error occurs, and headers have not
     * yet been sent.
     * 
     * @param string $page The filesystem path to an HTML document
     * 
     * @return void
     */
    public static function setErrorPage($page)
    {
        static::$errorPage = $page;
    }

    /**
     * Checks for a fatal error. This method will be registered as a shutdown function.
     *
     * @return void
     */
    public static function checkForFatalErrors()
    {
        if (function_exists('error_get_last')
            && ($error = error_get_last())
            && array_key_exists('type', $error))
        {
            if (($error['type'] & E_ERROR)
                || in_array($error['type'], static::$fatalErrorTypes)
                || $error['type'] === 0) //HHVM unhandled exception
            {
                static::handlePHPError($error['type'], $error['message'], $error['file'],
                                     $error['line']);
            }
        }
    }

    /**
     * Handles a PHP error.
     *
     * @param integer $severity One of PHP's E_* constants
     * @param string  $message  Error Message
     * @param string  $filename File error occured in
     * @param integer $lineno   Line number of the error
     *
     * @return boolean Return false to continue handling the error as normal, true to terminate
     */
    public static function handlePHPError($severity, $message, $filename, $lineno)
    {
        //Don't do anything if PHP is running in command-line mode
        if (PHP_SAPI == "cli")
            return false;
        
		//Don't do anything if error reporting is off. This ensures that when @ is used to suppress
		//errors, it will actually have that effect.
        if (error_reporting() == 0)
            return false;

		//If possible, set a suitable HTTP response status to indicate failure
		if (!headers_sent())
			header('HTTP/1.1 500 Internal Server Error');

        //Output message
        $errorPage = static::getErrorPage();
        if (!headers_sent() && is_readable($errorPage))
        {
            echo file_get_contents($errorPage);
        }
        else
        {
            echo '<p style="color: red; font-weight: bold">Sorry, this website has '
                .'encountered a fatal error.</p>';
        }
        flush();

		//Allow normal PHP error handling/logging to continue
		return false;
    }
}
