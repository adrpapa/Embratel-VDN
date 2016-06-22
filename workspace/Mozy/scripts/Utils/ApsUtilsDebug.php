<?php

class ApsUtilsDebug {

    private static $initialized = 0;
    private static $start_time = 0;

    private static $debug_level 	= 0; //Indicate debug level
    private static $levelNone 		= 0; // none
    private static $levelError 		= 1; // errorLog
    private static $levelWarn 		= 2; // warnLog
    private static $levelInfo 		= 3; // infoLog
    private static $levelRequest	= 4; // requestLog
    private static $levelDebug 		= 5; // debugLog

    private static $defaultTimeZone = 'Europe/Madrid';
    private static $showStackTrace = false;

    /**
     * http://php.net/manual/en/function.error-log.php
     * Says where the error should go. The possible message types are as follows:
     *
     * error_log() log types
     * 0	message is sent to PHP's system logger, using the Operating System's system logging mechanism or a file, depending on what the error_log configuration directive is set to. This is the default option.
     * 1	message is sent by email to the address in the destination parameter. This is the only message type where the fourth parameter, extra_headers is used.
     * 2	 No longer an option.
     * 3	message is appended to the file destination. A newline is not automatically added to the end of the message string.
     * 4	message is sent directly to the SAPI logging handler.
     * @var int
     */
    private static $message_type = 3;

    /**
     * Default value should be unique to the APS package
     * @var string
     */
    private static $log_file = 'log.txt';
    private static $log_path = '';
    private static $log_default_prefix = '';

    /**
     * List of subscriptions ids to debug for - Use setter to pass comma separated values
     * @var array
     */
    private static $debug_subscriptions = array();
    private static $debug_subscription = '0';
    private static $debug_subscriptionsUse = false;

    /**
     * List of account ids to debug for - Use setter to pass comma separated values
     * @var array
     */
    private static $debug_accounts = array();
    private static $debug_account = '0';
    private static $debug_accountsUse = false;


    public static function Init($debug_subscription = 0, $debug_account = 0, $log_default_prefix = '') {
        self::$initialized = 0;
        self::_init($debug_subscription, $debug_account, $log_default_prefix);
    }

    private static function _init($debug_subscription = 0, $debug_account = 0, $log_default_prefix = '') {
        if(!self::$initialized){
            self::$initialized = 1;

            $timezone = new DateTimeZone(self::$defaultTimeZone);
            self::$start_time = new DateTime('now', $timezone);

            $debug_config_array = parse_ini_file("config_debug.ini");

            self::$debug_subscription 	= $debug_subscription;
            self::$debug_account 		= $debug_account;


            self::$debug_level 	        = (int) $debug_config_array['debug_level'];
            self::$log_file 	        = $debug_config_array['log_file'];
            self::$log_path 	        = $debug_config_array['log_path'];
            self::$log_default_prefix 	= $log_default_prefix;
            if (!empty($debug_config_array['debug_subscriptions'])) {
                self::$debug_subscriptions = explode(',', $debug_config_array['debug_subscriptions']);
                // lets set a flag if we have this so we dont have to keep checking for empty array
                self::$debug_subscriptionsUse = true;
            }
            if (!empty($debug_config_array['debug_accounts'])) {
                self::$debug_accounts = explode(',', $debug_config_array['debug_accounts']);
                // lets set a flag if we have this so we dont have to keep checking for empty array
                self::$debug_accountsUse = true;
            }
            if($debug_config_array['debug_logphperrors'] == '1'){
                self::overridePhpHandlers();
            }
            if($debug_config_array['showStackTrace'] == '1'){
                self::$showStackTrace = true;
            }
        }
    }
	
	 /**
     * Change Debug Level
     * @param int $new_debug_level (0-5)
     */
    public static function ChangeDebugLevel($new_debug_level){
        self::_init();
        self::$debug_level 	= $new_debug_level;
    }

    /**
     * Error Logging - error conditions
     * @param string $message
     * @param string $classFunc
     * @param int $code
     */
    public static function Error($message, $classFunc = '', $code = 0, $log_prefix = '') {
        self::_init();
        $level = 'ERROR';
        // basic error logging
        if (self::$debug_level >= self::$levelError) {
            self::_log($message, $classFunc, $level, $code, $log_prefix);
        }
    }

    /**
     * Warn Logging - warning conditions
     * @param string $message
     * @param string $classFunc
     * @param int $code
     */
    public static function Warn($message, $classFunc = '', $code = 0, $log_prefix = '') {
        self::_init();
        $level = 'WARN';
        // basic error loggingwarn
        if (self::$debug_level >= self::$levelWarn) {
            self::_log($message, $classFunc, $level, $code, $log_prefix);
        }
    }

