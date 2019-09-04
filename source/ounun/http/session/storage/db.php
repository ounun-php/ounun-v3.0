<?php 

class session_storage_db extends session_storage
{
	private $db;

	function __construct($options = array())
	{
		parent::__construct($options);
		$this->register();
	}

	function open($save_path, $session_name)
	{
        $this->_connect();
		return true;
	}

	function close()
	{
		return $this->gc(ini_get("session.gc_maxlifetime"));
	}

	function read($id)
	{
		$sdb = $this->db->prepare("SELECT `data` FROM $this->table WHERE `sessionid`=?");
		if($sdb->execute(array($id)))
		{
			$r = $sdb->fetch(PDO::FETCH_ASSOC);
			return $r['data'];
		}
		return false;
	}

	function write($id, $data)
	{
		$sdb = $this->db->prepare("REPLACE INTO $this->table (`sessionid`, `lastvisit`, `data`) VALUES(?, ?, ?)");
		return $sdb->execute(array($id, TIME, $data));
	}

	function destroy($id)
	{
		$sdb = $this->db->prepare("DELETE FROM $this->table WHERE `sessionid`=?");
		return $sdb->execute(array($id));
	}

	function gc($maxlifetime)
	{
		$expiretime = TIME - $maxlifetime;
		$sdb = $this->db->prepare("DELETE FROM $this->table WHERE `lastvisit`<?");
		return $sdb->execute(array($expiretime));
	}
	
	function _connect()
	{
		if ($this->options['db_issame'])
		{
			$this->db = & factory::db();
			$table = $this->db->options['prefix'].'session';
		}
		else 
		{
			$this->db = & factory::db('db_session');
			$table = $this->db->options['table'];
		}
		$dbname = $this->db->options['dbname'];
		$this->table = "`$dbname`.`$table`";
	}
}
?>