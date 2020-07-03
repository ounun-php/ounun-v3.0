<?php
/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */

namespace ounun\wechat;


class crypt
{
    private string $_app_id;

    private string $_session_key;

    /**
     * 构造函数
     * @param string $session_key 用户在小程序登录后获取的会话密钥
     * @param string $app_id 小程序的appid
     */
    public function __construct(string $app_id, string $session_key)
    {
        $this->_session_key = $session_key;
        $this->_app_id      = $app_id;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param string $encrypted_data 加密的用户数据
     * @param string $iv 与用户数据一同返回的初始向量
     * @param string $data 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decrypt($encrypted_data, $iv, &$data)
    {
        if (strlen($this->_session_key) != 24) {
            return error_code::IllegalAesKey;
        }
        $aes_key = base64_decode($this->_session_key);


        if (strlen($iv) != 24) {
            return error_code::IllegalIv;
        }
        $aes_iv     = base64_decode($iv);
        $aes_cipher = base64_decode($encrypted_data);

        $result   = openssl_decrypt($aes_cipher, "AES-128-CBC", $aes_key, 1, $aes_iv);
        $data_obj = json_decode($result);
        if (empty($data_obj)) {
            return error_code::IllegalBuffer;
        }
        if ($data_obj->watermark->appid != $this->_app_id) {
            return error_code::IllegalBuffer;
        }
        $data = $result;
        return error_code::OK;
    }
}
