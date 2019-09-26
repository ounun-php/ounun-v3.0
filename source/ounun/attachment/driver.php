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
	
	function __construct(string $dir = '')
	{
		$this->set_dir($dir);
		$this->time = time();
	}
	
    function set($dir, $allow_exts = [])
    {
    	$this->set_dir($dir);
    	if ($allow_exts) {
    	    if(is_array($allow_exts)){
                $this->allow_exts = $allow_exts;
            }elseif (is_string($allow_exts)){
                $allow_exts = explode('|',$allow_exts);
                $this->allow_exts = $allow_exts;
            }
        }
    }
	
	function set_source($source)
	{
		if (strpos($source, 'http://') === false && !file_exists($source)) {
            return false;
        }
		$this->source = $source;
		return true;
	}
	
	function set_target($target = null, $file_ext = null)
	{
		if (is_null($target)) {
			$filename = $dir = null;
		} else {
			$pathinfo = pathinfo($target);
			$dir = $pathinfo['dirname'];
			$filename = $pathinfo['basename'];
			$file_ext = null;
		}
		$this->set_dir($dir);
		$this->set_filename($filename, $file_ext);
		$this->target = $this->dir.$this->filename;
		return true;
	}
	
	function set_dir($dir = null,int $time = 0 )
	{
        $time  = $time??time();
		if (is_null($dir)) {
			$dir = static::$path_upload.date('Y/md/',$time);
		} else {
			$dir = folder::path($dir);
		}
		$this->dir = $dir;
		return mkdir($this->dir,0777,true);
	}

    public function set_filename($filename = null, $fileext = null)
	{
		$this->filename = is_null($filename) ? $this->time.mt_rand(100, 999).'.'.$fileext : $filename;
	}

    public function copy($source, $target = null)
	{
		if (!$this->set_source($source)) {
            return false;
        }
		if (!$this->set_target($target, pathinfo($source, PATHINFO_EXTENSION))){
            return false;
        }
		if (!@copy($this->source, $this->target)) {
			return false;
		}
		return $this->target;
	}

    public function info($file = null)
	{
		if (is_null($file)) {
            $file = $this->target;
        }
		
		$info = [];
		$pathinfo = pathinfo($file);
		$info['file_path'] = $this->format($pathinfo['dirname'], false).'/';
        $info['file_ext'] = strtolower($pathinfo['extension']);
		$info['filename'] = $pathinfo['basename'];
		$info['filesize'] = filesize($file);
		$info['isimage'] = in_array($info['fileext'], ['jpg', 'jpeg', 'png', 'gif', 'bmp']) ? 1 : 0;
		if ($info['isimage']) {
			$image = getimagesize($file);
			$info['filemime'] = $image['mime'];
		}
		return $info;
	}

    /**
     * @param string $file
     * @return bool
     */
    public function is_image(string $file, string $extension = '')
	{
		return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
	}

    /**
     * @return array
     */
	public function files_get()
	{
		return $this->files;
	}

    /**
     * @param $file
     * @param $is
     * @return mixed
     */
    protected function format($file,$is)
    {
		return str_replace('\\', '/', preg_replace("/^".preg_quote(static::$path_upload, '/')."/", '', $file));
    }
}