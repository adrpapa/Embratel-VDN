<?php
    require_once "configConsts.php";
    require_once "elementalRest.php";
    require_once "utils.php";
    require_once "jobVOD.php";

    class DeltaContents {
    /*
    ** Classe que Lista conteúdos do cliente
    */
        public static function delete($id) {
        
            $outTemp = DeltaContents::getElementalRest()->restDelete($id);
        }

        public static function getElementalRest() {
            return new ElementalRest($hostname=ConfigConsts::DELTA_HOST,
                        $apiEndpoint='contents', $port=ConfigConsts::DELTA_PORT);
        }

        public static function getHLSFilter() {
            return new ElementalRest($hostname=ConfigConsts::DELTA_HOST,
                        $apiEndpoint='hls_contents', $port=ConfigConsts::DELTA_PORT);
        }

        public static function getContentsFromJob($jobID) {
            $jobXml = JobVOD::getElementalRest()->restGet($jobID);
//             print($jobXml->asXml());
            $input = $jobXml->input;
            $content = new DeltaContents();
            $content->input_uri  =   $input->file_input->uri."";
            $content->fileName = end(explode('/', $input->file_input->uri));
            // transformação do formato 2mn 26s em segundos
            preg_match_all("|([\d\.]+)\s*([^\d^\s^\.]+)|", $input->input_info->general->duration, $time_parts);
            $multi = array("ms"=>1, "s"=>1000, "mn"=>60*1000, "h"=>60*60*1000, "d"=>24*60*60*1000 );
            $content->totalMiliSeconds = 0;
             var_dump($time_parts);
            for( $ix=0; $ix < count($time_parts[0]); $ix++ ) {
                $unit = $time_parts[2][$ix];
                $multiplier = $multi[$unit] * $time_parts[1][$ix];
                $content->totalMiliSeconds += $multiplier;
                next($multi);
            }
            $content->file_size = $input->input_info->general->file_size."";
            $content->input_duration = $jobXml->content_duration->input_duration."";
            $content->stream_count = $jobXml->content_duration->stream_count."";
            $content->total_stream_duration = $jobXml->content_duration->total_stream_duration."";
            $content->jobDestination = $jobXml->output_group->apple_live_group_settings->destination->uri."";

            $cut = strpos($content->jobDestination, "Client_");
            // remove mount point thru client id from job destination
            $outpath = substr($content->jobDestination, $cut);
//             echo "Job Destination = $content->jobDestination outputpath = $outpath\n";
            
            $allContents = DeltaContents::getElementalRest()->restGet();
//             echo "Looking for outputpath = $outpath\n";
            foreach( $allContents->content as $xmlContent ){
                $path = $xmlContent->path;
                $cut = strpos($path, "Client_");
                $path = dirname(substr($path, $cut))."/";
                if( $path != $outpath ) {
                    continue;
                }
                $content->href = $xmlContent["href"]."";
                $toks = explode('/',$content->href);
                $content->id = $toks[count($toks)-1];
                $content->path = $xmlContent->path."";
                
                try {
                    $thisContent = DeltaContents::getHLSFilter()->restGet($content->id);
                    $content->endpoint = $thisContent->filter->custom_endpoint_uri."";
                }
                catch (InvalidArgumentException $ex)
                {
                    $content->endpoint = "N/A";
                }
                return $content;
            }
            throw new Exception('Job with ID $jobID does not have a matching content!!!');
        }
    }

//  DeltaContents::getContentsByFolder('/data/server/drive/watchfolders/Cliente1/')
//  foreach( DeltaContents::getContentsByFolder("/data/server/drive/watchfolders/Cliente1/") as $content ) {
//      var_dump($content);
//  }

//        print_r(DeltaContents::getContentsFromJob(165));
?>
