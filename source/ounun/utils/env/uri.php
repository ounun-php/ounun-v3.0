<?php

class uri extends object
{
	var $_uri = null;
	var $_scheme = null;
	var $_host = null;
	var $_port = null;
	var $_user = null;
	var $_pass = null;
	var $_path = null;
	var $_query = null;
	var $_fragment = null;
	var $_vars = array ();

	function __construct($uri = null)
	{
		if ($uri !== null) $this->parse($uri);
	}

	function &get_instance($uri = null)
	{
		static $instances = array();
		if(!isset($instances[$uri]))
		{
			if($uri === null)
			{
				$https = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 's://' : '://';
				if (!empty($_SERVER['PHP_SELF']) && !empty ($_SERVER['REQUEST_URI'])) 
				{
				     $uri = 'http'.$https.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				}
				else
				{
					 $uri = 'http'.$https.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
					 if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) $uri .= '?'.$_SERVER['QUERY_STRING'];
				}
				$uri = urldecode($uri);
				$uri = str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $uri);
				$uri = preg_replace(array('/eval\((.*)\)/', '/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/'), array('', '""'), $uri);
			}
			$instances[$uri] = new uri($uri);
		}
		return $instances[$uri];
	}

	function base($is_full = true)
	{
		static $base;
		if (!isset($base))
		{
				$uri	        = & uri::get_instance();
				$base['prefix'] = $uri->toString( array('scheme', 'host', 'port'));
				if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI']))
				{
					$base['path'] =  rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
				}
				else
				{
					$base['path'] =  rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
				}
		}
		return $is_full ? $base['prefix'].$base['path'].'/' : $base['path'];
	}

	function root($pathonly = false, $path = null)
	{
		static $root;
		if(!isset($root))
		{
			$uri	        = & uri::get_instance(uri::base());
			$root['prefix'] = $uri->toString( array('scheme', 'host', 'port') );
			$root['path']   = rtrim($uri->toString( array('path') ), '/\\');
		}
		
		if(isset($path))
		{
			$root['path']    = $path;
		}
		return $pathonly === false ? $root['prefix'].$root['path'].'/' : $root['path'];
	}

	function current()
	{
		static $current;
		if (is_null($current))
		{
			$uri	 = & uri::get_instance();
			$current = $uri->toString(array('scheme', 'host', 'port', 'path'));
		}
		return $current;
	}

	function parse($uri)
	{
		$retval = false;
		$this->_uri = $uri;
		if($_parts = parse_url($uri))
		{
			$retval = true;
		}
		
		if(isset($_parts['query']) && strpos($_parts['query'], '&amp;'))
		{
			$_parts['query'] = str_replace('&amp;', '&', $_parts['query']);
		}
		$this->_scheme = isset ($_parts['scheme']) ? $_parts['scheme'] : null;
		$this->_user = isset ($_parts['user']) ? $_parts['user'] : null;
		$this->_pass = isset ($_parts['pass']) ? $_parts['pass'] : null;
		$this->_host = isset ($_parts['host']) ? $_parts['host'] : null;
		$this->_port = isset ($_parts['port']) ? $_parts['port'] : null;
		$this->_path = isset ($_parts['path']) ? $_parts['path'] : null;
		$this->_query = isset ($_parts['query'])? $_parts['query'] : null;
		$this->_fragment = isset ($_parts['fragment']) ? $_parts['fragment'] : null;
		if(isset($_parts['query'])) parse_str($_parts['query'], $this->_vars);
		return $retval;
	}

	function toString($parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
	{
		$query = $this->get_query();
		$uri = '';
		$uri .= in_array('scheme', $parts)  ? (!empty($this->_scheme) ? $this->_scheme.'://' : '') : '';
		$uri .= in_array('user', $parts)	? $this->_user : '';
		$uri .= in_array('pass', $parts)	? (!empty($this->_pass) ? ':' : '') .$this->_pass. (!empty($this->_user) ? '@' : '') : '';
		$uri .= in_array('host', $parts)	? $this->_host : '';
		$uri .= in_array('port', $parts)	? (!empty($this->_port) ? ':' : '').$this->_port : '';
		$uri .= in_array('path', $parts)	? $this->_path : '';
		$uri .= in_array('query', $parts)	? (!empty($query) ? '?'.$query : '') : '';
		$uri .= in_array('fragment', $parts)? (!empty($this->_fragment) ? '#'.$this->_fragment : '') : '';
		return $uri;
	}

	function set_var($name, $value)
	{
		$this->_vars[$name] = $value;
		$this->_query = null;
	}

	function get_var($name, $default = null)
	{
		return isset($this->_vars[$name]) ? $this->_vars[$name] : $default;
	}

	function del_var($name)
	{
		if (isset($this->_vars[$name]))
		{
			unset($this->_vars[$name]);
			$this->_query = null;
		}
	}

	function set_query($query)
	{
		if(!is_array($query))
		{
			if(strpos($query, '&amp;') !== false)
			{
			   $query = str_replace('&amp;', '&', $query);
			}
			parse_str($query, $this->_vars);
		}
		else
		{
			$this->_vars = $query;
		}
		$this->_query = null;
	}
	
	function get_query()
	{
		if(is_null($this->_query))
		{
			$this->_query = http_build_query($this->_vars);
		}
		return $this->_query;
	}

	function get_scheme()
	{
		return $this->_scheme;
	}

	function set_scheme($scheme)
	{
		$this->_scheme = $scheme;
	}

	function get_user()
	{
		return $this->_user;
	}

	function set_user($user)
	{
		$this->_user = $user;
	}

	function get_pass()
	{
		return $this->_pass;
	}

	function set_pass($pass)
	{
		$this->_pass = $pass;
	}

	function get_host()
	{
		return $this->_host;
	}

	function set_host($host)
	{
		$this->_host = $host;
	}

	function get_port()
	{
		return isset($this->_port) ? $this->_port : null;
	}

	function set_port($port)
	{
		$this->_port = $port;
	}

	function get_path()
	{
		return $this->_path;
	}

	function set_path($path)
	{
		$this->_path = $this->_clean_path($path);
	}

	function get_fragment()
	{
		return $this->_fragment;
	}

	function set_fragment($anchor)
	{
		$this->_fragment = $anchor;
	}

	function is_ssl()
	{
		return $this->get_scheme() == 'https' ? true : false;
	}

	function is_internal($url)
	{
		$uri  = & uri::get_instance($url);
		$base = $uri->toString(array('scheme', 'host', 'port', 'path'));
		$host = $uri->toString(array('scheme', 'host', 'port'));
		if(stripos($base, uri::base()) !== 0 && !empty($host))
		{
			return false;
		}
		return true;
	}
	
	
	function _clean_path($path)
	{
		$path = preg_replace('#(/+)#', '/', $path);
		if(strpos($path, '.') === false) return $path; 
		$path = explode('/', $path);
		for ($i = 0; $i < count($path); $i++)
		{
			if ($path[$i] == '.')
			{
				unset($path[$i]);
				$path = array_values($path);
				$i--;
			}
			elseif ($path[$i] == '..')
			{
				if ($i == 1 AND $path[0] == '')
				{
					unset ($path[$i]);
					$path = array_values($path);
					$i--;
				}
				elseif ($i > 1 OR ($i == 1 AND $path[0] != ''))
				{
					unset($path[$i]);
					unset($path[$i-1]);
					$path = array_values($path);
					$i -= 2;
				}
			}
			else
			{
				continue;
			}
		}
		return implode('/', $path);
	}
}
