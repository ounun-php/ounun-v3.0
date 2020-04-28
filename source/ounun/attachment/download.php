<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\attachment;

class download extends driver
{

    public function __construct(string $dir = '', string $allow_exts = '', string $site_url = '')
    {
        parent::__construct($dir);
        $this->set($dir, $allow_exts, $site_url);
    }

    public function set($dir, $allow_exts = null, $site_url = null)
    {
        if (!is_null($site_url)) {
            $this->site_url = $site_url;
        }
        parent::set($dir, $allow_exts);
    }

    public function content($string)
    {
        return preg_replace('/(http:\/\/[^>]*?\.(' . $this->allow_exts . '))/ie', "\$this->by_file_callback('\\1')", $string);
    }

    private function file_callback($file)
    {
        if (!preg_match("#^(" . static::$url_site . ")#", $file)) {
            $file = static::$url_site . $this->file($file);
        }
        return $file;
    }

    public function file($file)
    {
        if (is_array($file)) {
            return array_map([$this, 'file'], $file);
        } else {
            $path = $this->copy($file);
            if (!$path) {
                return false;
            }
            $info           = $this->info($path);
            $this->_files[] = $info;
            return $info['filepath'] . $info['filename'];
        }
    }

    public function dir($dir)
    {
        $data = @scandir($dir);
        if (!$data) {
            return false;
        }

        $file = [];
        foreach ($data as $v) {
            $v = $dir . $v;
            if (is_file($v)) {
                $file[] = $v;
            }
        }
        return array_map([$this, 'file'], $file);
    }


    /**
     * @param string $image_url
     * @param int $w
     * @param int $h
     * @param int $q
     * @return string
     */
    static public function image_oss_url(string $image_url, int $w, int $h, int $q = 90, string $ext = '')
    {
        return "{$image_url}?x-oss-process=image/auto-orient,1/resize,m_fill,w_{$w},h_{$h}/quality,Q_{$q}{$ext}";
    }
}
