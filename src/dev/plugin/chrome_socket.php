<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\plugin;

use ounun\utils\caiji;

/**
 * 谷歌浏览器
 */
class chrome_socket
{
    protected $filename;
    protected $timeout = 30;
    protected $host;
    protected $port;
    protected $address;

    /** @var Client */
    protected $socket;

    public $tab;

    static protected $passType = ['stylesheet', 'image', 'media', 'font'];

    /**
     * chrome_socket constructor.
     * @param $host
     * @param $port
     * @param int $timeout
     * @param string $filename
     */
    public function __construct($host, $port, $timeout = 30, $filename = '')
    {

        $port          = intval($port);
        $this->port    = empty($port) ? 9222 : $port;
        $this->host    = empty($host) ? '127.0.0.1' : $host;
        $this->address = $this->host . ($this->port ? (':' . $this->port) : '');

        $timeout        = intval($timeout);
        $this->timeout  = $timeout <= 0 ? 30 : $timeout;
        $this->filename = $filename ? $filename : 'chrome';
    }

    /**
     *
     */
    public function __destruct()
    {
        if (!empty($this->tab)) {
            $this->closeTab($this->tab['id']);
        }
    }

    /**
     * 检查服务器是否开启
     * @return bool
     */
    public function host_is_open()
    {
        $data = caiji::html_get($this->address . '/json/version', null, array('timeout' => 5));
        $data = json_decode($data, true);
        if (!empty($data) && !empty($data['webSocketDebuggerUrl'])) {
            return true;
        }
        return false;
    }

