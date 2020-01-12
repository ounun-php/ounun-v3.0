<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\client;

use ounun\debug;

if( !defined( "IPPROTO_IP" ) ) {
    define( "IPPROTO_IP", 0 );
    define( "IP_MULTICAST_LOOP", 34 );
    define( "IP_MULTICAST_TTL", 33 );
}

class squid
{
	public $server_url;
	public $servers;
    public $htcp_multicast_address;
    public $htcp_port;
    public $htcp_multicast_ttl;

    /** @var debug */
    private $_debug;

    public function __construct($server_url, $servers, $htcp_multicast_address, $htcp_port, $HTCPMulticastTTL, string $debug_filename = '')
	{
		$this->server_url = $server_url;
		$this->servers = $servers;
		$this->htcp_multicast_address = $htcp_multicast_address;
		$this->htcp_port = $htcp_port;
		$this->htcp_multicast_ttl = $HTCPMulticastTTL;

		if($debug_filename){
            $this->_debug = new debug($debug_filename);
        }
	}

    /**
     * @param array $urls
     * @return mixed;
     * @throws
     */
    public function purge($urls = [])
	{
		if(empty($urls)) {
            return null;
        }
		if ($this->htcp_multicast_address && $this->htcp_port) {
			$this->purge_htcp($urls);
            return null;
		}
        $this->debug('DEBUG:'.__LINE__,'$this->purge', 'DEBUG');

		$max_socket_sper_squid = 8;
		$url_sper_socket = 400;
		$first_url = $this->expand($urls[0]);
		unset($urls[0]);
		$urls = array_values($urls);
		$socks_persq =  max(ceil(count($urls) / $url_sper_socket ),1);
		if($socks_persq == 1) {
			$url_sper_socket = count($urls);
		}
		elseif($socks_persq > $max_socket_sper_squid) {
			$url_sper_socket = ceil(count($urls) / $max_socket_sper_squid);
			$socks_persq = $max_socket_sper_squid;
		}
		$total_sockets = count($this->servers) * $socks_persq;
		$sockets = [];
		for ($ss=0; $ss < count($this->servers); $ss++) {
			$failed = false;
			$so = 0;
			while ($so < $socks_persq && !$failed) {
				if ($so == 0) {
					@list($server, $port) = explode(':', $this->servers[$ss]);
					if(!isset($port)) {
					    $port = 80;
                    }

					$error_code  = 0;
                    $error_msg   = false;
					$socket      = @fsockopen($server, $port, $error_code, $error_msg, 3);
					if (!$socket) {
						$failed = true;
						$total_sockets -= $socks_persq;
					}
					else {
						$msg = 'PURGE '.$first_url." HTTP/1.0\r\n"."Connection: Keep-Alive\r\n\r\n";
						@fputs($socket,$msg);
						$res = @fread($socket,512);
						if (strlen($res) > 250) {
							fclose($socket);
							$failed = true;
							$total_sockets -= $socks_persq;
						}
						else {
							@stream_set_blocking($socket,false);
							$sockets[] = $socket;
						}
					}
				}
				else {
					list($server, $port) = explode(':', $this->servers[$ss]);
					if(!isset($port)) $port = 80;
					$socket = @fsockopen($server, $port, $error_code, $error_msg, 2);
					@stream_set_blocking($socket,false);
					$sockets[] = $socket;
				}
				$so++;
			}
		}

		if ($url_sper_socket > 0) {
			for ($r = 0; $r < $url_sper_socket; $r++) {
				for ($s=0;$s < $total_sockets;$s++) {
					if($r != 0) {
						$res = '';
						$esc = 0;
						while (strlen($res) < 100 && $esc < 200  ) {
							$res .= @fread($sockets[$s],512);
							$esc++;
							usleep(20);
						}
					}
					$urindex = $r + $url_sper_socket * ($s - $socks_persq * floor($s / $socks_persq));
					$url = $this->expand($urls[$urindex]);
					$msg = 'PURGE '.$url." HTTP/1.0\r\n"."Connection: Keep-Alive\r\n\r\n";
					@fputs($sockets[$s],$msg);
				}
			}
		}
		foreach ($sockets as $socket) {
			$res = '';
			$esc = 0;
			while (strlen($res) < 100 && $esc < 200  ) {
				$res .= @fread($socket,1024);
				$esc++;
				usleep(20);
			}
			@fclose($socket);
		}
        $this->debug('DEBUG:'.__LINE__,'$this->purge', false);
		return null;
	}

    /**
     * @param array $urls
     * @throws
     */
	function purge_htcp($urls = [])
	{
        $this->debug('DEBUG:'.__LINE__, '$this->HTCPPurge', false);
		$htcpOpCLR = 4;

	    $conn = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if ($conn) {
			socket_set_option( $conn, IPPROTO_IP, IP_MULTICAST_LOOP, 0 );
			if ($this->htcp_multicast_ttl != 1) {
                socket_set_option($conn, IPPROTO_IP, IP_MULTICAST_TTL, $this->htcp_multicast_ttl);
            }

			foreach ($urls as $url ) {
				if( !is_string( $url ) ) {
					throw new \Exception('Bad purge URL');
				}
				$url = $this->expand($url);
				$htcpTransID = rand();
				$htcpSpecifier = pack('na4na*na8n', 4, 'HEAD', strlen($url), $url, 8, 'HTTP/1.0', 0);
				$htcpDataLen = 8 + 2 + strlen( $htcpSpecifier );
				$htcpLen = 4 + $htcpDataLen + 2;
				$htcpPacket = pack('nxxnCxNxxa*n',$htcpLen, $htcpDataLen, $htcpOpCLR,$htcpTransID, $htcpSpecifier, 2);
                $this->debug('DEBUG:'.__LINE__,"Purging URL $url via HTCP\n", false);
				socket_sendto($conn, $htcpPacket, $htcpLen, 0, $this->htcp_multicast_address, $this->htcp_port);
			}
		}
		else {
			$errstr = socket_strerror(socket_last_error());
            $this->debug('DEBUG:'.__LINE__,$this->purge_htcp().": Error opening UDP socket: $errstr\n", false);
		}
		$this->debug('DEBUG:'.__LINE__,'$this->HTCPPurge',  false);
	}

    /**
     * @param $url
     * @return string
     */
	public function expand($url)
	{
		if($url != '' && $url{0} == '/') {
			return $this->server_url.$url;
		}
		return $url;
	}

    /**
     * 调试日志
     * @param string $k
     * @param mixed $log 日志内容
     * @param bool $is_replace 是否替换
     */
	protected function debug(string $k, $log, $is_replace = true)
    {
        if($this->_debug){
            $this->_debug->logs($k, $log, $is_replace);
        }
    }
}
