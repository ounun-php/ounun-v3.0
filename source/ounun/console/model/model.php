<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
abstract class model
{
	protected   $db,
				$_table = null,
		        $_tablefields = [],
	            $_primary = null,
	            $_fields = [],
				$_readonly = [],
				$_create_autofill = [],
				$_update_autofill = [],
				$_filters_input = [],
				$_filters_output = [],
				$_validators = [],
				$_data = [],
				$_options = [],
		        $_fetch_style = self::FETCH_ASSOC;

    public      $_userid = null,
				$_username = null,
				$_groupid = null,
				$_roleid = null;

	function __construct()
	{
		$this->db = & factory::db();
		$current = online();
		if ($current)
		{
			$this->_userid = $current['userid'];
			$this->_username = $current['username'];
			$this->_groupid = $current['groupid'];
			$this->_roleid = $current['roleid'];
		}
	}

	public function __set($name, $value)
	{
		$this->_data[$name] = $value;
	}

	public function __get($name)
	{
		return isset($this->_data[$name]) ? $this->_data[$name] : null;
	}

	public function __isset($name)
	{
		return isset($this->_data[$name]);
	}

	public function __unset($name)
	{
		unset($this->_data[$name]);
	}

	public function __call($method, $args)
	{
		if(in_array($method, array('field','where','order','limit','offset','having','group','distinct','data'), true))
		{
            $this->_options[$method] = $args[0];
            return $this;
        }
		elseif(in_array($method, array('sum','min','max','avg'), true))
		{
            $field =  isset($args[0]) ? $args[0] : '*';
            return $this->get_field($method.'('.$field.') AS `count`');
        }
		elseif(preg_match("/^(get|gets|delete)_by_(.*)$/", $method, $matches))
		{
			$field = $matches[2];
			if(in_array($field, $this->_fields, true))
			{
				array_unshift($args, $field);
				return call_user_func_array(array(&$this, $matches[1].'_by'), $args);
			}
		}
		else
		{
			if (in_array($this->db->prefix().$method, $this->db->list_tables()))
			{
				return isset($args[1]) ? table($method, $args[0], $args[1]) : table($method, $args[0]);
			}
			else
			{
				throw new ct_exception(__CLASS__.':'.$method.' 方法不存在');
			}
		}
		return;
	}

	public function set_fetch_style($style)
	{
		$this->_fetch_style = $style;
	}

	function select($where = null, $fields = '*', $order = null, $limit = null, $offset = null, $data = [], $multiple = true)
	{
		if(!empty($this->_options))
		{
			$fields = isset($this->_options['distinct']) ? "distinct ".$this->_options['distinct'] : isset($this->_options['field']) ? $this->_options['field'] : $fields;
			$where = isset($this->_options['where']) ? $this->_options['where'] : $where;
			$having = isset($this->_options['having']) ? $this->_options['having'] : null;
			$order = isset($this->_options['order']) ? $this->_options['order'] : $order;
			$group = isset($this->_options['group']) ? $this->_options['group'] : null;
			$limit = isset($this->_options['limit']) ? $this->_options['limit'] : $limit;
			$offset = isset($this->_options['offset']) ? $this->_options['offset'] : $offset;
			$this->_options = array();
		}

		if (is_array($fields)) $fields = '`'.implode('`,`', $fields).'`';

		$this->_where($where);

		if(!$this->_before_select($where)) return false;

		$sql = "SELECT $fields FROM `$this->_table` ";
		if ($where) $sql .= " WHERE $where ";
		if ($order) $sql .= " ORDER BY $order ";
		if ($group) $sql .= " GROUP BY $group ";
		if ($having) $sql .= " HAVING $having ";
        if (is_null($limit) && !$multiple) $sql .= " LIMIT 1 ";

		$method = $multiple ? 'select' : 'get';

		$result = is_null($limit) ? $this->db->$method($sql, $data, $this->_fetch_style) : $this->db->limit($sql, $limit, $offset, $data, $this->_fetch_style);
		if ($result === false)
		{
			$this->error = $this->db->error();
			return false;
		}
		else
		{
			if ($multiple)
			{
				array_map(array(&$this, '_data_output'), $result);
			}
			else
			{
				$this->_data_output($result);
				$this->_data = $result;
			}
			$this->_after_select($result, $multiple);
			return $result;
		}
	}

	protected function _before_select(&$where) {return true;}
	protected function _after_select(&$result, $multiple = true) {}

	public function page($where = null, $fields = '*', $order = null, $page = 1, $size = 20, $data = array())
	{
		$offset = ($page-1)*$size;
		return $this->select($where, $fields, $order, $size, $offset, $data, true);
	}

