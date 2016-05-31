<?php

require('lib/tosca_classes4.0.php');

// header('Content-Type: text');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');  


$_POST = json_decode(file_get_contents('php://input'), true);
// print_r($_POST);

if (isset($_POST['template_name'])) 	$template_name 	  = $_POST['template_name'];    else  $template_name 	= 'demo_two_servers_one_network';	  
if (isset($_POST['template_version'])) 	$template_version = $_POST['template_version']; else  $template_version = '1.3';
	// if (is_numeric($template_version)) $template_version = number_format($template_version,1);
if (isset($_POST['template_author'])) 	$template_author  = $_POST['template_author'];  else  $template_author  = 'P. De Vito'; 
if (isset($_POST['disk_size01'])) 		$disk_size01      = $_POST['disk_size01'];      else  $disk_size01      = '10 GB';	  
if (isset($_POST['num_cpu01'])) 		$num_cpu01        = $_POST['num_cpu01'];        else  $num_cpu01        =  1;	  
if (isset($_POST['mem_size01']))  		$mem_size01       = $_POST['mem_size01'];       else  $mem_size01       = '512 MB'; 	  
if (isset($_POST['architecture01'])) 	$architecture01   = $_POST['architecture01'];   else  $architecture01   = 'x86_64';  
if (isset($_POST['type01']))  			$type01           = $_POST['type01'];           else  $type01           = 'Linux'; 		  
if (isset($_POST['distribution01'])) 	$distribution01   = $_POST['distribution01'];   else  $distribution01   = 'CirrOS';  
if (isset($_POST['version01']))  		$version01        = $_POST['version01'];        else  $version01        = '0.3.2'; 	  
if (isset($_POST['disk_size02'])) 		$disk_size02      = $_POST['disk_size02'];      else  $disk_size02      = '10 GB';		  
if (isset($_POST['num_cpu02'])) 		$num_cpu02        = $_POST['num_cpu02'];        else  $num_cpu02        =  1;	  	  
if (isset($_POST['mem_size02']))  		$mem_size02       = $_POST['mem_size02'];       else  $mem_size02       = '512 MB';  	  
if (isset($_POST['architecture02'])) 	$architecture02   = $_POST['architecture02'];   else  $architecture02   = 'x86_64';   
if (isset($_POST['type02']))  			$type02           = $_POST['type02'];           else  $type02           = 'Linux'; 	 		  
if (isset($_POST['distribution02'])) 	$distribution02   = $_POST['distribution02'];   else  $distribution02   = 'CirrOS';   
if (isset($_POST['version02']))  		$version02        = $_POST['version02'];        else  $version02        = '0.3.2'; 	 	  
if (isset($_POST['ip_version']))  		$ip_version       = $_POST['ip_version'];       else  $ip_version       =  4; 	  
if (isset($_POST['cidr']))  			$cidr         	  = $_POST['cidr'];             else  $cidr         	= '10.0.0.0/24'; 			  
if (isset($_POST['network_name']))  	$network_name 	  = $_POST['network_name'];     else  $network_name 	= 'Demo'; 	  
if (isset($_POST['start_ip']))  		$start_ip     	  = $_POST['start_ip'];         else  $start_ip     	= '10.0.0.100'; 		  
if (isset($_POST['end_ip']))  			$end_ip           = $_POST['end_ip'];           else  $end_ip           = '10.0.0.150'; 		  
if (isset($_POST['binding_node01']))  	$binding_node01   = $_POST['binding_node01'];   else  $binding_node01   = 'server01'; 		  
if (isset($_POST['binding_node02']))  	$binding_node02   = $_POST['binding_node02'];   else  $binding_node02   = 'server02'; 		  

$st = new tosca_service_template();
$st->tosca_definitions_version('tosca_simple_yaml_1_0');
$st->metadata(['template_name' => $template_name, 'template_version' => $template_version, 'template_author' => $template_author]);
$st->imports(array(	'TOSCA_definition_1_0' => "normative_types/TOSCA_definition_1_0.yml"));

$server1_cap_host = new tosca_capability('tosca.capabilities.Container');
$server1_cap_host->properties(array('disk_size' => $disk_size01, 'num_cpus' => $num_cpu01, 'mem_size' => $mem_size01));

$server1_cap_os = new tosca_capability('tosca.capabilities.OperatingSystem');
$server1_cap_os->properties(array('architecture' => $architecture01, 'type' => $type01, 'distribution' => $distribution01, 'version' => $version01));

$server1 = new tosca_node_template('tosca.nodes.Compute');
$server1->capabilities(array('host' => $server1_cap_host->get(), 'os' => $server1_cap_os->get()));

$server2_cap_host = new tosca_capability('tosca.capabilities.Container');
$server2_cap_host->properties(array('disk_size' => $disk_size02, 'num_cpus' => $num_cpu02, 'mem_size' => $mem_size02));

$server2_cap_os = new tosca_capability('tosca.capabilities.OperatingSystem');
$server2_cap_os->properties(array('architecture' => $architecture02, 'type' => $type02, 'distribution' => $distribution02, 'version' => $version02));

$server2 = new tosca_node_template('tosca.nodes.Compute');
$server2->capabilities(array('host' => $server2_cap_host->get(), 'os' => $server2_cap_os->get()));

$network = new tosca_node_template('tosca.nodes.network.Network');
$network->properties(array('ip_version' => $ip_version, 'cidr' => $cidr, 
							'network_name' => $network_name,
							'start_ip' => $start_ip,
							'end_ip' => $end_ip));

$binding1 = new tosca_requirement();
$link1 = new tosca_requirement();
$binding1->keys(array('node' => $binding_node01));
$link1->keys(array('node' => 'network01'));

$binding2 = new tosca_requirement();
$link2 = new tosca_requirement();
$binding2->keys(array('node' => $binding_node02));
$link2->keys(array('node' => 'network01'));

$port1 = new tosca_node_template('tosca.nodes.network.Port');
$port2 = new tosca_node_template('tosca.nodes.network.Port');
$port1->requirements(array('binding' => $binding1->get(), 'link' => $link1->get()));
$port2->requirements(array('binding' => $binding2->get(), 'link' => $link2->get()));

// $network_name = new tosca_parameter('string');
// $network_name->description('Network name');
// $network_cidr = new tosca_parameter('string');
// $network_cidr->description('CIDR for the network');
// $network_cidr->keys(array('default' => '10.0.0.0/24'));
// $network_start_ip = new tosca_parameter('string');
// $network_start_ip->description('Start IP for the allocation pool');
// $network_start_ip->keys(array('default' => '10.0.0.100'));
// $network_end_ip = new tosca_parameter('string');
// $network_end_ip->description('End IP for the allocation pool');
// $network_end_ip->keys(array('default' => '10.0.0.150'));

$tt = new tosca_topology_template();
// $tt->inputs(array('network_name' => $network_name->get(), 'network_cidr' => $network_cidr->get(), 'network_start_ip' => $network_start_ip->get(), 'network_end_ip' => $network_end_ip->get()));
$tt->node_templates(array('server01' => $server1->get(), 'server02' => $server2->get(), 'network01' => $network->get(), 'port1' => $port1->get(), 'port2' => $port2->get()));

$st->description('TOSCA simple profile with 2 servers bound to the 1 network');
$st->topology_template($tt->get());
$st->yaml('yaml_files/'.$template_name.'-'.$template_version.'.yml');

$data['message'] = $st->yaml();
// $data['message'] = 'Form data is going well';
echo json_encode($data);

// echo 'Form data is going well';

// echo 'Tutto OK!!';

?>