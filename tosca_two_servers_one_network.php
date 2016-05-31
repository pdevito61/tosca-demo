<?php

require('lib/tosca_classes4.0.php');

$st = new tosca_service_template();
$st->tosca_definitions_version('tosca_simple_yaml_1_0');
$st->imports(array(	'TOSCA_definition_1_0' => "types/TOSCA_definition_1_0.yml"));


$server_cap_host = new tosca_capability('tosca.capabilities.Container');
$server_cap_host->properties(array('disk_size' => '10 GB', 'num_cpus' => 1, 'mem_size' => '512 MB'));

$server_cap_os = new tosca_capability('tosca.capabilities.OperatingSystem');
$server_cap_os->properties(array('architecture' => 'x86_64', 'type' => 'Linux', 'distribution' => 'CirrOS', 'version' => '0.3.2'));

$server = new tosca_node_template('tosca.nodes.Compute');
$server->capabilities(array('host' => $server_cap_host->get(), 'os' => $server_cap_os->get()));

$network = new tosca_node_template('tosca.nodes.network.Network');
$network->properties(array('ip_version' => 4, 'cidr' => operator::get_input('network_cidr'), 
							'network_name' => operator::get_input('network_name'),
							'start_ip' => operator::get_input('network_start_ip'),
							'end_ip' => operator::get_input('network_end_ip')));

$binding1 = new tosca_requirement();
$link1 = new tosca_requirement();
$binding1->keys(array('node' => 'server01'));
$link1->keys(array('node' => 'network01'));

$binding2 = new tosca_requirement();
$link2 = new tosca_requirement();
$binding2->keys(array('node' => 'server02'));
$link2->keys(array('node' => 'network01'));

$port1 = new tosca_node_template('tosca.nodes.network.Port');
$port2 = new tosca_node_template('tosca.nodes.network.Port');
$port1->requirements(array('binding' => $binding1->get(), 'link' => $link1->get()));
$port2->requirements(array('binding' => $binding2->get(), 'link' => $link2->get()));

$network_name = new tosca_parameter('string');
$network_name->description('Network name');
$network_cidr = new tosca_parameter('string');
$network_cidr->description('CIDR for the network');
$network_cidr->keys(array('default' => '10.0.0.0/24'));
$network_start_ip = new tosca_parameter('string');
$network_start_ip->description('Start IP for the allocation pool');
$network_start_ip->keys(array('default' => '10.0.0.100'));
$network_end_ip = new tosca_parameter('string');
$network_end_ip->description('End IP for the allocation pool');
$network_end_ip->keys(array('default' => '10.0.0.150'));

$tt = new tosca_topology_template();
$tt->inputs(array('network_name' => $network_name->get(), 'network_cidr' => $network_cidr->get(), 'network_start_ip' => $network_start_ip->get(), 'network_end_ip' => $network_end_ip->get()));
$tt->node_templates(array('server01' => $server->get(), 'server02' => $server->get(), 'network01' => $network->get(), 'port1' => $port1->get(), 'port2' => $port2->get()));

$st->description('TOSCA simple profile with 2 servers bound to the 1 network');
$st->topology_template($tt->get());
$st->yaml("DEMO_2_servers.yml");

?>