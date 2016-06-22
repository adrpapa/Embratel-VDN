<?php
require_once "framework.php";
require "aps/2/runtime.php";

/**
 * Class mozyProConf
 * @type("http://www.mozy.com/mozyProAPS2/mozyProConf/1.1")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class mozyProConf extends \APS\ResourceBase {

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyPro/1.1")
     * @required
     */
    public $mozyPro;

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5[]")
     */
    public $mozyProAccount;

    /**
     * @type(string)
     * @title("logoUrl")
     */
    public $logoUrl;

    /**
     * @type(string)
     * @title("branded tab name")
     * @description("the name of the tab")
     */
    public $branded_tab_name;

    /**
     * @type(string)
     * @title("branded general name ")
     * @description("The name of the application that appears at home")
     */
    public $branded_general_name;

    /**
     * @type(string)
     * @title("branded description")
     * @description("Description of the application that appear at home ")
     */
    public $branded_general_description;
    
    /**
     * @type(string)
     * @title("backupHelp")
     * @description("backupHelp")
     */
    public $backupHelp;
    
    /**
     * @type(string)
     * @title("syncHelp")
     * @description("syncHelp")
     */
    public $syncHelp;
    
    ////////////////////////////// OLD MOZYPRO VARS TO THIS CLASS:
    
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
     * @access(referrer,false)
     */
    public $mozypro_adminpanel_login;

    /**
     * @type(string)
     * @title("mozypro_user_portal_url")
     * @description("mozypro_user_portal_url")
     * @access(referrer,false)
     */
    public $mozypro_user_portal_url;


    #############################################################################################################################################
    ## Main methods
    #############################################################################################################################################

    public function provision()
    {
    }

    public function configure($new)
    {
        if (!$new) {
            $new = $this;
        }
    }

    public function unprovision() {
        
    }

    #############################################################################################################################################
    ## Aux methods
    #############################################################################################################################################

    public function _getDefault()
    {
    }

}

?>
