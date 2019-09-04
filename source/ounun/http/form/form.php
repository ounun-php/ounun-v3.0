<?php

class form
{
	private $INI;
	private $elements;
	public $app = APP;
	private static $enctype = 'application/x-www-form-urlencoded';
	
	public function __construct()
	{
		require(dirname(__FILE__).'/form_element.php');
	}

	static public function &get_instance()
	{
		static $instance;
		if (!is_null($instance)) return $instance;
		$instance = new form();
		return $instance;
	}
	
	function load_ini($name)
	{
		$this->INI = loader::form($name);
	}
	
	public function execute($name)
	{
		$this->load_ini($name);
		foreach ($this->INI as $name=>$settings)
		{
			$method = $settings['type'];
			unset($settings['type']);
			$settings['name'] = $name;
			$this->elements[$name] = form_element::$method($settings);
		}
		return $this->elements;
	}
	
	public function __set($name, $value)
	{
		$this->elements[$name] = $value;
	}

	public function __get($name)
	{
		return $this->elements[$name];
	}
	
	public function __isset($name)
	{
		return isset($this->elements[$name]);
	}

    public function __unset($name)
    {
        unset($this->elements[$name]);
    }
    
	public function set_app($app)
	{
		$this->app = $app;
	}
}