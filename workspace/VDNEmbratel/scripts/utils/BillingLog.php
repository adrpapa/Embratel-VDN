<?php

require_once realpath(dirname( __FILE__ ))."/../elemental_api/configConsts.php";
require_once realpath(dirname( __FILE__ ))."/../elemental_api/utils.php";

class BillingLog {
    public function __construct( $context, $name ){
        $current_ts = date_create();
        $billingLogPath = ConfigConsts::BILLING_LOG_PATH.date_format( $current_ts, "/Y/m/");
        if (!file_exists($billingLogPath)) {
            mkdir($billingLogPath, 0755, true);
        }
        $clientID = formatClientID($context);
        $this->billingLog = $billingLogPath.$clientID."_".$name.".log";
        $this->billingError = $billingLogPath.$clientID."_".$name."_error.log";
        $this->billingDebug = $billingLogPath.$clientID."_".$name."_dbg.log";
//         echo "Creating logs for $name: $this->billingLog, $this->billingError, $this->billingDebug\n";
    }

    private function write($logfile, $msg){
        $current_ts = date_create();
        file_put_contents($logfile, date_format( $current_ts, "c") . ";" . $msg."\n", FILE_APPEND);
    }

    public function debug($msg){
        $this->write($this->billingDebug, $msg);
        if(ConfigConsts::debug) {
            echo "DEBUG: $msg";
        }
    }

    public function error($msg){
        $this->write($this->billingError, $msg);
        echo "ERROR: $msg";
    }

    public function log($msg){
        $this->write($this->billingLog, $msg);
    }
}
?>
