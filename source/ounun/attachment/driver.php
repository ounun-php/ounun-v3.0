<?php
namespace ounun\attachment;

abstract class driver
{
    static public $url_site = '/';

    static public $url_upload = '';

    static public $path_upload = '';

	protected $dir;

	protected $filename;

	protected $source;

	protected $target;

	protected $time;

	protected $files = [];
	
	function __construct($dir = null) 
	{
		$this->set_dir($dir);
		$this->time = time();
	}
	
    function set($dir, $allow_exts = null)
    {
    	$this->set_dir($dir);
    	if (!is_null($allow_exts)) $this->allow_exts = $allow_exts;
    }
	
	function set_source($source)
	{
		if (strpos($source, 'http://') === false && !file_exists($source)) {
            return false;
        }
		$this->source = $source;
		return true;
	}
	
	function set_target($target = null, $fileext = null)
	{
		if (is_null($target)) {
			$filename = $dir = null;
		} else {
			$pathinfo = pathinfo($target);
			$dir = $pathinfo['dirname'];
			$filename = $pathinfo['basename'];
			$fileext = null;
		}
		$this->set_dir($dir);
		$this->set_filename($filename, $fileext);
		$this->target = $this->dir.$this->filename;
		return true;
	}
	
	function set_dir($dir = null,$time = 0 )
	{
        $time  = $time??time();
		if (is_null($dir)) {
			$dir = static::$path_upload.date('Y/md/',$time);
		} else {
			$dir = folder::path($dir);
		}
		$this->dir = $dir;
		return folder::create($this->dir);
	}
	
	function set_filename($filename = null, $fileext = null)
	{
		$this->filename = is_null($filename) ? $this->time.mt_rand(100, 999).'.'.$fileext : $filename;
	}
	
	function copy($source, $target = null)
	{
		if (!$this->set_source($source)) return false;
		if (!$this->set_target($target, pathinfo($source, PATHINFO_EXTENSION))) return false;
        
		if (!@copy($this->source, $this->target)) {
			return false;
		}
		return $this->target;
	}
	
	function info($file = null)
	{
		if (is_null($file)) $file = $this->target;
		
		$info = array();
		$pathinfo = pathinfo($file);
		$info['filepath'] = $this->format($pathinfo['dirname'], false).'/';
		$info['filename'] = $pathinfo['basename'];
		$info['fileext'] = strtolower($pathinfo['extension']);
		$info['filesize'] = filesize($file);
		$info['isimage'] = in_array($info['fileext'], array('jpg', 'jpeg', 'png', 'gif', 'bmp')) ? 1 : 0;
		if ($info['isimage'])
		{
			$image = @getimagesize($file);
			$info['filemime'] = $image['mime'];
		}
		return $info;
	}
	
	function is_image($file)
	{
		return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), array('jpg', 'jpeg', 'png', 'gif', 'bmp'));
	}
	
	function get_files()
	{
		return $this->files;
	}
	
    protected function format($file,$is)
    {
		return str_replace('\\', '/', preg_replace("/^".preg_quote(static::$path_upload, '/')."/", '', $file));
    }
}