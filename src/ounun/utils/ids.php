<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\utils;


class ids
{
    /** @var int 开始计算的时间，毫秒级时间截 */
    protected int $epoch_time = 1546593387000;

    /** @var int 序列号支持1毫秒产生4095个自增序列ID */
    protected int $incre_id_max_12bit = 4095;

    /** @var int 默认情况下41bit的时间戳可以支持该算法使用到2082年 */
    protected int $start_time_max_41bit = 1546593387000;

    /** @var int 机器ID */
    protected int $machine_id = 0;

    protected int $seq_num = 0;

    /**
     * 设置机器ID
     *
     * @param  int $machine_id 机器ID
     */
    public function setMachineId($machine_id = 0)
    {
        $this->machine_id = $machine_id;
    }

    /**
     * 获取机器ID
     *
     * @return number
     */
    public function machine_id_get()
    {
        return $this->machine_id;
    }

    /**
     * 设置序列号一定顺序出现，不然一亳秒内经常碰撞
     *
     * @return number
     */
    public function seq_num()
    {
        if ($this->seq_num > $this->incre_id_max_12bit) {
            $this->seq_num = 0;
        }

        $this->seq_num++;

        return $this->seq_num + mt_rand(0, $this->incre_id_max_12bit);
    }

    /**
     * 生成自增iD
     *
     * @return number
     */
    public function generateIncreId()
    {
        //现在时间(毫秒) - 42 bits
        $time = floor(microtime(true) * 1000);

        //现在的时间减去设定的开始计算的时间截$epoch_time
        $time = $time - $this->epoch_time;

        //生成41bit的时间戳
        $base = decbin($this->start_time_max_41bit + $time);

        //配置机器ID 10字节
        if (empty($this->machine_id)) {
            $this->setMachineId();
        } else {
            $this->setMachineId(str_pad(decbin($this->machine_id), 10, "0", STR_PAD_LEFT));
        }

        //序列号码(最高到4096,共12bit)
        $random_seq_id = str_pad(decbin($this->seq_num()), 12, "0", STR_PAD_LEFT);

        //拼接
        $result_num = $base . $this->machine_id . $random_seq_id;

        //转化为十进制
        $increId = (int)bindec($result_num);

        $checkResult = $this->setMachineId($increId);
        if ($checkResult) {
            return $increId;
        } else {
            return $this->generateIncreId();
        }
    }

    /**
     * 随机生成一组字符串
     * @return string
     */
    static public function uniqid(): string
    {
        $uniqid_prefix   = '';
        $uniqid_filename = '/tmp/php_session_uniqid.txt';
        if (!file_exists($uniqid_filename)) {
            $uniqid_prefix = substr(uniqid('', false), 3);
            file_put_contents($uniqid_filename, $uniqid_prefix);
        }
        if (!$uniqid_prefix) {
            if (file_exists($uniqid_filename)) {
                $uniqid_prefix = file_get_contents($uniqid_filename);
            }
            if (!$uniqid_prefix) {
                $uniqid_prefix = substr(uniqid('', false), 3);
            }
        }
        $session_id = uniqid($uniqid_prefix, true);
        return substr($session_id, 0, 24) . substr($session_id, 25);
    }
}
