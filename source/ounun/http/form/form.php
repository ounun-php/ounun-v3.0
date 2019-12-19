<?php
namespace ounun\http\form;
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

class form
{
	protected $INI;

    protected $elements;

    /** @var string enctype */
    protected static $_enctype = 'application/x-www-form-urlencoded';

    /** @var $this */
    protected static $_instance;

    /**
     * @return $this
     */
	static public function i()
	{
	    if(empty(static::$_instance)){
            static::$_instance = new static();
        }
		return static::$_instance;
	}

	public function load_ini($name)
	{
		$this->INI = loader::form($name);
	}

    public function app_set($app)
    {
        $this->app = $app;
    }

	public function execute($name)
	{
		$this->load_ini($name);
		foreach ($this->INI as $name=>$settings) {
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
}
