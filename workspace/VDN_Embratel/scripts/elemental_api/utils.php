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
    
    function formatClientID( $context ) {
        $apsc = \APS\Request::getController();
        $subscription = $apsc->getResource($context->subscription->aps->id);
        echo "\n********\nSubscription: ".$subscription->subscriptionId."\n***********\n\n";
        return sprintf("Client_%06d",$subscription->subscriptionId);
    }
?>
