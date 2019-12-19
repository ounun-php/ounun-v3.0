<?php
namespace ounun\http\session;
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
abstract class storage
{
    /** @var self */
    static protected $_instances;

    /**
     * @param string $name
     * @param array $options
     * @return self
     */
    static function i($name = 'file', $options = [])
    {
        if (empty(static::$_instances[$name])) {
            static::$_instances[$name] = new static($options);
        }
        return static::$_instances[$name];
    }


	protected $options;

    /**
     * storage constructor.
     * @param array $options
     */
	public function __construct($options = [])
	{
		$this->options = $options;
	}

    /**
     *
     */
    public function register()
	{
        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );
	}
}
