<?php
namespace ounun\attachment;

class download extends driver
{
	public $allow_exts = 'jpg|jpeg|gif|png|bmp';
	// public $site_url = SITE_URL;
	
	function __construct($dir = null, $allow_exts = null, $site_url = null)
    {
    	parent::__construct($dir);
    	$this->set($dir, $allow_exts, $site_url);
    }
    
    public function set($dir, $allow_exts = null, $site_url = null)
    {
    	if (!is_null($site_url)) $this->site_url = $site_url;
        parent::set($dir, $allow_exts);
    }
    
    public function by_content($string)
    {
        return preg_replace('/(http:\/\/[^>]*?\.('.$this->allow_exts.'))/ie', "\$this->by_file_callback('\\1')", $string);
    }
    
    private function by_file_callback($file)
    {
    	if (!preg_match("#^(".$this->site_url.")#", $file))
    	{
    		$file = static::$url_.$this->by_file($file);
    	}
    	return $file;
    }
    
    public function by_file($file)
    {
    	if (is_array($file))
    	{
    		return array_map(array($this, 'by_file'), $file);
    	}
    	else 
    	{
	    	$path = $this->copy($file);
	    	if (!$path) return false;
	    	$info = $this->info($path);
	    	$this->files[] = $info;
	    	return $info['filepath'].$info['filename'];
    	}
    }
    
    public function by_dir($dir)
    {
    	$data = @scandir($dir);
    	if (!$data) return false;
    	
    	$file = array();
    	foreach ($data as $v)
    	{
    		$v = $dir.$v;
    		if (is_file($v)) $file[] = $v;
    	}
    	return array_map(array($this, 'by_file'), $file);
    }
}