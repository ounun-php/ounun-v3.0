<?php
namespace ounun\client;

class ftp
{
	private $ftp_stream;
	
	function connect($host, $username, $password, $path, $pasv = true, $port = 21, $timeout = 90, $ssl = false)
	{
		@set_time_limit(0);
		$port = intval($port);
		$timeout = intval($timeout);
		$func = $ssl ? 'ftp_ssl_connect' : 'ftp_connect';
		if($this->ftp_stream = @$func($host, $port, $timeout)) 
		{
			if($this->login($username, $password))
			{
				if($pasv) $this->pasv(true);
				if($this->chdir($path)) 
				{
					return $this->ftp_stream;
				} 
				else 
				{
					$this->errno = 3;
					$this->error = "Chdir '$path' error.";
					$this->close($this->ftp_stream);
					return false;
				}
			} 
			else 
			{
				$this->errno = 2;
				$this->error = '530 Not logged in.';
				$this->close($this->ftp_stream);
				return false;
			}
		} 
		else 
		{
			$this->errno = 1;
			$this->error = "Couldn't connect to $host:$port.";
			return false;
		}
	}

	function login($username, $password)
	{
		return @ftp_login($this->ftp_stream, $username, $password);
	}
	
	function pasv($pasv = true)
	{
		return @ftp_pasv($this->ftp_stream, $pasv);
	}

	function set_option($option, $value)
	{
		return @ftp_set_option($this->ftp_stream, $option, $value);
	}

	function pwd()
	{
		return @ftp_pwd($this->ftp_stream);
	}

	function cdup()
	{
		return @ftp_cdup($this->ftp_stream);
	}
	
	function chdir($directory)
	{
		return @ftp_chdir($this->ftp_stream, $directory);
	}
	
	function mkdir($directory)
	{
		return @ftp_mkdir($this->ftp_stream, $directory);
	}
	
	function rmdir()
	{
		return @ftp_rmdir($this->ftp_stream, $directory);
	}

	function get($local_file, $remote_file, $mode, $resumepos = 0)
	{
		$mode = intval($mode);
		$resumepos = intval($resumepos);
		return @ftp_get($this->ftp_stream, $local_file, $remote_file, $mode, $resumepos);
	}
	
	function put($remote_file, $local_file, $mode, $startpos = 0)
	{
		$mode = intval($mode);
		$startpos = intval($startpos);
		return @ftp_put($this->ftp_stream, $remote_file, $local_file, $mode, $startpos);
	}
	
	function size($remote_file)
	{
		return @ftp_size($this->ftp_stream, $remote_file);
	}
	
	function mdtm($remote_file)
	{
		return @ftp_mdtm($this->ftp_stream, $remote_file);
	}
	
	function chmod($mode, $filename)
	{
		return @ftp_chmod($this->ftp_stream, $mode, $filename);
	}
	
	function rename($oldname, $newname)
	{
		return @ftp_rename($this->ftp_stream, $oldname, $newname);
	}
	
	function delete($path)
	{
		return @ftp_delete($this->ftp_stream, $path);
	}

	function raw($command)
	{
		return @ftp_raw($this->ftp_stream, $command);
	}

	function rawlist($directory)
	{
		return @ftp_rawlist($this->ftp_stream, $directory);
	}

	function nlist($directory)
	{
		return @ftp_nlist($this->ftp_stream, $directory);
	}

	function nb_continue()
	{
		return @ftp_nb_continue($this->ftp_stream);
	}

	function nb_fget($handle, $remote_file, $mode = FTP_BINARY, $resumepos = 0)
	{
		return @ftp_nb_fget($this->ftp_stream, $handle, $remote_file , $mode, $resumepos);
	}

	function nb_fput($remote_file, $handle, $mode = FTP_BINARY, $resumepos = 0)
	{
		return @ftp_nb_fput($this->ftp_stream, $remote_file, $handle, $mode, $resumepos);
	}

	function nb_get($local_file, $remote_file, $mode = FTP_BINARY, $resumepos = 0)
	{
		return @ftp_nb_get($this->ftp_stream, $local_file, $remote_file, $mode, $resumepos);
	}

	function nb_put($remote_file, $local_file, $mode = FTP_BINARY, $resumepos = 0)
	{
		return @ftp_nb_put($this->ftp_stream, $remote_file, $local_file, $mode, $resumepos);
	}
	
	function exec($command)
	{
		return @ftp_exec($this->ftp_stream, $command);
	}
	
	function site($cmd)
	{
		return @ftp_site($this->ftp_stream, $cmd);
	}
	
	function close()
	{
		return @ftp_close($this->ftp_stream);
	}
}