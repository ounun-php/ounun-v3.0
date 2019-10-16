<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
abstract class session_storage extends object
{
	protected $options;
	
	function __construct($options = array())
	{
		$this->options = $options;
	}

	static function &get_instance($name = 'file', $options = array())
	{
		static $instances = array();
		if (!isset($instances[$name]))
		{
			$class = 'session_storage_'.$name;
			if(!class_exists($class))
			{
				$path = dirname(__FILE__).'/'.'storage'.'/'.$name.'.php';
				if (!file_exists($path)) exit('Unable to load session storage class: '.$name);
				require_once($path);
			}
			$instances[$name] = new $class($options);
		}
		return $instances[$name];
	}

	function register()
	{
		session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));
	}
}