    /**
     * Notice Logging - normal, but significant, condition
     * @param string $message
     * @param string $classFunc
     * @param int $code
     */
    public static function Notice($message, $classFunc = '', $code = 0, $log_prefix = '') {
        self::_init();
        $level = 'NOTICE';
        // basic error loggingwarn
        if (self::$debug_level >= self::$levelWarn) {
            self::_log($message, $classFunc, $level, $code, $log_prefix);
        }
    }

    /**
     * Info Logging - informational message
     * @param string $message
     * @param string $classFunc
     * @param int $code
     */
    public static function Info($message, $classFunc = '', $code = 0, $log_prefix = '') {
        self::_init();
        $level = 'INFO';
        // basic error loggingwarn
        if (self::$debug_level >= self::$levelInfo) {
            self::_log($message, $classFunc, $level, $code, $log_prefix);
        }
    }

    /**
     * Request Logging - request-level message
     * @param string $message
     * @param string $classFunc
     * @param int $code
     */
    public static function Request($message = '', $classFunc = '', $code = 0, $log_prefix = '') {
        self::_init();
        $level = 'REQUEST';
        // basic debug logging
        if (self::$debug_level >= self::$levelRequest) {
            if (self::$debug_subscriptionsUse) {
                if (in_array(self::$debug_subscription, self::$debug_subscriptions)) {
                    self::_log($message, $classFunc, $level, $code, $log_prefix);
                    return;
                }
            }
            if (self::$debug_accountsUse) {
                if (in_array(self::$debug_account, self::$debug_accounts)) {
                    self::_log($message, $classFunc, $level, $code, $log_prefix);
                    return;
                }
            }
            if (!self::$debug_accountsUse && !self::$debug_subscriptionsUse){
                self::_log($message, $classFunc, $level, $code, $log_prefix);
            }
        }
    }

    /**
     * Debug Logging - debug-level message
     * @param string $message
     * @param string $classFunc
     * @param int $code
     */
    public static function Debug($message = '', $classFunc = '', $code = 0, $log_prefix = '') {
        self::_init();
        $level = 'DEBUG';
        // basic debug logging
        if (self::$debug_level >= self::$levelDebug) {
            if (self::$debug_subscriptionsUse) {
                if (in_array(self::$debug_subscription, self::$debug_subscriptions)) {
                    self::_log($message, $classFunc, $level, $code, $log_prefix);
                    return;
                }
            }
            if (self::$debug_accountsUse) {
                if (in_array(self::$debug_account, self::$debug_accounts)) {
                    self::_log($message, $classFunc, $level, $code, $log_prefix);
                    return;
                }
            }
            if (!self::$debug_accountsUse && !self::$debug_subscriptionsUse) {
                self::_log($message, $classFunc, $level, $code, $log_prefix);
            }
        }
    }

    private static function _log($message='', $classFunc='', $level = '', $code = 0, $log_prefix = '') {
        $recordsep = "~~ ";
        $separator = ' | ';
        if($message == ''){
            $message = self::getReflectionCallerClass()."::".self::getReflectionCallerFunction()." argv(" . json_encode(self::getReflectionCallerArgs()) . ")";
            if($classFunc == ''){
                $classFunc = $separator.'### '.self::getReflectionCallerClass().'::'.self::getReflectionCallerFunction();
            }
        }
        $msg = self::formatMessage($message, $classFunc, $level, $code);

        $timezone = new DateTimeZone(self::$defaultTimeZone);
        $date = new DateTime('now', $timezone);
        $dateStr = $date->format('Y-m-d');
        $logPath = self::$log_path . $dateStr . '_' . self::$debug_account . '_' . self::$debug_subscription .'_'. $log_prefix . self::$log_default_prefix . self::$log_file;
        //$logPath = self::$log_path . $dateStr . '_' . self::$log_file;


        $msg = $recordsep.self::$start_time->diff($date)->format('%H:%I:%S') . $separator . $msg;

        error_log($msg, self::$message_type, $logPath);
    }




    public static function getReflectionCallerFunction() {
        $result ='';
        $trace = debug_backtrace();
        if($trace){
            if(count($trace)>=4){
                $result = $trace[3]['function'];
            }
        }
        return empty($result) ? 'unknown' : $result;
    }

    public static function getReflectionCallerClass() {
        $result ='';
        $trace = debug_backtrace();
        if($trace){
            if(count($trace)>=4){
                $result = $trace[3]['class'];
            }
        }
        return empty($result) ? 'unknown' : $result;
    }

