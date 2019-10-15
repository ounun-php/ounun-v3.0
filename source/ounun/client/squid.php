<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\client;

class squid
{
	private $serverUrl,
	        $servers,
	        $HTCPMulticastAddress, 
	        $HTCPPort,
	        $HTCPMulticastTTL;
	        
	function __construct($serverUrl, $servers, $HTCPMulticastAddress, $HTCPPort, $HTCPMulticastTTL)
	{
		$this->serverUrl = $serverUrl;
		$this->servers = $servers;
		$this->HTCPMulticastAddress = $HTCPMulticastAddress;
		$this->HTCPPort = $HTCPPort;
		$this->HTCPMulticastTTL = $HTCPMulticastTTL;
	}
	        
	function purge($urlArr)
	{
		if(empty($urlArr)) {
            return;
        }
		if ($this->HTCPMulticastAddress && $this->HTCPPort) {
			return $this->HTCPPurge($urlArr);
		}
		$fname = '$this->purge';
		log::add($fname, 'DEBUG');

		$maxsocketspersquid = 8;
		$urlspersocket = 400;
		$firsturl = $this->expand($urlArr[0]);
		unset($urlArr[0]);
		$urlArr = array_values($urlArr);
		$sockspersq =  max(ceil(count($urlArr) / $urlspersocket ),1);
		if($sockspersq == 1) 
		{
			$urlspersocket = count($urlArr);
		} 
		elseif($sockspersq > $maxsocketspersquid) 
		{
			$urlspersocket = ceil(count($urlArr) / $maxsocketspersquid);
			$sockspersq = $maxsocketspersquid;
		}
		$totalsockets = count($this->servers) * $sockspersq;
		$sockets = array();
		for ($ss=0; $ss < count($this->servers); $ss++) 
		{
			$failed = false;
			$so = 0;
			while ($so < $sockspersq && !$failed) 
			{
				if ($so == 0) 
				{
					@list($server, $port) = explode(':', $this->servers[$ss]);
					if(!isset($port)) $port = 80;
					$error = $errstr = false;
					$socket = @fsockopen($server, $port, $error, $errstr, 3);
					if (!$socket) 
					{
						$failed = true;
						$totalsockets -= $sockspersq;
					} 
					else 
					{
						$msg = 'PURGE '.$firsturl." HTTP/1.0\r\n"."Connection: Keep-Alive\r\n\r\n";
						@fputs($socket,$msg);
						$res = @fread($socket,512);
						if (strlen($res) > 250) 
						{
							fclose($socket);
							$failed = true;
							$totalsockets -= $sockspersq;
						} 
						else 
						{
							@stream_set_blocking($socket,false);
							$sockets[] = $socket;
						}
					}
				} 
				else 
				{
					list($server, $port) = explode(':', $this->servers[$ss]);
					if(!isset($port)) $port = 80;
					$socket = @fsockopen($server, $port, $error, $errstr, 2);
					@stream_set_blocking($socket,false);
					$sockets[] = $socket;
				}
				$so++;
			}
		}

		if ($urlspersocket > 0) 
		{
			for ($r = 0; $r < $urlspersocket; $r++) 
			{
				for ($s=0;$s < $totalsockets;$s++) 
				{
					if($r != 0) 
					{
						$res = '';
						$esc = 0;
						while (strlen($res) < 100 && $esc < 200  ) 
						{
							$res .= @fread($sockets[$s],512);
							$esc++;
							usleep(20);
						}
					}
					$urindex = $r + $urlspersocket * ($s - $sockspersq * floor($s / $sockspersq));
					$url = $this->expand($urlArr[$urindex]);
					$msg = 'PURGE '.$url." HTTP/1.0\r\n"."Connection: Keep-Alive\r\n\r\n";
					@fputs($sockets[$s],$msg);
				}
			}
		}
		foreach ($sockets as $socket) 
		{
			$res = '';
			$esc = 0;
			while (strlen($res) < 100 && $esc < 200  ) 
			{
				$res .= @fread($socket,1024);
				$esc++;
				usleep(20);
			}
			@fclose($socket);
		}
		log::add($fname, 'DEBUG');
	}

	function HTCPPurge($urlArr) 
	{
		$fname = '$this->HTCPPurge';
		log::add($fname, 'DEBUG');
		$htcpOpCLR = 4;
		if( !defined( "IPPROTO_IP" ) ) 
		{
			define( "IPPROTO_IP", 0 );
			define( "IP_MULTICAST_LOOP", 34 );
			define( "IP_MULTICAST_TTL", 33 );
		}
	    $conn = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if ($conn) 
		{
			socket_set_option( $conn, IPPROTO_IP, IP_MULTICAST_LOOP, 0 );
			if ($this->HTCPMulticastTTL != 1) {
                socket_set_option($conn, IPPROTO_IP, IP_MULTICAST_TTL, $this->HTCPMulticastTTL);
            }

			foreach ($urlArr as $url ) 
			{
				if( !is_string( $url ) ) 
				{
					throw new ct_exception('Bad purge URL');
				}
				$url = $this->expand($url);
				$htcpTransID = rand();
				$htcpSpecifier = pack('na4na*na8n', 4, 'HEAD', strlen($url), $url, 8, 'HTTP/1.0', 0);
				$htcpDataLen = 8 + 2 + strlen( $htcpSpecifier );
				$htcpLen = 4 + $htcpDataLen + 2;
				$htcpPacket = pack('nxxnCxNxxa*n',$htcpLen, $htcpDataLen, $htcpOpCLR,$htcpTransID, $htcpSpecifier, 2);
				log::add("Purging URL $url via HTCP\n", 'DEBUG');
				socket_sendto($conn, $htcpPacket, $htcpLen, 0, $this->HTCPMulticastAddress, $this->HTCPPort);
			}
		} 
		else
		{
			$errstr = socket_strerror(socket_last_error());
			log::add("$this->HTCPPurge(): Error opening UDP socket: $errstr\n", 'DEBUG');
		}
		log::add($fname, 'DEBUG');
	}
	
	function expand($url)
	{
		if($url != '' && $url{0} == '/') 
		{
			return $this->serverUrl.$url;
		}
		return $url;
	}
}