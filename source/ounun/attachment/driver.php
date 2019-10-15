<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\attachment;

abstract class driver
{
    static public $url_site = '/';

    static public $url_upload = '';

    static public $path_upload = '';

    /** @var array  如果是*，代表任意类型 */
    public $allow_exts = ['gif','jpg','jpeg','bmp','png','txt','zip','rar','doc','docx','xls','ppt','pdf'];

    public $filesize_max = 1024;

	protected $_dir;

	protected $_filename;

	protected $_source;

	protected $_target;

	protected $_time;

	protected $_files = [];

    /**
     * driver constructor.
     * @param string $dir
     */
    public function __construct(string $dir = '')
	{
		$this->set_dir($dir);
		$this->_time = time();
	}

    /**
     * @param string $dir
     * @param array $allow_exts
     */
    public function set(string $dir, array $allow_exts = [])
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

    /**
     * @param $source
     * @return bool
     */
    public function source_set($source)
	{
		if (strpos($source, 'http://') === false && !file_exists($source)) {
            return false;
        }
		$this->_source = $source;
		return true;
	}

    public function set_target($target = null, $file_ext = null)
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
		$this->_target = $this->_dir.$this->_filename;
		return true;
	}

    public function set_dir($dir = '',int $time = 0 )
	{
        $time  = $time??time();
		if (is_null($dir)) {
			$dir = static::$path_upload.date('Y/md/',$time);
		} else {
			$dir = folder::path($dir);
		}
		$this->_dir = $dir;
		return mkdir($this->_dir,0777,true);
	}

    public function set_filename($filename = null, $fileext = null)
	{
		$this->_filename = is_null($filename) ? $this->_time.mt_rand(100, 999).'.'.$fileext : $filename;
	}

    public function copy($source, $target = null)
	{
		if (!$this->source_set($source)) {
            return false;
        }
		if (!$this->set_target($target, pathinfo($source, PATHINFO_EXTENSION))){
            return false;
        }
		if (!@copy($this->_source, $this->_target)) {
			return false;
		}
		return $this->_target;
	}

    public function info($file = null)
	{
		if (is_null($file)) {
            $file = $this->_target;
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
		return $this->_files;
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