<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\apps\logic;


use ounun\config\parse\ini;

class user extends \ounun\apps\logic
{
    /** @var int 登录时间有误(比现在还晚) */
    const E_Time_Error = 1;

    /** @var int 登录超时 */
    const E_Time_Out = 2;

    /** @var int hex校验出错 */
    const E_Hex = 3;

    /** @var int 解析有问题 */
    const E_Analysis = 98;

    /** @var int Cookie不存在 */
    const E_Cookie_Non_Exist = 99;

    /** @var array 错误提示信息  */
    const Error_Msg  = [
        self::E_Time_Error       => '登录时间有误(比现在还晚)',
        self::E_Time_Out         => '登录超时',
        self::E_Hex              => 'hex校验出错',
        self::E_Analysis         => '解析有问题',
        self::E_Cookie_Non_Exist => 'Cookie不存在',
    ];

    /** @var int 用户Uid */
    public $uid   = 0;

    /** @var string 用户昵称 */
    public $uname = '';

    /** @var string 注册用户名 */
    public $account = '';

    /** @var string 用户所在组group_id */
    public $group_id = 0;

    /** @var string 用户应用角色role_id(多角色游戏) */
    public $role_id  = 0;

    /** @var string 微信open_id */
    public $open_id  = '';

    /** @var string 微信unique_id */
    public $unique_id  = '';

    /** @var array 受权类型 */
    public $oauth_type = [];

    /** @var string 通信私钥   */
    protected $_key_private = '';

    /** @var string 登录超时，最长时间 */
//    protected $_login_overtime = 3600;

    /** @var string cookie域名 */
    protected $_cookie_domain = '';

    /** @var string cookiey主健 */
    protected $_cookie_key = '';

    /** @var string cookie前缀 */
    protected $_cookie_pre = '';

    /**
     * 设定 私钥&域名
     * @param string $key_private
     * @param string $domain
     * @param string $cookie_key
     * @param string $cookie_pre
     */
    public function key_set(string $key_private,string $domain,string $cookie_key = '_',string $cookie_pre = 'yg')
    {
        $this->_key_private   = $key_private;
        $this->_cookie_domain = $domain;
        $this->_cookie_key    = $cookie_key;
        $this->_cookie_pre    = $cookie_pre;
    }

    /**
     * 检查是否登录
     * @param string $key
     * @return array 返回UID >0:UID  <0:没有登录
     */
    public function check()
    {
        if ($_COOKIE[$this->_cookie_key]) {
            list($yg, $ot, $uid_en, $uname_en, $account, $group_id, $role_id, $open_id, $unique_id, $time_en, $type, $hex) = explode('.', $_COOKIE[$this->_cookie_key]);
            if ($this->_cookie_pre == $yg && $uid_en && $time_en && $hex) {
                $uname    = base64_decode($uname_en);
                $uid      = \short_url_decode($uid_en);
                $time     = \short_url_decode($time_en);
                $now_time = time();
                if ($time > $now_time) {
                    return $this->error(self::E_Time_Error); // 登录时间 比现在还晚
                }
                if ($type && $time + $type * 3600 < $now_time) {
                    return $this->error(self::E_Time_Out); // 登录超时
                }
                $str     = $uid.$ot.$uname.$account.$group_id.$role_id.$open_id.$unique_id.$type.$time.$this->_key_private;
                $hex_old = substr(md5($str), 12, 6) . substr(sha1($str), 16, 10);
                if ($hex == $hex_old) {
                    $this->oauth_type = $this->_oauth_types(0,$ot);
                    $this->uid        = $uid;
                    $this->uname      = $uname;
                    $this->account    = $account;
                    $this->group_id   = $group_id;
                    $this->role_id    = $role_id;
                    $this->open_id    = $open_id;
                    $this->unique_id  = $unique_id;
                    $data = [
                        'oauth_type' => $this->oauth_type,
                        'uid'        => $this->uid,
                        'uname'      => $this->uname,
                        'account'    => $this->account,
                        'group_id'   => $this->group_id,
                        'role_id'    => $this->role_id,
                        'open_id'    => $this->open_id,
                        'unique_id'  => $this->unique_id,
                    ];
                    return succeed($data);
                }
                return $this->error(self::E_Hex); // $hex
            }
            return $this->error(self::E_Analysis); // 解析有问题
        }
        return $this->error(self::E_Cookie_Non_Exist); // Cookie不存在
    }

    /**
     * 登录
     * @param int $uid           用户Uid
     * @param string $uname      用户昵称
     * @param string $account    注册用户名
     * @param int $group_id      用户所在组group_id
     * @param int $role_id       用户应用角色role_id(多角色游戏)
     * @param string $open_id    微信open_id
     * @param string $unique_id  微信unique_id
     * @param int $oauth_type    受权类型
     * @param int $type          0:不限  n:小时
     * @return string
     */
    public function login(int $uid, string $uname = '',string $account = '',int $group_id = 0,int $role_id = 0,string $open_id = '',string $unique_id = '',int $oauth_type = 0, int $type = 0)
    {
        $cstr = '';
        if ($uid) {
            $time     = time();
            $ot       = $this->_oauth_types($oauth_type);
            $ot       = implode('-', $ot);
            $str      = $uid.$ot.$uname.$account.$group_id.$role_id.$open_id.$unique_id.$type.$time.$this->_key_private;
            $uname_en = base64_encode($uname);
            $uid_en   = \short_url_encode($uid);
            $time_en  = \short_url_encode($time);

            $cstr     = "{$this->_cookie_pre}.{$ot}.{$uid_en}.{$uname_en}.{$account}.{$group_id}.{$role_id}.{$open_id}.{$unique_id}.{$time_en}.{$type}." . substr(md5($str), 12, 6) . substr(sha1($str), 16, 10);
            setcookie($this->_cookie_key, $cstr, $time * 2, '/', $this->_cookie_domain);
        }
        return $cstr;
    }

    /**
     * 退出
     */
    public function out()
    {
        setcookie($this->_cookie_key, '', -1, '/', $this->_cookie_domain);
    }

    /**
     * @param int $oauth_type
     * @param string $cookie_value
     * @param array $oauth_type_rs
     * @return array
     */
    private function _oauth_types(int $oauth_type = 0, string $cookie_value = '',array $oauth_type_rs = [])
    {
        if(empty($oauth_type_rs)){
            $cookie_value  = $cookie_value ? $cookie_value : $_COOKIE[$this->_cookie_key];
            $oauth_type_rs = [];
            if ($cookie_value) {
                $ts = explode('.', $_COOKIE[$this->_cookie_key])[1];
                if ($ts) {
                    $ts = explode('-', $ts);
                    if ($ts) {
                        foreach ($ts as $v) {
                            $v = (int)$v;
                            $oauth_type_rs[$v] = $v;
                        }
                    }
                }
            }
        }
        $oauth_type = (int)$oauth_type;
        if ($oauth_type) {
            $oauth_type_rs[$oauth_type] = $oauth_type;
        }
        if ($oauth_type_rs) {
            return array_values($oauth_type_rs);
        }
        return $oauth_type_rs;
    }
}
