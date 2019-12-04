<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\client;

class ldap
{
	var $host = null;
	var $auth_method = null;
	var $port = null;
	var $base_dn = null;
	var $users_dn = null;
	var $search_string = null;
	var $use_ldapV3 = null;
	var $no_referrals = null;
	var $negotiate_tls = null;
	var $username = null;
	var $password = null;
	var $_resource = null;
	var $_dn = null;

	function __construct($configObj = null)
	{
		if (is_object($configObj))
		{
			$vars = get_class_vars(get_class($this));
			foreach (array_keys($vars) as $var)
			{
				if (substr($var, 0, 1) != '_')
				{
					if ($param = $configObj->get($var))
					{
						$this-> $var = $param;
					}
				}
			}
		}
	}

	function connect()
	{
		if ($this->host == '') return false;
		$this->_resource = ldap_connect($this->host, $this->port);
		if ($this->_resource)
		{
			if ($this->use_ldapV3)
			{
				if (!@ldap_set_option($this->_resource, LDAP_OPT_PROTOCOL_VERSION, 3))
				{
					return false;
				}
			}
			if (!@ldap_set_option($this->_resource, LDAP_OPT_REFERRALS, intval($this->no_referrals)))
			{
				return false;
			}
			if ($this->negotiate_tls)
			{
				if (!@ldap_start_tls($this->_resource))
				{
					return false;
				}
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	function close()
	{
		 ldap_close($this->_resource);
	}

	function setDN($username,$nosub = 0)
	{
		if ($this->users_dn == '' || $nosub)
		{
			$this->_dn = $username;
		}
		else if(strlen($username))
		{
			$this->_dn = str_replace('[username]', $username, $this->users_dn);
		}
		else
		{
			$this->_dn = '';
		}
	}

	function getDN()
	{
		return $this->_dn;
	}

	function anonymous_bind()
	{
		$bindResult = ldap_bind($this->_resource);
		return $bindResult;
	}

	function bind($username = null, $password = null, $nosub = 0)
	{
		if (is_null($username))
		{
			$username = $this->username;
		}
		if (is_null($password))
		{
			$password = $this->password;
		}
		$this->setDN($username,$nosub);
		$bindResult = @ldap_bind($this->_resource, $this->getDN(), $password);
		return $bindResult;
	}

	function simple_search($search)
	{
		$results = explode(';', $search);
		foreach($results as $key=>$result)
		{
			$results[$key] = '('.$result.')';
		}
		return $this->search($results);
	}

	function search($filters, $dnoverride = null)
	{
		$attributes = [];
		if ($dnoverride)
		{
			$dn = $dnoverride;
		}
		else
		{
			$dn = $this->base_dn;
		}

		$resource = $this->_resource;

		foreach ($filters as $search_filter)
		{
			$search_result = @ldap_search($resource, $dn, $search_filter);
			if ($search_result && ($count = @ldap_count_entries($resource, $search_result)) > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$attributes[$i] = Array ();
					if (!$i)
					{
						$firstentry = @ldap_first_entry($resource, $search_result);
					}
					else
					{
						$firstentry = @ldap_next_entry($resource, $firstentry);
					}
					$attributes_array = @ldap_get_attributes($resource, $firstentry);
					foreach ($attributes_array as $ki => $ai)
					{
						if (is_array($ai))
						{
							$subcount = $ai['count'];
							$attributes[$i][$ki] = Array ();
							for ($k = 0; $k < $subcount; $k++)
							{
								$attributes[$i][$ki][$k] = $ai[$k];
							}
						}
					}
					$attributes[$i]['dn'] = @ldap_get_dn($resource, $firstentry);
				}
			}
		}
		return $attributes;
	}

	function replace($dn, $attribute)
	{
		return @ldap_mod_replace($this->_resource, $dn, $attribute);
	}

	function modify($dn, $attribute)
	{
		return @ldap_modify($this->_resource, $dn, $attribute);
	}

	function remove($dn, $attribute)
	{
		$resource = $this->_resource;
		return @ldap_mod_del($resource, $dn, $attribute);
	}

	function compare($dn, $attribute, $value)
	{
		return @ldap_compare($this->_resource, $dn, $attribute, $value);
	}

	function read($dn, $attribute = [])
	{
		$base = substr($dn,strpos($dn,',')+1);
		$cn = substr($dn,0,strpos($dn,','));
		$result = @ldap_read($this->_resource, $base, $cn);

		if ($result)
		{
			return @ldap_get_entries($this->_resource, $result);
		}
		else
		{
			return $result;
		}
	}

	function delete($dn)
	{
		return @ldap_delete($this->_resource, $dn);
	}

	function create($dn, $entries)
	{
		return @ldap_add($this->_resource, $dn, $entries);
	}

	function add($dn, $entry)
	{
		return @ldap_mod_add($this->_resource, $dn, $entry);
	}

	function rename($dn, $newdn, $newparent, $deleteolddn)
	{
		return @ldap_rename($this->_resource, $dn, $newdn, $newparent, $deleteolddn);
	}

	function getErrorMsg()
	{
		return @ldap_error($this->_resource);
	}

	function ipToNetAddress($ip)
	{
		$parts = explode('.', $ip);
		$address = '1#';

		foreach ($parts as $int)
		{
			$tmp = dechex($int);
			if (strlen($tmp) != 2)
			{
				$tmp = '0' . $tmp;
			}
			$address .= '\\' . $tmp;
		}
		return $address;
	}

	function LDAPNetAddr($networkaddress)
	{
		$addr = "";
		$addrtype = intval(substr($networkaddress, 0, 1));
		$networkaddress = substr($networkaddress, 2);
		if (($addrtype == 8) || ($addrtype = 9))
		{
			$networkaddress = substr($networkaddress, (strlen($networkaddress)-4));
		}
		$addrtypes = array (
			'IPX',
			'IP',
			'SDLC',
			'Token Ring',
			'OSI',
			'AppleTalk',
			'NetBEUI',
			'Socket',
			'UDP',
			'TCP',
			'UDP6',
			'TCP6',
			'Reserved (12)',
			'URL',
			'Count'
		);
		$len = strlen($networkaddress);
		if ($len > 0)
		{
			for ($i = 0; $i < $len; $i += 1)
			{
				$byte = substr($networkaddress, $i, 1);
				$addr .= ord($byte);
				if ( ($addrtype == 1) || ($addrtype == 8) || ($addrtype = 9) )
				{
					$addr .= ".";
				}
			}
			if ( ($addrtype == 1) || ($addrtype == 8) || ($addrtype = 9) )
			{
				$addr = substr($addr, 0, strlen($addr) - 1);
			}
		}
		else
		{
			$addr .= "address not available.";
		}
		return Array('protocol'=>$addrtypes[$addrtype], 'address'=>$addr);
	}

	function generatePassword($password, $type='md5')
	{
		$userpassword = '';
		switch(strtolower($type))
		{
			case 'sha':
				$userpassword = '{SHA}' . base64_encode( pack( 'H*', sha1( $password ) ) );
			case 'md5':
			default:
				$userpassword = '{MD5}' . base64_encode( pack( 'H*', md5( $password ) ) );
				break;
		}
		return $userpassword;
	}
}
