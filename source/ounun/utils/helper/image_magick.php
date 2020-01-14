<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\utils\helper;

class image_magick
{
	public $debug = false;
	public $filepath;
	public $saveas;
	public $log = [];
	public $error = '';
	public $imageQuality;

	function __construct($filepath = '')
	{
        $this->filepath = $filepath;
        $this->saveas = $filepath;
        $this->imageQuality = 85;
	}

	function setSource($path)
	{
        $this->filepath = $path;
        return $this ;
    }

    function setDestination ($path)
    {
        $this->saveas = $path ;
        return $this;
    }

	function setImageQuality($value)
	{
        $this->imageQuality = intval($value);
        return $this;
    }

	function blur($r = 5)
	{
		$filename = $this->filepath;
		$filename = escapeshellcmd($filename);
		$cmd = 'convert "'.$filename.'" -channel RGBA -blur 0x'.$r.' "'.$filename.'"';
		$this->execute($cmd);
		return $this;
	}

	function stroke_outline()
	{
		$filename = $this->filepath;
		$filename = '"'.escapeshellcmd($filename).'"';
		$cmd = 'convert -background none -stroke black '.$filename
		.' ( +clone   -background navy   -shadow 80x3+3+3 ) +swap '
		.' -background none -layers merge +repage  '.$filename;
		$this->execute($cmd);
		return $this;
	}

	public function execute($cmd)
	{
		$ret = null;
        $out = [];
         echo $cmd.'<br />';
        chdir(IMAGE_MAGICK_PATH);
        //exec($cmd .' 2>&1', $out, $ret);

        passthru($cmd .' 2>&1',$ret);

        if($ret != 0)
        {
           	$err = 'Error executing "'. $cmd.'" <br>';
           	$err.='return code: '. $ret .' <br>command output :"'. implode("<br>", $out).'"';
           	if ($this->debug)
           		echo $err;
           	else
           		$this->error[] = $err;
        }

        $this->log[] = array(
            'cmd' => $cmd
            ,'return' => $ret
            ,'output' => $out
        );


        return $ret ;
	}
}
