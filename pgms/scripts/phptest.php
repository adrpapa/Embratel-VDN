<?php
//     $content = new stdClass();
//     $content_storage_size = "1min 3s";
//     preg_match_all("|([\d\.]+)\s*([^\d^\.]+)|", $content_storage_size, $time_parts);
//     $multi = array("ms" => 1, "s" => 1000, "mn" => 60*1000, "h" => 60*60*1000, "d" => 24*60*60*1000 );
//     var_dump($time_parts);
// 
//     $content->totalMiliSeconds = 0;
//     foreach( array_reverse($time_parts[0]) as $tp ) {
//         $multiplier =$multi[] * intval($tp);
//         $content->totalMiliSeconds += $multiplier;
//         next($multi);
//     }
//     var_dump($content)

$array = array('apple', 'orange', 'pear', 'banana', 'apple',
'pear', 'kiwi', 'kiwi', 'kiwi');

$arrCount = array_count_values($array);

if( count($array) > count($arrCount))
foreach($arrCount as $key => $value) {
    if( $value > 1 ){
        print("Values $key has $value values\n");
        }
    }

echo "Caca is " .print_r($array, true);

$data="logs/vods".date("Ymd").".log";
echo $data."\n";
?>