    /**
     * 开启谷歌服务器
     * @throws
     */
    public function openHost()
    {
        if (!in_array(strtolower($this->host), array('localhost', '127.0.0.1', '0.0.0.0'))) {
            return;
        }
        $command = $this->filename;
        if (empty($command)) {
            $command = 'chrome';
        } else {
            if (Is_Win) {
                if (file_exists($command)) {
                    $command = '"' . $command . '"';
                }
            }
        }
        $commandStr = sprintf('%s --headless --remote-debugging-port=%s', $command, $this->port);
        if (!function_exists('proc_open')) {
            throw new \Exception('请开启proc_open函数或者手动执行命令：' . $commandStr);
        }
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );
        $pipes          = array();
        $handle         = proc_open($commandStr, $descriptorspec, $pipes);
        $hdStatus       = proc_get_status($handle);
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
    }

    /**
     * 握手浏览器
     * @param string $url
     * @param array $headers
     * @param array $options
     */
    public function websocket($url = '', $headers = array(), $options = array())
    {
        $headers            = is_array($headers) ? $headers : array();
        $headers            = array_change_key_case($headers, CASE_LOWER);
        $options            = is_array($options) ? $options : array();
        $options['timeout'] = $options['timeout'] > 0 ? $options['timeout'] : $this->timeout;
        if (!empty($headers)) {
            $options['headers'] = is_array($options['headers']) ? $options['headers'] : array();
            $options['headers'] = array_merge($options['headers'], $headers);
        }
        if (empty($url)) {
            $url = $this->tab['webSocketDebuggerUrl'];
        }
        $this->socket = new Client($url, $options);
    }

    /**
     * 发送数据
     * @param $method
     * @param array $params
     * @param int $id
     * @return array
     * @throws
     */
    public function send($method, $params = array(), $id = 0)
    {
        if (empty($id)) {
            static $no = 1;
            $no++;
            $id = $no;
        }
        $data = array(
            'id'     => $id,
            'method' => $method,
            'params' => $params
        );
        $this->socket->send(json_encode($data));
        return $data;
    }

    /**
     * 获取渲染的页面
     * @param $url
     * @param array $headers
     * @param array $options
     * @param null $fromEncode
     * @param null $postData
     * @return array|mixed|string|null
     */
    public function getRenderHtml($url, $headers = array(), $options = array(), $fromEncode = null, $postData = null)
    {
        if (!preg_match('/^\w+\:\/\//', $url)) {

            $url = 'http://' . $url;
        }
        $this->send('Network.enable');
        if (!empty($headers)) {
            foreach ($headers as $k => $v) {
                if (strcasecmp($k, 'cookie') == 0) {
                    $this->send('Network.clearBrowserCookies');
                    break;
                }
            }
            $this->send('Network.setExtraHTTPHeaders', array('headers' => $headers));
        }

        $this->send('Network.setRequestInterception', array('patterns' => array(
            array('urlPattern' => '*', 'interceptionStage' => 'Request')
        )));

        if (!empty($options['proxy'])) {

        }
        $this->send('Page.enable');
        if (isset($postData)) {

            if (!is_array($postData)) {

                if (preg_match_all('/([^\&]+?)\=([^\&]*)/', $postData, $m_post_data)) {
                    $new_post_data = array();
                    foreach ($m_post_data[1] as $k => $v) {
                        $new_post_data[$v] = rawurldecode($m_post_data[2][$k]);
                    }
                    $postData = $new_post_data;
                } else {
                    $postData = array();
                }
            }

            $formHtml = '';
            foreach ($postData as $k => $v) {
                $formHtml .= '<input type="text" name="' . $k . '" value="' . addslashes($v) . '">';
            }

            $postForm = 'var postForm=document.createElement("form");';
            if (!empty($postData) && !empty($fromEncode) && !in_array(strtolower($fromEncode), array('auto', 'utf-8', 'utf8'))) {

                $postForm .= 'postForm.acceptCharset="' . $fromEncode . '";';
            }
            $postForm .= 'postForm.method="post";'
                . 'postForm.action="' . $url . '";'
                . 'postForm.innerHTML=\'' . $formHtml . '\';'
                . 'document.documentElement.appendChild(postForm);'
                . 'postForm.submit();';

            $sendData = $this->send('Runtime.evaluate', array('expression' => $postForm));
        } else {

            $sendData = $this->send('Page.navigate', array('url' => $url));
        }


        $complete  = false;
        $startTime = time();
        while ((time() - $startTime) <= $this->timeout) {

            $data = $this->receive();
            if (!$data) {

                break;
            }
            if ($data['method'] == 'Page.loadEventFired') {

                $complete = true;
                break;
            } elseif ($data['method'] == 'Network.requestIntercepted') {

                $ncParams = array('interceptionId' => $data['params']['interceptionId']);
                if (in_array(strtolower($data['params']['resourceType']), self::$passType)) {

                    $ncParams['errorReason'] = 'Aborted';
                }
                $this->send('Network.continueInterceptedRequest', $ncParams);
            }
        }
        if ($complete) {

            $sendData = $this->send('Runtime.evaluate', array('expression' => 'document.documentElement.outerHTML'));
            $data     = $this->receiveById($sendData['id'], false);
            $data     = $data['result']['result']['value'];
            if (preg_match('/^\{(.+\:.+,*){1,}\}$/', strip_tags($data))) {

                $data = strip_tags($data);
            }
            return $data;
        }
        return null;
    }

    /**
     * 接收数据帧
     * @return mixed|null
     */
    public function receive()
    {
        try {
            $data = $this->socket->receive();
        } catch (\Exception $ex) {
            $data = null;
        }
        return $data ? json_decode($data, true) : null;
    }

    /**
     * 接收id相应的数据
     * @param $id
     * @param bool $returnAll
     * @return array|mixed|null
     */
    public function receiveById($id, $returnAll = false)
    {
        $startTime = time();
        $complete  = false;
        $result    = null;
        $all       = array();
        while ((time() - $startTime) <= $this->timeout) {

            $data = $this->receive();
            if (!$data) {

                break;
            }
            if ($data['id'] == $id) {
                $result = $data;
                break;
            }
            if ($data['method'] == 'Network.requestIntercepted') {

                $ncParams = array('interceptionId' => $data['params']['interceptionId']);
                if (in_array(strtolower($data['params']['resourceType']), self::$passType)) {

                    $ncParams['errorReason'] = 'Aborted';
                }
                $this->send('Network.continueInterceptedRequest', $ncParams);
            }
            if ($returnAll) {
                $all[] = $data;
            }
        }
        if ($returnAll) {
            return array('all' => $all, 'result' => $result);
        } else {
            return $result;
        }
    }

    /**
     * 获取所有标签页
     * @return array|bool|mixed|string|null
     */
    public function getTabs()
    {
        $data = caiji::html_get($this->address . '/json');
        $data = empty($data) ? array() : json_decode($data, true);
        return $data;
    }

    /**
     * 新建空白标签页
     * @return array|bool|mixed|string|null
     */
    public function newTab()
    {
        $data      = caiji::html_get($this->address . '/json/new');
        $data      = empty($data) ? array() : json_decode($data, true);
        $this->tab = $data;
        return $data;
    }

    /**
     * 关闭标签页
     * @param $id
     */
    public function closeTab($id)
    {
        caiji::html_get($this->address . '/json/close/' . $id, null, array('timeout' => 1));
    }
}
