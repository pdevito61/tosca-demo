<?php

require('lib/tosca_classes4.0.php');

$filename = "Moodle-Template";
$yaml = $filename.".yml";

$ST = new tosca_service_template($yaml);

if (isset($ST)) {
	$yaml = $filename."_parsed.yml";
	$ST->yaml($yaml);
}	  
	  
	  
?>