<?php
    function cleanClientID( $ClientID ) {
        return cleanName( $ClientID );
    }

    function cleanName( $name ) {
        $ax_name = trim($name);
        $ax_name = preg_replace('/\s/', '_', $ax_name);
        $ax_name = preg_replace('/[^A-Za-z0-9\-\_]/', '', $ax_name);
        return $ax_name;
    }
    
    function getClientID( $context ) {
        $apsc = \APS\Request::getController();
        $subscription = $apsc->getResource($context->subscription->aps->id);
//         echo "\n********\nSubscription: ".$subscription->subscriptionId."\n***********\n\n";
        return $subscription->subscriptionId;
    }
    
    function formatClientID( $context ) {
        return sprintf("Client_%06d",getClientID( $context ));
    }
    
    function idFromHref($xml){
        $href = $xml["href"]."";      
        $toks = explode('/',$href);
        return $toks[count($toks)-1];
    }
    
    function getLogger($path){
        $logger = \APS\LoggerRegistry::get();
        $logger->setLogFile("logs/".$path);
        return $logger;
    }
    
    /*
    Convert text durations to milliseconds
    */
    function txtDuration2ms($duration){
        preg_match_all("|([\d\.]+)\s*([^\d^\s^\.]+)|", $duration, $time_parts);
        $multi = array("ms"=>1, "s"=>1000, "mn"=>60*1000, "h"=>60*60*1000, "d"=>24*60*60*1000 );
        $totalMiliSeconds = 0;
//              var_dump($time_parts);
        for( $ix=0; $ix < count($time_parts[0]); $ix++ ) {
            $unit = $time_parts[2][$ix];
            $multiplier = $multi[$unit] * $time_parts[1][$ix];
            $totalMiliSeconds += $multiplier;
            next($multi);
        }
        return $totalMiliSeconds;
    }
        
        
//     echo txtDuration2ms("1mn 2s");
?>
