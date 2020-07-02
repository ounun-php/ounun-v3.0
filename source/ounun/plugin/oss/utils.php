<?php


namespace ounun\plugin\oss;


class utils
{
    /** @var string  */
    protected string $_src_dir = '';

    /** @var string  */
    protected string $_target_dir = '';

    /**
     * utils constructor.
     * @param string $src_dir
     * @param string $target_dir
     */
    public function __construct(string $src_dir,string $target_dir)
    {
        $this->_src_dir    = $src_dir;
        $this->_target_dir = $target_dir;
    }

    /**
     * @param $dir
     */
    public function scan($dir)
    {
        $src_dir2 = $this->_src_dir.$dir;
        echo "{$src_dir2} \t --> i\n";
        if ($dh = opendir($src_dir2)) {
            while (($file = readdir($dh)) !== false) {
                // echo $src_dir2.$file." \t --> \n";
                if($file=="." || $file=="..") {
                    continue;
                }elseif(is_dir($src_dir2.$file)) {
                    if(!file_exists($this->_target_dir.$dir)) {
                        echo "mkdir \t -> ".$this->_target_dir.$dir."\n";
                        mkdir($this->_target_dir.$dir,0777,true);
                    }
                    //echo Dir_Src.$dir.$file." --> d\n";
                    $this->scan("{$dir}{$file}/");
                } else {
                    $target_file2 = $this->_target_dir.$dir.$file;
                    if(!file_exists($target_file2)) {
                        $dir3 = dirname($target_file2);
                        if(!file_exists($dir3)) {
                            echo "mkdir \t -> ".$dir3."\n";
                            mkdir($dir3,0777,true);
                        }
                        echo "{$dir3} \t ->  ".$src_dir2.$file." \t -> ".$target_file2."\n";
                        copy($src_dir2.$file,$target_file2);
                    }
                }
            }
            closedir($dh);
        }
    }

    /**
     * @param string $dir
     * @param string $dir_root
     */
    static public function  rename(string $dir,string $dir_root)
    {
        $src_dir2 = $dir_root.$dir;
        echo "{$src_dir2} \t --> dir\n";
        if ($dh = opendir($src_dir2)) {
            while (($file = readdir($dh)) !== false) {
                // echo $src_dir2.$file." \t --> \n";
                if($file=="." || $file=="..") {
                    continue;
                }elseif(is_dir($src_dir2.$file)) {
                    self::rename("{$dir}{$file}/",$dir_root);
                } else {
                    if(strstr($file,'litecoin')) {
                        $file2 = str_replace('litecoin','fcash',$file);
                        echo " {$file} --> {$file2}\n";
                        \rename($src_dir2.$file,$src_dir2.$file2);
                    }
                }
            }
            closedir($dh);
        }
    }
}
