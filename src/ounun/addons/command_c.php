<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;


class command_c
{
    /**
     * none         = "\033[0m"
     * black        = "\033[0;30m"
     * dark_gray    = "\033[1;30m"
     * blue         = "\033[0;34m"
     * light_blue   = "\033[1;34m"
     * green        = "\033[0;32m"
     * light_green  = "\033[1;32m"
     * cyan         = "\033[0;36m"
     * light_cyan   = "\033[1;36m"
     * red          = "\033[0;31m"
     * light_red    = "\033[1;31m"
     * purple       = "\033[0;35m"
     * light_purple = "\033[1;35m"
     * brown        = "\033[0;33m"
     * yellow       = "\033[1;33m"
     * light_gray   = "\033[0;37m"
     * white        = "\033[1;37m"
     *
     * 输出特效格式控制：
     * \033[0m                关闭所有属性
     * \033[1m                设置高亮度
     * \033[4m                下划线
     * \033[5m                闪烁
     * \033[7m                反显
     * \033[8m                消隐
     * \033[30m -- \033[37m   设置前景色
     * \033[40m -- \033[47m   设置背景色
     *
     * 字背景颜色范围: 40--49     字颜色: 30--39
     * 40: 黑            30: 黑
     * 41:红             31: 红
     * 42:绿             32: 绿
     * 43:黄             33: 黄
     * 44:蓝             34: 蓝
     * 45:紫             35: 紫
     * 46:深绿           36: 深绿
     * 47:白色           37: 白色
     *
     * 光标位置等的格式控制：
     * \033[nA             光标上移n行
     * \03[nB              光标下移n行
     * \033[nC             光标右移n行
     * \033[nD             光标左移n行
     * \033[y;xH           设置光标位置
     * \033[2J             清屏
     * \033[K              清除从光标到行尾的内容
     * \033[s              保存光标位置
     * \033[u              恢复光标位置
     * \033[?25l           隐藏光标
     * \33[?25h            显示光标
     */

    /** @var string 默认执行的命令 */
    const Default_Cmd = 'help';
    /** @var string  */
    const Color_None = "\033[0m";
    /** @var string  */
    const Color_Black = "\033[0;30m";
    /** @var string  */
    const Color_Dark_Gray = "\033[1;30m";
    /** @var string  */
    const Color_Blue = "\033[0;34m";
    /** @var string  */
    const Color_Light_BBlue = "\033[1;34m";
    /** @var string  */
    const Color_Green = "\033[0;32m";      // success
    /** @var string  */
    const Color_Light_Green = "\033[1;32m";
    /** @var string  */
    const Color_Cyan = "\033[0;36m";
    /** @var string  */
    const Color_Light_Cyan = "\033[1;36m";
    /** @var string 红色 */
    const Color_Red = "\033[0;31m";
    /** @var string  */
    const Color_Light_Red = "\033[1;31m"; // error
    /** @var string  */
    const Color_Purple = "\033[0;35m";
    /** @var string  */
    const Color_Light_Purple = "\033[1;35m";
    /** @var string  */
    const Color_Brown = "\033[0;33m";
    /** @var string  */
    const Color_Yellow = "\033[1;33m";    // info
    /** @var string  */
    const Color_Light_Gray = "\033[0;37m";
    /** @var string 灰色 */
    const Color_White = "\033[1;37m";

    /** @var array 不同深度的颜色 */
    const Depth_Colors = [
        self::Color_None,
        self::Color_Blue,
        self::Color_Black,
        self::Color_Green,
        self::Color_Cyan,
        self::Color_Red,
        self::Color_Purple,
        self::Color_Brown,
    ];

    /** @var int 深度的颜色数量 */
    const Depth_Colors_Count = 7;


    /** @var int 等待空置状态 */
    const Status_Await = 11;
    /** @var int 工作中... */
    const Status_Runing = 12;
    /** @var int 过载状态 */
    const Status_Full = 19;

    /** @var int 默认(等待处理) */
    const Status_Data_Null = 100;
    /** @var int 正常 */
    const Status_Data_Ok = 110;
    /** @var int 出错(问题URL) */
    const Status_Data_Fail = 120;

