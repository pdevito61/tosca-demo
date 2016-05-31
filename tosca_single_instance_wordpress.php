<?php

require('lib/tosca_classes4.0.php');

$st = new tosca_service_template();
$st->tosca_definitions_version('tosca_simple_yaml_1_0');
$st->description('TOSCA simple profile with wordpress, web server and mysql on the same server.');
$st->imports(array(	'TOSCA_definition_1_0' => 'types/TOSCA_definition_1_0.yml',
					'Wordpress types' => 'custom_types/wordpress.yaml'));

// Input parameters
$cpus = new tosca_parameter('integer');
$cpus->description('Number of CPUs for the server.');
$cpus->keys(array( 'constraints' => operator::valid_values( [ 1, 2, 4, 8 ]),
				   'default' => 1));
$db_name = new tosca_parameter('string');
$db_name->description( 'The name of the database.');
$db_name->keys(array( 'default' => 'wordpress' ));
$db_user = new tosca_parameter('string');
$db_user->description( 'The user name of the DB user.');
$db_user->keys(array( 'default' => 'wp_user'));
$db_pwd = new tosca_parameter('string');
$db_pwd->description( 'The WordPress database admin account password.');
$db_pwd->keys(array( 'default' => 'wp_pass' ));
$db_root_pwd = new tosca_parameter('string');
$db_pwd->description( 'Root password for MySQL.');
$db_port = new tosca_parameter('tosca.datatypes.network.PortDef');
$db_port->description( 'Port for the MySQL database.');
$db_port->keys(array( 'default' => 3306 ));

// wordpress node
$wp_configure = new tosca_operation();
$wp_configure->implementation( 'wordpress/wordpress_configure.sh' );
$wp_configure->inputs(array(  'wp_db_name' => 'wordpress',
							  'wp_db_user' => 'wp_user',
							  'wp_db_password' => 'wp_pass' ));

$wp_if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');
$wp_if->operations(array( 'create' => 'wordpress/wordpress_install.sh', 'configure' => $wp_configure->get() ));

$wp = new tosca_node_template('tosca.nodes.WebApplication.WordPress');
$wp->requirements(array('host' => 'webserver', 'database_endpoint' => 'mysql_database' ));
$wp->interfaces(array( 'Standard' => $wp_if->get() ));

// mysql_database node
$db_endpoint = new tosca_capability('tosca.capabilities.Endpoint.Database');
$db_endpoint->properties( array( 'port' => operator::get_input( 'db_port' )));

$db_configure = new tosca_operation();
$db_configure->implementation( 'mysql/mysql_database_configure.sh' );
$db_configure->inputs(array(  'db_name' => 'wordpress',
							  'db_user' => 'wp_user',
							  'db_password' => 'wp_pass',
							  'db_root_password' => 'passw0rd' ));

$db_if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');
$db_if->operations(array( 'configure' => $db_configure->get() ));

$db = new tosca_node_template('tosca.nodes.Database');
$db->properties( array(
						'name' => operator::get_input( 'db_name'),
						'user' => operator::get_input( 'db_user'),
						'password' => operator::get_input( 'db_pwd' )));

$db->capabilities(array( 'database_endpoint' => $db_endpoint->get() ));
$db->requirements(array( 'host' => 'mysql_dbms' ));
$db->interfaces(array( 'Standard' => $db_if->get() ));

// mysql DBMS node
$dbms_configure = new tosca_operation();
$dbms_configure->implementation( 'mysql/mysql_dbms_configure.sh' );
$dbms_configure->inputs(array(  'db_port' => 3366 ));

$dbms_create = new tosca_operation();
$dbms_create->implementation( 'mysql/mysql_dbms_install.sh' );
$dbms_create->inputs(array( 'db_root_password' => 'passw0rd' ));

$dbms_if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');
$dbms_if->operations(array( 'create' => $dbms_create->get(), 'start' => 'mysql/mysql_dbms_start.sh', 'configure' => $dbms_configure->get() ));

$dbms = new tosca_node_template('tosca.nodes.DBMS');

$dbms->properties( array( 
						'root_password' => operator::get_input( 'db_root_pwd' ),
						'port' => operator::get_input( 'db_port' )));
$dbms->requirements(array( 'host' => 'server' ));
$dbms->interfaces(array( 'Standard' => $dbms_if->get()));

//  webserver node
$ws_if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');
$ws_if->operations(array( 'create' => 'webserver/webserver_install.sh', 'start' => 'webserver/webserver_start.sh' ));

$ws = new tosca_node_template('tosca.nodes.WebServer');

$ws->requirements(array( 'host' => 'server'));
$ws->interfaces(array( 'Standard' => $ws_if->get() ));

// server node
$host = new tosca_capability('tosca.capabilities.Container');
$host->properties(array(
						'disk_size' => '10 GB',
						'num_cpus' => operator::get_input( 'cpus' ),
						'mem_size' => '4096 MB' ));
$os = new  tosca_capability('tosca.capabilities.OperatingSystem');
$os->properties(array(
						'architecture' => 'x86_64',
						'type' => 'Linux',
						'distribution' => 'Ubuntu',
						'version' => 14.04 ));

$server = new tosca_node_template('tosca.nodes.Compute');
$server->capabilities(array( 'host' => $host->get(), 'os' => $os->get() ));

// Output parameters
$website_url = new tosca_parameter('string');
$website_url->description( 'URL for Wordpress wiki.' );
$website_url->keys(array( 'value' => operator::get_attribute( 'server', 'private_address') ));



$tt = new tosca_topology_template();
$tt->inputs(array(
    'cpus' => $cpus->get(),
    'db_name' => $db_name->get(),
    'db_user' => $db_user->get(),
    'db_pwd' => $db_pwd->get(),
    'db_root_pwd' => $db_root_pwd->get(),
    'db_port' => $db_port->get() ));

$tt->node_templates(array( 'wordpress' => $wp->get(), 'mysql_database' => $db->get(), 'mysql_dbms' => $dbms->get(), 'webserver' => $ws->get(), 'server' => $server->get() ));

$tt->outputs(array( 'website_url' => $website_url->get() ));



$st->topology_template($tt->get());
$st->yaml("DEMO_wordpress.yml");

?>