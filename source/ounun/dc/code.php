<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\dc;


class code
{
    /**
     * 获得数据
     * @param string $filename
     * @return mixed
     */
    static public function read(string $filename)
    {
        if (file_exists($filename)) {
            return require $filename;
        }
        return null;
    }

    /**
     * 获得数据
     * @param string $filename
     * @return mixed
     */
    static public function write(string $filename, $data, bool $recursive = false)
    {
        if($recursive){
            $dir      = dirname($filename);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        $str = var_export($data, true);
        return file_put_contents($filename, '<?php ' . "return {$str};\n");
    }
}
