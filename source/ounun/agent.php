<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun;

/**
 * 接口输出类
 * Class agent
 * @package ounun
 */
class agent
{
    // 设备类型
    const Device_Mobile = 1;
    const Device_Desktop = 2;
    const Device_Unknown = 0;

    // 浏览器类型
    const Browser_Type_iPhone = 1;
    const Browser_Type_iPad = 2;
    const Browser_Type_iPod = 3;
    const Browser_Type_Android = 4;
    const Browser_Type_Xzh_App = 5;
    const Browser_Type_Unknown = 0;

    // 系统类型
    const Os_Type_Ios = 1;
    const Os_Type_ANDROID = 2;
    const Os_Type_Unknown = 0;

    // 是否Retina屏
    const Retina_Type_Yes = 1;
    const Retina_Type_Not = 0;

    // 是否IOS6系统
    const Ios6_Yes = 1;
    const Ios6_No = 0;

    // 是否微信打开
    const Wechat_Msg_Yes = 1;
    const Wechat_Msg_No = 0;

    // APP已经安装
    const App_Installed_Yes = 1;
    const App_Installed_Not = 0;

    // -------------------------------------------------------

    const Status_Off = 0;

    const Status_On = 1;

    const Status_Success = 0;


    const Menu_Currentself = 1;

    const Menu_History = 2;

    const Menu_Conditional = 3;


    const Personal_Base_Type = 1;

    const Personal_Auth_Type = 2;

    const Personal_List_Type = 3;


    const Display_Type_Welcome = 9;

    const Display_Type_Last_Visit = 1;

    const Display_Type_Account = 2;

    const Display_Type_Wx_App = 3;

    const Display_Type_Webapp = 4;

    const Display_Type_Phoneapp = 5;

    const Display_Type_Platform = 6;

    const Display_Type_Module = 7;



    /**
     * @param string $agent
     * @return mixed|string
     */
    public static function agent_get($agent = '')
    {
        $agent = empty($agent) ? $_SERVER['HTTP_USER_AGENT'] : $agent;
        return $agent;
    }


    // 得到agent完整类型信息
    public static function device_info_get()
    {
        return [
            'device_type'  => self::device_type(),
            'browser_type' => self::browser_type(),
            'is_retina'    => self::is_retina(),
            'os_type'      => self::os_type(),
            'is_ios6'      => self::is_ios6(),
        ];
    }

    // 浏览器类型
    public static function browser_type($agent = '')
    {
        $agent = self::agent_get($agent);
        if (stripos($agent, 'baiduboxapp') !== false) {
            return self::Browser_Type_Xzh_App;
        }

        if (stripos($agent, 'iphone') !== false) {
            return self::Browser_Type_iPhone;
        }

        if (stripos($agent, 'ipad') !== false) {
            return self::Browser_Type_iPad;
        }

        if (stripos($agent, 'ipod') !== false) {
            return self::Browser_Type_iPod;
        }

        if (stripos($agent, 'android') !== false) {
            return self::Browser_Type_Android;
        }

        return self::Browser_Type_Unknown;
    }

    // 系统类型
    public static function os_type($agent = '')
    {
        $agent = self::agent_get($agent);
        $browserType = self::browser_type($agent);

        switch ($browserType) {
            case self::Browser_Type_iPhone:
            case self::Browser_Type_iPad:
            case self::Browser_Type_iPod:
                $osType = self::Os_Type_Ios;
                break;
            case self::Browser_Type_Android:
                $osType = self::Os_Type_ANDROID;
                break;
            default:
                $osType = self::Os_Type_Unknown;
        }

        return $osType;
    }

    // 设备类型
    public static function device_type()
    {
        if (self::is_mobile()) {
            return self::Device_Mobile;
        } else {
            return self::Device_Desktop;
        }
    }

    // retina屏
    public static function is_retina($agent = '')
    {
        $agent = self::agent_get($agent);
        $osType = self::os_type($agent);

        if (($osType == self::Os_Type_Ios) && (self::is_ios6($agent) != 1)) {
            return self::Retina_Type_Yes;
        } else {
            return self::Retina_Type_Not;
        }
    }

    // ios6系统的手机(iphone4, iphone4s)
    public static function is_ios6($agent = '')
    {
        $agent = self::agent_get($agent);

        if (stripos($agent, 'iPhone OS 6')) {
            return self::Ios6_Yes;
        } else {
            return self::Ios6_No;
        }
    }

    // 检查是否在微信中打开
    public static function is_wechat_message($agent = '')
    {
        $agent = self::agent_get($agent);

        if (stripos($agent, 'MicroMessenger') !== false) {
            return self::Wechat_Msg_Yes;
        } else {
            return self::Wechat_Msg_No;
        }
    }

    // 已安装APP
    public static function is_app_installed()
    {
        if (isset($_GET['isappinstalled']) && ($_GET['isappinstalled'] == 1)) {
            return self::App_Installed_Yes;
        } else {
            return self::App_Installed_Not;
        }
    }

    /**
     * 是移动设备访问
     * @return bool
     */
    public static function is_mobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
            return true;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = [
                'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp',
                'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu',
                'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave',
                'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'WindowsWechat'
            ];
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备, 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
                && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}
