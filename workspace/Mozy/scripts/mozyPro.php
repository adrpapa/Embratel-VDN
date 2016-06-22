<?php

require_once "framework.php";
require "aps/2/runtime.php";
require_once "Utils/Wrapper.php";
require_once 'Utils/pbaAccountServiceManager.php';

/**
 * Class mozyPro
 * @type("http://www.mozy.com/mozyProAPS2/mozyPro/1.1")
 * @implements("http://aps-standard.org/types/core/application/1.0")
 */
class mozyPro extends \APS\ResourceBase {
    
    /**
     * @type(string)
     * @title("APIPBA")
     * @description("APIPBA")
     * @access(referrer,false)
     */
    public $APIPBA;
    
    /**
     * @type(string)
     * @title("userPBA")
     * @description("userPBA")
     * @access(referrer,false)
     */
    public $userPBA;
    
    /**
     * @type(string)
     * @title("passPBA")
     * @description("passPBA")
     * @access(referrer,false)
     */
    public $passPBA;

    /**
     * @type(boolean)
     * @title("orderType")
     * @access(referrer,false)
     */
    public $orderType=false;

    /**
     * @type(string)
     * @title("ws_prefix")
     * @description("ws_prefix")
     * @access(referrer,false)
     */
    public $ws_prefix;

    /**
     * @type(string)
     * @title("ws_sufix")
     * @description("ws_sufix")
     * @access(referrer,false)
     */
    public $ws_sufix;

    /**
     * @type(string)
     * @title("api_key")
     * @description("api_key")
     * @access(referrer,false)
     */
    public $api_key;

    /**
     * @type(string)
     * @title("root_partner_id")
     * @description("root_partner_id")
     * @access(referrer,false)
     */
    public $root_partner_id;

    /**
     * @type(string)
     * @title("root_role_id")
     * @description("root_role_id")
     * @access(referrer,false)
     */
    public $root_role_id;

    /**
     * @type(string)
     * @title("mozypro_adminpanel_login")
     * @description("mozypro_adminpanel_login")
     */
    public $mozypro_adminpanel_login;

    /**
     * @type(string)
     * @title("mozypro_user_portal_url")
     * @description("mozypro_user_portal_url")
     */
    public $mozypro_user_portal_url;

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5[]")
     * */
    public $mozyProAccount;

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyProConf/1.1[]")
     * */
    public $mozyProConf;


    #############################################################################################################################################
    ## Main methods
    #############################################################################################################################################

    public function configure($new = null)
    {

        // Using the wrapper for check the new PBA IP, PBA IP can be empty
        if($new->APIPBA != '')
        {
            // Validate PBA user and password
            $this->_validatePBACredentials($new);
        }
        
        if (!$new)
        {
            $new = $this;
        }
    }

    public function provision() {
    }

    public function _getDefault() {
    }

    public function retrieve() {
        return;
    }

    public function upgrade() {
        // Migrate this vars to mozyProConf
    }

    public function unprovision() {
        $logger = new SCITLogger\SCITLogger();

        $logger->info("unprovision function" . print_r($this, true));
        $logger->info("unprovision function" . print_r($_SERVER["HTTP_APS_INSTANCE_ID"], true));
        try {
            unlink('./' . $this->aps->id . '.ini');
            unlink('config/' . $_SERVER["HTTP_APS_INSTANCE_ID"] . '.pem');
            unlink('config/' . $_SERVER["HTTP_APS_INSTANCE_ID"]);
        } catch (Exception $ex) {
            //unlink('./'.$this->aps->id.'.ini',true);
            $logger->info("unprovision function" . print_r($ex, true));
            $logger->info("unprovision text function" . $ex->getMessage());
        }
    }

    #############################################################################################################################################
    ##	We may define additional functions that will not be public, this means that doesn't represent an APS operation
    ##	in this application on this type we have 2:
    ##	* One to interact with the class logger
    ##	* one to be able to write ini files in same format as redable by php function parse_ini_file
    #############################################################################################################################################

    
    private function _validatePBACredentials($new)
    {
        if(($new->userPBA != '' && $new->passPBA != '') || ($new->userPBA == '' && $new->passPBA == ''))
        {
            //Chek PBA url
            if(property_exists($new, 'APIPBA')){
                if(!empty($new->APIPBA)){
                    $userPBA = '';
                    if(property_exists($new, 'userPBA')){
                        $userPBA = $new->userPBA;
                    }
                    $passPBA = '';
                    if(property_exists($new, 'passPBA')){
                        $passPBA = $new->passPBA;
                    }
                    $accountDetails = PBAAccountServiceManager::AccountDetailsGet($new->APIPBA, '1', $userPBA, $passPBA);
                    if(is_array($accountDetails)){
                        if(array_key_exists("VendorAccountID",$accountDetails)){
                            return;
                        }
                    }
                    throw new \Exception("PBA Credentials are not correct", 411);
                }
            }
        }
        else
        {
            throw new \Exception("PBA Credentials are not correct", 411);
        }
    }
}

## Close of the Class
?>
