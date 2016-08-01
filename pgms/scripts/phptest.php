<?php
    $content = new stdClass();
    $content_storage_size = "1min 3s";
    preg_match_all("|([\d\.]+)\s*([^\d^\.]+)|", $content_storage_size, $time_parts);
    $multi = array("ms" => 1, "s" => 1000, "mn" => 60*1000, "h" => 60*60*1000, "d" => 24*60*60*1000 );
    var_dump($time_parts);

    $content->totalMiliSeconds = 0;
    foreach( array_reverse($time_parts[0]) as $tp ) {
        $multiplier =$multi[] * intval($tp);
        $content->totalMiliSeconds += $multiplier;
        next($multi);
    }
    var_dump($content)
?>
