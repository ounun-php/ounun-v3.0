<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\plugin\form\helper;

class tree_view
{
	public $data = [];

    public $html = '';

	public function __construct($data = [])
	{
		$this->data = $data;
	}

	function set($data)
	{
		if (!is_array($data)) {
		    return false;
        }
		$this->data = $data;

		return $this->data;
	}

	function get($id = null, $treeid = 'tree', $eval = '', $deep = 1)
	{
		$childs = $this->child_get($id);
		if (!$childs) {
            return '';
        }
		if ($deep == 1) $eval = addslashes($eval);
		$space = str_repeat("\t", $deep-1);
		$html = $treeid ? "<ul id=\"$treeid\">\n" : "\n".$space."<ul>\n";
		foreach ($childs as $k=>$r)
		{
			$child = $this->child_has($k) ? $this->get($k, '', $eval, $deep+1) : '';
			extract($r);
			eval("\$html .= \"$space\t$eval\n\";");
		}
		$html .= $space."</ul>\n".$space;
		return $html;
	}

	function select($id = null, $selectedid = null, $eval = '', $deep = 1)
	{
		$childs = $this->child_get($id);
		if (!$childs) {
		    return ;
        }
		if ($deep == 1) {
			$eval = addslashes($eval);
			$this->html = '';
		}
		$space = $deep > 1 ? str_repeat('&nbsp;&nbsp; ', $deep-1) : '';
		foreach ($childs as $k=>$r) {
			extract($r);
			$selected = $selectedid == $k ? 'selected' : '';
			eval("\$this->html .= \"$eval\n\";");
			if ($this->child_has($k)) $this->select($k, $selectedid, $eval, $deep+1);
		}
		return $this->html;
	}

	function pos($id, $eval = '')
	{
		if(!is_array($this->data) || !isset($this->data[$id])) return false;
		$parents = $this->parent_get($id);
		$parents[] = $this->data[$id];
		$eval = addslashes($eval);
		$html = '';
		foreach ($parents as $r)
		{
			extract($r);
			eval("\$html .= \"$eval\n\";");
		}
		return $html;
	}

    public function parent_get($id)
	{
		if(!is_array($this->data) || !isset($this->data[$id])) return false;
		static $parents = [];
		$parentid = $this->data[$id]['parentid'];
        if (is_null($parentid)) {
        	krsort($parents);
        } else {
        	$parents[] = $this->data[$parentid];
        	$this->parent_get($parentid);
        }
        return $parents;
	}

    public function child_get($id)
	{
		if(!is_array($this->data) || (!is_null($id) && !isset($this->data[$id]))) {
		    return false;
        }
		if (is_numeric($id)) {
		    $id = intval($id);
        }
		$childs = [];
		foreach($this->data as $k=>$r) {
			if (is_numeric($r['parentid'])) {
			    $r['parentid'] = intval($r['parentid']);
            }
			if($r['parentid'] === $id) {
			    $childs[$k] = $r;
            }
		}
		return $childs;
	}

	public function child_has($id)
	{
		if(!is_array($this->data) || !isset($this->data[$id])) {
		    return false;
        }
		if (is_numeric($id)) {
		    $id = intval($id);
        }
		foreach($this->data as $k=>$r) {
			if (is_numeric($r['parentid'])) {
			    $r['parentid'] = intval($r['parentid']);
            }
			if($r['parentid'] === $id) {
			    return true;
            }
		}
		return false;
	}
}