    public static function getReflectionCallerArgs() {
        $result ='';
        $trace = debug_backtrace();
        if($trace){
            if(count($trace)>=4){
                $result = $trace[3]['args'];
            }
        }
        return empty($result) ? 'unknown' : $result;
    }


    /**
     *
     * @param array $stacktrace
     */
    public static function getStacktraceString($stacktrace) {
        if (self::$showStackTrace) {
            $output = "\nStacktrace:\n";
            foreach ($stacktrace as $traceKey => $trace) {
                $output .= "[{$traceKey}]\t";
                if (isset($trace['file'])) {
                    $output .= $trace['file'] . " - ";
                }
                if (isset($trace['class'])) {
                    $output .= $trace['class'] . "::";
                }
                if (isset($trace['function'])) {
                    $output .= $trace['function'] . "() - ";
                }
                if (isset($trace['line'])) {
                    $output .= "line(" . $trace['line'] . ")";
                }
                $output .= "\n";
            }
            return $output;
        }
        return '';
    }

    /**
     * Format debug message
     * @param string $message
     * @return string
     */
    public static function formatMessage($message, $classFunc = '', $level = '', $code = 0) {
        $recordsep = "~~ ";
        $separator = ' | ';
        //$fmsg = $recordsep;
        $fmsg = '';

        //Formatted timestamp
        $timezone = new DateTimeZone(self::$defaultTimeZone);
        $date = new DateTime('now', $timezone);

        $fmsg .= $date->format('Y-m-d H:i:s P');

        $fmsg .= $separator;

        //Log level
        $fmsg .= $level;

        $fmsg .= $separator;

        //unique session identifier
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $fmsg .= $_SERVER['REMOTE_ADDR'];
        } else {
            $fmsg .= '0.0.0.0(UNKNOWN)';
        }
        $fmsg .= ' : ';

        //class/function
        $fmsg .= $classFunc;

        $fmsg .= $separator;

        //message
        $fmsg .= $message;

        $fmsg .= $separator;

        if (!empty($code)) {
            $fmsg .= (string) $code;
        }
        $fmsg .= "\n";
        return $fmsg;
    }

    public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {

        /*
         * Generate message to be logged
         */
        $stackTrace = debug_backtrace();
        $message = $errstr . "' in " . $errfile . ":" . $errline
            . ApsUtilsDebug::getStacktraceString($stackTrace);
        /*
         * getting priority
         */
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
                //$priority = 'NOTICE';
                ApsUtilsDebug::Notice($message, 'PHP', 500);
                break;
            case E_WARNING:
            case E_USER_WARNING:
                //$priority = 'WARNING';
                ApsUtilsDebug::Warn($message, 'PHP', 500);
                break;
            default:
                //$priority = 'ERROR';
                ApsUtilsDebug::Error($message, 'PHP', 500);
                break;
        }

        /*
         * Skipping default PHP error handler
         */
        return true;
    }

    public static function exception_handler($e) {
        /*
         * generating message to log
         */
        $message = "EXCEPTION in class '" . get_class($e) . "' with message '"
            . $e->getMessage() . "' in " . $e->getFile() . ":" . $e->getLine()
            . PHP_EOL . "Stack trace:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;

        /*
         * Logging
         */
        ApsUtilsDebug::Error($message, get_class($e), 500);
    }

    public static function shutdown_function() {
        $lastPHPError = error_get_last();
        if (is_array($lastPHPError)) {
            switch ($lastPHPError['type']) {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_PARSE:
                case E_USER_ERROR:
                    $stackTrace = debug_backtrace();
                    $message = 'ERR ' . $lastPHPError['type'] . ' with message '
                        . $lastPHPError['message'] . ' in ' . $lastPHPError['file'] . ':' . $lastPHPError['line']
                        . ApsUtilsDebug::getStacktraceString($stackTrace);

                    ApsUtilsDebug::Error($message, 'PHP', 500);
                    break;
            }
        }
    }

    public static function overridePhpHandlers() {
        // Set default error handler to this class

        /*
         * Handling most errors here
         */
        set_error_handler(array('ApsUtilsDebug', 'error_handler'));


        /*
         * Logging uncaught exceptions
         */
        set_exception_handler(array('ApsUtilsDebug', 'exception_handler'));


        /*
         * Logging errors that couldn't have been handled normally.
         *
         * Some critical errors cannot be handled with set_error_handler()
         * (http://php.net/manual/en/function.set-error-handler.php).
         * That's why we register another handler that will be executed on code shutdown.
         */
        register_shutdown_function(array('ApsUtilsDebug', 'shutdown_function'));
    }

}

?>