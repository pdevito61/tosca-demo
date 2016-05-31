<?php
class tosca_root {    										 // OK 6.0, OK delete
	protected $_structure = array();	// internal attribute for tosca entities in multidimentional-array format
	protected $_entity_objects = null;	// internal attribute for tosca entities objects 
										// $_entity_objects[entity_type].entity_object || $_entity_objects[entity_type][entity_name].entity_object
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			// case 'entity_name':
				// $classname = 'class_name';
				// break;
			default:
		}
		return $classname;
	}
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
	protected function entity_objects($e_type = null, $e_name = null) {
		if (isset($e_type)) {
			if (!isset($e_name)) {		
				if(isset($this->_entity_objects[$e_type])) return $this->_entity_objects[$e_type];
			}
			else if (is_string($e_name)) {
				if(isset($this->_entity_objects[$e_type])) {
					if (array_key_exists($e_name, $this->_entity_objects[$e_type])) return $this->_entity_objects[$e_type][$e_name];
				}
			}
		}
		else if (isset($this->_entity_objects)) return $this->_entity_objects;
		return null;
	}
	protected function new_entity_object($e_type = null,$e_name = null, $e_value = null, $new = false) {
		try {
			if (!isset($e_type)) {
				throw new Exception('Entity_type is mandatory');
			}
			if(!is_array($e_value)) {
				throw new Exception('Invalid entity value');
			}
			if (isset($e_name) and $e_name != '') {
				$this->_structure[$e_type][$e_name] = $e_value;
				if ($new) {
					$classname = $this->entity_object_name($e_type);
					if (isset($classname)) {
						$this->_entity_objects[$e_type][$e_name] = new $classname(null, $e_value);
					}
				}
			}
			else {
				$this->_structure[$e_type] = $e_value;
				if ($new) {
					$classname = $this->entity_object_name($e_type);
					if (isset($classname)) {
						$this->_entity_objects[$e_type] = new $classname(null, $e_value);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function single_entity_delete($e_type = null) {
		try {
			if (!isset($e_type)) throw new Exception('Argument $entity_type is mandatory');
			if (!is_string($e_type)) throw new Exception('Invalid argument $entity_type; it must be a string');
			if (!isset($this->_structure[$e_type])) return;
			unset($this->_structure[$e_type]);
			if (isset($this->_entity_objects[$e_type])) unset($this->_entity_objects[$e_type]);
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function simple_list($e_type = null, $entities = null, $new = false) {
		try {
			if (!isset($e_type)) {
				throw new Exception('Entity_type is mandatory');
			}
			if(!is_array($entities)) {
				throw new Exception('Invalid entities');
			}
			foreach($entities as $e_name => $e_value) {
				$this->_structure[$e_type][$e_name] = $e_value;
				if ($new) {
					$classname = $this->entity_object_name($e_type);
					if (isset($classname)) {
						$this->_entity_objects[$e_type][$e_name] = new $classname(null, $e_value);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function list_delete($e_type = null, $todel = null, $sequenced = false) {
		try {
			if (!isset($e_type)) throw new Exception('Argument $entity_type is mandatory');
			if (!is_string($e_type)) throw new Exception('Invalid argument $entity_type; it must be a string');
			if (!isset($this->_structure[$e_type])) return;
			if (isset($todel) ) {
				if(!is_array($todel)) {
					throw new Exception('Invalid argument entities to delete; it must be array');
				}
				foreach($todel as $name) {
					if ($sequenced) {
						// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
						foreach($this->_structure[$e_type] as $pos => $prop) {
							if (array_key_exists($name, $prop)) {
								unset($this->_structure[$e_type][$pos]);
								break;
							}
						}
						$this->_structure[$e_type] = array_values($this->_structure[$e_type]);
					}
					else {
						if (array_key_exists($name, $this->_structure[$e_type])) unset($this->_structure[$e_type][$name]);
					}
					if (isset($this->_entity_objects[$e_type][$name])) unset($this->_entity_objects[$e_type][$name]);
				}
				if (count($this->_structure[$e_type]) == 0) unset($this->_structure[$e_type]);
				if (isset($this->_entity_objects[$e_type])) {
					if (count($this->_entity_objects[$e_type]) == 0) unset($this->_entity_objects[$e_type]);
				}
			}
			else {
				unset($this->_structure[$e_type]);
				if (isset($this->_entity_objects[$e_type])) unset($this->_entity_objects[$e_type]);
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function list_of_strings($e_type = null, $entities = null) {
		try {
			if (!isset($e_type)) {
				throw new Exception('Entity_type is mandatory');
			}
			if (!is_array($entities)) {
				throw new Exception('Invalid entities');
			}
			foreach($entities as $string) {
				try {
					if (!is_string($string)) {
						throw new Exception('Invalid entity, not string');
					}
					if (!isset($this->_structure[$e_type]))
						$this->_structure[$e_type][] = $string;
					else if (array_search($string, $this->_structure[$e_type]) === false) $this->_structure[$e_type][] = $string;
				} catch(Exception $e) {
					$this->error_out($e);
				}
			}
			
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function list_of_strings_delete($e_type = null, $todel = null) {
		try {
			if (!isset($e_type)) throw new Exception('Argument $entity_type is mandatory');
			if (!is_string($e_type)) throw new Exception('Invalid argument $entity_type; it must be a string');
			if (!isset($this->_structure[$e_type])) return;
			if (isset($todel) ) {
				if(!is_array($todel)) {
					throw new Exception('Invalid argument entities to delete; it must be array');
				}
				foreach($todel as $name) {
					if (($pos = array_search($name, $this->_structure[$e_type])) !== false) unset($this->_structure[$e_type][$pos]);
					$this->_structure[$e_type] = array_values($this->_structure[$e_type]);
					if (isset($this->_entity_objects[$e_type][$name])) unset($this->_entity_objects[$e_type][$name]);
				}
				if (count($this->_structure[$e_type]) == 0) unset($this->_structure[$e_type]);
				if (isset($this->_entity_objects[$e_type])) {
					if (count($this->_entity_objects[$e_type]) == 0) unset($this->_entity_objects[$e_type]);
				}
			}
			else {
				unset($this->_structure[$e_type]);
				if (isset($this->_entity_objects[$e_type])) unset($this->_entity_objects[$e_type]);
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function list_of_sequenced($e_type = null, $entities = null) {
		try {
			if (!isset($e_type)) {
				throw new Exception('Entity_type is mandatory');
			}
			if (!is_array($entities)) {
				throw new Exception('Invalid entities');
			}
			foreach($entities as $item) {
				foreach($item as $item_name => $item_val) {
					$this->sequenced_list($this->_structure[$e_type], $item_name, $item_val );
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function simple_string($e_type = null, $str_value = null) {
		try {
			if (!isset($e_type)) {
				throw new Exception('Entity_type is mandatory');
			}
			if (isset($str_value)) {
				if (!is_string($str_value)) {
					throw new Exception('Invalid argument, not string');
				}
				$this->_structure[$e_type] = $str_value;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		if (isset($this->_structure[$e_type])) return $this->_structure[$e_type];
	}
	protected function map_of($e_type = null, $entities = null) {
		try {
			if (!isset($e_type)) {
				throw new Exception('Entity_type is mandatory');
			}
			if(!is_array($entities)) {
				throw new Exception('Invalid entities');
			}
			foreach($entities as $key_name => $key_value) {
				$this->_structure[$e_type][$key_name] = $key_value;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		if (isset($this->_structure[$e_type])) return $this->_structure[$e_type];
	}
	protected function range_of($e_type = null, $lower = null, $upper = null) {
		try {
			if (!isset($e_type)) throw new Exception('Entity_type is mandatory');
			if (!isset($lower)) throw new Exception('Lower bound is mandatory');
			if (!isset($upper)) throw new Exception('Upper bound is mandatory');
			if(is_int($lower) and is_int($upper) and $upper >= $lower) {
				$this->_structure[$e_type][] = $lower;
				$this->_structure[$e_type][] = $upper;
			}
			else if (is_int($lower) and ($upper == 'UNBOUNDED')) {
				$this->_structure[$e_type][] = $lower;
				$this->_structure[$e_type][] = $upper;
			}
			else throw new Exception('Invalid lower bound ('.$lower.') or upper bound ('.$upper.')');
		} catch(Exception $e) {
			$this->error_out($e);
		}
		if (isset($this->_structure[$e_type])) return $this->_structure[$e_type];
	}
	
	public function get(){ if (isset($this->_structure)) return $this->_structure;
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
	public function description($ds = null) {
		return $this->simple_string(__FUNCTION__, $ds);
	}
}
class tosca_node_filter extends  tosca_root {      			 // OK 6.0, OK delete
	function __construct($dummy = null, $struct = null) {
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
		switch ($entity) {
			case 'description':
				$this->single_entity_delete($entity);
				break;
			case 'properties':
			case 'capabilities':
				$this->list_delete($entity, $todel, true);
				break;
		}
	}
}
class tosca_requirement extends tosca_root {     		 	 // OK 6.0, OK delete
	function __construct($dummy = null, $struct = null) {
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
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			case 'node_filter':
				$classname = 'tosca_node_filter';
				break;
			default:
		}
		return $classname;
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
							$this->new_entity_object($key_name, null, $key_value, $new);
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
	public function get_node_filter() { 
		return $this->entity_objects(substr(__FUNCTION__, 4));
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'node':
			case 'relationship':
			case 'capability':
			case 'node_filter':
				$this->single_entity_delete($entity);
				break;
		}
	}
}
class tosca_requirement_definition extends tosca_root {		 // OK 6.0, OK delete
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'description':
						$this->description($value);
						break;
					case 'node':
					case 'relationship':
					case 'capability':
						$this->keys(array($key => $value));
						break;
					case 'occurrences':
						$this->occurrences($value[0], $value[1]);
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
						if ( $key_name == 'node' or $key_name == 'relationship' or 
							 $key_name == 'capability') {							// to do: capability must be mandatory
							$this->simple_string($key_name,$key_value);
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
	public function occurrences($lower = null, $upper = null) {
		if (!isset($lower) and !isset($upper)) ;// do nothing
		else $this->range_of(__FUNCTION__, $lower, $upper);
		return $this->_structure[__FUNCTION__];
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'node':
			case 'relationship':
			case 'capability':
			case 'occurrences':
				$this->single_entity_delete($entity);
				break;
		}
	}
}
class tosca_topology_template extends tosca_root{    		 // OK 6.0, OK delete
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function check_group($gr_def) {
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
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			case 'inputs':
			case 'outputs':
				$classname = 'tosca_parameter';
				break;
			case 'node_templates':
				$classname = 'tosca_node_template';
				break;
			case 'groups':
				$classname = 'tosca_group';
				break;
			case 'substitution_mappings':
				$classname = 'tosca_substitution_mapping';
				break;
			// case 'entity_name':
				// $classname = 'class_name';
				// break;
			default:
		}
		return $classname;
	}

	public function inputs($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_inputs($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function outputs($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_outputs($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function node_templates($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_node_templates($name = null) { 
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
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
						$this->new_entity_object(__FUNCTION__, $name, $def, $new);
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
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function substitution_mappings($sm = null, $new = false) {
		if (isset($sm)) {
			$this->new_entity_object(__FUNCTION__, null, $sm, $new);
		}
		return $this->_structure['substitution_mappings'];
	}
	public function get_substitution_mappings() {
		return $this->entity_objects(substr(__FUNCTION__, 4));
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'substitution_mappings':
				$this->single_entity_delete($entity);
				break;
			case 'inputs':
			case 'outputs':
			case 'node_templates':
			case 'groups':
				$this->list_delete($entity, $todel);
				break;
		}
	}
}
class tosca_definitions extends tosca_root {         		 // OK 6.0
	private $_normative_pathname = 'normative_types/';
	private $_normative_filename = 'TOSCA_definition_1_0.yml';
	private $_normative = false;
	private static  $_def = [			// array for type defininitions organized in family types
			'primitive_types' => [],
			'node_types' => [],
			'group_types' => [],
			'relationship_types' => [],
			'capability_types' => [],
			'interface_types' => [],
			'data_types' => [],
			'artifact_types' => [],
			'policy_types' => []
		];			

	protected function import_definitions($definitions = null) {
		if (count(self::$_def['primitive_types']) == 0) {
		self::$_def['primitive_types'] = array( 	
								'string' => null,
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
		if (!isset($definitions)) {
			if (!$this->_normative) $parsed = yaml_parse_file($this->_normative_pathname.$this->_normative_filename);
			$this->_normative = true;
		}
		else if (is_array($definitions)) $parsed = $definitions;
		else if (is_file($definitions))  $parsed = yaml_parse_file($definitions);
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
				self::$_def[$key_name] = array_merge(self::$_def[$key_name], $key_value);
			}
		}
	}
	protected function check_type($typename, $family_type = null) {
		if (!isset($family_type)) {
			foreach (self::$_def as $f_type => $t_def) {
				if(array_key_exists($typename, $t_def)) {
					return true;
				}
			}
		}
		else {
			if (!in_array($family_type, $this->family_types())) return false; 
			if(array_key_exists($typename, self::$_def[$family_type])) return true;
		}
		return false;
	}
	protected function list_of_types($e_type = null, $entities = null, $family_type = null) {  // list of strings
		try {
			if (!isset($e_type)) {
				throw new Exception('Entity_type is mandatory');
			}
			if (!is_array($entities)) {
				throw new Exception('Invalid argument');
			}
			foreach($entities as $type) {
				try {
					if (!$this->check_type($type, $family_type)) {
						throw new Exception('Invalid type: '.$type);
					}
					if (!isset($this->_structure[$e_type]))
						$this->_structure[$e_type][] = $type;
					else if (array_search($type, $this->_structure[$e_type]) === false) $this->_structure[$e_type][] = $type;
				} catch(Exception $e) {
					$this->error_out($e);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function super_type($type_name, $e_type = null) {
		try {
			$type_def = null;
			if (!isset($type_name)) {
				throw new Exception('Type name is mandatory');
			}
			foreach($this->definitions() as $f_type => $t_defs) {
				if ($f_type != 'primitive_types') {
					if (array_key_exists($type_name, $t_defs)) {
						$type_def = $t_defs[$type_name];
						break;
					}
				}
			}
			if (array_key_exists('derived_from', $type_def)) {
				$type_def2 = $this->super_type($type_def['derived_from']);
				unset($type_def['derived_from']);
				$type_def = array_merge_recursive($type_def, $type_def2);
				if (array_key_exists('description', $type_def)) unset($type_def['description']);
				if (array_key_exists('version', $type_def)) unset($type_def['version']);
			}
			if (!isset($e_type)) return $type_def;
			if (array_key_exists($e_type, $type_def)) return $type_def[$e_type];
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return null;
	}
	
	public function definitions($family_type = null) {
		if (!isset($family_type)) return self::$_def;
		if (!in_array($family_type, $this->family_types()))	return self::$_def;
		return self::$_def[$family_type];
	}
	public function type_names($family_type = null) {
		if (!isset($family_type)) return array_merge(array_keys(self::$_def['primitive_types']), array_keys(self::$_def['node_types']), array_keys(self::$_def['group_types']),
													 array_keys(self::$_def['relationship_types']), array_keys(self::$_def['capability_types']), array_keys(self::$_def['interface_types']),
													 array_keys(self::$_def['data_types']), array_keys(self::$_def['artifact_types']), array_keys(self::$_def['policy_types']));
		if (in_array($family_type, $this->family_types())) return array_keys(self::$_def[$family_type]);
	}
	public function family_types() {
		return array_keys(self::$_def);
	}
}
class tosca_operation extends tosca_root {    				 // OK 6.0, OK delete
	function __construct($dummy = null, $struct = null) {
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
	
	public function inputs($entities = null) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
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
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'implementation':
				$this->single_entity_delete($entity);
				break;
			case 'inputs':
				$this->list_delete($entity, $todel);
				break;
		}
	}
}
class tosca_service_template extends tosca_definitions {     // OK 6.0, OK delete, OK typedef
	function __construct($struct = null) {
		$this->import_definitions();
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
					case 'node_types':
					case 'group_types':
					case 'capability_types':
					case 'interface_types':
					case 'data_types':
					case 'artifact_types':
					case 'relationship_types':
					case 'policy_types':
						$this->$key($value, true);
						break;
					case 'topology_template':
						$this->topology_template($value, true);
						break;
					case 'imports':
						foreach ($value as $file) {
							$this->imports($file);
						}
						break;
					case 'dsl_defintions':
					case 'repositories':
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			case 'topology_template':
				$classname = 'tosca_topology_template';
				break;
			case 'node_types':
			case 'group_types':
			case 'capability_types':
			case 'interface_types':
			case 'data_types':
			case 'artifact_types':
			case 'relationship_types':
			case 'policy_types':
				$classname = 'tosca_'.$e_type;
				$classname = substr_replace($classname ,"",-1);  // delete last character 's'
				break;
			// case 'entity_name':
				// $classname = 'class_name';
				// break;
			default:
		}
		return $classname;
	}

	public function imports($imp = null) {
		try {
			if(isset($imp)) {
				if(!is_array($imp)) {
					throw new Exception('Invalid argument');
				}
				foreach($imp as $imp_name => $imp_value) {
					$this->sequenced_list($this->_structure['imports'], $imp_name, $imp_value);
					$this->import_definitions($imp_value);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['imports'];
	}
	public function tosca_definitions_version($profile) {
		return $this->simple_string(__FUNCTION__, $profile);
	}
	public function metadata($mds = null) {
		return $this->map_of(__FUNCTION__, $mds);
	}
	public function topology_template($tt, $new = false) {
		if (isset($tt)) {
			$this->new_entity_object(__FUNCTION__, null, $tt, $new);
		}
		return $this->_structure['topology_template'];
	}
	public function get_topology_template() { 
		return $this->entity_objects(substr(__FUNCTION__, 4));
	}
	public function node_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_node_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function group_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_group_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function capability_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_capability_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function interface_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_interface_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function data_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_data_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function artifact_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_artifact_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'tosca_definitions_version':
			case 'topology_template':
				$this->single_entity_delete($entity);
				break;
			case 'metadata':
			case 'node_types':
			case 'group_types':
			case 'capability_types':
			case 'interface_types':
			case 'data_types':
			case 'artifact_types':
			case 'relationship_types':
			case 'policy_types':
				$this->list_delete($entity, $todel);
				break;
			case 'imports':
				$this->list_delete($entity, $todel, true);
				break;
		}
	}
	public function type_info($type_name, $e_type = null) {
		return $this->super_type($type_name, $e_type);
	}
}
class tosca_typified extends tosca_definitions {       		 // OK 6.0
	private  $_type = null;			// string
	
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
			if (isset($typename) and isset($clear) and ($this->definitions() !== null)) {
				if (!$this->check_type($typename)) {
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
		foreach($this->definitions() as $family_type => $definition) {
			foreach($definition as $ty_name => $ty_def) {
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
	protected function simple_checked_list($e_type = null, $entities = null, $new = false) {
		try {
			if (!isset($e_type)) {
				throw new Exception('Entity_type is mandatory');
			}
			if(!is_array($entities)) {
				throw new Exception('Invalid entities');
			}
			foreach($entities as $e_name => $e_value) {
				try {
					if (!$this->check_name($e_name, $e_type)) {
						throw new Exception('Invalid capability '.$e_name);
					}
					$this->_structure[$e_type][$e_name] = $e_value;
					if ($new) {
						$classname = $this->entity_object_name($e_type);
						if (isset($classname)) {
							$this->_entity_objects[$e_type][$e_name] = new $classname(null, $e_value);
						}
					}
				} catch(Exception $e) {
					$this->error_out($e);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	
	public function type() {
		return $this->_type;
	}
	public function type_info($e_type = null) {
		return $this->super_type($this->_type, $e_type);
	}
}
class tosca_node_template  extends  tosca_typified {       	 // OK 6.0, OK delete
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
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			// case 'entity_name':
				// $classname = 'class_name';
				// break;
			case 'artifacts':
				$classname = 'tosca_artifact';
				break;
			case 'capabilities':
				$classname = 'tosca_capability';
				break;
			case 'requirements':
				$classname = 'tosca_requirement';
				break;
			case 'node_filter':
				$classname = 'tosca_node_filter';
				break;
			case 'interfaces':
				$classname = 'tosca_interface';
				break;
			default:
		}
		return $classname;
	}

	public function properties($entities = null) {
		if (isset($entities) ) $this->simple_checked_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function attributes($entities = null) {
		if (isset($entities) ) $this->simple_checked_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function interfaces($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_interfaces($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function capabilities($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_checked_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_capabilities($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function artifacts($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_artifacts($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function requirements($attr = null, $new = false) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, __FUNCTION__)) {
							throw new Exception('Invalid requirement '.$attr_name);
						}
						$this->sequenced_list($this->_structure[__FUNCTION__], $attr_name, $attr_value);
						if ($new) {
							$classname = $this->entity_object_name(__FUNCTION__);
							$this->_entity_objects[__FUNCTION__][$attr_name] = new tosca_requirement(null, $attr_value);
						}
						
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_requirements($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function node_filter($nf = null, $new = false) {
		if (isset($nf)) {
			$this->new_entity_object(__FUNCTION__, null, $nf, $new);
		}
		return $this->_structure['node_filter'];
	}
	public function get_node_filter() { 
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'node_filter':
				$this->single_entity_delete($entity);
				break;
			case 'artifacts':
			case 'properties':
			case 'attributes':
			case 'capabilities':
			case 'interfaces':
				$this->list_delete($entity, $todel);
				break;
			case 'requirements':
				$this->list_delete($entity, $todel, true);
				break;
		}
	}
}
class tosca_interface  extends  tosca_typified {       		 // OK 6.0, OK delete
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
					default:
						$this->operations(array($key => $value), true);
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			case 'operations':
				$classname = 'tosca_operation';
				break;
			default:
		}
		return $classname;
	}

	public function inputs($entities = null) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function operations($op = null, $new = false) {
		try {
			if (isset($op)) {
				if (!is_array($op)) {
					throw new Exception('Invalid argument');
				}
				foreach($op as $name => $value) {
					$this->_structure[$name] = $value;
					if ($new) {
						$classname = $this->entity_object_name(__FUNCTION__);
						if (isset($classname)) {
							$this->_entity_objects[__FUNCTION__][$name] = new $classname(null, $value);
						}
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure;
	}
	public function get_operations($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function delete($entity, $todel = null) {
		try {
			switch ($entity) {
				case 'inputs':
					$this->list_delete($entity, $todel);
					break;
				case 'operations':
					if (isset($todel) ) {
						if(!is_array($todel)) {
							throw new Exception('Invalid argument $todel; it must be array');
						}
						foreach($todel as $name) {
							if (array_key_exists($name, $this->_structure)) {
								unset($this->_structure[$name]);
								if (isset($this->_entity_objects[$entity][$name])) unset($this->_entity_objects[$entity][$name]);
							}
						}
						if (count($this->_entity_objects[$entity]) == 0) unset($this->_entity_objects[$entity]);
					}
					else {
						foreach($this->_structure as $name => $val) {
							if ($name != 'inputs') unset($this->_structure[$name]);
						}
						unset($this->_entity_objects[$entity]);
					}
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class tosca_interface_definition  extends  tosca_typified {	 // OK 6.0, OK delete
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
					case 'inputs':
						$this->inputs($value, true);
						break;
					default:
						$this->operations(array($key => $value), true);
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			case 'inputs':
				$classname = 'tosca_property_definition';
				break;
			case 'operations':
				$classname = 'tosca_operation';
				break;
			default:
		}
		return $classname;
	}

	public function inputs($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_inputs($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function operations($op = null, $new = false) {
		try {
			if (isset($op)) {
				if (!is_array($op)) {
					throw new Exception('Invalid argument');
				}
				foreach($op as $name => $value) {
					$this->_structure[$name] = $value;
					if ($new) {
						$classname = $this->entity_object_name(__FUNCTION__);
						if (isset($classname)) {
							$this->_entity_objects[__FUNCTION__][$name] = new $classname(null, $value);
						}
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure;
	}
	public function get_operations($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function delete($entity, $todel = null) {
		try {
			switch ($entity) {
				case 'inputs':
					$this->list_delete($entity, $todel);
					break;
				case 'operations':
					if (isset($todel) ) {
						if(!is_array($todel)) {
							throw new Exception('Invalid argument $todel; it must be array');
						}
						foreach($todel as $name) {
							if (array_key_exists($name, $this->_structure)) {
								unset($this->_structure[$name]);
								if (isset($this->_entity_objects[$entity][$name])) unset($this->_entity_objects[$entity][$name]);
							}
						}
						if (count($this->_entity_objects[$entity]) == 0) unset($this->_entity_objects[$entity]);
					}
					else {
						foreach($this->_structure as $name => $val) {
							if (!in_array($name, ['inputs', 'type'])) unset($this->_structure[$name]);
						}
						unset($this->_entity_objects[$entity]);
					}
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
class tosca_capability  extends  tosca_typified {       	 // OK 6.0, OK delete
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

	public function properties($entities = null) {
		if (isset($entities) ) $this->simple_checked_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function attributes($entities = null) {
		if (isset($entities) ) $this->simple_checked_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'properties':
			case 'attributes':
				$this->list_delete($entity, $todel);
				break;
		}
	}
}
class tosca_capability_definition  extends  tosca_typified { // OK 6.0, OK delete
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
					case 'description': 
						$this->description($value);
						break;
					case 'properties':
						$this->properties($value, true);
						break;
					case 'attributes':
						$this->attributes($value, true);
						break;
					case 'valid_source_types':
						$this->valid_source_types($value);
						break;
					case 'occurrences':
						$this->occurrences($value[0], $value[1]);
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			// case 'entity_name':
				// $classname = 'class_name';
				// break;
			case 'attributes':
				$classname = 'tosca_attribute_definition';
				break;
			case 'properties':
				$classname = 'tosca_property_definition';
				break;
			default:
		}
		return $classname;
	}

	public function properties($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_properties($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function attributes($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_attributes($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function valid_source_types($entities = null) { 
		if (isset($entities)) $this->list_of_types(__FUNCTION__, $entities, 'node_types');
		return $this->_structure[__FUNCTION__];
	}
	public function occurrences($lower = null, $upper = null) {
		if (!isset($lower) and !isset($upper)) ;// do nothing
		else $this->range_of(__FUNCTION__, $lower, $upper);
		return $this->_structure[__FUNCTION__];
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description': 
			case 'occurrences':
				$this->single_entity_delete($entity);
				break;
			case 'properties':
			case 'attributes':
				$this->list_delete($entity, $todel);
				break;
			case 'valid_source_types':
				$this->list_of_strings_delete($entity, $todel);
				break;
		}
	}
}
class tosca_artifact extends tosca_typified {        		 // OK 6.0, OK delete
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
						if ( $key_name == 'file' or $key_name == 'repository' or       // to do: file must be mandatory
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
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'file':
			case 'repository':
			case 'deploy_path':
				$this->single_entity_delete($entity);
				break;
		}
	}
}
class tosca_parameter extends tosca_typified {       		 // OK 6.0, OK delete
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
					case 'constraints':
						$this->keys(array($key => $value));
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
						if ( $key_name == 'value' or $key_name == 'required' or $key_name == 'default' ) $this->_structure[$key_name] = $key_value;
						else if ($key_name == 'status') $this->simple_string($key_name, $key_value);
						else if ($key_name == 'constraints') $this->list_of_sequenced($key_name, $key_value);
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
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'value':
			case 'required':
			case 'default':
			case 'status':
				$this->single_entity_delete($entity);
				break;
			case 'constraints':
				$this->list_delete($entity, $todel, true);
				break;
		}
	}
}
class tosca_property_definition extends tosca_typified {	 // OK 6.0, OK delete
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
					case 'required':
					case 'default':
					case 'status':
					case 'constraints':
						$this->keys(array($key => $value));
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
						if ( $key_name == 'required' or $key_name == 'default' ) $this->_structure[$key_name] = $key_value;
						else if ($key_name == 'status') $this->simple_string($key_name, $key_value);
						else if ($key_name == 'constraints') $this->list_of_sequenced($key_name, $key_value);
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
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description': 
			case 'required':
			case 'default':
			case 'status':
				$this->single_entity_delete($entity);
				break;
			case 'constraints':
				$this->list_delete($entity, $todel, true);
				break;
		}
	}
}  
class tosca_attribute_definition extends tosca_typified {	 // OK 6.0, OK delete
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
					case 'default':
					case 'status':
						$this->keys(array($key => $value));
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
						if ( $key_name == 'default' ) {
							$this->_structure[$key_name] = $key_value;
						}
						else if ($key_name == 'status') $this->simple_string($key_name, $key_value);
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
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description': 
			case 'default':
			case 'status':
				$this->single_entity_delete($entity);
				break;
		}
	}
}  
class tosca_group extends tosca_typified {    				 // OK 6.0, OK delete
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
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			case 'interfaces':
				$classname = 'tosca_interface';
				break;
			default:
		}
		return $classname;
	}

	public function properties($entities = null) {
		if (isset($entities) ) $this->simple_checked_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function interfaces($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_interfaces($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function targets($entities = null) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->single_entity_delete($entity);
				break;
			case 'properties':
			case 'interfaces':
			case 'targets':
				$this->list_delete($entity, $todel);
				break;
		}
	}
}
class tosca_substitution_mapping extends tosca_typified {    // OK 6.0, OK delete
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
	
	public function capabilities($entities = null) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function requirements($entities = null) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->single_entity_delete($entity);
				break;
			case 'capabilities':
			case 'requirements':
				$this->list_delete($entity, $todel);
				break;
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
class tosca_common_type extends tosca_definitions {			 // OK 6.0

	public function derived_from($tn = null) {
		try {
			if (isset($tn)) {
				if (!$this->check_type($tn)) {
					throw new Exception('Invalid typename '.$tn);
				}
				$this->_structure['derived_from'] = $tn;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['derived_from'];
	}
	public function version($vn = null) {
		return $this->simple_string(__FUNCTION__, $vn);
	}
}
class tosca_node_type extends tosca_common_type {			 // OK 6.0	OK delete
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'derived_from':
						$this->derived_from($value);
						break;
					case 'version':
						$this->version($value);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'properties':
						$this->properties($value, true);
						break;
					case 'artifacts':
						$this->artifacts($value, true);
						break;
					case 'attributes':
						$this->attributes($value, true);
						break;
					case 'requirements':
						foreach ($value as $req) {
							$this->requirements($req, true);
						}
						break;
					case 'capabilities':
						$this->capabilities($value, true);
						break;
					case 'interfaces':
						$this->interfaces($value, true);
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			// case 'entity_name':
				// $classname = 'class_name';
				// break;
			case 'artifacts':
				$classname = 'tosca_artifact';
				break;
			case 'attributes':
				$classname = 'tosca_attribute_definition';
				break;
			case 'requirements':
				$classname = 'tosca_requirement_definition';
				break;
			case 'capabilities':
				$classname = 'tosca_capability_definition';
				break;
			case 'interfaces':
				$classname = 'tosca_interface_definition';
				break;
			case 'properties':
				$classname = 'tosca_property_definition';
				break;
			default:
		}
		return $classname;
	}

	public function artifacts($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_artifacts($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function properties($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_properties($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function attributes($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_attributes($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function requirements($attr = null, $new = false) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					$this->sequenced_list($this->_structure[__FUNCTION__], $attr_name, $attr_value);
					if ($new) {
						$classname = $this->entity_object_name(__FUNCTION__);
						$this->_entity_objects[__FUNCTION__][$attr_name] = new $classname(null, $attr_value);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_requirements($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function capabilities($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_capabilities($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function interfaces($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_interfaces($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->single_entity_delete($entity);
				break;
			case 'artifacts':
			case 'properties':
			case 'attributes':
			case 'capabilities':
			case 'interfaces':
				$this->list_delete($entity, $todel);
				break;
			case 'requirements':
				$this->list_delete($entity, $todel, true);
				break;
		}
	}
}
class tosca_artifact_type extends tosca_common_type {		 // OK 6.0	OK delete
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'derived_from':
						$this->derived_from($value);
						break;
					case 'version':
						$this->version($value);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'mime_type':
						$this->mime_type($value);
						break;
					case 'file_ext':
						$this->file_ext($value);
						break;
					case 'properties':
						$this->properties($value, true);
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			// case 'entity_name':
				// $classname = 'class_name';
				// break;
			case 'properties':
				$classname = 'tosca_property_definition';
				break;
			default:
		}
		return $classname;
	}

	public function properties($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_properties($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function mime_type($mt = null) {
		return $this->simple_string(__FUNCTION__, $mt);
	}
	public function file_ext($entities = null) {
		if (isset($entities)) $this->list_of_strings(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'mime_type':
				$this->single_entity_delete($entity);
				break;
			case 'properties':
				$this->list_delete($entity, $todel);
				break;
			case 'file_ext':
				$this->list_of_strings_delete($entity, $todel);
				break;
		}
	}
}
class tosca_capability_type extends tosca_common_type {		 // OK 6.0	OK delete
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'derived_from':
						$this->derived_from($value);
						break;
					case 'version':
						$this->version($value);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'properties':
						$this->properties($value, true);
						break;
					case 'attributes':
						$this->attributes($value, true);
						break;
					case 'valid_source_types':
						$this->valid_source_types($value);
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			case 'attributes':
				$classname = 'tosca_attribute_definition';
				break;
			case 'properties':
				$classname = 'tosca_property_definition';
				break;
			default:
		}
		return $classname;
	}

	public function attributes($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_attributes($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function properties($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_properties($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function valid_source_types($entities = null) {
		if (isset($entities)) $this->list_of_types(__FUNCTION__, $entities, 'node_types');
		return $this->_structure[__FUNCTION__];
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->single_entity_delete($entity);
				break;
			case 'properties':
			case 'attributes':
				$this->list_delete($entity, $todel);
				break;
			case 'valid_source_types':
				$this->list_of_strings_delete($entity, $todel);
				break;
		}
	}
}
class tosca_data_type extends tosca_common_type {			 // OK 6.0	OK delete
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'derived_from':
						$this->derived_from($value);
						break;
					case 'version':
						$this->version($value);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'properties':
						$this->properties($value, true);
						break;
					case 'constraints':
						$this->constraints($value);
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			case 'properties':
				$classname = 'tosca_property_definition';
				break;
			default:
		}
		return $classname;
	}

	public function properties($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_properties($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function constraints($entities = null) {
		if (isset($entities) ) $this->list_of_sequenced(__FUNCTION__, $entities);
		return $this->_structure[__FUNCTION__];
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->single_entity_delete($entity);
				break;
			case 'properties':
				$this->list_delete($entity, $todel);
				break;
			case 'constraints':
				$this->list_delete($entity, $todel, true);
				break;
		}
	}
}
class tosca_group_type extends tosca_common_type {			 // OK 6.0	OK delete
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'derived_from':
						$this->derived_from($value);
						break;
					case 'version':
						$this->version($value);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'properties':
						$this->properties($value, true);
						break;
					case 'targets':
						$this->targets($value);
						break;
					case 'interfaces':
						$this->interfaces($value, true);
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			// case 'entity_name':
				// $classname = 'class_name';
				// break;
			case 'interfaces':
				$classname = 'tosca_interface_definition';
				break;
			case 'properties':
				$classname = 'tosca_property_definition';
				break;
			default:
		}
		return $classname;
	}

	public function properties($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_properties($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function interfaces($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_interfaces($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function targets ($entities = null) {
		if (isset($entities)) $this->list_of_types(__FUNCTION__, $entities, 'node_types');
		return $this->_structure[__FUNCTION__];
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->single_entity_delete($entity);
				break;
			case 'properties':
			case 'interfaces':
				$this->list_delete($entity, $todel);
				break;
			case 'targets':
				$this->list_of_strings_delete($entity, $todel);
				break;
		}
	}
}
class tosca_interface_type extends tosca_common_type {		 // OK 6.0	OK delete
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'derived_from':
						$this->derived_from($value);
						break;
					case 'version':
						$this->version($value);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'inputs':
						$this->inputs($value, true);
						break;
					default:
						$this->operations(array($key => $value), true);
						break;
				}
			}
		}
	}
	protected function entity_object_name($e_type) {
		$classname = null;
		// echo __CLASS__ .'::'. __FUNCTION__ ."\n";
		switch ($e_type) {
			case 'inputs':
				$classname = 'tosca_property_definition';
				break;
			case 'operations':
				$classname = 'tosca_operation';
				break;
			default:
		}
		return $classname;
	}
	public function inputs($entities = null, $new = false) {
		if (isset($entities) ) $this->simple_list(__FUNCTION__, $entities, $new);
		return $this->_structure[__FUNCTION__];
	}
	public function get_inputs($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function operations($op = null, $new = false) {
		try {
			if (isset($op)) {
				if (!is_array($op)) {
					throw new Exception('Invalid argument');
				}
				foreach($op as $name => $value) {
					$this->_structure[$name] = $value;
					if ($new) {
						$classname = $this->entity_object_name(__FUNCTION__);
						if (isset($classname)) {
							$this->_entity_objects[__FUNCTION__][$name] = new $classname(null, $value);
						}
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure;
	}
	public function get_operations($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function delete($entity, $todel = null) {
		try {
			switch ($entity) {
				case 'description':
					$this->single_entity_delete($entity);
					break;
				case 'inputs':
					$this->list_delete($entity, $todel);
					break;
				case 'operations':
					if (isset($todel) ) {
						if(!is_array($todel)) {
							throw new Exception('Invalid argument $todel; it must be array');
						}
						foreach($todel as $name) {
							if (array_key_exists($name, $this->_structure)) {
								unset($this->_structure[$name]);
								if (isset($this->_entity_objects[$entity][$name])) unset($this->_entity_objects[$entity][$name]);
							}
						}
						if (count($this->_entity_objects[$entity]) == 0) unset($this->_entity_objects[$entity]);
					}
					else {
						foreach($this->_structure as $name => $val) {
							if (!in_array($name, ['inputs', 'derived_from', 'version', 'description'])) { 
								unset($this->_structure[$name]);
							}
						}
						unset($this->_entity_objects[$entity]);
					}
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
}
?>