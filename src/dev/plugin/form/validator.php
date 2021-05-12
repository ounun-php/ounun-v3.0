<?php
namespace ounun\plugin\form;
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

// last updated by yanbingbing,
// check is not empty string before regex check
// if not isset or empty string return true (is valid)
// if need not empty just add rule: not_empty
class validator
{
	const Error_Msg = [];

    const Charset = 'utf-8';

    const Rules = [
        'email'     => '/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/',
        'url'       => '/^[a-zA-Z]{2,5}:\/\/(\w+(-\w+)*)(\.(\w+(-\w+)*))*(\?\S*)?$/',
        'telephone' => '/^(86)?(\d{3,4}-)?(\d{7,8})$/',
        'mobile'    => '/^1\d{10}$/',
        'zip'       => '/^[1-9]\d{5}$/',
        'qq'        => '/^[1-9]\d{4,}$/',
        'date'      => '/^(\d{4})(-|\/)(\d{1,2})\2(\d{1,2})$/',
        'datetime'  => '/^(\d{4})(-|\/)(\d{1,2})\2(\d{1,2})\s(\d{1,2}):(\d{1,2}):(\d{1,2})$/',
        'chinese'   => '/^[\u4e00-\u9fa5]+$/',
        'english'   => '/^[A-Za-z]+$/',
        'varname'	=>	'/^[a-zA-Z][\w]{0,254}$/',	//变量名,函数名,控制器名等
        'integer'	=>	'/^[\d]+$/',			//整数验证
    ];

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


	public function execute($value, $validator = [])
	{
		if (empty($validator)) {
		    return true;
        }
		foreach ($validator as $rule => $args)
		{
			array_unshift($args, $value);
			$error = array_pop($args);

			if (!$this->valid($rule, $args))
			{
				$this->error = $error;
				return false;
			}
		}
		return true;
	}

	public function valid($rule, $args)
	{
		if (method_exists($this, $rule)) {
			return call_user_func_array(array($this, $rule), $args);
		} elseif (isset($this->rules[$rule])) {
			return !isset($args[0]) || strlen((string) $args[0]) == 0 || preg_match(static::Rules[$rule], (string) $args[0]);
		} elseif (function_exists($rule)) {
			return call_user_func_array($rule, $args);
		} else {
			return !isset($args[0]) || strlen((string) $args[0]) == 0 || preg_match($rule, (string) $args[0]);
		}
	}

	static public function not_empty($value)
	{
		return !empty($value);
	}

    static public function min_length($value, $len , $charset = 'utf8')
    {
        return mb_strlen((string) $value, $charset) >= $len;
    }

    static public function max_length($value, $len, $charset = 'utf8')
    {
        return mb_strlen((string) $value, $charset) <= $len;
    }

    static public function min($value, $min)
    {
        return !isset($value) || $value >= $min;
    }

    static public function max($value, $max)
    {
        return !isset($value) || $value <= $max;
    }

    static public function type($value, $type)
    {
        return !isset($value) || gettype($value) == $type;
    }

    static public function alnum($value)
    {
        return !isset($value) || ctype_alnum($value);
    }

    static public function alpha($value)
    {
        return !isset($value) || ctype_alpha($value);
    }

    static public function alnumu($value)
    {
        return !isset($value) || preg_match('/[^a-z0-9_]/', $value) == 0;
    }

    static public function cntrl($value)
    {
        return !isset($value) || ctype_cntrl($value);
    }

    static public function digit($value)
    {
        return !isset($value) || ctype_digit($value);
    }

    static public function graph($value)
    {
        return !isset($value) || ctype_graph($value);
    }

    static public function upper($value)
    {
        return !isset($value) || ctype_upper($value);
    }

    static public function lower($value)
    {
        return !isset($value) || ctype_lower($value);
    }

    static public function punct($value)
    {
        return !isset($value) || ctype_punct($value);
    }

    static public function whitespace($value)
    {
        return !isset($value) || ctype_space($value);
    }

    static public function xdigit($value)
    {
        return !isset($value) || ctype_xdigit($value);
    }

    static public function ascii($value)
    {
        return !isset($value) || strlen((string) $value) == 0 || preg_match('/[^\x20-\x7f]/', $value) == 0;
    }

    static public function ip($value)
    {
    	if (!isset($value) || strlen((string) $value) == 0)
    	{
    		return true;
    	}
        $test = @ip2long($value);
        return $test !== - 1 && $test !== false;
    }

    static public function domain($value)
    {
        return !isset($value) || strlen((string) $value) == 0 || preg_match('/[a-z0-9\.]+/i', $value);
    }
}
