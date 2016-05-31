<?php

require('lib/tosca_classes4.0.php');

$filename = "DEMO_wordpress";
$yaml = $filename.".yml";

$ST = new tosca_service_template($yaml);

if (isset($ST)) {
	$TT = $ST->get_topology_template();
	if (isset($TT)) {
/*
   wordpress:
      properties: 
        context_root: { get_input: context_root }

		interfaces:
		  configure:
			inputs:
              wp_db_name: { get_property: [ mysql_database, name ] }
              wp_db_user: { get_property: [ mysql_database, user ] }
              wp_db_password: { get_property: [ mysql_database, password ] }   
              # In my own template, find requirement/capability, find port property
              wp_db_port: { get_property: [ SELF, database_endpoint, port ] }

   mysql_database:
      properties:
        port: { get_input: db_port }
 

   mysql_dbms:
      interfaces:
        Standard:              
          inputs:
              db_root_password: { get_property: [ mysql_dbms, root_password ] }
			  eliminare input dall'operazione configure
*/
		$wordpress = $TT->get_node_templates('wordpress');
		if (isset($wordpress)) {
			$wordpress->properties(array('context_root' => operator::get_input('context_root')));
			
			$if_standard = $wordpress->get_interfaces('Standard');
			if (isset($if_standard)) {
				$configure = $if_standard->get_operations('configure');
				if (isset($configure)) {
					$configure->inputs(array( 'wp_db_name' => operator::get_property('mysql_database', 'name'),
											  'wp_db_user' => operator::get_property('mysql_database', 'user'),
											  'wp_db_password' => operator::get_property('mysql_database', 'password'),   
											  'wp_db_port' => operator::get_property('SELF', 'port', 'database_endpoint')));
					$if_standard->operations(array('configure' => $configure->get()));
				}
				$wordpress->interfaces(array('Standard' => $if_standard->get()));
			}
			$TT->node_templates(array('wordpress' => $wordpress->get()));
		}
		
		$mysql_database = $TT->get_node_templates('mysql_database');
		if (isset($mysql_database)) {
			$mysql_database->properties(['port' => operator::get_input('db_port')]);
			$TT->node_templates(array('mysql_database' => $mysql_database->get()));
		}
		
		$mysql_dbms = $TT->get_node_templates('mysql_dbms');
		if (isset($mysql_dbms)) {
			$if_standard = $mysql_dbms->get_interfaces('Standard');
			if (isset($if_standard)) {
				$if_standard->inputs(array('db_root_password' => operator::get_property('mysql_dbms', 'root_password')));

				$configure = $if_standard->get_operations('configure');
				if (isset($configure)) {
					$configure->delete('inputs');
					$if_standard->operations(array('configure' => $configure->get()));
				}
				$mysql_dbms->interfaces(array('Standard' => $if_standard->get()));
			}
			$TT->node_templates(array('mysql_dbms' => $mysql_dbms->get()));
		}
		
		$website_url = $TT->get_outputs('website_url');
		if (isset($website_url)) {
			$website_url->keys(array('value' => operator::get_attribute('server', 'public_address')));
			$TT->outputs(array('website_url' => $website_url->get()));
		}
		$ST->topology_template($TT->get());
	}
	$ST->yaml("DEMO_wordpress_modified.yml");
}	  
	  
	  
?>