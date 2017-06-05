<?php

require_once realpath(dirname( __FILE__ ))."/../loader.php";
require_once realpath(dirname( __FILE__ ))."/../elemental_api/configConsts.php";

class UsageReport {
	# recebe codigo do cliente (6 digitos) inicio e fim formato aaaa-mm-dd
	function __construct ( $client, $start, $end, $byDate, $byDS ) {
		$this->client = $client ;
		$this->start  = $start;
		$this->startDate = strtotime($start);
		$this->end    = $end;
		$this->endDate = strtotime($end);
		$this->byDS   = $byDS;
		$this->byDate = $byDate;
		$this->header = array(
			_("Serviço de Entrega"),
			_("Recurso"),
			_("Data/Hora"),
			_("Consumo(GB)"),
			"ID"
		);
		$this->dir = ConfigConsts::$BILLING_LOG_PATH;
		$this->filenameMask = "%s/%04d/%02d/Client_%06d_usage.log";
		$this->titles = array();
		if( $this->byDS ) {
			array_push($this->titles, $this->header[0]);
		}
		array_push($this->titles, $this->header[1]);
		if( $this->byDate ) {
			array_push($this->titles, $this->header[2]);
		}
		array_push($this->titles, $this->header[3]);
	}

	function genReport() {
		$this->lines = array();
		$startParts = explode("-", $this->start);
		$endParts = explode("-", $this->end);
		$month = $startParts[1];
		$day = $startParts[2];
		for( $year = $startParts[0] ; $year <= $endParts[0]; $year++ ){
			while( true ){
				$startDate = sprintf('%04d-%02d-%02d',
					$year, $month, $day);
				if( $startDate > $this->end ) {
					break;
				}

				$filename = sprintf($this->filenameMask,
					$this->dir, $year, $month, $this->client);
				if( file_exists($filename) ) {
					echo "Processing ". $startDate
						. " File: " . $filename. "\n";
					$this->processFile( $filename );
				} else {
					echo "File does not exist: " . $filename. "\n";
				}
				$month ++;
				if( $month > 12 ) {
					$month = 1;
					$day = 1;
					break;
				}
			}
		}
		$data = array();
		foreach( $this->lines as $line ) {
			$fields=array();
			foreach( $this->titles as $title ) {
				$fields[$title] = $line[$title];
			}
			array_push($data, $fields );
		}
		$usageReport = array(
			"titles" => $this->titles,
			"data" => $data
		);
		return $usageReport;
	}

	function processFile( $filename ) {
		# 0          1   2    3      4        5
		#"resultTime;id;>name;domain;resource;value";
		# 0                   1        2         3            4
		# Serviço de Entrega, Recurso, Data/Hora,Consumo(GB), ID
		if (($handle = fopen($filename, "r")) !== FALSE) {
			$line = array();
			while (($data = fgetcsv($handle, 300, ";")) !== FALSE) {
				if( sizeof($data ) < 4 )
					continue;
				$date = substr($data[0],0,10);
				if( $date < $this->start || $date > $this->end ) {
					echo "Record with date $date out of range $this->startDate => $this->endDate";
					continue;
				}
				$line = array(
					$this->header[0] => $data[2],
					$this->header[1] => $data[4],
					$this->header[2] => date('d/m/Y H:00',strtotime($data[0])),
					$this->header[3] => $data[5],
					$this->header[4] => $data[1]
				);
				$this->putLine($line);
			}
			fclose($handle);
		}
	}

	function putLine($line) {
		$key = ($this->byDS ? $line[$this->header[0]] : "");
		$key .= $line[$this->header[1]];
		if( $this->byDate ) {
			$key .= $line[$this->header[2]];
		}
		if( ! array_key_exists( $key, $this->lines) ) {
			$this->lines[$key] = $line;
			return;
		}
		$this->lines[$key][$this->header[3]] += $line[$this->header[3]];
		
	}
}

#function _ ($str){
#	return $str;
#}

?>
