<?php

use Embratel\Common\Log\NullLogger;
use Embratel\Common\Log\Logger;

require_once realpath(dirname( __FILE__ ))."/../../../elemental_api/configConsts.php";


class Parallels
{
	/**
	 * 
	 * @var Logger
	 */
	private $logger;
	
    public function __construct($log = '')
    {
    	if (!$log) {
    		$this->logger = new NullLogger();
    	} else {
            $this->logger = $log;
        }
    }

    private function busca($server_parallels = "PBA", $metodo = "Execute", $parametros = "", &$error = null)
    {
        $this->logger->debug("[" . __METHOD__ . "] >> : {$server_parallels}, {$metodo}, " . var_export($parametros, true));
        
        $server_conecta = '';
        switch ($server_parallels) {
            case 'PBA':
                $server_conecta = ConfigConsts::$PBA_API;
                break;
            case 'POA':
                $server_conecta = ConfigConsts::$POA_API;
                break;
            default:
                $error = 'Servidor parallels inválido';
                $this->logger->error("[" . __METHOD__ . "] << {$error}");
                return false;
        }
        
        $request = xmlrpc_encode_request($metodo, $parametros);
        
        $context = stream_context_create(array(
            'http' => array(
                'method' => "POST",
                'header' => "Content-Type: text/xml",
                'content' => $request,
                'timeout' => 60
            )
        ));
        
        $file = file_get_contents($server_conecta, false, $context);
        
        if (empty($file)) {
            $e = error_get_last();
            $error = $e['message'];
            unset($e);
            $this->logger->error("[" . __METHOD__ . "] << {$error}");
            return false;
        }
        
        $response = xmlrpc_decode($file);
        if ($response && xmlrpc_is_fault($response)) {
            $error = "xmlrpc: " . base64_decode($response['faultString']) . " (" . $response['faultCode'] . ")";
            $this->logger->error("[" . __METHOD__ . "] << {$error}");
            return false;
        } else {
            $this->logger->verbose("[" . __METHOD__ . "] " . print_r($response, true));
            $this->logger->debug("[" . __METHOD__ . "] <<");
            return $response;
        }
    }

    public function getSubscription($subscription_id)
    {
        $this->logger->debug("[" . __METHOD__ . "] >> : {$subscription_id}");
        $param = "";
        $param["Server"] = "BM";
        $param["Method"] = "SubscriptionDetailsGet_API";
        $param["Params"][] = intval($subscription_id);
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $this->logger->debug("[" . __METHOD__ . "] << ");
        return array(
            'SubscriptionID' => trim($dados[0]),
            'SubscriptionName' => trim($dados[1]),
            'AccountID' => trim($dados[2]),
            'PlanID' => trim($dados[3]),
            'PlanName' => trim($dados[4]),
            'Status' => trim($dados[5]),
            'ServStatus' => trim($dados[6])
        );
    }

    public function getSubscriptionEx($SubscriptionID)
    {
        $this->logger->debug("[" . __METHOD__ . "] >> : {$SubscriptionID}");
        
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "SubscriptionDetailsGetEx_API";
        $param["Params"][] = intval($SubscriptionID); // SubscriptionID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $this->logger->debug("[" . __METHOD__ . "] << ");
        return array(
            'SubscriptionID' => trim($dados[0]),
            'SubscriptionName' => trim($dados[1]),
            'AccountID' => trim($dados[2]),
            'PlanID' => trim($dados[3]),
            'PlanName' => trim($dados[4]),
            'Status' => trim($dados[5]),
            'ServStatus' => trim($dados[6]),
            'StartDate' => trim($dados[7]),
            'ExpirationDate' => trim($dados[8]),
            'LastBillDate' => trim($dados[9]),
            'NextBillDate' => trim($dados[10]),
            'BillingPeriodType' => trim($dados[11]),
            'BillingPeriod' => trim($dados[12])
        );
    }
    
    // GetLastToServStatusTransitionDate_API
    public function getSubscriptionServiceStatusLastDate($Subscription, $ServStatus)
    {
        $this->logger->debug("[" . __METHOD__ . "] >> : {$Subscription}, {$ServStatus}");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "GetLastToServStatusTransitionDate_API";
        $param["Params"][] = intval($Subscription); // AccountID
        $param["Params"][] = intval($ServStatus); // AccountID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $this->logger->debug("[" . __METHOD__ . "] <<");
        return array(
            'ServiceStatusLastDate' => trim($dados[0])
        );
    }
    
    // SubscrParamValueGet_API
    public function getSubscriptionDomainID($SubscriptionID)
    {
        $this->logger->debug("[" . __METHOD__ . "] >> {$SubscriptionID}");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "SubscrParamValueGet_API";
        $param["Params"][] = intval($SubscriptionID); // AccountID
        $param["Params"][] = strval("DomainID"); // AccountID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $this->logger->debug("[" . __METHOD__ . "] <<");
        return array(
            'DomainID' => trim($dados[0])
        );
    }

    public function getDomain($DomainName)
    {
        $this->logger->debug("[" . __METHOD__ . "] >> {$DomainName} ");
        
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "DomainInfoGet_API";
        $param["Params"][] = strval($DomainName);
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $this->logger->debug("[" . __METHOD__ . "] << ");
        return array(
            'AccountID' => trim($dados[0]),
            'VendorID' => trim($dados[1]),
            'DomainID' => trim($dados[2]),
            'FullDomainName' => trim($dados[3]),
            'DomainZoneID' => trim($dados[4]),
            'Status' => trim($dados[5]),
            // 'External DNS' => trim($dados[6]), //obsolete
            'RegistrationPeriod' => trim($dados[7]),
            'startDate' => trim($dados[8]),
            'ExpirationDate' => trim($dados[9]),
            'PrimaryNameServer' => trim($dados[10]),
            'SecondaryNameServer' => trim($dados[11]),
            'ThirdNameServer' => trim($dados[12]),
            'FourthNameSever' => trim($dados[13]),
            'CompanyName' => trim($dados[14])
        );
    }

