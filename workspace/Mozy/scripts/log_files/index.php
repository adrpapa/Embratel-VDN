<?php
/*
if($_SERVER['REMOTE_ADDR']!='88.12.56.75'){
    echo "Unable to access error logs";
    die;
}
*/
?>
<html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error Log Viewer</title>

    <!-- Bootstrap -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">
    <div class="row">	
<?php
function showStep($step, $title, $content, $firsStep = false){
	?>
		<div class="panel">
			<div class="panel-heading">
				<h4 class="panel-title">
					<a data-toggle="collapse" data-parent="#accordion" href="#step_<?php echo $step;?>">
                        <?php
                            if($firsStep){
                                echo '<b>'.$title.'</b>';
                            }
                        else{
                            echo $title;
                            }
                        ?>
					</a>
				</h4>
			</div>
			<div id="step_<?php echo $step;?>" class="panel-collapse collapse">
				<div class="panel-body">
					<pre><?php echo $content;?></pre>
				</div>
			</div>
		</div>
	<?php	
}

function read_log($filename)
{
	echo '<div class="panel-group" id="accordion">';
	$data = null;

	if (false === $fh = fopen($filename, 'rb', false))
	{
		return false;
	}

	$currentStep =0;
	$previousTitle = '';
	$previousContent = '';
    $firsStep = false;
	while (($buffer = fgets($fh)) !== false) {
		//echo '<div>'.$buffer.'</div>';
		//echo "<br><br>";
		if(strpos($buffer, '~~') === 0){
			if($previousTitle){
				showStep($currentStep, $previousTitle, $previousContent, $firsStep);
			}
			$currentStep ++;
			$columnas = explode('|', $buffer);
			$previousTitle = str_replace('~~ ', '', $columnas[0]).'('.$columnas[2].')'.substr($columnas[4],0,100);

            if($firsStep){
                $firsStep =false;
            }
            else{
                $firsStep = $columnas[0] == '~~ 00:00:00 ';
            }
			$previousContent ='';
		}
		$previousContent.=$buffer;
    }
	
	if($previousTitle){
		showStep($currentStep, $previousTitle, $previousContent);
	}
	
    if (!feof($fh)) {
        echo "Error: unexpected fgets() fail\n";
    }

	fclose($fh);
	
	echo '</div>';

	return $data;
}

function getLogFiles($path)
{
	// Is the path a folder?
	if (!is_dir($path))
	{
		return false;
	}
	
	$arr = array();
	// Read the source directory
	if (!($handle = @opendir($path)))
	{
		return $arr;
	}
	while (($file = readdir($handle)) !== false)
	{
		if ($file != '.' && $file != '..' )
		{
			if (preg_match("/.txt/", $file))
			{
				// Compute the fullpath
				$fullpath = $path . '/' . $file;

				// Compute the isDir flag
				if(!is_dir($fullpath)){
					$arr[] = $file;
				}
			}			
		}
	}
	closedir($handle);
    arsort($arr);
	return array_values($arr);
}

function readLogFile($logFile){
	read_log($logFile);
}

if(isset($_GET["logFile"])){
	$logFile=$_GET["logFile"];
	echo '<h2><a href="index.php">Return to log list</a></h2><br>';
	echo '<div class="text-right"><a type="button" class="btn btn-success" href="index.php?logFile='.$logFile.'">Refresh</a></div>';
	readLogFile($logFile);
	$timezone = new DateTimeZone('Europe/Madrid');
    $date = new DateTime('now', $timezone);
    $dateStr = $date->format('h:i:s');
	echo '<div class="text-left">Last Load '.$dateStr.'</div><br>';
	echo '<div class="text-right"><a type="button" name="endpage" class="btn btn-success" href="index.php?logFile='.$logFile.'">Refresh</a></div><br>';
}
else{
	$logFiles = getLogFiles(getcwd());
	echo '<h2>Error Log Files</h2>';
    echo '<div class="text-right"><a type="button" class="btn btn-success" href="index.php">Refresh</a></div>';
    echo "<ul>";
	foreach($logFiles as $logFile){
		echo '<li><a href="index.php?logFile='.$logFile.'">'.$logFile.'</a></li>';
	}
	echo "</ul>";
}
?>
</div>
</div>
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <script src="../bootstrap/js/tab.js"></script>
</body>
</html>