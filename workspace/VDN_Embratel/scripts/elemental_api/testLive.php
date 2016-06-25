#!/usr/bin/env php

<?php    
    require_once "live.php";
    require_once "jobVOD.php";
    require_once "deltaOutputTemplate.php";
    require_once "deltaInput.php";

   parse_str(implode('&', array_slice($argv, 1)), $_GET);
   @$id = $_GET['id'];
   @$func = $_GET['func'];
   @$filter = $_GET['filter'];
   @$cmd = $_GET['cmd'];
   @$alvo = $_GET['alvo'];
    var_dump($cmd);
    var_dump($func);
   $type = "live";

//     @$id = 358;
//     @$func = 'del';
//     @$filter = NULL;
//     @$cmd = null;
//     @$alvo = 'vod';
//     $type = "vod";

    $name = "Job Teste Ftl 1";
    $clientID = "Cliente1";
    $level = "std";

    printf("\nUsando Codigo do Cliente=%s, Plano=%s, Tipo=%s, Label=%s\n\n", $clientID, $level, $type, $name);
    printf("\n\tAlvo=%s, Func=%s, cmd=%s\n\n", $alvo, $func, $cmd );
    
    if( $alvo === 'live' ) {
        if( $cmd == NULL && $func == NULL ) {
            print "usage:\n";
            print "Criar evento Live:\n";
            print "\ttestLive.php func=new\n";
            print "lista todos eventos live ativos:\n";
            print "\ttestLive.php func=list\n";
            print "Solicitar start/stop/cancel/reset para o evento id=n:\n";
            print "\ttestLive.php id=<n> cmd=[start|stop|cancel|reset|archive]";
            print "\n\n";
        }
        if( $cmd ){
            LiveEvent::getElementalRest()->postRecord($id, $cmd);
            exit();
        }

        switch( $func ) {
            case 'new':
                $live = LiveEvent::newStandardLiveEvent( $name, $clientID);
                break;
            case 'del':
                LiveEvent::delete($id);
                break;
            case 'list':
                $events = LiveEvent::getEventList($id, $filter);
                if($id) {
                    print($events->asXml());
                    printf("%s - %s %s\n",$events->id, $events->name, $events->status);
                } else {
                    foreach ( $events->live_event as $event ) {
                        $liveEvent = LiveEvent::liveEventFromXML($event);
                        printf("%02d - %-30s \t ===> %s\n",$liveEvent->id, $liveEvent->name, $liveEvent->status);
                    }
                }
                break;
        }
        return;
    }
    else {
    	if( $cmd ){
    		JobVOD::getElementalRest()->postRecord($id, $cmd);
    		exit();
    	}
    	
    	switch( $func ) {
    		case 'new':
    			ElementalRest::$auth = new Auth( 'elemental','elemental' );
    			$job = JobVOD::newJobVOD($name, "http://www.sample-videos.com/video/mp4/480/big_buck_bunny_480p_1mb.mp4", $clientID, $level);
    			break;    	
    		case 'del':
    				ElementalRest::$auth = new Auth( 'elemental','elemental' );
    				JobVOD::delete($id);
    				break;    			
    		case 'list':
    			ElementalRest::$auth = new Auth( 'elemental','elemental' );
    			$jobs = JobVOD::getJobList($id, $filter);
    			if($id) {
    				print($jobs->asXml());
    				printf("%s - %s %s\n",$jobs->id, $jobs->name, $jobs->status);
    			} else {
    				foreach ( $jobs->live_event as $job ) {
    					$job = JobVOD::jobVODFromXML($job);
    					printf("%02d - %-30s \t ===> %s\n",$job->id, $job->name, $job->status);
    				}
    			}
    			break;    			
    	}
    }

    if( $cmd == NULL && $func == NULL ) {
        throw new invalidargumentexception("\n\n***** Favor informar cmd(delete,start,stop...) ou func(new,del,list...) *****\n\n");
    }
    
    if( $alvo === 'deltaot' ) {
        switch( $func ) {
            case 'new':
                DeltaOutputTemplate::getClientOutputTemplate( $clientID, $type=$type, $level=$level);
                break;
            case 'del':
                DeltaOutputTemplate::delete($clientID, $type);
                break;
            case 'list':
                $outputTemplates = DeltaOutputTemplate::getOutputTemplateList($id);
                foreach ( $outputTemplates as $outputTemplate) {
                    printf("%s - %s\n",$outputTemplate->id, $outputTemplate->name);
                }
                break;
        }
        return;
    }

    if( $alvo === 'deltaif' ) {
        switch( $func ) {
            case 'new':
                if( $type === "live" ) {
                    DeltaInputFilter::newUdpInputFilter( $clientID, $name, $level );
                } else {
                    DeltaInputFilter::newVodInputFilter( $clientID, $level );
                }
                break;
            case 'del':
                DeltaInputFilter::delete($id);
                break;
            case 'list':
                $inputFilters = DeltaInputFilter::getInputFilterList($id);
                if($id) {
                    print_r($inputFilters);
                } else {
                    foreach ( $inputFilters->input_filter as $inputFilter) {
                        if( $inputFilter->filter_type != 'udp_input' &&
                            $inputFilter->filter_type != 'watch_folder_input') {
                            continue;
                        }
                        $href = explode('/', $inputFilter['href']);
                        if( (string)$inputFilter->filter_type === "watch_folder_input" ) {
                            printf("%3d - %-24s %-24s %s %s\n",end($href), $inputFilter->label,
                                    $inputFilter->filter_type,
                                    $inputFilter->filter_settings->template_id, 
                                    $inputFilter->filter_settings->incoming->uri);
                        } else {
                            $port = end(explode(':', $inputFilter->filter_settings->udp_input->uri));
                            printf("%3d - %-20s %-24s %s %s\n",end($href), $inputFilter->label, 
                                    $inputFilter->filter_type,
                                    $inputFilter->filter_settings->udp_input->uri, 
                                    $inputFilter->filter_settings->storage_location );
                        }
//                         print '\n\n'.$inputFilter->asXml().'\n\n';
                    }
                    break;
                }
        }
        return;
    }
    throw new invalidargumentexception("\n\n***** Favor informar alvo=deltaot|deltaif|live *****\n\n");

?>
