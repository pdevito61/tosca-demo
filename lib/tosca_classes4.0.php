<?php
class tosca_root {
	protected $_structure = array();	// internal structure for tosca entities
	protected $_inputs = null;
	protected $_outputs = null;
	protected function error_out($e) {
		echo $e."\n\n";
	}
	protected function sequenced_list(&$seq_list, $attr_name, $attr_value) {
		if (!isset($seq_list)) {
			//echo "lista non esistente  \n";
			$seq_list[][$attr_name] = $attr_value;
		}
		else { 
			//echo "lista esistente  \n";
			$found = false;
			//print_r($seq_list);
			foreach ($seq_list as $pos => $req) {
				if (array_key_exists($attr_name, $req)) {
					$seq_list[$pos][$attr_name] = $attr_value;
					$found = true;
					//echo "Trovato!  pos: ".$pos."\n";
					break;
				}
			}
			if (!$found) $seq_list[][$attr_name] = $attr_value; 
		}
	}

	public function get(){ return $this->_structure;
	}
	public function description($ds = null) {
		try {
			if (isset($ds)) {
				if (!is_string($ds)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['description'] = $ds;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['description'];
	}
	public function inputs($par = null, $new = false) {
		try {
			if (isset($par)) {
				if (!is_array($par)) {
					throw new Exception('Invalid argument');
				}
				foreach($par as $name => $def) {
					$this->_structure['inputs'][$name] = $def;
					if ($new) $this->_inputs[$name] = new tosca_parameter(null, $def);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['inputs'];
	}
	public function get_inputs($name = null) {
		if (!isset($name)) {		
			return $this->_inputs;
		}
		else if (is_string($name)) {
			if(isset($this->_inputs)) {
				if (array_key_exists($name, $this->_inputs)) return $this->_inputs[$name];
			}
		}
		return null;
	}
	public function outputs($par = null, $new = false) {
		try {
			if (isset($par)) {
				if (!is_array($par)) {
					throw new Exception('Invalid argument');
				}
				foreach($par as $name => $def) {
					$this->_structure['outputs'][$name] = $def;
					if ($new) $this->_outputs[$name] = new tosca_parameter(null, $def);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['outputs'];
	}
	public function get_outputs($name = null) {
		if (!isset($name)) {		
			return $this->_outputs;
		}
		else if (is_string($name)) {
			if(isset($this->_outputs)) {
				if (array_key_exists($name, $this->_outputs)) return $this->_outputs[$name];
			}
		}
		return null;
	}
	public function delete($entity, $todel = null) {
		try {
			if (!isset($entity)) throw new Exception('Argument $entity is mandatory');
			if (!is_string($entity)) throw new Exception('Invalid argument $entity; it must be a string');
			if (!isset($this->_structure[$entity])) return;
			
			switch ($entity) {
				case 'properties':
				case 'capabilities':
				case 'attributes':
				case 'metadata':
				case 'inputs':
				case 'outputs':
				case 'node_templates':
				case 'groups':
				case 'artifacts':
				case 'interfaces':
											// deleting a list of entities
					if (isset($todel) ) {
						if(!is_array($todel)) {
							throw new Exception('Invalid argument $todel; it must be array');
						}
						foreach($todel as $name) {
							if (array_key_exists($name, $this->_structure[$entity])) unset($this->_structure[$entity][$name]);
						}
						if (count($this->_structure[$entity]) == 0) unset($this->_structure[$entity]);
					}
					else {
						unset($this->_structure[$entity]);
					}
					break;
				
				case 'description':
				case 'tosca_definitions_version':
				case 'topology_template':
				case 'substitution_mappings':
				case 'value':
				case 'required':
				case 'default': 
				case 'status':
				case 'constraints':
				case 'node_filter':
				case 'capability':
				case 'node':
				case 'relationship':
				case 'implementation':
				case 'file':
				case 'repository':
				case 'deploy_path':
				
											// deleting a single entity
					unset($this->_structure[$entity]);
					break;
					
				case 'requirements':		// deleting a sequenced list of entities
					if (isset($todel) ) {
						if(!is_array($todel)) {
							throw new Exception('Invalid argument $todel; it must be array');
						}
						foreach($todel as $name) {
							foreach($this->_structure[$entity] as $pos => $prop) {
								if (array_key_exists($name, $prop)) unset($this->_structure[$entity][$pos]);
							}
						}
						if (count($this->_structure[$entity]) == 0) unset($this->_structure[$entity]);
					}
					else {
						unset($this->_structure[$entity]);
					}
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	public function yaml($file = null) {
		$out = false;
		if (isset($file)) {
			$out = yaml_emit_file($file, $this->get());
		}
		else {
			$out = yaml_emit($this->get());
		}
		return $out;
	}
}
class tosca_node_filter extends  tosca_root {
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'properties':
						foreach ($value as $prop) {
							$this->properties($prop);
						}
						break;
					case 'capabilities':
						foreach ($value as $cap) {
							foreach($cap as $cap_name => $pr) {
								$properties = array();
								foreach($pr['properties'] as $prop) {
									$properties = array_merge($properties, $prop);
								}
								$this->capabilities(array($cap_name => $properties));
							}
							
						}
						break;
				}
			}
		}
	}
	
	public function properties($pr = null) {
		try {
			if(isset($pr)) {
				if (!is_array($pr)) {
					throw new Exception('Invalid argument');
				}
				foreach($pr as $name => $property) {
					$this->sequenced_list($this->_structure['properties'], $name, $property);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['properties'];
	}
	public function capabilities($cp = null) {
		try {
			if(isset($cp)) {
				if (!is_array($cp)) {
					throw new Exception('Invalid argument');
				}
				foreach($cp as $cp_name => $properies) {
					$this->sequenced_list($this->_structure['capabilities'], $cp_name, null);
					foreach($this->_structure['capabilities'] as $pos => $cap) {
						if ( array_key_exists($cp_name, $cap)) break;
					}
					if (!is_array($properies)) {
						throw new Exception('Invalid properties list');
					}
					foreach($properies as $pr_name => $prop) {
						$this->sequenced_list($this->_structure['capabilities'][$pos][$cp_name]['properties'], $pr_name, $prop);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['capabilities'];
	}
	public function delete($entity, $todel = null) {
		try {
			if (!isset($entity)) throw new Exception('Argument $entity is mandatory');
			if (!is_string($entity)) throw new Exception('Invalid argument $entity; it must be a string');
			if (!isset($this->_structure[$entity])) return;
			
			switch ($entity) {
				case 'properties':
				case 'capabilities':
					if (isset($todel) ) {
						if(!is_array($todel)) {
							throw new Exception('Invalid argument $todel; it must be array');
						}
						foreach($todel as $name) {
							foreach($this->_structure[$entity] as $pos => $prop) {
								if (array_key_exists($name, $prop)) unset($this->_structure[$entity][$pos]);
							}
						}
						if (count($this->_structure[$entity]) == 0) unset($this->_structure[$entity]);
					}
					else {
						unset($this->_structure[$entity]);
					}
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class tosca_requirement extends tosca_root {
	private $_node_filter = null;
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			$this->keys($struct, true);
		}
		else if (is_string($struct)) {
			$this->keys(array('node' => $struct));
		}
	}

	public function keys($keys = null, $new = false) {
		try {
			if(isset($keys)) {
				if(!is_array($keys)) {
					throw new Exception('Invalid argument');
				}
				foreach($keys as $key_name => $key_value) {
					try {
						if ( $key_name == 'node' or $key_name == 'relationship' or 
							 $key_name == 'capability') {
							$this->_structure[$key_name] = $key_value;
						}
						else if ($key_name == 'node_filter') {
							$this->_structure[$key_name] = $key_value;
							if ($new) $this->_node_filter = new tosca_node_filter($key_value);
						}
						else {
							throw new Exception('Invalid key name '.$key_name);
						}
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure;
	}
	public function get_node_filter() { return $this->_node_filter;
	}
	public function delete($entity, $todel = null) {
		try {
			parent::delete($entity, $todel);
			switch ($entity) {
				case 'node_filter':
					if (isset($this->_node_filter)) unset($this->_node_filter);
					$this->_node_filter = null;
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class tosca_topology_template extends tosca_root{
	private $_substitution_mappings = null;
	private $_node_templates = null;
	private $_groups = null;
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function check_group($gr_def) {
		//print_r($this->node_templates());
		$check = true;
		foreach ($gr_def['targets'] as $gr_member) {
			if (!array_key_exists($gr_member, $this->node_templates())) {
				$check = false;
				break;
			}
		}
		return $check;
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'description':
						$this->description($value);
						break;
					case 'inputs':
						$this->inputs($value, true);
						break;
					case 'node_templates':
						$this->node_templates($value, true);
						break;
					case 'relationship_templates':
						//$this->relationship_templates($value);
						break;
					case 'groups':
						$this->groups($value, true);
						break;
					case 'policies':
						//$this->policies($value);
						break;
					case 'outputs':
						$this->outputs($value, true);
						break;
					case 'substitution_mappings':
						$this->substitution_mappings($value, true);
						break;
				}
			}
		}
	}
	
	public function node_templates($nt = null, $new = false) {
		try {
			if (isset($nt)) {
				if (!is_array($nt)) {
					throw new Exception('Invalid argument');
				}
				foreach($nt as $name => $node) {
					$this->_structure['node_templates'][$name] = $node;
					if ($new) $this->_node_templates[$name] = new tosca_node_template(null, $node);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['node_templates'];
	}
	public function get_node_templates($name = null) { 
		if (!isset($name)) {		
			return $this->_node_templates;
		}
		else if (is_string($name)) {
			if(isset($this->_node_templates)) {
				if (array_key_exists($name, $this->_node_templates)) return $this->_node_templates[$name];
			}
		}
		return null;
	}
	public function groups($gr = null, $new = false) {
		try {
			if (isset($gr)) {
				if (!is_array($gr)) {
					throw new Exception('Invalid argument');
				}
				foreach($gr as $name => $def) {
					try {
						if (!$this->check_group($def)) {
							throw new Exception('Invalid group '.$name);
						}
						$this->_structure['groups'][$name] = $def;
						if ($new) $this->_groups[$name] = new tosca_group(null, $def);
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['groups'];
	}
	public function get_groups($name = null) {
		if (!isset($name)) {		
			return $this->_groups;
		}
		else if (is_string($name)) {
			if(isset($this->_groups)) {
				if (array_key_exists($name, $this->_groups)) return $this->_groups[$name];
			}
		}
		return null;
	}
	public function substitution_mappings($sm = null, $new = false) {
		try {
			if (isset($sm)) {
				if(!is_array($sm)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['substitution_mappings'] = $sm;
				if ($new) $this->_substitution_mappings = new tosca_substitution_mapping(null, $sm);
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['substitution_mappings'];
	}
	public function get_substitution_mappings() { return $this->_substitution_mappings;
	}
	public function delete($entity, $todel = null) {
		try {
			parent::delete($entity, $todel);
			switch ($entity) {
				case 'substitution_mappings':
					if (isset($this->_substitution_mappings)) unset($this->_substitution_mappings);
					$this->_substitution_mappings = null;
					break;
				case 'inputs':
					if (isset($this->_inputs)) {
						if (isset($todel) ) {
							foreach($todel as $name) {
								if (array_key_exists($name, $this->_inputs)) unset($this->_inputs[$name]);
							}
							if (count($this->_inputs) == 0) {
								unset($this->_inputs);
								$this->_inputs = null;
							}
						}
						else {
							unset($this->_inputs);
							$this->_inputs = null;
						}
					}
					break;
				case 'outputs':
					if (isset($this->_outputs)) {
						if (isset($todel) ) {
							foreach($todel as $name) {
								if (array_key_exists($name, $this->_outputs)) unset($this->_outputs[$name]);
							}
							if (count($this->_outputs) == 0) {
								unset($this->_outputs);
								$this->_outputs = null;
							}
						}
						else {
							unset($this->_outputs);
							$this->_outputs = null;
						}
					}
					break;
				case 'node_templates':
					if (isset($this->_node_templates)) {
						if (isset($todel) ) {
							foreach($todel as $name) {
								if (array_key_exists($name, $this->_node_templates)) unset($this->_node_templates[$name]);
							}
							if (count($this->_node_templates) == 0) {
								unset($this->_node_templates);
								$this->_node_templates = null;
							}
						}
						else {
							unset($this->_node_templates);
							$this->_node_templates = null;
						}
					}
					break;
				case 'groups':
					if (isset($this->_groups)) {
						if (isset($todel) ) {
							foreach($todel as $name) {
								if (array_key_exists($name, $this->_groups)) unset($this->_groups[$name]);
							}
							if (count($this->_groups) == 0) {
								unset($this->_groups);
								$this->_groups = null;
							}
						}
						else {
							unset($this->_groups);
							$this->_groups = null;
						}
					}
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class tosca_definitions extends tosca_root {
	protected static  $_def = null;			// array for type defininitions

}
class tosca_operation extends tosca_root {
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'description':
						$this->description($value);
						break;
					case 'inputs':
						$this->inputs($value);
						break;
					case 'implementation':
						if (is_string($value))
							$this->implementation($value);
						else if (is_array($value)) 
							$this->implementation($value['primary'], $value['dependencies']);
						break;
				}
			}
		}
		else if (is_string($struct)) {
			$this->implementation($struct);
		}
	}
	
	public function implementation($art_name = null, $dep_art_names = null) {
		try {
			if (isset($art_name)) {
				if (!is_string($art_name)) {
					throw new Exception('Invalid argument implementation artifact');
				}
				if(isset($dep_art_names)) {
					if (!is_array($dep_art_names)) {
						throw new Exception('Invalid argument list of dependent artifacts');
					}
					if (isset($this->_structure['implementation']) && is_string($this->_structure['implementation'])) {
						unset($this->_structure['implementation']);
					}
					$this->_structure['implementation']['primary'] = $art_name;
					foreach($dep_art_names as $dependent)
						$this->_structure['implementation']['dependencies'][] = $dependent;
				}
				else {
					$this->_structure['implementation'] = $art_name;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['implementation'];
	}
}
class tosca_service_template extends tosca_definitions {
	private $_topology_template = null;
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_file($struct)) {
			$parsed = yaml_parse_file($struct);
			if ($parsed != false) $struct = $parsed;
		}
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'description':
						$this->description($value);
						break;
					case 'tosca_definitions_version':
						$this->tosca_definitions_version($value);
						break;
					case 'metadata':
						$this->metadata($value);
						break;
					case 'topology_template':
						$this->topology_template($value, true);
						break;
					case 'imports':
						$this->imports($value);
						break;
					case 'dsl_defintions':
					case 'repositories':
						break;
				}
			}
		}
	}
	protected static function import_definitions($file) {
		if (!isset(self::$_def)) {
		self::$_def = array( 	'string' => null,
								'integer' => null,
								'float' => null,
								'boolean' => null,
								'timestamp' => null,
								'range' => null,
								'list' => null,
								'map' => null,
								'scalar-unit.size' => null,
								'scalar-unit.time' => null,);
		}
		$parsed = yaml_parse_file($file);
		foreach($parsed as $key_name => $key_value) {
			switch ($key_name) {
			case 'artifact_types':
			case 'data_types':
			case 'capability_types':
			case 'interface_types':
			case 'relationship_types':
			case 'node_types':
			case 'group_types':
			case 'policy_types':
				self::$_def = array_merge(self::$_def, $key_value);
			}
		}
	}

	public static function definitions() {
		return self::$_def;
	}
	public function imports($imp = null) {
		try {
			if(isset($imp)) {
				if(!is_array($imp)) {
					throw new Exception('Invalid argument');
				}
				foreach($imp as $imp_name => $imp_value) {
					$this->_structure['imports'][$imp_name] = $imp_value;
					$this->import_definitions($imp_value);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['imports'];
	}
	public function tosca_definitions_version($profile) {
		try {
			if (isset($profile) ) {
				if (!is_string($profile)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['tosca_definitions_version'] = $profile;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['tosca_definitions_version'];
	}
	public function metadata($mds = null) {
		try {
			if(isset($mds)) {
				if(!is_array($mds)) {
					throw new Exception('Invalid argument');
				}
				foreach($mds as $key_name => $key_value) {
					$this->_structure['metadata'][$key_name] = $key_value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['metadata'];
	}
	public function topology_template($tt, $new = false) {
		try {
			if (isset($tt)) {
				if(!is_array($tt)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['topology_template'] = $tt;
				if ($new) $this->_topology_template = new tosca_topology_template($tt);
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['topology_template'];
	}
	public function get_topology_template() { return $this->_topology_template;
	}
	public function delete($entity, $todel = null) {
		try {
			parent::delete($entity, $todel);
			switch ($entity) {
				case 'topology_template':
					if (isset($this->_topology_template)) unset($this->_topology_template);
					$this->_topology_template =null;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class tosca_typified extends tosca_definitions {
	protected $_interfaces = null;
	protected  $_type = null;			// string
	
	function __construct($type_name = null, $clear) {
		try {
			if(!isset($type_name)) {
				throw new Exception('Missing argument');
			}
			$this->set_type($type_name, $clear);
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function set_type($typename = null, $clear = null) {
		try {
			if (isset($typename) and isset($clear) and isset(self::$_def)) {
				if(!array_key_exists($typename, self::$_def)) {
					throw new Exception('Invalid typename '.$typename);
				} 
				$this->_type = $typename;
				if ($clear) $this->_structure['type'] = $typename;
			}
			else {
				throw new Exception('Invalid argument');
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function check_name($attr_name, $attr_value, $type_to_check = null) {
		// check for attribute type
		//echo "\n attr_name: ".$attr_name."\n";
		$check = false;
		$derived_from_type = null;
		if ($type_to_check == null) $type_to_check = $this->type();
		foreach(self::$_def as $ty_name => $ty_def) {
			if ($type_to_check == $ty_name) {
			//echo "type found ".$type_to_check." \n";
				if(array_key_exists('derived_from', $ty_def)) $derived_from_type = $ty_def['derived_from'];
				if(array_key_exists($attr_value, $ty_def)) {
					if ($attr_value != 'requirements') {
						foreach($ty_def[$attr_value] as $at_name => $at_def) {
							//echo "Confronto ".$attr_name." e ".$at_name."\n";
							if( $attr_name == $at_name ) {
								$check = true;
								//echo "Found! ".$attr_name." Break internal loop\n";
								break;
							}
						}
					}
					else {
						foreach($ty_def[$attr_value] as $req) {
							if (array_key_exists($attr_name,$req)) {
								$check = true;
								//echo "Found! ".$attr_name." Break internal loop\n";
								break;
							}
						}
					}
				}
			}
			if ($check) {
				//echo "Found! Break external loop\n";
				break;
			}
		}
		if (!$check) {
			if(isset($derived_from_type)) {
			// type is derived; check recursively for source type
			//echo "derived from ".$derived_from_type."\n";
				$check = $this->check_name($attr_name, $attr_value, $derived_from_type);
			}
		}
		return $check;
	}
	
	public function type() {
		return $this->_type;
	}
	public function properties($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, 'properties')) {
							throw new Exception('Invalid property '.$attr_name);
						}
						$this->_structure['properties'][$attr_name] = $attr_value;
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['properties'];
	}
	public function attributes($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, 'attributes')) {
							throw new Exception('Invalid attribute '.$attr_name);
						}
						$this->_structure['attributes'][$attr_name] = $attr_value;
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['attributes'];
	}
	public function interfaces($attr = null, $new = false) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, 'interfaces')) {
							throw new Exception('Invalid attribute '.$attr_name);
						}
						$this->_structure['interfaces'][$attr_name] = $attr_value;
						if ($new) $this->_interfaces[$attr_name] = new tosca_interface(null, $attr_value);
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['interfaces'];
	}
	public function get_interfaces($name = null) {
		if (!isset($name)) {		
			return $this->_interfaces;
		}
		else if (is_string($name)) {
			if(isset($this->_interfaces)) {
				if (array_key_exists($name, $this->_interfaces)) return $this->_interfaces[$name];
			}
		}
		return null;
	}
}
class tosca_node_template  extends  tosca_typified {
	private $_requirements = null;
	private $_artifacts = null;
	private $_capabilities = null;
	private $_node_filter = null;
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type': 
						$this->set_type($value, true);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'requirements':
						foreach ($value as $req) {
							//print_r($req);
							$this->requirements($req, true);
						}
						break;
					case 'artifacts':
						$this->artifacts($value, true);
						break;
					case 'properties':
						$this->properties($value);
						break;
					case 'attributes':
						$this->attributes($value);
						break;
					case 'capabilities':
						$this->capabilities($value, true);
						break;
					case 'interfaces':
						$this->interfaces($value, true);
						break;
					case 'node_filter':
						$this->node_filter($value, true);
						break;
					case 'directives':
					case 'copy':
						break;
				}
			}
		}
	}

	public function capabilities($attr = null, $new = false) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, 'capabilities')) {
							throw new Exception('Invalid capability '.$attr_name);
						}
						$this->_structure['capabilities'][$attr_name] = $attr_value;
						if ($new) $this->_capabilities[$attr_name] = new tosca_capability(null, $attr_value);
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['capabilities'];
	}
	public function get_capabilities($name = null) {
		if (!isset($name)) {		
			return $this->_capabilities;
		}
		else if (is_string($name)) {
			if(isset($this->_capabilities)) {
				if (array_key_exists($name, $this->_capabilities)) return $this->_capabilities[$name];
			}
		}
		return null;
	}
	public function artifacts($attr = null, $new = false) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					$this->_structure['artifacts'][$attr_name] = $attr_value;
					if ($new) $this->_artifacts[$attr_name] = new tosca_artifact(null, $attr_value);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['artifacts'];
	}
	public function get_artifacts($name = null) {
		if (!isset($name)) {		
			return $this->_artifacts;
		}
		else if (is_string($name)) {
			if(isset($this->_artifacts)) {
				if (array_key_exists($name, $this->_artifacts)) return $this->_artifacts[$name];
			}
		}
		return null;
	}
	public function requirements($attr = null, $new = false) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					//echo "\n\n req name: ".$attr_name." req value: \n\n";
					//print_r($attr_value);
					try {
						if (!$this->check_name($attr_name, 'requirements')) {
							throw new Exception('Invalid requirement '.$attr_name);
						}
						$this->sequenced_list($this->_structure['requirements'], $attr_name, $attr_value);
						if ($new) $this->_requirements[$attr_name] = new tosca_requirement($attr_value);
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['requirements'];
	}
	public function get_requirements($name = null) {
		if (!isset($name)) {		
			return $this->_requirements;
		}
		else if (is_string($name)) {
			if(isset($this->_requirements)) {
				if (array_key_exists($name, $this->_requirements)) return $this->_requirements[$name];
			}
		}
		return null;
	}
	public function node_filter($nf = null, $new = false) {
		try {
			if (isset($nf)) {
				if(!is_array($nf)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['node_filter'] = $nf;
				if ($new) $this->_node_filter = new tosca_node_filter($nf);
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['node_filter'];
	}
	public function get_node_filter() { return $this->_node_filter;
	}
	public function delete($entity, $todel = null) {
		try {
			parent::delete($entity, $todel);
			switch ($entity) {
				case 'capabilities':
					if (isset($this->_capabilities)) {
						if (isset($todel) ) {
							foreach($todel as $name) {
								if (array_key_exists($name, $this->_capabilities)) unset($this->_capabilities[$name]);
							}
							if (count($this->_capabilities) == 0) {
								unset($this->_capabilities);
								$this->_capabilities = null;
							}
						}
						else {
							unset($this->_capabilities);
							$this->_capabilities = null;
						}
					}
					break;
				case 'requirements':
					if (isset($this->_requirements)) {
						if (isset($todel) ) {
							foreach($todel as $name) {
								if (array_key_exists($name, $this->_requirements)) unset($this->_requirements[$name]);
							}
							if (count($this->_requirements) == 0) {
								unset($this->_requirements);
								$this->_requirements = null;
							}
						}
						else {
							unset($this->_requirements);
							$this->_requirements = null;
						}
					}
					break;
				case 'artifacts':
					if (isset($this->_artifacts)) {
						if (isset($todel) ) {
							foreach($todel as $name) {
								if (array_key_exists($name, $this->_artifacts)) unset($this->_artifacts[$name]);
							}
							if (count($this->_artifacts) == 0) {
								unset($this->_artifacts);
								$this->_artifacts = null;
							}
						}
						else {
							unset($this->_artifacts);
							$this->_artifacts = null;
						}
					}
					break;
				case 'interfaces':
					if (isset($this->_interfaces)) {
						if (isset($todel) ) {
							foreach($todel as $name) {
								if (array_key_exists($name, $this->_interfaces)) unset($this->_interfaces[$name]);
							}
							if (count($this->_interfaces) == 0) {
								unset($this->_interfaces);
								$this->_interfaces = null;
							}
						}
						else {
							unset($this->_interfaces);
							$this->_interfaces = null;
						}
					}
					break;
				case 'node_filter':
					if (isset($this->_node_filter)) unset($this->_node_filter);
					$this->_node_filter = null;
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class tosca_interface  extends  tosca_typified {
	private $_operations = null;
	function __construct($type_name = null, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, false);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type':
						$this->set_type($value, false);
						break;
					case 'inputs':
						$this->inputs($value);
						break;
					//case 'operations':
					default:
						/*if (is_array($value)) {
							$this->operations(array($key => $value), true);
						}
						else if (is_string($value)) {
							$this->operations(array($key => $value));
						}*/
						$this->operations(array($key => $value), true);
						break;
				}
			}
		}
	}

	public function operations($op = null, $new = false) {
		try {
			if (isset($op)) {
				if (!is_array($op)) {
					throw new Exception('Invalid argument');
				}
				foreach($op as $name => $value) {
					$this->_structure[$name] = $value;
					if ($new) $this->_operations[$name] = new tosca_operation($value);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		//return array_diff_key($this->_structure, array('inputs' => 'hjgfj'));
		return $this->_structure;
	}
	public function get_operations($name = null) {
		if (!isset($name)) {		
			return $this->_operations;
		}
		else if (is_string($name)) {
			if(isset($this->_operations)) {
				if (array_key_exists($name, $this->_operations)) return $this->_operations[$name];
			}
		}
		return null;
	}
	public function delete($entity, $todel = null) {
		try {
			parent::delete($entity, $todel);
			switch ($entity) {
				case 'operations':
					if (isset($todel) ) {
						if(!is_array($todel)) {
							throw new Exception('Invalid argument $todel; it must be array');
						}
						foreach($todel as $name) {
							if (array_key_exists($name, $this->_structure)) {
								unset($this->_structure[$name]);
								unset($this->_operations[$name]);
							}
						}
					}
					else {
						foreach($this->_structure as $name => $val) {
							if ($name != 'inputs') unset($this->_structure[$name]);
						}
						unset($this->_operations);
						$this->_operations = null;
					}
					if (count($this->_structure) == 0) {
						unset($this->_structure);
						$this->_structure = array();
						unset($this->_operations);
						$this->_operations = null;
					}
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class tosca_capability  extends  tosca_typified {
	function __construct($type_name = null, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type':
						$this->set_type($value, true);
						break;
					case 'properties':
						$this->properties($value);
						break;
					case 'attributes':
						$this->attributes($value);
						break;
				}
			}
		}
	}
}
class tosca_artifact extends tosca_typified {
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type': 
						$this->set_type($value, true);
						break;
					case 'description': 
						$this->description($value);
						break;
					case 'file':
					case 'repository':
					case 'deploy_path':
						$this->keys(array($key => $value));
				}
			}
		}
		else if (is_string($struct)) {
			$this->set_type('tosca.artifacts.File', true);
			$this->keys(array('file' => $struct));
		}
		else { // error
		}
	}
	
	public function keys($keys = null) {
		try {
			if(isset($keys)) {
				if(!is_array($keys)) {
					throw new Exception('Invalid argument');
				}
				foreach($keys as $key_name => $key_value) {
					try {
						if ( $key_name == 'file' or $key_name == 'repository' or 
							 $key_name == 'deploy_path' ) {
							$this->_structure[$key_name] = $key_value;
						}
						else {
							throw new Exception('Invalid key name '.$key_name);
						}
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure;
	}
}
class tosca_parameter extends tosca_typified {
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type':
						$this->set_type($value, true);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'value':
					case 'required':
					case 'default':
					case 'status':
					case 'entry_schema':
						$this->keys(array($key => $value));
						break;
					case 'constraints':
						foreach ($value as $constr) {
							$this->keys(array('constraints' => $constr));
						}
						break;
				}
			}
		}
	}

	public function keys($keys = null) {
		try {
			if(isset($keys)) {
				if(!is_array($keys)) {
					throw new Exception('Invalid argument');
				}
				foreach($keys as $key_name => $key_value) {
					try {
						if ( $key_name == 'value' or $key_name == 'required' or
							 $key_name == 'default' or $key_name == 'status' or $key_name == 'entry_schema' ) {
							$this->_structure[$key_name] = $key_value;
						}
						else if ($key_name == 'constraints') {
							if (is_array($key_value)) {			// it must be an operator
								foreach($key_value as $op_name => $op_val) {
									$this->sequenced_list($this->_structure['constraints'], $op_name, $op_val );
								}
							}
						}
						else {
							throw new Exception('Invalid key name '.$key_name);
						}
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure;
	}

}
class tosca_group extends tosca_typified {
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type': 
						$this->set_type($value, true);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'interfaces':
						$this->interfaces($value, true);
						break;
					case 'properties':
						$this->properties($value);
						break;
					case 'targets':
						$this->targets($value);
						break;
				}
			}
		}
	}

	public function targets($tg = null) {
		try {
			if (isset($tg)) {
				if (!is_array($tg)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['targets'] = $tg;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['targets'];
	}
	public function delete($entity, $todel = null) {
		try {
			parent::delete($entity, $todel);
			switch ($entity) {
				case 'interfaces':
					if (isset($this->_interfaces)) {
						if (isset($todel) ) {
							foreach($todel as $name) {
								if (array_key_exists($name, $this->_interfaces)) unset($this->_interfaces[$name]);
							}
							if (count($this->_interfaces) == 0) {
								unset($this->_interfaces);
								$this->_interfaces = null;
							}
						}
						else {
							unset($this->_interfaces);
							$this->_interfaces = null;
						}
					}
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class tosca_substitution_mapping extends tosca_typified {
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, false);
			$this->_structure['node_type'] = $type_name;
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'node_type': 
						$this->set_type($value, false);
						$this->_structure['node_type'] = $value;
						break;
					case 'description':
						$this->description($value);
						break;
					case 'capabilities':
						$this->capabilities($value);
						break;
					case 'requirements':
						$this->requirements($value);
						break;
				}
			}
		}
	}
	
	public function capabilities($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					$this->_structure['capabilities'][$attr_name] = $attr_value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['capabilities'];
	}
	public function requirements($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					$this->_structure['requirements'][$attr_name] = $attr_value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['requirements'];
	}
	public function delete($entity, $todel = null) {
		try {
			if (!isset($entity)) throw new Exception('Argument $entity is mandatory');
			if (!is_string($entity)) throw new Exception('Invalid argument $entity; it must be a string');
			if (!isset($this->_structure[$entity])) return;
			switch ($entity) {
				case 'capabilities':
				case 'requirements':
					if (isset($todel) ) {
						if(!is_array($todel)) {
							throw new Exception('Invalid argument $todel; it must be array');
						}
						foreach($todel as $name) {
							if (array_key_exists($name, $this->_structure[$entity])) unset($this->_structure[$entity][$name]);
						}
						if (count($this->_structure[$entity]) == 0) unset($this->_structure[$entity]);
					}
					else {
						unset($this->_structure[$entity]);
					}
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class operator {
	public static function equal($value) {
		$_structure = array('equal' => $value);
		return $_structure;
	}
	public static function greater_than($value) {
		$_structure = array('greater_than' => $value);
		return $_structure;
	}
	public static function greater_or_equal($value) {
		$_structure = array('greater_or_equal' => $value);
		return $_structure;
	}
	public static function less_than($value) {
		$_structure = array('less_than' => $value);
		return $_structure;
	}
	public static function less_or_equal($value) {
		$_structure = array('less_or_equal' => $value);
		return $_structure;
	}
	public static function in_range($lower, $upper) {
		$_structure = array();
		if(is_int($lower) and is_int($upper) and $upper >= $lower) {
			$_structure['in_range'][] = $lower;
			$_structure['in_range'][] = $upper;
		}
		else if (is_int($lower) and ($upper == 'UNBOUNDED')) {
			$_structure['in_range'][] = $lower;
			$_structure['in_range'][] = $upper;
		}
		return $_structure;
	}
	public static function valid_values($list_val) {
		$_structure = array();
		if (is_array($list_val)) {
			foreach($list_val as $val) $_structure['valid_values'][] = $val;
		}
		return $_structure;
	}
	public static function length($value) {
		$_structure = array('length' => $value);
		return $_structure;
	}
	public static function min_length($value) {
		$_structure = array('min_length' => $value);
		return $_structure;
	}
	public static function max_length($value) {
		$_structure = array('max_length' => $value);
		return $_structure;
	}
	public static function occurrences($lower, $upper) {
		$_structure = array();
		if(is_int($lower) and is_int($upper) and $upper >= $lower) {
			$_structure['occurrences'][] = $lower;
			$_structure['occurrences'][] = $upper;
		}
		else if (is_int($lower) and ($upper == 'UNBOUNDED')) {
			$_structure['occurrences'][] = $lower;
			$_structure['occurrences'][] = $upper;
		}
		return $_structure;
	}
	public static function concat($list_val) {
		$_structure = array();
		if (is_array($list_val)) {
			foreach($list_val as $val) $_structure['concat'][] = $val;
		}
		return $_structure;
	}
	public static function token($string, $token, $index) {
		$_structure = array();
		if (is_string($string) and is_string($token) and is_int($index)) {
			$_structure['token'][] = $string;
			$_structure['token'][] = $token;
			$_structure['token'][] = $index;
		}
		return $_structure;
	}
	public static function get_input($name) {
		if (is_string($name)) $_structure = array('get_input' => $name);
		return $_structure;
	}
	/*
	get_property:  [ <modelable_entity_name>, <optional_req_or_cap_name>, <property_name>,  <nested_property_name_or_index_1>, ..., <nested_property_name_or_index_n> ]
	get_attribute: [ <modelable_entity_name>, <optional_req_or_cap_name>, <attribute_name>, <nested_attribute_name_or_index_1>, ..., <nested_attribute_name_or_index_n>,   ]
	*/
	public static function get_property($entity, $property, $op_name = null) {
		$_structure = array();
		if (is_string($entity) and is_string($property)) {
			$_structure['get_property'][] = $entity;
			if (isset($op_name) and is_string($op_name)) $_structure['get_property'][] = $op_name;
			$_structure['get_property'][] = $property;
		}
		return $_structure;
	}
	public static function get_attribute($entity, $attribute, $op_name = null) {
		$_structure = array();
		if (is_string($entity) and is_string($attribute)) {
			$_structure['get_attribute'][] = $entity;
			if (isset($op_name) and is_string($op_name)) $_structure['get_attribute'][] = $op_name;
			$_structure['get_attribute'][] = $attribute;
		}
		return $_structure;
	}
	public static function get_operation_output($entity, $if_name, $op_name, $out_var) {
		$_structure = array();
		if (is_string($entity) and is_string($if_name) and is_string($op_name) and is_string($out_var)) {
			$_structure['get_operation_output'][] = $entity;
			$_structure['get_operation_output'][] = $if_name;
			$_structure['get_operation_output'][] = $op_name;
			$_structure['get_operation_output'][] = $out_var;
		}
		return $_structure;
	}
	public static function get_nodes_of_type($node_type) {
		$_structure = array();
		if (is_string($node_type)) $_structure['get_nodes_of_type'] = $node_type;
		return $_structure;
	}
	public static function get_artifact($entity, $artifact, $location = null, $remove = null) {
		$_structure = array();
		if (is_string($entity) and is_string($artifact)) {
			$_structure['get_artifact'][] = $entity;
			$_structure['get_artifact'][] = $artifact;
			if (isset($location) and is_string($location)) $_structure['get_artifact'][] = $location;
			if (isset($remove) and is_bool($remove)) $_structure['get_artifact'][] = $remove;
		}
		return $_structure;
	}
	public static function map_of($node, $value) {
		$_structure = array($node, $value);
		return $_structure;
	}
}
?>