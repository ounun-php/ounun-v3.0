<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun;

/** 常量 */
class c
{
    /** @var int 成功 - 状态 */
    const Status_Success = 0;

    /** @var int 否 - 状态 */
    const Status_No = 0;
    /** @var int 是 - 状态 */
    const Status_Yes = 1;

    /** @var int 禁用 - 状态 */
    const Status_Disabled = 1000;
    /** @var int 开启 - 状态 */
    const Status_Enabled = 1010;

    /** @var int 关 - 状态 */
    const Status_Off = 2000;
    /** @var int 开 - 状态 */
    const Status_On = 2010;

    /** @var int 正常(灰) */
    const Status_Normal = 3000;
    /** @var int 等待空置状态 */
    const Status_Await = 3030;
    /** @var int 工作中... */
    const Status_Runing = 3050;
    /** @var int 过载状态 */
    const Status_Full = 3090;
    /** @var int 完成 - 状态 */
    const Status_Done = 3100;
    /** @var int 成功(绿色) */
    const Status_Succeed = 3299;

    /** @var int 失败 - 状态 */
    const Status_Fail = 4100;
    /** @var int 突出(橙黄) */
    const Status_Warning = 4106;
    /** @var array 状态 */
    const Status = [
        self::Status_No       => '否',
        self::Status_Yes      => '是',
        self::Status_Disabled => '禁用',
        self::Status_Enabled  => '开启',
        self::Status_Off      => '关',
        self::Status_On       => '开',

        self::Status_Normal  => '正常(灰)',
        self::Status_Await   => '空置',
        self::Status_Runing  => '工作中...',
        self::Status_Full    => '过载状态',
        self::Status_Done    => '完成',
        self::Status_Succeed => '成功(绿色)',

        self::Status_Fail    => '失败',
        self::Status_Warning => '突出(橙黄)',
    ];

    /** @var int 已删除 */
    const Delete_Yes = 1;
    /** @var int 没删险 */
    const Delete_No = 0;

    /** @var int 验证成功 */
    const Check_Yes = 1;
    /** @var int 没有验证 */
    const Check_No = 0;

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
}
