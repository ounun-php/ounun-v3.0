<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun;

/** 常量 */
class c
{
    /** @var int 禁用 - 状态 */
    const Status_Disabled = 0;
    /** @var int 使用 - 状态 */
    const Status_Enabled = 1;
    /** @var int 否 - 状态 */
    const Status_No = 0;
    /** @var int 是 - 状态 */
    const Status_Yes = 1;

    /** @var int 正常(灰) */
    const Status_Normal = 0;
    /** @var int 等待空置状态 */
    const Status_Await = 3;
    /** @var int 工作中... */
    const Status_Runing = 5;
    /** @var int 过载状态 */
    const Status_Full = 9;
    /** @var int 完成 - 状态 */
    const Status_Done = 10;
    /** @var int 成功(绿色) */
    const Status_Succeed = 99;
    /** @var int 失败 - 状态 */
    const Status_Fail = 100;
    /** @var int 突出(橙黄) */
    const Status_Warning = 106;

    /** @var int 已删除 */
    const Del_Yes = 1;
    /** @var int 没删险 */
    const Del_No = 0;

    /** @var int 验证成功 */
    const Check_Yes = 1;
    /** @var int 没有验证 */
    const Check_No  = 0;
    /** @var int 默认超时时长 */
    const Check_Timeout = 1800;
    /** @var int 登录校验 超时间 */
    const Check_Overtime_Max = 600;


    /** @var string Json - 输出Ajax格式 */
    const Format_Json = 'json';
    /** @var string XML - 输出Ajax格式 */
    const Format_Xml = 'xml';
    /** @var string XML - 输出Ajax格式 */
    const Format_Xml_Simple = 'xml_simple';
    /** @var string JsonP - 输出Ajax格式 */
    const Format_Jsonp = 'jsonp';
    /** @var string JsonP - 输出Ajax格式 */
    const Format_Eval = 'eval';
    /** @var string JavaScript - 输出Ajax格式 */
    const Format_JS = 'javascript';
    /** @var string Html - 输出Ajax格式 */
    const Format_Html = 'html';
    /** @var string Html Table - 输出Ajax格式 */
    const Format_Html_Table = 'html_table';


    /** @var string kw::$container  取值范围: wechat, android, ipad, iphone, ipod, unknown */
    const Container_Wechat = 'wechat';
    /** @var string */
    const Container_Android = 'android';
    /** @var string */
    const Container_Ipad = 'ipad';
    /** @var string */
    const Container_Iphone = 'iphone';
    /** @var string */
    const Container_Ipod = 'ipod';
    /** @var string */
    const Container_Unknown = 'unknown';

    /** @var string kw::$os 取值范围: windows (pc端), mobile(手机端), unknown */
    const Os_Windows = 'windows';
    /** @var string */
    const Os_Mobile = 'mobile';
    /** @var string */
    const Os_Unknown = 'unknown';

    /** @var string 字符串 - 字段类型 */
    const Type_String = 'string';
    /** @var string 整数 - 字段类型  */
    const Type_Int     = 'int';
    /** @var string 浮点数 - 字段类型  */
    const Type_Float   = 'float';
    /** @var string 枚举 - 字段类型  */
    const Type_Enum   = 'enum';
    /** @var string bool - 字段类型  */
    const Type_Bool   = 'bool';
    /** @var string 下线(子集) - 字段类型  */
    // const Type_Child   = 'child';
}