    public function getPlan($PlanID)
    {
        $this->logger->debug("[" . __METHOD__ . "] >> {$PlanID} ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "PlanDetailsGet_API";
        $param["Params"][] = intval($PlanID); // AccountID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $this->logger->debug("[" . __METHOD__ . "] << ");
        return array(
            'PlanID' => trim($dados[0]),
            'Name' => trim($dados[1]),
            'CategoryID' => trim($dados[2]),
            'ResourceCurrencyID' => trim($dados[3]),
            'ShortDescription' => trim($dados[4]),
            'LongDescription' => trim($dados[5]),
            'GateName' => trim($dados[6]),
            'GroupID' => trim($dados[7]),
            'IsParentReq' => trim($dados[8]),
            'RecurringType' => trim($dados[9]),
            'BillingPeriodType' => trim($dados[10]),
            'BillingPeriod' => trim($dados[11]),
            'ShowPriority' => trim($dados[12]),
            'Default_PlanPeriodID' => trim($dados[13]),
            'IsOTFI' => trim($dados[14]),
            'DocID' => trim($dados[15])
        );
    }

    public function getAllPlanCategory()
    {
        $this->logger->debug("[".__METHOD__ ."] >> ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "PlanCategoryListGet_API";
        $param["Params"][] = intval(VENDOR_ID); // AccountID
        $param["Params"][] = "1"; // SortNo
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $categories = array();
        
        foreach ($dados as $cat) {
            $categories[] = array(
                'PlanCategoryID' => $cat[0],
                'Name' => $cat[1],
                'Description' => $cat[2]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $categories;
    }

    public function getAllResourcesRateFromPlan($PlanID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$PlanID}");
        
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "PlanRateListFullGet_API";
        $param["Params"][] = intval($PlanID);
        $param["Params"][] = - 1;
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $res_rates = array();
        
        foreach ($dados as $res) {
            $res_rates[] = array(
                'ResourceRateID' => $res[0],
                'ResourceRateName' => $res[1],
                'Description' => $res[2],
                'Included_Value' => $res[3],
                'Upper_Limit' => $res[4],
                'Lower_Limit' => $res[5],
                'UOM' => $res[6],
                'Setup_Fee' => $res[7],
                'Recurring_Fee' => $res[8],
                'Measurable' => $res[9],
                'ResourceID' => $res[10],
                'SetupFeeDescr' => $res[11],
                'RecurrFeeDescr' => $res[12],
                'IsVisible' => $res[13],
                'IsMain' => $res[14],
                'StoreText' => $res[15],
                'IsSFperUnit' => $res[16],
                'IsRFperUnit' => $res[17],
                'IsSFforUpgrade' => $res[18],
                'StorePriceText' => $res[19],
                'ResourceCategoryID' => $res[20],
                'SortOrder' => $res[21],
                'IsOveruseFeeTiered' => $res[22],
                'IsRecurringFeeTiered' => $res[23]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $res_rates;
    }

    public function getSubscriptionApplicationInstances($SubscriptionID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$SubscriptionID} ");
        
        $param = "";
        $param["subscription_id"] = intval($SubscriptionID); // SubscriptionID
        $dados_app = $this->busca("POA", "pem.APS.getSubscriptionApplicationInstances", $param);
        if ($dados_app === false) {
            return array();
        }
        // [application_instance_id]
        // [application_id]
        // [package_version]
        // [url]
        // [status]
        // [rt_id]
        $this->logger->debug("[".__METHOD__ ."] << ");
        
        return isset($dados_app["result"]) ? $dados_app["result"] : array();
    }

    public function getSubscriptionToken($AccountID, $SubscriptionID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$AccountID}, {$SubscriptionID} ");
        
        $param = "";
        $param["account_id"] = intval($AccountID); // SubscriptionID
        $param["subscription_id"] = intval($SubscriptionID); // SubscriptionID
        $dados_app = $this->busca("POA", "pem.APS.getSubscriptionToken", $param);
        if ($dados_app === false) {
            return array();
        }
        // [aps_token]
        // [controller_uri]
        $this->logger->debug("[".__METHOD__ ."] << ");
        
        return isset($dados_app["result"]) ? $dados_app["result"] : array();
    }

    public function getSubscriptionAPS2($AccountID, $SubscriptionID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> $AccountID, $SubscriptionID ");
        
        $token = getSubscriptionToken($AccountID, $SubscriptionID);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, POA_APS_2_RESOURCE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'APS-Token:' . $token['aps_token']
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $aps_resources = curl_exec($ch);
        curl_close($ch);
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return json_decode($aps_resources, true);
    }

    public function getAllServiceInstancesFromAplicationInstance($AplicationInstanceID, $ServiceID = "")
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$AplicationInstanceID}, {$ServiceID} ");
        $param = "";
        $param["application_instance_id"] = $AplicationInstanceID;
        $param["service_id"] = $ServiceID;
        $dados_app = $this->busca("POA", "pem.APS.getServiceInstances", $param);
        if ($dados_app === false) {
            return array();
        }
        // [service_instance_id]
        // [service_id]
        // [service_instance_status]
        // [parent_service_instance_id]
        // [resource_type_id]
        // [settings]
        // * [name]
        // * [value]
        $this->logger->debug("[".__METHOD__ ."] << ");
        return isset($dados_app["result"]) ? $dados_app["result"] : array();
    }

    public function getAllSettingsFromAplicationInstance($AplicationInstanceID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> $AplicationInstanceID ");
        $param = "";
        $param["application_instance_id"] = $AplicationInstanceID;
        $dados_app = $this->busca("POA", "pem.APS.getApplicationInstanceSettings", $param);
        if ($dados_app === false) {
            return array();
        }
        // * [name]
        // * [value]
        $this->logger->debug("[".__METHOD__ ."] << ");
        return isset($dados_app["result"]) ? $dados_app["result"] : array();
    }

    public function getAllPlanPeriodsFromPlan($PlanID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> $PlanID ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "PlanPeriodListGet_API";
        $param["Params"][] = intval($PlanID);
        $param["Params"][] = 2;
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $plan_period = array();
        
        foreach ($dados as $period) {
            $plan_period[] = array(
                'PlanPeriodID' => $period[0],
                'Period' => $period[1],
                'PeriodType' => $period[2],
                'Trial' => $period[3],
                'SetupFee' => $period[4],
                'SubscriptionFee' => $period[5],
                'RenewalFee' => $period[6],
                'TransferFee' => $period[7],
                'NonRefundableAmount' => $period[8],
                'RefundPeriod' => $period[9],
                'Enabled' => $period[10],
                'FeeText' => $period[11],
                'SortNumber' => $period[12],
                'IsOTFI' => $period[13],
                'DepositFee' => $period[14],
                'DepositDescr' => $period[15]
            );
        }
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $plan_period;
    }

    public function getCategory($CategoryID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$CategoryID} ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "PlanCategoryDetailsGet_API";
        $param["Params"][] = intval($CategoryID); // AccountID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return array(
            'CategoryID' => trim($dados[0]),
            'Name' => trim($dados[1]),
            'Description' => trim($dados[2]),
            'OwnerAccountID' => trim($dados[3])
        );
    }

    public function getAllSubscriptionsFromAccount($AccountID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$AccountID} ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "GetCustomerSubscriptionList_API";
        $param["Params"][] = intval($AccountID); // AccountID
        $param["Params"][] = - 1; // AccountID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $subscriptions = array();
        
        foreach ($dados as $subs) {
            $subscriptions[] = array(
                'SubscriptionID' => $subs[0],
                'SubscriptionName' => $subs[1],
                'PlanID' => $subs[2],
                'PlanName' => $subs[3],
                'PlanPeriodID' => $subs[4],
                'Period' => $subs[5],
                'PeriodType' => $subs[6],
                'StartDate' => $subs[7],
                'ExpirationDate' => $subs[8],
                'Status' => $subs[9],
                'ServStatus' => $subs[10],
                'ContainerName' => $subs[11]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $subscriptions;
    }

    public function getAllSubscriptionsFromResourceRate($ResourceRateID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$ResourceRateID} ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "SubscriptionListByResourceRateGet_API";
        $param["Params"][] = intval($ResourceRateID); // AccountID
        $param["Params"][] = - 1; // AccountID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $subscriptions = array();
        
        foreach ($dados as $subs) {
            $subscriptions[] = array(
                'SubscriptionID' => $subs[0],
                'SubscriptionName' => $subs[1],
                'PlanID' => $subs[2],
                'PlanName' => $subs[3],
                'PlanPeriodID' => $subs[4],
                'Status' => $subs[5],
                'ServStatus' => $subs[6]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $subscriptions;
    }

    public function getResourceList($SubscriptionID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$SubscriptionID} ");
        $param = "";
        $param["Server"] = "BM";
        $param["Method"] = "SubscriptionResourcesListGet_API";
        $param["Params"][] = intval($SubscriptionID);
        $param["Params"][] = 1;
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        
        $resources = array();
        $dados = $dados_aux["Result"][0];
        foreach ($dados as $recs) {
            $resources[] = array(
                'ResourceID' => $recs[0],
                'ResourceRateID' => $recs[1],
                'ResourceName' => $recs[2],
                'StoreDescription' => $recs[3],
                'StorePriceText' => $recs[4],
                'StoreSortOrder' => $recs[5],
                'Status' => $recs[6],
                'ResourceCategory' => $recs[7],
                'IncludedAmount' => $recs[8],
                'AdditionalAmount' => $recs[9],
                'UsedAmount' => floatval($recs[10]),
                'OrderedAmount' => floatval($recs[11]),
                'Unit' => $recs[12],
                'MinUnits' => $recs[13],
                'MaxUnits' => $recs[14],
                'Measurable' => $recs[15],
                'RelativeStatus' => $recs[16],
                'OrderNumber' => $recs[17],
                'SetupFee' => floatval($recs[18]),
                'RecurringFee' => floatval($recs[19]),
                'OveruseFee' => floatval($recs[20]),
                'Location' => $recs[21],
                'IsOveruseFeeTiered' => $recs[22],
                'IsRecurringFeeTiered' => $recs[23]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $resources;
    }

    public function getOrders($OrderTypeID = " ", $OrderStatusID = " ", $StartTime = "2012-01-01")
    {
        $this->logger->debug("[".__METHOD__ ."] >> $OrderTypeID, $OrderStatusID, $StartTime");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "OrderByStatusListGet_API";
        $param["Params"][] = intval(VENDOR_ID); // Int VendorAccountID; 1-embratel
        $param["Params"][] = strval($OrderTypeID); // Str OrderTypeID;
        $param["Params"][] = strval($OrderStatusID); // Str OrderStatusID;
        $param["Params"][] = strtotime($StartTime); // Int StartTime;
        $param["Params"][] = 1; // Int SortNo.
        $dados_aux = $this->busca('PBA', "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $orders = array();
        
        foreach ($dados as $order) {
            $orders[] = array(
                'OrderID' => trim($order[0]),
                'OrderNumber' => trim($order[1]),
                'CustomerID' => trim($order[2]),
                'CustomerName' => trim($order[3]),
                'OrderStatus' => trim($order[4]),
                'OrderType' => trim($order[5]),
                'CreationTime' => trim($order[6]),
                'OrderDate' => trim($order[7]),
                'Total' => trim($order[8]),
                'TaxTotal' => trim($order[9]),
                'DiscountTotal' => trim($order[10]),
                'MerchTotal' => trim($order[11]),
                'ExpirationDate' => trim($order[12]),
                'PromoCode' => trim($order[13]),
                'SalesBranchID' => trim($order[14]),
                'SalesBranchName' => trim($order[15]),
                'SalesPersonID' => trim($order[16]),
                'SalesPersonName' => trim($order[17]),
                'Comments' => trim($order[18])
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $orders;
    }

    /**
     * Retorna as informações básicas dos pedidos de uma assinatura
     * 
     * @param int $SubscriptionID
     *            ID da assinatura
     * @return array Lista com os pedidos, se não existirem retorna um array vazio
     */
    public function getAllOrdersFromSubscription($SubscriptionID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$SubscriptionID}");
        $param = "";
        $param["Server"] = "BM";
        $param["Method"] = "SalesOrderGetSalesOrderBySubscList";
        $param["Params"][] = $SubscriptionID; // $SubscriptionID
        $param["Params"][] = 1; // sort
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            $this->logger->debug("<< getAllOrdersFromSubscription: array()");
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $orders = array();
        foreach ($dados as $o) {
            $orders[] = array(
                'OrderID' => $o[0],
                'OrderNumber' => $o[1],
                'OrderType' => $o[2],
                'OrderDate' => $o[3],
                'StatusIcon' => $o[4],
                'Status' => $o[5],
                'OrderValue' => $o[6],
                'TimeWait' => $o[7],
                'User' => $o[8]
            );
        }
        $this->logger->verbose("[".__METHOD__ ."] ". print_r($orders, true));
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $orders;
    }

    /**
     * Retorna as informações completas dos pedidos de uma assinatura.
     * Faz uma chamada
     * para a API getAllOrdersFromSubscription() e para cada registro retornado faz uma
     * chamada ao getOrder() e funde as informações de ambos.
     * 
     * @param int $SubscriptionID
     *            ID da assinatura
     * @return array Lista com os pedidos, se não existirem retorna um array vazio
     */
    public function getAllOrdersWithAllInfoFromSubscription($SubscriptionID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$SubscriptionID} ");
        $detailedOrderCoolection = array();
        $shortOrderCollection = getAllOrdersFromSubscription($SubscriptionID);
        foreach ($shortOrderCollection as $shortOrder) {
            $order = getOrder($shortOrder['OrderID']);
            $order['StatusIcon'] = $shortOrder['StatusIcon'];
            $order['Status'] = $shortOrder['Status'];
            $order['OrderValue'] = $shortOrder['OrderValue'];
            $order['TimeWait'] = $shortOrder['TimeWait'];
            $order['User'] = $shortOrder['User'];
            
            $detailedOrderCoolection[] = $order;
        }
        $this->logger->verbose("[".__METHOD__ ."] " . print_r($detailedOrderCoolection, true));
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $detailedOrderCoolection;
    }

    public function getOrder($OrderID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$OrderID} ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "GetOrder_API";
        $param["Params"][] = intval($OrderID);
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $order = $dados_aux["Result"][0];
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return array(
            'OrderID' => trim($order[0]),
            'OrderNumber' => trim($order[1]),
            'VendorAccountID' => trim($order[2]),
            'CustomerID' => trim($order[3]),
            'OrderStatus' => trim($order[4]),
            'OrderType' => trim($order[5]),
            'CreationTime' => trim($order[6]),
            'OrderDate' => trim($order[7]),
            'Total' => trim($order[8]),
            'TaxTotal' => trim($order[9]),
            'DiscountTotal' => trim($order[10]),
            'MerchTotal' => trim($order[11]),
            'Comments' => trim($order[12]),
            'ExpirationDate' => trim($order[13]),
            'PromoCode' => trim($order[14]),
            'SalesBranchID' => trim($order[15]),
            'SalesPersonID' => trim($order[16]),
            'CurrencyID' => trim($order[17])
        );
    }

    public function getAllSubscriptionsFromOrder($OrderID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$OrderID} ");
        
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "GetSubscriptionsListByOrder_API";
        $param["Params"][] = intval($OrderID); // AccountID
        $param["Params"][] = - 1; // AccountID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $subscriptions = array();
        
        foreach ($dados as $subs) {
            $subscriptions[] = array(
                'SubscriptionID' => $subs[0]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $subscriptions;
    }

    public function getOrderFinancialDetails($OrderID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$OrderID} ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "OrderFinDetailsListGetExt_API";
        $param["Params"][] = intval($OrderID); // Order id
        $param["Params"][] = intval(1); // Sort number
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $orders = array();
        
        foreach ($dados as $order) {
            $orders[] = array(
                'DetailID' => trim($order[0]),
                'SKU' => trim($order[1]),
                'Description' => trim(str_replace('&#32;', "", $order[2])),
                'Quantity' => trim($order[3]),
                'UOM' => trim($order[4]),
                'Duration' => trim($order[5]),
                'Period' => trim($order[6]),
                'UnitPrice' => trim(str_replace('BRL ', "", $order[7])),
                'TaxCategory' => trim($order[8]),
                'DiscountAmount' => trim(str_replace('BRL ', "", $order[9])),
                'ExtendedPrice' => trim(str_replace('BRL ', "", $order[10])),
                'StartDate' => trim($order[11]),
                'EndDate' => trim($order[12]),
                'SubscriptionID' => trim($order[13]),
                'DetailType' => trim($order[14]),
                'ResourceDescription' => trim($order[15]),
                'Currency' => 'BRL'
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $orders;
    }

    public function getAllAssignedDocsFromOrder($OrderID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> $OrderID ");
        
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "AppliedDocsListGet_API";
        $param["Params"][] = intval($OrderID); // Int OrderID
        $param["Params"][] = 1; // DocType 1 - order
        $param["Params"][] = 1;
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $docs = array();
        /**
         * DocType 1 - order , 2- doc
         * DocTypeDet 20 - Invoice, 50 - Payment, 70 - Refund, 75 - Void Check, 80 - Credit Memo, 90 - Debit Memo, 100 - Fraud Check
         */
        foreach ($dados as $doc) {
            $docs[] = array(
                'DocID' => trim($doc[0]),
                'DocType' => trim($doc[1]),
                'AdjType' => trim($doc[2]),
                'DocTypeDet' => trim($doc[3]),
                'AdjSum' => trim($doc[4]),
                'CurrencyID' => trim($doc[5]),
                'AdjustID' => trim($doc[6]),
                'ReversedByAdjID' => trim($doc[7])
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $docs;
    }

    public function getAllAssignedDocsFromDoc($DocID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$DocID} ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "AppliedDocsListGet_API";
        $param["Params"][] = intval($DocID); // Int OrderID
        $param["Params"][] = 2; // DocType 2 - document
        $param["Params"][] = 1;
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $docs = array();
        /**
         * DocType 1 - order , 2- doc
         * DocTypeDet 20 - Invoice, 50 - Payment, 70 - Refund, 75 - Void Check, 80 - Credit Memo, 90 - Debit Memo, 100 - Fraud Check
         */
        foreach ($dados as $doc) {
            $docs[] = array(
                'DocID' => trim($doc[0]),
                'DocType' => trim($doc[1]),
                'AdjType' => trim($doc[2]),
                'DocTypeDet' => trim($doc[3]),
                'AdjSum' => trim($doc[4]),
                'CurrencyID' => trim($doc[5]),
                'AdjustID' => trim($doc[6]),
                'ReversedByAdjID' => trim($doc[7])
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $docs;
    }

    public function getAllLogFromOrder($OrderID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$OrderID} ");
        
        $param = "";
        $param["Server"] = "BM";
        $param["Method"] = "GetOrderLogList";
        $param["Params"][] = $OrderID; // OrderID
        $param["Params"][] = "1"; // SortNo
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $logs = array();
        
        foreach ($dados as $log) {
            $logs[] = array(
                'LogID' => trim($log[0]),
                'StatusID' => trim($log[1]),
                'StatusName' => trim($log[2]),
                'Description' => trim($log[3]),
                'Login' => trim($log[4]),
                'Date' => trim($log[5])
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $logs;
    }

    public function getPayment($DocID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> $DocID ");
        
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "PaymentInfoGet_API";
        $param["Params"][] = intval($DocID);
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $doc = $dados_aux["Result"][0];
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return array(
            'DocID' => trim($doc[0]),
            'DocNum' => trim($doc[1]),
            'DocType' => trim($doc[2]),
            'Status' => trim($doc[3]),
            'CurrencyID' => trim($doc[4]),
            'Total' => trim($doc[5]),
            'TaxTotal' => trim($doc[6]),
            'DocBBalance' => trim($doc[7]),
            'AvailBal' => trim($doc[8]),
            'Vendor_AccountID' => trim($doc[9]),
            'Customer_AccountID' => trim($doc[10]),
            'Crtd_DateTime' => trim($doc[11]),
            'Crtd_User_UsersID' => trim($doc[12]),
            'DocDate' => trim($doc[13]),
            'Description' => trim($doc[14]),
            'BranchID' => trim($doc[15]),
            'SalesID' => trim($doc[16]),
            'PayToolID' => trim($doc[17]),
            'ETransStatus' => trim($doc[18]),
            'FromIP' => trim($doc[19]),
            'AuthCode' => trim($doc[20]),
            'TrNumber' => trim($doc[21]),
            'PTransActivityID' => trim($doc[22]),
            'Benef_AccountID' => trim($doc[23]),
            'PluginID' => trim($doc[24]),
            'ChrgAttempts' => trim($doc[25]),
            'PayToolType' => trim($doc[26])
        );
    }

    public function validateUser($Login, $Password)
    {
        $this->logger->debug("[".__METHOD__ ."] >> $Login ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "UserForVendorValidate_API";
        $param["Params"][] = strval($Login);
        $param["Params"][] = strval($Password);
        $param["Params"][] = VENDOR_ID;
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $user = $dados_aux["Result"][0];
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return array(
            'AccountID' => trim($user[0]),
            'PasswordStrength' => trim($user[1]),
            'UserID' => trim($user[2])
        );
    }

    public function getAccountByCustomAttribute($campo, $valor)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$campo}, {$valor}");
        
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "GetAccountListByCustomAttribute_API";
        $param["Params"][] = strval($campo); // AttributeID = "CPF" ou "CNPJ"
        $param["Params"][] = strval($valor); // AttributeValue
        $param["Params"][] = 1; // SortNo.
        $dados_raw = $this->busca("PBA", 'Execute', $param);
        if ($dados_raw === false) {
            return array();
        }
        $dados = $dados_raw["Result"][0];
        
        $accounts = array();
        foreach ($dados as $acc) {
            $local_acc_det = getAccount($acc[0]);
            // Apenas clientes Embratel
            if ((int) $local_acc_det['VendorAccountID'] === VENDOR_ID) {
                $accounts[] = array(
                    "AccountID" => trim($acc[0]),
                    "AccountName" => trim($acc[1])
                );
            }
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $accounts;
    }

    public function getUsers($AccountID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$AccountID} ");
        
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "GetUsersListForAccount_API";
        $param["Params"][] = intval($AccountID);
        $param["Params"][] = 1;

        $dados_aux = $this->busca("PBA", 'Execute', $param);
        if ($dados_aux === false) {
            return array();
        }
        
        $dados = $dados_aux['Result'][0];
        
        $users = array();
        foreach ($dados as $user) {
            $users[] = array(
                'UserID' => trim($user[0]),
                'Login' => trim($user[1]),
                'FullName' => trim($user[2])
            );
        }
        $this->logger->verbose("[".__METHOD__ ."] " . print_r($users, true));
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $users;
        // Full Access
    }

    public function getAccount($AccountID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$AccountID}");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "AccountDetailsGet_API";
        $param["Params"][] = intval($AccountID); // AccountID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return array(
            'AccountID' => trim($dados[0]),
            'VendorAccountID' => trim($dados[1]),
            'CompanyName' => trim($dados[2]),
            'Address1' => trim($dados[3]),
            'Address2' => trim($dados[4]),
            'City' => trim($dados[5]),
            'State' => trim($dados[6]),
            'Zip' => trim($dados[7]),
            'CountryID' => trim($dados[8]),
            'PostalAddress' => trim($dados[9]),
            'accFName' => trim($dados[10]),
            'accMName' => trim($dados[11]),
            'accLName' => trim($dados[12]),
            'accEmail' => trim($dados[13]),
            'accPhCountryCode' => trim($dados[14]),
            'accPhAreaCode' => trim($dados[15]),
            'accPhNumber' => trim($dados[16]),
            'accPhExtention' => trim($dados[17]),
            'accFaxCountryCode' => trim($dados[18]),
            'accFaxAreaCode' => trim($dados[19]),
            'accFaxNumber' => trim($dados[20]),
            'accFaxExtention' => trim($dados[21]),
            'CreationDate' => trim($dados[22]),
            'TaxStatus' => trim($dados[23]),
            'AStatus' => trim($dados[23]),
            'FullyRegistred' => trim($dados[23])
        );
        
    }

    public function getAccountExtended($AccountID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$AccountID} ");
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "AccountExtendedDetailsGet_API";
        $param["Params"][] = intval($AccountID); // AccountID
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return array(
            'AccountID' => trim($dados[0]),
            'CompanyName' => trim($dados[1]),
            'Address1' => trim($dados[2]),
            'Address2' => trim($dados[3]),
            'City' => trim($dados[4]),
            'State' => trim($dados[5]),
            'Zip' => trim($dados[6]),
            'CountryID' => trim($dados[7]),
            'PostalAddress' => trim($dados[8]),
            'AdminFName' => trim($dados[9]),
            'AdminMName' => trim($dados[10]),
            'AdminLName' => trim($dados[11]),
            'AdminEmail' => trim($dados[12]),
            'AdminPhCountryCode' => trim($dados[13]),
            'AdminPhAreaCode' => trim($dados[14]),
            'AdminPhNumber' => trim($dados[15]),
            'AdminPhExtention' => trim($dados[16]),
            'AdminFaxCountryCode' => trim($dados[17]),
            'AdminFaxAreaCode' => trim($dados[18]),
            'AdminFaxNumber' => trim($dados[19]),
            'AdminFaxExtention' => trim($dados[20]),
            'BillFName' => trim($dados[21]),
            'BillMName' => trim($dados[22]),
            'BillLName' => trim($dados[23]),
            'BillEmail' => trim($dados[24]),
            'BillPhCountryCode' => trim($dados[25]),
            'BillPhAreaCode' => trim($dados[26]),
            'BillPhNumber' => trim($dados[27]),
            'BillPhExtention' => trim($dados[28]),
            'BillFaxCountryCode' => trim($dados[29]),
            'BillFaxAreaCode' => trim($dados[30]),
            'BillFaxNumber' => trim($dados[31]),
            'BillFaxExtention' => trim($dados[32]),
            'TechFName' => trim($dados[33]),
            'TechMName' => trim($dados[34]),
            'TechLName' => trim($dados[35]),
            'TechEmail' => trim($dados[36]),
            'TechPhCountryCode' => trim($dados[37]),
            'TechPhAreaCode' => trim($dados[38]),
            'TechPhNumber' => trim($dados[39]),
            'TechPhExtention' => trim($dados[40]),
            'TechFaxCountryCode' => trim($dados[41]),
            'TechFaxAreaCode' => trim($dados[42]),
            'TechFaxNumber' => trim($dados[43]),
            'TechFaxExtention' => trim($dados[44]),
            'ClassID' => trim($dados[45]),
            'TaxZoneID' => trim($dados[46])
        );
    }

    public function getAllPaytoolsFromAccount($AccountID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$AccountID} ");
        
        $param = "";
        $param["Server"] = "BM";
        $param["Method"] = "PayToolListForAccountGet_API";
        $param["Params"][] = $AccountID; // accountId
        $param["Params"][] = "11"; // sortOrder
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $paytools = array();
        
        foreach ($dados as $pay) {
            $paytools[] = array(
                'PayToolID' => $pay[0],
                'PayToolType' => $pay[1],
                'PaySystemID' => $pay[2],
                'PaySystem' => $pay[3],
                'CutNumber' => $pay[4],
                'ExpDate' => $pay[5],
                'CardHolderName' => $pay[6],
                'AccHolderName' => $pay[7],
                'BankNumber' => $pay[8],
                'AccountNumber' => $pay[9],
                'UseForAutoPayments' => $pay[10]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $paytools;
    }

    public function getUser($UserID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> $UserID ");
        
        $param = array();
        $param["Server"] = "BM";
        $param["Method"] = "UserDetailsGet_API";
        $param["Params"][] = intval($UserID); // UserID.
        $dados_aux = $this->busca("PBA", 'Execute', $param);
        if ($dados_aux === false) {
            return array();
        }
        $attr = $dados_aux["Result"][0];
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return array(
            'UserID' => $attr[0],
            'Login' => trim($attr[1]),
            'AccountID' => $attr[2],
            'FName' => trim($attr[3]),
            'MName' => trim($attr[4]),
            'LName' => trim($attr[5]),
            'Email' => trim($attr[6]),
            'Address1' => trim($attr[7]),
            'Address2' => trim($attr[8]),
            'City' => trim($attr[9]),
            'State' => trim($attr[10]),
            'Zip' => trim($attr[11]),
            'CountryID' => $attr[12],
            'PhCountryCode' => trim($attr[13]),
            'PhAreaCode' => trim($attr[14]),
            'PhNumber' => trim($attr[15]),
            'PhExtention' => trim($attr[16]),
            'FaxCountryCode' => trim($attr[17]),
            'FaxAreaCode' => trim($attr[18]),
            'FaxNumber' => trim($attr[19]),
            'FaxExtention' => trim($attr[20]),
            'Status' => trim($attr[21])
        );
    }

    /**
     * Busca todos os usuarios de uma certa conta
     * 
     * @param int $SubscriptionID            
     * @return array Retornar um array com os dados do usuario (vide getUser())
     */
    public function getFirstUserActiveBySubscription($SubscriptionID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$SubscriptionID} ");

        $firstUserEnabled = '';
        $userCollection = getUsersBySubscription($SubscriptionID);
        foreach ($userCollection as $user) {
            $userDetail = getUser($user['UserID']);
            /**
             * Esta escrito na documentação da Parallels
             * 0 if the user is enabled
             * 1 if the user is disabled
             */
            if ($userDetail['Status'] == '0') {
                // Assim que acho um usuario ativo eu retorno
                $firstUserEnabled = $user;
                break;
            }
        }
        $this->logger->verbose("[".__METHOD__."] " . print_r($firstUserEnabled, true));
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $firstUserEnabled;
    }

    public function getUsersBySubscription($SubscriptionID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$SubscriptionID} ");
        $subscriptionDetails = $this->getSubscription($SubscriptionID);
        if (isset($subscriptionDetails['AccountID'])) {
            $this->logger->debug("[".__METHOD__ ."] << ");
            return $this->getUsers($subscriptionDetails['AccountID']);
        }
        $this->logger->debug("[".__METHOD__ ."] << empty");
        return array();
    }

    public function getUserRoles($UserID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$UserID}");
        $param = "";
        $param["member_id"] = intval($UserID); // Int Staff Member ID;
        $dados = $this->busca("POA", "pem.getAccountMemberRoles", $param);
        if ($dados === false) {
            $this->logger->debug("[".__METHOD__ ."] << empty");
            return array();
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $dados['result']['roles'];
    }

    /**
     * Send e-mail using PBA template
     * @param string $TemplateName Template code
     * @param integer $SubscriptionID Subscription ID
     * @param integer $UserID PBA User ID
     * @param array $PlaceHolders Values to send to template: array( 'key' => 'value', ...)
     */
    public function sendNotification($TemplateName, $SubscriptionID, $UserID, $PlaceHolders)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$TemplateName}, {$SubscriptionID}, {$UserID}");
        
        $param = array();
        $param["Server"] = "MESSAGE";
        $param["Method"] = "SendSubscriptionNotificationForUser_API";
        $param["Params"][] = strval($TemplateName);
        $param["Params"][] = intval($SubscriptionID);
        $param["Params"][] = intval($UserID);
        foreach ($PlaceHolders as $key => $value) {
            $param["Params"][] = strval($key);
            $param["Params"][] = strval($value);
        }
        $dados = $this->busca('PBA', 'Execute', $param);
        if ($dados === false) {
            $this->logger->debug("[".__METHOD__ ."] << false");
            return false;
        }
        $this->logger->debug("[".__METHOD__ ."] << {$dados['Result'][0]['Status']}");
        return $dados['Result'][0]['Status'];
    }

    public function getAllAttributesForAccount($AccountID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> {$AccountID} ");
        
        $param = "";
        $param["Server"] = "BM";
        $param["Method"] = "GetObjAttrListExt_API";
        $param["Params"][] = "0"; // Object type 0 = account
        $param["Params"][] = $AccountID; // accountId
        $param["Params"][] = "1"; // sortOrder
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $attributes = array();
        
        foreach ($dados as $attr) {
            $attributes[] = array(
                'AttributeID' => $attr[0],
                'TypeOfAttribute' => $attr[1],
                'Value' => $attr[2],
                'Tags' => $attr[3]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $attributes;
    }

    public function getAllAttributesForUser($UserID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> $UserID ");
        
        $param = "";
        $param["Server"] = "BM";
        $param["Method"] = "GetObjAttrListExt_API";
        $param["Params"][] = "1"; // Object type 1 = user
        $param["Params"][] = $UserID; // user_id
        $param["Params"][] = "1"; // sortOrder
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $attributes = array();
        
        foreach ($dados as $attr) {
            $attributes[] = array(
                'AttributeID' => $attr[0],
                'TypeOfAttribute' => $attr[1],
                'Value' => $attr[2],
                'Tags' => $attr[3]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $attributes;
    }

    public function getAllAttributesForOrder($OrderID)
    {
        $this->logger->debug("[".__METHOD__ ."] >> $OrderID ");
        $param = "";
        $param["Server"] = "BM";
        $param["Method"] = "GetObjAttrListExt_API";
        $param["Params"][] = "2"; // Object type 2 = order
        $param["Params"][] = $OrderID; // accountId
        $param["Params"][] = "1"; // sortOrder
        $dados_aux = $this->busca("PBA", "Execute", $param);
        if ($dados_aux === false) {
            return array();
        }
        $dados = $dados_aux["Result"][0];
        
        $attributes = array();
        
        foreach ($dados as $attr) {
            $attributes[] = array(
                'AttributeID' => $attr[0],
                'TypeOfAttribute' => $attr[1],
                'Value' => $attr[2],
                'Tags' => $attr[3]
            );
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $attributes;
    }

    /**
     * Method increments the resources usage of specified subscription by amount specified.
     * Resource usage is increased or decreased depending on whether delta amount is positive or negative.
     *
     * @param int $SubscriptionID            
     * @param array $ResourcesIncrement
     *            an array of n ['ResourceID' => int, 'DeltaAmount' => double]
     * @return $message
     */
    public function incrementResourceUsage($SubscriptionID, $ResourcesIncrement = array())
    {
        $this->logger->debug("[".__METHOD__ ."] >> $SubscriptionID");
        $param = "";
        $param["Server"] = "DUMMYGATE";
        $param["Method"] = "IncrementResourceUsage_API";
        $param["Params"][] = intval($SubscriptionID);
        foreach ($ResourcesIncrement as $res) {
            $param["Params"][] = intval($res['ResourceID']);
            $param["Params"][] = doubleval($res['DeltaAmount']);
        }
        $dados = $this->busca("PBA", "Execute", $param);
        if ($dados === false) {
            return array();
        }
        
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $dados['Result'][0]['Status'];
    }

    /**
     * Method increments the resources usage of specified subscription by amount specified.
     * Resource usage is increased or decreased depending on whether delta amount is positive or negative.
     *
     * <b>Note:</b> before using this method, please make sure that the option "Allow Manual Resource Usage Update" is set to "Yes" in the Service Gate Settings of the Dummy Gate.
     *
     * @param int $SubscriptionID            
     * @param array $ResourcesAmount
     *            an array of n ['ResourceID' => int, 'FullAmount' => double]
     * @return $message
     */
    public function setResourceUsage($SubscriptionID, $ResourcesAmount = array())
    {
        $this->logger->debug("[".__METHOD__ ."] >> $SubscriptionID");
        $param = "";
        $param["Server"] = "DUMMYGATE";
        $param["Method"] = "SetResourceUsage_API";
        $param["Params"][] = intval($SubscriptionID);
        foreach ($ResourcesAmount as $res) {
            $param["Params"][] = intval($res['ResourceID']);
            $param["Params"][] = doubleval($res['FullAmount']);
        }
        $dados = $this->busca("PBA", "Execute", $param);
        if ($dados === false) {
            return array();
        }
        $this->logger->debug("[".__METHOD__ ."] << ");
        return $dados['Result'][0]['Status'];
    }
    
    
    public function getCustomerClass($customer_class_id){
    	$this->logger->debug("[".__METHOD__ ."] >> $customer_class_id");
    	$param = "";
    	$param["Server"] = "BM";
    	$param["Method"] = "CustomerClassGet";
    	$param["Params"][] = intval($customer_class_id);

    	$dados = $this->busca("PBA", "Execute", $param);
    	if ($dados === false) {
    		return array();
    	}
    	$dados = $dados['Result'][0];
    	
    	$this->logger->debug("[".__METHOD__ ."] << ");
        return array(
            'ClassID' => $dados[0],
            'Name' => trim($dados[1]),
            'AccountID' => $dados[2],
            'IsVendorDefault' => trim($dados[3]),
            'TermID' => trim($dados[4]),
            'CycleID' => trim($dados[5]),
            'CCExpScheduleID' => trim($dados[6]),
            'PromoCode' => trim($dados[7]),
            'IsStatementReq' => trim($dados[8]),
            'AutoApply' => trim($dados[9]),
            'InvBillingType' => trim($dados[10]),
            'Color' => trim($dados[11]),
            'ForcePlansForCust' => $dados[12],
            'BOPerSubscription' => trim($dados[13]),
        );
    }
}
?>