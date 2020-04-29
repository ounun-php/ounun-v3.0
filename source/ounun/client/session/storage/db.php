<?php
namespace ounun\client\session\storage;


use ounun\db\pdo;
use ounun\http\session\storage;

/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

class db extends storage
{
	/** @var pdo */
	protected $db;

	/**
	 * db constructor.
	 * @param array $options
	 */
    public function __construct($options = [])
	{
		parent::__construct($options);
		$this->register();
	}

	public function open($save_path, $session_name)
	{
        $this->_connect();
		return true;
	}

	public function close()
	{
		return $this->gc(ini_get("session.gc_maxlifetime"));
	}

	public function read($id)
	{
		$sdb = $this->db->prepare("SELECT `data` FROM $this->table WHERE `sessionid`=?");
		if($sdb->execute(array($id)))
		{
			$r = $sdb->fetch(\PDO::FETCH_ASSOC);
			return $r['data'];
		}
		return false;
	}

	public function write($id, $data)
	{
		$sdb = $this->db->prepare("REPLACE INTO $this->table (`sessionid`, `lastvisit`, `data`) VALUES(?, ?, ?)");
		return $sdb->execute(array($id, TIME, $data));
	}

	public function destroy($id)
	{
		$sdb = $this->db->prepare("DELETE FROM $this->table WHERE `sessionid`=?");
		return $sdb->execute(array($id));
	}

	public function gc($maxlifetime)
	{
		$expiretime = TIME - $maxlifetime;
		$sdb = $this->db->prepare("DELETE FROM $this->table WHERE `lastvisit`<?");
		return $sdb->execute(array($expiretime));
	}

	public function _connect()
	{
		if ($this->options['db_issame']) {
			$this->db = & factory::db();
			$table = $this->db->options['prefix'].'session';
		}
		else {
			$this->db = & factory::db('db_session');
			$table = $this->db->options['table'];
		}
		$dbname = $this->db->options['dbname'];
		$this->table = "`$dbname`.`$table`";
	}
}
