<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\wechat;


class error_code
{
    /** @var int 正常 */
    const OK = 0;

    /** @var int encodingAesKey 非法 */
    const IllegalEncodingAesKey = -41001;
    /** @var int Iv 非法 */
    const IllegalIv = -41002;
    /** @var int aes 解密失败 */
    const IllegalAesKey = -41003;
    /** @var int 解密后得到的buffer非法 */
    const IllegalBuffer = -41004;

    /** @var int base64加密失败 */
    const EncodeBase64Error = -41005;
    /** @var int base64解密失败 */
    const DecodeBase64Error = -41016;

    /** @var array 数据 */
    const Msg_List = [
        self::OK => '正常',

        self::IllegalEncodingAesKey => 'encodingAesKey非法',
        self::IllegalAesKey         => 'aes解密失败',
        self::IllegalBuffer         => '解密后得到的buffer非法',

        self::EncodeBase64Error => 'base64加密失败',
        self::DecodeBase64Error => 'base64解密失败',

        -1        => '通用错误',
        -401001   => 'SDK 通用错误：无权限使用 API',
        -401002   => 'SDK 通用错误：API 传入参数错误',
        -401003   => 'SDK 通用错误：API 传入参数类型错误',
        -402001   => 'SDK 数据库错误：检测到循环引用',
        -402002   => 'SDK 数据库错误：初始化监听失败',
        -402003   => 'SDK 数据库错误：重连 WebSocket 失败',
        -402004   => 'SDK 数据库错误：重建监听失败',
        -402005   => 'SDK 数据库错误：关闭监听失败',
        -402006   => 'SDK 数据库错误：收到服务器错误信息',
        -402007   => 'SDK 数据库错误：从服务器收到非法数据',
        -402008   => 'SDK 数据库错误：WebSocket 连接异常',
        -402009   => 'SDK 数据库错误：WebSocket 连接断开',
        -402010   => 'SDK 数据库错误：检查包序失败',
        -402011   => 'SDK 数据库错误：未知异常',
        -501001   => '云资源通用错误：云端系统错误',
        -403001   => 'SDK 文件存储错误：上传的文件超出大小上限',
        '-40400x' => 'SDK 云函数错误：云函数调用失败',
        -404011   => 'SDK 云函数错误：云函数执行失败',
        -501002   => '云资源通用错误：云端响应超时',
        -501003   => '云资源通用错误：请求次数超出环境配额',
        -501004   => '云资源通用错误：请求并发数超出环境配额',
        -501005   => '云资源通用错误：环境信息异常',
        -501007   => '云资源通用错误：参数错误',
        -501009   => '云资源通用错误：操作的资源对象非法或不存在',
        -501015   => '云资源通用错误：读请求次数配额耗尽',
        -501016   => '云资源通用错误：写请求次数配额耗尽',
        -501017   => '云资源通用错误：磁盘耗尽',
        -501018   => '云资源通用错误：资源不可用',
        -501019   => '云资源通用错误：未授权操作',
        -501020   => '云资源通用错误：未知参数错误',
        -501021   => '云资源通用错误：操作不支持',
        -502001   => '云资源数据库错误：数据库请求失败',
        -502002   => '云资源数据库错误：非法的数据库指令',
        -502003   => '云资源数据库错误：无权限操作数据库',
        -502005   => '云资源数据库错误：集合不存在',
        -502010   => '云资源数据库错误：操作失败',
        -502011   => '云资源数据库错误：操作超时',
        -502012   => '云资源数据库错误：插入失败',
        -502013   => '云资源数据库错误：创建集合失败',
        -502014   => '云资源数据库错误：删除数据失败',
        -502015   => '云资源数据库错误：查询数据失败',
        -503001   => '云资源文件存储错误：云文件请求失败',
        -503002   => '云资源文件存储错误：无权限访问云文件',
        -503003   => '云资源文件存储错误：文件不存在',
        -503003   => '云资源文件存储错误：非法签名',
        -504001   => '云资源云函数错误：云函数调用失败',
        -504002   => '云资源云函数错误：云函数执行失败',
        -601001   => '微信后台通用错误：系统错误',
        -601002   => '微信后台通用错误：系统参数错误',
        -601003   => '微信后台通用错误：系统网络错误',
        -604001   => '微信后台云函数错误：回包大小超过 1M',
        -604101   => '微信后台云函数错误：无权限调用此 API',
        -604102   => '微信后台云函数错误：调用超时',
        -604103   => '微信后台云函数错误：云调用系统错误',
        -604104   => '微信后台云函数错误：非法调用来源',
        -604101   => '微信后台云函数错误：调用系统错误',
        -605101   => '微信后台 HTTP API 错误：查询语句解析失败',
    ];
}