	public function get($where = null, $fields = '*', $order = null)
	{
		return $this->select($where, $fields, $order, null, null, array(), false);
	}

	public function get_by($field, $value, $fields = '*', $order = null)
	{
		return $this->select("`$field`=?", $fields, $order, null, null, array($value), false);
	}

	public function gets_by($field, $value, $fields = '*', $order = null, $limit = null, $offset = 0)
	{
		return $this->select("`$field`=?", $fields, $order, $limit, $offset, array($value), true);
	}

	public function get_field($field, $where = null, $data = array())
	{
		$r = $this->select($where, $field, null, null, null, $data, false);
		return array_shift($r);
	}

	public function gets_field($field, $where = null, $data = array())
	{
		$result = array();
		$data = $this->select($where, $field, null, null, null, $data, true);
		foreach($data as $r)
		{
			$result[] = array_shift($r);
		}
		return $result;
	}

	public function count($where = null, $data = array())
	{
		$this->_where($where);
		if (!empty($where)) $where = " WHERE $where";
		$r = $this->db->get("SELECT count(*) as `count` FROM `$this->_table` $where", $data);
		return $r ? $r['count'] : false;
	}

	public function primary()
	{
		return isset($this->_primary) ? $this->_primary : $this->db->get_primary($this->_table);
	}

	public function exists($field, $value)
	{
		return $this->db->get("SELECT `$field` FROM `$this->_table` WHERE `$field`=?", array($value)) ? true : false;
	}

	function insert($data = array())
	{
		$this->_data($data);

		if(!$this->_before_insert($data)) return false;

		$this->_create_autofill($data);

		if (!$this->_validate($data)) return false;

		$this->_data_input($data);

		$id = $this->db->insert("INSERT INTO `$this->_table` (`".implode('`,`', array_keys($data))."`) VALUES(".implode(',', array_fill(0, count($data), '?')).")", array_values($data));
		if ($id === false)
		{
		    $this->error = $this->db->error();
			return false;
		}
		else
		{
			$this->_after_insert($data);
			return $id;
		}
	}

	protected function _before_insert(&$data) {return true;}
	protected function _after_insert(&$data) {}

	public function copy_by_id($id, $data = array())
	{
		$r = $this->db->get("SELECT * FROM `$this->_table` WHERE `$this->_primary`=?", array($id));
		if (!$r) return false;
		unset($r[$this->_primary]);
		if ($data) $r = array_merge($r, $data);
		return $this->insert($r);
	}

	function update($data = array(), $where = null, $limit = null, $order = null)
	{
		if(!empty($this->_options))
		{
			$where = isset($this->_options['where']) ? $this->_options['where'] : $where;
			$order = isset($this->_options['order']) ? $this->_options['order'] : $order;
			$limit = isset($this->_options['limit']) ? $this->_options['limit'] : $limit;
			$offset = isset($this->_options['offset']) ? $this->_options['offset'] : $offset;
			$this->_options = array();
		}

		$this->_data($data);

		$this->_where($where);

		if(!$this->_before_update($data, $where)) return false;

		$this->_update_autofill($data);

		$this->_readonly($data);

		if (!$this->_validate($data)) return false;

		$this->_data_input($data);

		$sql = "UPDATE `$this->_table` SET `".implode('`=?,`', array_keys($data))."`=?";
		if ($where) $sql .= " WHERE $where ";
		if ($order) $sql .= " ORDER BY $order ";
		if ($limit) $sql .= " LIMIT $limit ";
		$result = $this->db->update($sql, array_values($data));

		if ($result === FALSE)
		{
			$this->error = $this->db->error();
			return false;
		}
		else
		{
			$this->_after_update($data, $where);
			return $result;
		}
	}

	protected function _before_update(&$data, $where) {return true;}
	protected function _after_update(&$data, $where) {}

	public function set_field($field, $value, $where = null)
	{
		return $this->update(array($field=>$value), $where);
	}

	public function set_inc($field, $where = null, $step = 1, $data = array())
	{
		$this->_where($where);
		return $this->db->update("UPDATE `$this->_table` SET `$field`=`$field`+$step WHERE $where", $data);
	}

	public function set_dec($field, $where = null, $step = 1, $data = array())
	{
		$this->_where($where);
		return $this->db->update("UPDATE `$this->_table` SET `$field`=`$field`-$step WHERE $where", $data);
	}

