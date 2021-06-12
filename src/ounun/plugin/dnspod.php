<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\plugin;

/*
 * DNSPod API PHP Web 示例
 * http://www.likexian.com/
 *
 * Copyright 2011-2014, Kexian Li
 * Released under the Apache License, Version 2.0
 *
 */



class dnspod
{
    const Grade_List = [
        'D_Free' => '免费套餐',
        'D_Plus' => '豪华 VIP套餐',
        'D_Extra' => '企业I VIP套餐',
        'D_Expert' => '企业II VIP套餐',
        'D_Ultra' => '企业III VIP套餐',
        'DP_Free' => '新免费套餐',
        'DP_Plus' => '个人专业版',
        'DP_Extra' => '企业创业版',
        'DP_Expert' => '企业标准版',
        'DP_Ultra' => '企业旗舰版',
    ];

    const Status_List = [
        'enable' => '启用',
        'pause'  => '暂停',
        'spam'   => '封禁',
        'lock'   => '锁定',
    ];

    /** @var string API根据路径 */
    protected string $_api_base = 'https://dnsapi.cn/';

    /** @var string 登录TOKEN */
    protected string $_login_token = '';
    /** @var int  登录TOKEN ID */
    protected int $_login_token_id = 0;

    /** @var string */
    protected string $_lang = 'cn';
    /** @var string */
    protected string $_error_on_empty = 'no';
    /** @var string */
    protected string $_format = 'json';

    /**
     * 构造方法，传入默认Token
     *
     * @param string $token
     * @param int $token_id
     * @param string $format
     * @param string $lang
     * @param string $error_on_empty
     */
    public function __construct(string $token, int $token_id, $format = 'json', $lang = 'cn', $error_on_empty = 'no')
    {
        $this->_login_token    = $token;
        $this->_login_token_id = $token_id;

        $this->_lang           = $lang;
        $this->_error_on_empty = $error_on_empty;
        $this->_format         = $format;
    }


    /**
     * @param $domain_id
     * @return array
     */
    public function record($domain_id)
    {
        return $this->_api('Record.List', ['domain_id' => $domain_id]);
    }

    /**
     * @param $domain_id
     * @param $value
     * @param string $sub_domain
     * @param string $type A CNAME MX TXT NS AAAA SRV 显性URL 隐性URL
     * @param int $ttl
     * @param int $mx
     * @param string $line
     * @return array|mixed
     */
    public function record_create($domain_id, $value, $sub_domain = '@', $type = 'CNAME', $ttl = 600, $mx = 10, $line = '默认')
    {
        $data = [
            'domain_id'   => $domain_id,
            'sub_domain'  => $sub_domain ? $sub_domain : '@',
            'record_type' => $type,
            'record_line' => $line,
            'value'       => $value,
            'mx'          => $mx,
            'ttl'         => $ttl,
        ];
        return $this->_api('Record.Create', $data);
    }

    /**
     * @param $domain_id
     * @param $record_id
     * @return array|mixed
     */
    public function record_remove($domain_id, $record_id)
    {
        $data = [
            'domain_id' => $domain_id,
            'record_id' => $record_id
        ];
        return $this->_api('Record.Remove', $data);
    }

    /**
     * @param $domain_id
     * @return array|mixed
     */
    public function domain_remove($domain_id)
    {
        $data = [
            'domain_id' => $domain_id
        ];
        return $this->_api('Domain.Remove', $data);
    }

    /**
     * @param $offset
     * @param $length
     * @return array|mixed
     */
    public function domain_list($offset,$length)
    {
        $data = [
            'offset' => $offset,
            'length' => $length,
        ];
        return $this->_api('Domain.List', $data);
    }

    /**
     * @param $api
     * @param $data
     * @return array|mixed
     */
    private function _api($api, $data)
    {
        if ($api == '' || !is_array($data)) {
            return $this->_message(false, '内部错误：参数错误');
        }

        $data_ext = [
            'login_token'    => "{$this->_login_token_id},{$this->_login_token}",
            'format'         => $this->_format,
            'lang'           => $this->_lang,
            'error_on_empty' => $this->_error_on_empty,
        ];
        $api      = $this->_api_base . $api;
        $data     = array_merge($data, $data_ext);

        $result = $this->_post($api, $data);
        if (!$result) {
            return $this->_message(false, '内部错误：调用失败');
        }

        $result  = explode("\r\n\r\n", $result);
        $results = json_decode($result[1], true);
        if (!is_array($results)) {
            return $this->_message(false, '内部错误：返回异常');
        }

        if ($results['status']['code'] != 1 && $results['status']['code'] != 50) {
            return $this->_message(false, $results['status']['message']);
        }

        return $results;
    }

    /**
     * @param $status
     * @param $message
     * @param bool $is_out
     * @return array
     */
    private function _message($status, $message, $is_out = false)
    {
        if ($is_out) {
            $msg = "----------------------------------\n" .
                ($status ? '操作成功' : '操作失败') . "\n" .
                "提示:{$message}\n" .
                "----------------------------------\n";
            exit($msg);
        } else {
            return ['ret' => $status, 'msg' => $message];
        }
    }

    /**
     *
     * @param string $url
     * @param array $data
     * @return array|mixed
     */
    private function _post(string $url,array $data)
    {
        if ($url == '' || !is_array($data)) {
            return $this->_message('danger', '内部错误：参数错误', '');
        }
        // sleep(3);
        $ch = curl_init();
        if (!$ch) {
            return $this->_message('danger', '内部错误：服务器不支持CURL', '');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        // curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERAGENT, 'DNSPod API PHP Web Client/1.0.0 (i@lhxzs.com)');
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