    /** @var int 正常(灰) */
    const Status_Normal = 0;
    /** @var int 失败(红色) */
    const Status_Fail = 1;
    /** @var int 警告(橙黄) */
    const Status_Warning = 6;
    /** @var int 成功(绿色) */
    const Status_Succeed = 99;

    const Status = [
        /** @var array 1:空置(等待) 2:运行中... 99:满载(过载) */
        self::Status_Await     => '空置(等待)',
        self::Status_Runing    => '运行中...',
        self::Status_Full      => '满载(过载)',
        /** @var array 1:空置(等待) 2:运行中... 99:满载(过载) */
        self::Status_Data_Null => '默认',
        self::Status_Data_Ok   => '正常',
        self::Status_Data_Fail => '出错',
        /** @var array 0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色) */
        self::Status_Normal    => '正常(灰)',  // self::Status_Data_Null  => '默认',
        self::Status_Fail      => '失败(红色)',
        self::Status_Warning   => '突出(橙黄)',
        self::Status_Succeed   => '成功(绿色)',
    ];

    /** @var int 审核中 - 任务执行状态 */
    const Task_Status_Checking = 0;
    /** @var int 审核未过 - 任务执行状态 */
    const Task_Status_Check_Bad = 9;
    /** @var int 排队中(已审) - 任务执行状态 */
    const Task_Status_Queueing = 1;
    /** @var int 执行中 - 任务执行状态 */
    const Task_Status_Runing = 2;
    /** @var int 分配完成 - 任务执行状态 */
    const Task_Status_Distribution = 3;
    /** @var int 执行完成 - 任务执行状态 */
    const Task_Status_Complete = 101;
    /** @var int 用户终止 - 任务执行状态 */
    const Task_Status_User_Stop = 102;
    /** @var int 系统终止 - 任务执行状态 */
    const Task_Status_System_Stop = 103;
    /** @var int 参数出错已取消 - 任务执行状态 */
    const Task_Status_Error_Stop = 104;
    /** @var int 失败 - 任务执行状态 */
    const Task_Status_Fail = 105;

    /** @var array 任务执行状态 */
    const Task_Status = [
        self::Task_Status_Checking     => '审核中',
        self::Task_Status_Check_Bad    => '弃,审核未过',
        self::Task_Status_Queueing     => '排队中(已审)',
        self::Task_Status_Runing       => '执行中',
        self::Task_Status_Distribution => '分配完,执行中',
        self::Task_Status_Complete     => '执行完成',
        self::Task_Status_User_Stop    => '用户终止',
        self::Task_Status_Error_Stop   => '参数出错已取消',
        self::Task_Status_System_Stop  => '系统终止',
        self::Task_Status_Fail         => '失败',
    ];


    /** @var int 采集全部 - 模式 */
    const Mode_All = 0;
    /** @var int 检查 - 模式 */
    const Mode_Check = 1;
    /** @var int 更新 - 模式 */
    const Mode_Dateup = 2;
    /** @var array 模式  0:采集全部  1:检查  2:更新   见 self::Mode_XXX */
    const Modes = [
        self::Mode_All    => '全部',
        self::Mode_Check  => '检查',
        self::Mode_Dateup => '更新',
    ];


    /** @var int 定时任务 */
    const Type_Crontab = 0;
    /** @var int 循环任务 */
    const Type_Interval = 1;
    /** @var array 任务类型列表 */
    const Types = [
        self::Type_Crontab  => '定时',
        self::Type_Interval => '循环',
    ];

    /** @var string 采集 - 运行类型 */
    const Run_Type_Caiji = 'caiji';
    /** @var string 发布 - 运行类型 */
    const Run_Type_Post = 'post';
    /** @var string 系统 - 运行类型 */
    const Run_Type_System = 'system';
    /** @var string 站点 - 运行类型 */
    const Run_Type_Site = 'site';
    /** @var string 总后台管理(采集) - 运行类型 */
    const Run_Type_Admin = 'admin';
    /** @var array 运行类型(采集/发布/系统) */
    const Run_Types = [
        self::Run_Type_Caiji  => '采集',
        self::Run_Type_Post   => '发布',
        self::Run_Type_System => '网站地图',

        self::Run_Type_Site  => '站点',
        self::Run_Type_Admin => '采集(总后台)',
    ];
}