	function delete($where = null, $limit = null, $order = null, $data = array())
	{
		if(!empty($this->_options))
		{
			$where = isset($this->_options['where']) ? $this->_options['where'] : $where;
			$order = isset($this->_options['order']) ? $this->_options['order'] : $order;
			$limit = isset($this->_options['limit']) ? $this->_options['limit'] : $limit;
			$offset = isset($this->_options['offset']) ? $this->_options['offset'] : $offset;
			$this->_options = array();
		}

		$this->_where($where);

		if(!$this->_before_delete($where)) return false;

		$sql = "DELETE FROM `$this->_table`";
		if ($where) $sql .= " WHERE $where ";
		if ($order) $sql .= " ORDER BY $order ";
		if ($limit) $sql .= " LIMIT $limit ";

		$result = $this->db->delete($sql, $data);
		if ($result === FALSE)
		{
			$this->error = $this->db->error();
			return false;
		}
		else
		{
			$this->_after_delete($where);
			return $result;
		}
	}

	protected function _before_delete(&$where) {return true;}
	protected function _after_delete(&$where) {}

	public function delete_by($field, $value, $limit = null, $order = null)
	{
		return $this->delete("`$field`=?", $limit, $order, array($value));
	}

    private function _data_input(& $data)
	{
		foreach ($data as $key=>$val)
		{
			if(!in_array($key, $this->_fields, true))
			{
				unset($data[$key]);
			}
			elseif(is_scalar($val))
			{
				$fieldtype = $this->_fieldtype($key);
				if(strpos($fieldtype, 'int') !== false)
				{
					$data[$key] = intval($val);
				}
				elseif(strpos($fieldtype, 'float') !== false || strpos($fieldtype, 'double') !== false)
				{
					$data[$key] = floatval($val);
				}
			}
		}
        return true;
    }

	private function _data_output(& $data)
	{
		if(empty($this->_filters_output) || !is_array($data)) return false;
		extract($data);
		foreach ($this->_filters_output as $field=>$filter)
		{
			eval("\$data[$field] = $filter;");
		}
		return true;
	}

	protected function _fieldtype($field)
	{
		static $fields;
		if(is_null($fields) || !isset($fields[$this->_table]))
		{
			$data = $this->db->list_fields($this->_table);
			foreach($data as $k=>$v)
			{
				$fields[$v['Field']] = $v;
			}
		}
		return $fields[$field]['Type'];
	}

	private function _validate(& $data)
	{
		if (empty($this->_validators)) return true;
		$validator = & factory::validator();
		foreach ($this->_validators as $field=>$v)
		{
			if (!isset($data[$field]) || ($data[$field]['required'] == false && $v == '')) continue;
			if (!$validator->execute($data[$field], $v))
			{
				$this->error = $validator->error();
				return false;
			}
		}
		return true;
	}

	private function _create_autofill(& $data)
	{
		if (empty($this->_create_autofill)) return true;
		foreach ($this->_create_autofill as $field=>$val)
		{
			if (!isset($data[$field])) $data[$field] = $val;
		}
	}

	private function _update_autofill(& $data)
	{
		if (empty($this->_update_autofill)) return true;
		foreach ($this->_update_autofill as $field=>$val)
		{
			if (!isset($data[$field])) $data[$field] = $val;
		}
	}

	private function _readonly(& $data)
	{
		if (empty($this->_readonly)) return true;
		foreach ($this->_readonly as $field=>$val)
		{
			if (isset($data[$field])) unset($data[$field]);
		}
	}

	private function _where(& $where)
	{
		if (empty($where) && isset($this->_data[$this->_primary])) $where = $this->_data[$this->_primary];

		if (is_numeric($where))
		{
			$where = "`$this->_primary`=$where";
		}
		elseif (is_array($where))
		{
			$where = array_map('addslashes', $where);

			if (isset($where[0]))
			{
				$ids = is_numeric($where[0]) ? implode_ids($where, ',') : "'".implode_ids($where, "','")."'";
				$where = "`$this->_primary` IN($ids)";
			}
			else
			{
				$condition = array();
				foreach ($where as $field=>$value)
				{
					$condition[] = "`$field`='$value'";
				}
				$where = implode(' AND ', $condition);
			}
		}
		elseif (preg_match("/^[0-9a-z\'\"\,\s]+$/i", $where))
		{
			$where = strpos($where, ',') === false ? "`$this->_primary`='$where'" : "`$this->_primary` IN($where)";
		}
	}

	private function _data(& $data)
	{
		if(empty($data))
		{
			if(!empty($this->_options['data']))
			{
				$data = $this->_options['data'];
			}
			elseif(!empty($this->_data))
			{
				$data = $this->_data;
			}
			elseif(!empty($_POST))
			{
				$data = $_POST;
			}
		}
	}

	protected function filter_array($data, $keys)
	{
		foreach ($data as $field=>$v)
		{
			if (!in_array($field, $keys)) unset($data[$field]);
		}
		return $data;
	}
}
