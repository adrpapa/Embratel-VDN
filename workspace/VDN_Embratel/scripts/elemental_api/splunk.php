<?php

require_once "configConsts.php";
require_once "elementalRest.php";

class SplunkStats {
	
	
	public $lastQueryTime;
	public $lastResultTime;
	public $gigaTransfered = 0;
	/*
	** Função para obter o billing dpo CISCO VDS através do Splunk
	** Recebe como parametros:
	** deliveryService = nome do delivery service a colher estatísticas
	** lastQueryTime = horário(máquina local) quando foi feita a última busca bem sucedida string formato ISO
	** lastResultTime = horário do último resultado acumulado string formato ISO
	** retorna um objeto SplunkStats contendo essas mesma datas atualizadas, 
	** e o acumulado de GB desde a última medição
	*/
	public static function getBilling( $clientID, $deliveryService, $lastResultTime ) {
		$urlMask = "http://" . ConfigConsts::SPLUNK_ADDRESS 
				. ConfigConsts::SPLUNK_ENDPOINT
				. ConfigConsts::SPLUNK_QUERY;
		$current_ts = date_create();
		$last_ts = clone $current_ts;
		$last_ts->modify('-7 day');
		if($lastResultTime != null) {
			$last_ts = $lastResultTime == null ?  : date_create($lastResultTime);
		}
		$diff = ($current_ts->getTimestamp() - $last_ts->getTimestamp())/60;
		echo "current ts=".date_format( $current_ts, "c")."last ts=".date_format( $last_ts, "c")." diff=".$diff."\n";
		$splunkStats = new self();
		$splunkStats->lastResultTime = $lastResultTime;
		$splunkStats->gigaTransfered = 0;
		$billingLogPath = ConfigConsts::BILLING_LOG_PATH.date_format( $current_ts, "/Y/m/");
		if (!file_exists($billingLogPath)) {
			mkdir($billingLogPath, 0755, true);
		}
		$billingLog = $billingLogPath.$clientID."_splunk.log";
		$billingFail = $billingLogPath.$clientID."_splunk_error.log";
		file_put_contents($billingLog, date_format( $current_ts, "c") . " debug... Lookup Billing ".$deliveryService." last result time:".$lastResultTime."\n", FILE_APPEND);
		while( $diff > 5 ){
			// decidimos qual o intervalo a utilizar - 10min, 1hora ou 24horas
			// para recuperar dados em caso de parada na coleta. Nesse caso, só faz
			// busca nas 24horas (granul. dia) depois na hora anterior
			if( $diff > 11 ) {
				if( $diff > 61 ) {
					$timeRange='24h';
					$bucketSize='1h';
					$diff = 60;
				} else {
					$timeRange='1h';
					$bucketSize='5m';
					//TODO trocar por diff 0 porque não pode pedir 10 min
					//senão perde os 5 min da hora atual até 10 min antes de agora
					//complicado? acredita! muda para 0 que vai funcionar sem banguela
					//só se ficar fora por mais de 24hs...
					$diff = 10;
				}
			} else {
				$timeRange='10m';
				$bucketSize='5m';
				$diff = 0;
			}
			$curl_obj = new ElementalRest(ConfigConsts::SPLUNK_ADDRESS,'servlet');
			curl_setopt($curl_obj->ch, CURLOPT_URL, 
					sprintf($urlMask, $timeRange, $bucketSize, $deliveryService));
			echo sprintf($urlMask, $timeRange, $bucketSize, $deliveryService)."\n";
			$data = curl_exec($curl_obj->ch);
			// TODO Quebrar log por data..
			
			if( ! isset($data) || $data == '"None"' || $data == '""') {
				file_put_contents($billingFail, date_format( $current_ts, "c")
					  . " ***** No data for ".$deliveryService." url: ".$urlMask."\n", FILE_APPEND);
				continue;
			}
// 			echo $data." ===== data\n\n";
			$rlstObj=json_decode(json_decode($data));
			foreach( $rlstObj->results as $result ) {
				if( $result->_time > $splunkStats->lastResultTime ) {
					$splunkStats->gigaTransfered += $result->TotalGB;
					$splunkStats->lastResultTime = $result->_time;
					file_put_contents($billingLog,date_format( $current_ts, 'c').", $result->DeliveryServiceName, $result->_time, $result->TotalGB\n", FILE_APPEND);
					echo "$result->DeliveryServiceName, $result->_time, $result->TotalGB\n";
				}
			}
		}
// 		if( ConfigConsts::debug ) {
// 			var_dump($data);
// 			print( $data );
// 		}
		return $splunkStats;
	}
}

// $splunkStats = SplunkStats::getBilling("Client_1000001","ds-eventodeltalive-1000001", null, null);
// var_dump($splunkStats);
// $splunkStats = SplunkStats::getBilling("Client_1000001","ds-eventodeltaive-1000001", $splunkStats->lastResultTime);
// var_dump($splunkStats);
// $splunkStats = SplunkStats::getBilling("Client_1000001","ds-eventodeltaive-1000001", $splunkStats->lastResultTime);
// var_dump($splunkStats);

// 	$last_update = date_create("2016-07-20T15:00:00-0300");
// 	$current_date = date_create();
// 	print_r($diff);

?>
