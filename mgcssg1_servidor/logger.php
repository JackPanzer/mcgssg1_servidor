<?php

function logToFile($file, $text){
	$date = date("d/m/Y");
	$path = "logs/".$file;
	$fichero = fopen($path, "a+w");
	
	$log = $date . ": " . $text . "\r\n";
}

?>