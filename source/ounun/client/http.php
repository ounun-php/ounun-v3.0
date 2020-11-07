<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\client;

use ounun\utils\file;

class http
{
    /** @var array */
    protected static array $_instances = [];

    public static string $_request_url;

    public static string $_request_uri;

    public static string $_request_base;

    public static array $_pathinfo;

    /**
     * Returns a reference to the global Browser object, only creating it
     * if it doesn't already exist.
     *
     * This method must be invoked as:
     *      <pre>  $browser = http::i([$user_agent[, $accept]]);</pre>
     *
     * @param string|null $user_agent The browser string to parse.
     * @param string|null $accept The HTTP_ACCEPT settings to use.
     * @param string $signature
     * @return $this  The Browser object.
     */
    public static function i($user_agent = null, $accept = null, string $signature = '')
    {
        if (empty($signature)) {
            $signature = serialize([$user_agent, $accept]);
        }

        if (empty(static::$_instances[$signature])) {
            static::$_instances[$signature] = new static($user_agent, $accept);
        }

        return static::$_instances[$signature];
    }


    /** @var string */
    public string $scheme = 'http';
    /** @var string|null Browser name. */
    public ?string $browser;
    /** @var string|null */
    public ?string $uri;

    /** @var string|null */
    public ?string $host = '';
    /** @var int */
    public int $port = 80;
    /** @var string|null */
    public ?string $user;
    /** @var string|null */
    public ?string $pass;
    /** @var string|null */
    public ?string $_path;
    /** @var string|null */
    public ?string $_query;
    /** @var array|null */
    public ?array $query_vars;
    /** @var string|null */
    public ?string $fragment;

    /** @var integer Major version number. */
    public int $major_version = 0;
    /** @var integer Minor version number. */
    public int $minor_version = 0;

    /** @var string|null Full user agent string. */
    protected string $_agent = '';
    /** @var string|null Lower-case user agent string. */
    protected string $_lower_agent = '';
    /** @var string|null HTTP_ACCEPT string */
    protected string $_accept = '';
    /** @var string|null Platform the browser is running on. */
    protected string $_platform = '';
    /** @var boolean Is this a mobile browser? */
    protected bool $_mobile = false;

    /** @var string */
    public string $method;
    /** @var string */
    public string $cookie;
    /** @var string */
    public string $post;
    /** @var array */
    public array $header = [];
    /** @var string */
    public string $body = '';
    /** @var string */
    public string $request_data = '';
    /** @var array */
    public array $data;


    /** @var string */
    public string $content_type;
    /** @var string 错误信息 */
    public string $error_msg;
    /** @var int 错误代码 */
    public int $error_code;
    /** @var bool */
    public bool $is_ok = false;


    /** @var array Known robots. */
    protected array $_robots = [
        /* The most common ones. */
        'Googlebot',
        'msnbot',
        'Slurp',
        'Yahoo',
        /* The rest alphabetically. */
        'Arachnoidea',
        'ArchitextSpider',
        'Ask Jeeves',
        'B-l-i-t-z-Bot',
        'Baiduspider',
        'BecomeBot',
        'cfetch',
        'ConveraCrawler',
        'ExtractorPro',
        'FAST-WebCrawler',
        'FDSE robot',
        'fido',
        'geckobot',
        'Gigabot',
        'Girafabot',
        'grub-client',
        'Gulliver',
        'HTTrack',
        'ia_archiver',
        'InfoSeek',
        'kinjabot',
        'KIT-Fireball',
        'larbin',
        'LEIA',
        'lmspider',
        'Lycos_Spider',
        'Mediapartners-Google',
        'MuscatFerret',
        'NaverBot',
        'OmniExplorer_Bot',
        'polybot',
        'Pompos',
        'Scooter',
        'Teoma',
        'TheSuBot',
        'TurnitinBot',
        'Ultraseek',
        'ViolaBot',
        'webbandit',
        'www.almaden.ibm.com/cs/crawler',
        'ZyBorg',
    ];

    /** @var array Features. */
    protected array $_features = [
        'html'       => true,
        'hdml'       => false,
        'wml'        => false,
        'images'     => true,
        'iframes'    => false,
        'frames'     => true,
        'tables'     => true,
        'java'       => true,
        'javascript' => true,
        'dom'        => false,
        'utf'        => false,
        'rte'        => false,
        'homepage'   => false,
        'accesskey'  => false,
        'optgroup'   => false,
        'xmlhttpreq' => false,
        'cite'       => false,
        'xhtml+xml'  => false,
        'mathml'     => false,
        'svg'        => false
    ];

    /** @var array Quirks */
    protected array $_quirks = [
        'avoid_popup_windows'        => false,
        'break_disposition_header'   => false,
        'break_disposition_filename' => false,
        'broken_multipart_form'      => false,
        'cache_same_url'             => false,
        'cache_ssl_downloads'        => false,
        'double_linebreak_textarea'  => false,
        'empty_file_input_value'     => false,
        'must_cache_forms'           => false,
        'no_filename_spaces'         => false,
        'no_hidden_overflow_tables'  => false,
        'ow_gui_1.3'                 => false,
        'png_transparency'           => false,
        'scrollbar_in_way'           => false,
        'scroll_tds'                 => false,
        'windowed_controls'          => false,
    ];

    /**
     * List of viewable image MIME subtypes.
     * This list of viewable images works for IE and Netscape/Mozilla.
     *
     * @var array
     */
    protected array $_images = ['jpeg', 'gif', 'png', 'pjpeg', 'x-png', 'bmp'];


    /**
     * Create a browser instance (Constructor).
     *
     * @param string $user_agent The browser string to parse.
     * @param string $accept The HTTP_ACCEPT settings to use.
     * @param string $uri
     */
    public function __construct($user_agent = '', $accept = '', $uri = '')
    {
        $this->header = [];
        $this->is_ok  = false;
        $this->body   = '';

        $this->method = 'GET';
        $this->cookie = '';
        $this->post   = '';

        $this->error_code = 0;
        $this->error_msg  = '';

        $this->match($user_agent, $accept);

        if ($uri !== null) {
            $this->parse($uri);
        }
    }

    /**
     * Parses the user agent string and inititializes the object with
     * all the known features and quirks for the given browser.
     *
     * @param string|null $userAgent The browser string to parse.
     * @param string|null $accept The HTTP_ACCEPT settings to use.
     */
    public function match($userAgent = null, $accept = null)
    {
        // Set our agent string.
        if (is_null($userAgent)) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $this->_agent = trim($_SERVER['HTTP_USER_AGENT']);
            }
        } else {
            $this->_agent = $userAgent;
        }
        $this->_lower_agent = strtolower($this->_agent);

        // Set our accept string.
        if (is_null($accept)) {
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                $this->_accept = strtolower(trim($_SERVER['HTTP_ACCEPT']));
            }
        } else {
            $this->_accept = strtolower($accept);
        }


        // Check if browser excepts content type xhtml+xml
        if (strpos($this->_accept, 'application/xhtml+xml')) {
            $this->feature_set('xhtml+xml');
        }

        // Check for a mathplayer plugin is installed, so we can use MathML on several browsers
        if (strpos($this->_lower_agent, 'mathplayer') !== false) {
            $this->feature_set('mathml');
        }

        // Check for UTF support.
        if (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
            $this->feature_set('utf', strpos(strtolower($_SERVER['HTTP_ACCEPT_CHARSET']), 'utf') !== false);
        }

        if (!empty($this->_agent)) {
            $this->platform_set();

            if (strpos($this->_lower_agent, 'mobileexplorer') !== false ||
                strpos($this->_lower_agent, 'openwave') !== false ||
                strpos($this->_lower_agent, 'opera mini') !== false ||
                strpos($this->_lower_agent, 'operamini') !== false) {
                $this->feature_set('frames', false);
                $this->feature_set('javascript', false);
                $this->quirk_set('avoid_popup_windows');
                $this->_mobile = true;
            } elseif (preg_match('|Opera[/ ]([0-9.]+)|', $this->_agent, $version)) {
                $this->browser_set('opera');
                list($this->major_version, $this->minor_version) = explode('.', $version[1]);
                $this->feature_set('javascript', true);
                $this->quirk_set('no_filename_spaces');

                if ($this->major_version >= 7) {
                    $this->feature_set('dom');
                    $this->feature_set('iframes');
                    $this->feature_set('accesskey');
                    $this->feature_set('optgroup');
                    $this->quirk_set('double_linebreak_textarea');
                }
            } elseif (strpos($this->_lower_agent, 'elaine/') !== false ||
                strpos($this->_lower_agent, 'palmsource') !== false ||
                strpos($this->_lower_agent, 'digital paths') !== false) {
                $this->browser_set('palm');
                $this->feature_set('images', false);
                $this->feature_set('frames', false);
                $this->feature_set('javascript', false);
                $this->quirk_set('avoid_popup_windows');
                $this->_mobile = true;
            } elseif ((preg_match('|MSIE ([0-9.]+)|', $this->_agent, $version)) ||
                (preg_match('|Internet Explorer/([0-9.]+)|', $this->_agent, $version))) {

                $this->browser_set('msie');
                $this->quirk_set('cache_ssl_downloads');
                $this->quirk_set('cache_same_url');
                $this->quirk_set('break_disposition_filename');

                if (strpos($version[1], '.') !== false) {
                    list($this->major_version, $this->minor_version) = explode('.', $version[1]);
                } else {
                    $this->major_version = $version[1];
                    $this->minor_version = 0;
                }

                /* IE (< 7) on Windows does not support alpha transparency in
                 * PNG images. */
                if (($this->major_version < 7) &&
                    preg_match('/windows/i', $this->_agent)) {
                    $this->quirk_set('png_transparency');
                }

                /* Some Handhelds have their screen resolution in the
                 * user agent string, which we can use to look for
                 * mobile agents. */
                if (preg_match('/; (120x160|240x280|240x320|320x320)\)/', $this->_agent)) {
                    $this->_mobile = true;
                }

                switch ($this->major_version) {
                    case 7:
                        $this->feature_set('javascript', 1.4);
                        $this->feature_set('dom');
                        $this->feature_set('iframes');
                        $this->feature_set('utf');
                        $this->feature_set('rte');
                        $this->feature_set('homepage');
                        $this->feature_set('accesskey');
                        $this->feature_set('optgroup');
                        $this->feature_set('xmlhttpreq');
                        $this->quirk_set('scrollbar_in_way');
                        break;

                    case 6:
                        $this->feature_set('javascript', 1.4);
                        $this->feature_set('dom');
                        $this->feature_set('iframes');
                        $this->feature_set('utf');
                        $this->feature_set('rte');
                        $this->feature_set('homepage');
                        $this->feature_set('accesskey');
                        $this->feature_set('optgroup');
                        $this->feature_set('xmlhttpreq');
                        $this->quirk_set('scrollbar_in_way');
                        $this->quirk_set('broken_multipart_form');
                        $this->quirk_set('windowed_controls');
                        break;

                    case 5:
                        if ($this->platform_get() == 'mac') {
                            $this->feature_set('javascript', 1.2);
                            $this->feature_set('optgroup');
                        } else {
                            // MSIE 5 for Windows.
                            $this->feature_set('javascript', 1.4);
                            $this->feature_set('dom');
                            $this->feature_set('xmlhttpreq');
                            if ($this->minor_version >= 5) {
                                $this->feature_set('rte');
                                $this->quirk_set('windowed_controls');
                            }
                        }
                        $this->feature_set('iframes');
                        $this->feature_set('utf');
                        $this->feature_set('homepage');
                        $this->feature_set('accesskey');
                        if ($this->minor_version == 5) {
                            $this->quirk_set('break_disposition_header');
                            $this->quirk_set('broken_multipart_form');
                        }
                        break;

                    case 4:
                        $this->feature_set('javascript', 1.2);
                        $this->feature_set('accesskey');
                        if ($this->minor_version > 0) {
                            $this->feature_set('utf');
                        }
                        break;

                    case 3:
                        $this->feature_set('javascript', 1.5);
                        $this->quirk_set('avoid_popup_windows');
                        break;
                }
            } elseif (preg_match('|amaya/([0-9.]+)|', $this->_agent, $version)) {
                $this->browser_set('amaya');
                $this->major_version = $version[1];
                if (isset($version[2])) {
                    $this->minor_version = $version[2];
                }
                if ($this->major_version > 1) {
                    $this->feature_set('mathml');
                    $this->feature_set('svg');
                }
                $this->feature_set('xhtml+xml');
            } elseif (preg_match('|W3C_Validator/([0-9.]+)|', $this->_agent, $version)) {
                $this->feature_set('mathml');
                $this->feature_set('svg');
                $this->feature_set('xhtml+xml');
            } elseif (preg_match('|ANTFresco/([0-9]+)|', $this->_agent, $version)) {
                $this->browser_set('fresco');
                $this->feature_set('javascript', 1.5);
                $this->quirk_set('avoid_popup_windows');
            } elseif (strpos($this->_lower_agent, 'avantgo') !== false) {
                $this->browser_set('avantgo');
                $this->_mobile = true;
            } elseif (preg_match('|Konqueror/([0-9]+)|', $this->_agent, $version) ||
                preg_match('|Safari/([0-9]+)\.?([0-9]+)?|', $this->_agent, $version)) {
                // Konqueror and Apple's Safari both use the KHTML
                // rendering engine.
                $this->browser_set('konqueror');
                $this->quirk_set('empty_file_input_value');
                $this->quirk_set('no_hidden_overflow_tables');
                $this->major_version = $version[1];
                if (isset($version[2])) {
                    $this->minor_version = $version[2];
                }

                if (strpos($this->_agent, 'Safari') !== false &&
                    $this->major_version >= 60) {
                    // Safari.
                    $this->feature_set('utf');
                    $this->feature_set('javascript', 1.4);
                    $this->feature_set('dom');
                    $this->feature_set('iframes');
                    if ($this->major_version > 125 ||
                        ($this->major_version == 125 &&
                            $this->minor_version >= 1)) {
                        $this->feature_set('accesskey');
                        $this->feature_set('xmlhttpreq');
                    }
                    if ($this->major_version > 522) {
                        $this->feature_set('svg');
                        $this->feature_set('xhtml+xml');
                    }
                } else {
                    // Konqueror.
                    $this->feature_set('javascript', 1.5);
                    switch ($this->major_version) {
                        case 3:
                            $this->feature_set('dom');
                            $this->feature_set('iframes');
                            $this->feature_set('xhtml+xml');
                            break;
                    }
                }
            } elseif (preg_match('|Mozilla/([0-9.]+)|', $this->_agent, $version)) {
                $this->browser_set('mozilla');
                $this->quirk_set('must_cache_forms');

                list($this->major_version, $this->minor_version) = explode('.', $version[1]);
                switch ($this->major_version) {
                    case 5:
                        if ($this->platform_get() == 'win') {
                            $this->quirk_set('break_disposition_filename');
                        }
                        $this->feature_set('javascript', 1.4);
                        $this->feature_set('dom');
                        $this->feature_set('accesskey');
                        $this->feature_set('optgroup');
                        $this->feature_set('xmlhttpreq');
                        $this->feature_set('cite');
                        if (preg_match('|rv:(.*)\)|', $this->_agent, $revision)) {
                            if ($revision[1] >= 1) {
                                $this->feature_set('iframes');
                            }
                            if ($revision[1] >= 1.3) {
                                $this->feature_set('rte');
                            }
                            if ($revision[1] >= 1.5) {
                                $this->feature_set('svg');
                                $this->feature_set('mathml');
                                $this->feature_set('xhtml+xml');
                            }
                        }
                        break;

                    case 4:
                        $this->feature_set('javascript', 1.3);
                        $this->quirk_set('buggy_compression');
                        break;

                    case 3:
                    default:
                        $this->feature_set('javascript', 1);
                        $this->quirk_set('buggy_compression');
                        break;
                }
            } elseif (preg_match('|Lynx/([0-9]+)|', $this->_agent, $version)) {
                $this->browser_set('lynx');
                $this->feature_set('images', false);
                $this->feature_set('frames', false);
                $this->feature_set('javascript', false);
                $this->quirk_set('avoid_popup_windows');
            } elseif (preg_match('|Links \(([0-9]+)|', $this->_agent, $version)) {
                $this->browser_set('links');
                $this->feature_set('images', false);
                $this->feature_set('frames', false);
                $this->feature_set('javascript', false);
                $this->quirk_set('avoid_popup_windows');
            } elseif (preg_match('|HotJava/([0-9]+)|', $this->_agent, $version)) {
                $this->browser_set('hotjava');
                $this->feature_set('javascript', false);
            } elseif (strpos($this->_agent, 'UP/') !== false ||
                strpos($this->_agent, 'UP.B') !== false ||
                strpos($this->_agent, 'UP.L') !== false) {
                $this->browser_set('up');
                $this->feature_set('html', false);
                $this->feature_set('javascript', false);
                $this->feature_set('hdml');
                $this->feature_set('wml');

                if (strpos($this->_agent, 'GUI') !== false &&
                    strpos($this->_agent, 'UP.Link') !== false) {
                    /* The device accepts Openwave GUI extensions for
                     * WML 1.3. Non-UP.Link gateways sometimes have
                     * problems, so exclude them. */
                    $this->quirk_set('ow_gui_1.3');
                }
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'Xiino/') !== false) {
                $this->browser_set('xiino');
                $this->feature_set('hdml');
                $this->feature_set('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'Palmscape/') !== false) {
                $this->browser_set('palmscape');
                $this->feature_set('javascript', false);
                $this->feature_set('hdml');
                $this->feature_set('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'Nokia') !== false) {
                $this->browser_set('nokia');
                $this->feature_set('html', false);
                $this->feature_set('wml');
                $this->feature_set('xhtml');
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'Ericsson') !== false) {
                $this->browser_set('ericsson');
                $this->feature_set('html', false);
                $this->feature_set('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_lower_agent, 'wap') !== false) {
                $this->browser_set('wap');
                $this->feature_set('html', false);
                $this->feature_set('javascript', false);
                $this->feature_set('hdml');
                $this->feature_set('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_lower_agent, 'docomo') !== false ||
                strpos($this->_lower_agent, 'portalmmm') !== false) {
                $this->browser_set('imode');
                $this->feature_set('images', false);
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'BlackBerry') !== false) {
                $this->browser_set('blackberry');
                $this->feature_set('html', false);
                $this->feature_set('javascript', false);
                $this->feature_set('hdml');
                $this->feature_set('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'MOT-') !== false) {
                $this->browser_set('motorola');
                $this->feature_set('html', false);
                $this->feature_set('javascript', false);
                $this->feature_set('hdml');
                $this->feature_set('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_lower_agent, 'j-') !== false) {
                $this->browser_set('mml');
                $this->_mobile = true;
            }
        }
    }


    public function post($url, $data = [], $referer = '', $limit = 0, $timeout = 30, $block = true)
    {
        $this->method       = 'POST';
        $this->content_type = "Content-Type: application/x-www-form-urlencoded\r\n";
        if ($data) {
            $post = '';
            foreach ($data as $k => $v) {
                $post .= $k . '=' . rawurlencode($v) . '&';
            }
            $this->post .= substr($post, 0, -1);
        }
        return $this->request($url, $referer, $limit, $timeout, $block);
    }

    public function get($url, $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
    {
        $this->method = 'GET';
        return $this->request($url, $referer, $limit, $timeout, $block);
    }

    public function upload(string $url, string $content_type, $data = [], $files = [], $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
    {
        $this->method       = 'POST';
        $boundary           = "AaB03x";
        $this->content_type = "Content-Type: multipart/form-data; boundary={$boundary}\r\n";
        if ($data) {
            foreach ($data as $k => $v) {
                $this->post .= "--{$boundary}\r\n";
                $this->post .= "Content-Disposition: form-data; name=\"" . $k . "\"\r\n";
                $this->post .= "\r\n" . $v . "\r\n";
                $this->post .= "--{$boundary}\r\n";
            }
        }
        foreach ($files as $k => $v) {
            $this->post .= "--{$boundary}\r\n";
            $this->post .= "Content-Disposition: file; name=\"{$k}\"; filename=\"" . basename($v) . "\"\r\n";
            $this->post .= "Content-Type: " . $content_type . "\r\n";
            $this->post .= "\r\n" . file_get_contents($v) . "\r\n";
            $this->post .= "--{$boundary}\r\n";
        }
        $this->post .= "--{$boundary}--\r\n";
        return $this->request($url, $referer, $limit, $timeout, $block);
    }

    protected function request($url, $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
    {
        $matches = parse_url($url);
        $host    = $matches['host'];
        $path    = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
        $port    = $matches['port'] ? $matches['port'] : 80;
        if ($referer == '') {
            $referer = $url;
        }
        $out = "$this->method $path HTTP/1.1\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Referer: $referer\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
        $out .= "Host: $host\r\n";
        if ($this->cookie) $out .= "Cookie: $this->cookie\r\n";
        if ($this->method == 'POST') {
            $out .= $this->content_type;
            $out .= "Content-Length: " . strlen($this->post) . "\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Connection: Close\r\n\r\n";
            $out .= $this->post;
        } else {
            $out .= "Connection: Close\r\n\r\n";
        }
        if ($timeout > ini_get('max_execution_time')) @set_time_limit($timeout);
        $fp = fsockopen($host, $port, $errno, $error, $timeout);
        if (!$fp) {
            $this->error_code = $errno;
            $this->error_msg  = $error;
            return false;
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            fwrite($fp, $out);
            $this->request_data = '';
            $status             = stream_get_meta_data($fp);
            if (!$status['timed_out']) {
                $maxsize = min($limit, 1024000);
                if ($maxsize == 0) $maxsize = 1024000;
                $start = false;
                while (!feof($fp)) {
                    if ($start) {
                        $line = fread($fp, $maxsize);
                        if (strlen($this->request_data) > $maxsize) break;
                        $this->request_data .= $line;
                    } else {
                        $line         = fgets($fp);
                        $this->header .= $line;
                        if ($line == "\r\n" || $line == "\n") $start = true;
                    }
                }
            }
            fclose($fp);
            return $this->is_ok();
        }
    }

    public function save($file)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            file::create($dir);
        }
        return file_put_contents($file, $this->data);
    }

    public function cookie_set($name, $value)
    {
        $this->cookie .= "$name=$value;";
    }

    public function cookie_get()
    {
        $cookies = [];
        $subject = implode('', $this->header);
        if (preg_match_all("|Set-Cookie: ([^;]*);|", $subject, $m)) {
            foreach ($m[1] as $c) {
                list($k, $v) = explode('=', $c);
                $cookies[$k] = $v;
            }
        }
        return $cookies;
    }

    public function data_get()
    {
        return $this->data;
    }

    public function header_get()
    {
        return $this->header;
    }

    public function status_get()
    {
        $subject = implode('', $this->header);
        preg_match("|^HTTP/1.1 ([0-9]{3}) (.*)|", $subject, $m);
        return [$m[1], $m[2]];
    }

    public function is_ok()
    {
        $status = $this->status_get();
        if (intval($status[0]) != 200) {
            $this->error_code = $status[0];
            $this->error_msg  = $status[1];
            return false;
        }
        return true;
    }

    /**
     * Match the platform of the browser.
     *
     * This is a pretty simplistic implementation, but it's intended
     * to let us tell what line breaks to send, so it's good enough
     * for its purpose.
     */
    public function platform_set()
    {
        if (strpos($this->_lower_agent, 'wind') !== false) {
            $this->_platform = 'win';
        } elseif (strpos($this->_lower_agent, 'mac') !== false) {
            $this->_platform = 'mac';
        } else {
            $this->_platform = 'unix';
        }
    }

    /**
     * Return the currently matched platform.
     *
     * @return string  The user's platform.
     */
    public function platform_get()
    {
        return $this->_platform;
    }


    /**
     * Retrieve the current browser's version.
     * @return string  The current browser's version.
     */
    public function version_get()
    {
        return $this->major_version . '.' . $this->minor_version;
    }

    /**
     * Return the full browser agent string.
     *
     * @return string  The browser agent string.
     */
    function agent_get()
    {
        return $this->_agent;
    }

    /**
     * Set unique behavior for the current browser.
     *
     * @param string $quirk The behavior to set.
     * @param bool $value Special behavior parameter.
     */
    function quirk_set(string $quirk, $value = true)
    {
        $this->_quirks[$quirk] = $value;
    }

    /**
     * Check unique behavior for the current browser.
     *
     * @param string $quirk The behavior to check.
     * @return boolean  Does the browser have the behavior set?
     */
    function quirk_has(string $quirk)
    {
        return !empty($this->_quirks[$quirk]);
    }

    /**
     * Retrieve unique behavior for the current browser.
     *
     * @param string $quirk The behavior to retrieve.
     * @return string  The value for the requested behavior.
     */
    function quirk_get(string $quirk)
    {
        return isset($this->_quirks[$quirk])
            ? $this->_quirks[$quirk]
            : null;
    }

    /**
     * Set capabilities for the current browser.
     *
     * @param string $feature The capability to set.
     * @param bool $value Special capability parameter.
     */
    function feature_set(string $feature, $value = true)
    {
        $this->_features[$feature] = $value;
    }


    /**
     * Check the current browser capabilities.
     * @param string $feature The capability to check.
     * @return boolean  Does the browser have the capability set?
     */
    function hasFeature($feature)
    {
        return !empty($this->_features[$feature]);
    }

    /**
     * Retrieve the current browser capability.
     *
     * @param string $feature The capability to retrieve.
     * @return string  The value of the requested capability.
     */
    function getFeature($feature)
    {
        return isset($this->_features[$feature])
            ? $this->_features[$feature]
            : null;
    }

    /**
     * Determines if a browser can display a given MIME type.
     *
     * @param string $mimetype The MIME type to check.
     * @return boolean  True if the browser can display the MIME type.
     */
    function is_viewable($mimetype)
    {
        $mimetype = strtolower($mimetype);
        list($type, $subtype) = explode('/', $mimetype);

        if (!empty($this->_accept)) {
            $wildcard_match = false;

            if (strpos($this->_accept, $mimetype) !== false) {
                return true;
            }

            if (strpos($this->_accept, '*/*') !== false) {
                $wildcard_match = true;
                if ($type != 'image') {
                    return true;
                }
            }

            /* image/jpeg and image/pjpeg *appear* to be the same
            * entity, but Mozilla doesn't seem to want to accept the
            * latter.  For our purposes, we will treat them the
            * same.
            */
            if ($this->is_browser('mozilla') &&
                ($mimetype == 'image/pjpeg') &&
                (strpos($this->_accept, 'image/jpeg') !== false)) {
                return true;
            }

            if (!$wildcard_match) {
                return false;
            }
        }

        if (!$this->hasFeature('images') || ($type != 'image')) {
            return false;
        }

        return (in_array($subtype, $this->_images));
    }

    /**
     * Determine if the given browser is the same as the current.
     *
     * @param string $browser The browser to check.
     * @return boolean  Is the given browser the same as the current?
     */
    public function is_browser($browser)
    {
        return ($this->browser === $browser);
    }

    /**
     * Determines if the browser is a robot or not.
     *
     * @return boolean  True if browser is a known robot.
     */
    public function is_robot()
    {
        foreach ($this->_robots as $robot) {
            if (strpos($this->_agent, $robot) !== false) {
                return true;
            }
        }
        return false;
    }




    public static function method_get()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function base_get()
    {
        if (!is_null(self::$_request_base)) {
            return self::$_request_base;
        }
        $base                = self::is_ssl() ? 'https://' : 'http://';
        $base                .= self::host_get();
        self::$_request_base = $base;
        return $base;
    }

    public static function url_get()
    {
        if (!is_null(self::$_request_url)) return self::$_request_url;
        $url                = self::is_ssl() ? 'https://' : 'http://';
        $url                .= self::host_get();
        $url                .= self::uri_get();
        self::$_request_url = $url;
        return $url;
    }

    public static function uri_get()
    {
        if (!is_null(self::$_request_uri)) {
            return self::$_request_uri;
        }

        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $uri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            $uri = '';
        }
        self::$_request_uri = $uri;
        return $uri;
    }

    public static function query_string_get()
    {
        return $_SERVER['QUERY_STRING'];
    }

    public static function pathinfo_get()
    {
        if (!is_null(self::$_pathinfo)) return self::$_pathinfo;

        if (!empty($_SERVER['PATH_INFO'])) {
            self::$_pathinfo = $_SERVER['PATH_INFO'];
            return $_SERVER['PATH_INFO'];
        }
        $pathinfo = substr(self::uri_get(), strlen(self::scriptname_get()));
        if (substr($pathinfo, 0, 1) == '/') {
            if ($_SERVER['QUERY_STRING']) $pathinfo = substr($pathinfo, 0, strpos($pathinfo, '?'));
            self::$_pathinfo = $pathinfo;
        }
        return self::$_pathinfo;
    }

    public static function scriptname_get()
    {
        $script = self::env_get('SCRIPT_NAME');
        return $script ? $script : self::env_get('ORIG_SCRIPT_NAME');
    }

    public static function referer_get()
    {
        return self::env_get('HTTP_REFERER');
    }

    public static function host_get()
    {
        $host = self::env_get('HTTP_X_FORWARDED_HOST');
        return $host ? $host : self::env_get('HTTP_HOST');
    }

    public static function language_get()
    {
        return self::env_get('HTTP_ACCEPT_LANGUAGE');
    }

    public static function charset_get()
    {
        return $_SERVER['HTTP_ACCEPT_CHARSET'];
    }

    public static function clientip_get()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match("/[\d.]{7,15}/", $ip, $matches) ? $matches[0] : 'unknown';
    }

    public static function env_get($key)
    {
        return isset($_SERVER[$key]) ? $_SERVER[$key] : (isset($_ENV[$key]) ? $_ENV[$key] : null);
    }


    public static function is_ssl()
    {
        return (strtolower(self::env_get('HTTPS')) === 'on'
            || strtolower(self::env_get('HTTP_SSL_HTTPS')) === 'on'
            || self::env_get('HTTP_X_FORWARDED_PROTO') == 'https'
            || getenv('SSL_PROTOCOL_VERSION'));
    }

    public static function is_xml_http_request()
    {
        return (self::env_get('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    public static function is_post()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public static function is_get()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public static function is_ie()
    {
        return strpos(self::env_get('HTTP_USER_AGENT'), 'MSIE') ? TRUE : FALSE;
    }

    public static function is_spider()
    {
        static $is_spider;
        if (!is_null($is_spider)) return $is_spider;
        $browsers = 'msie|netscape|opera|konqueror|mozilla';
        $spiders  = 'bot|spider|google|isaac|surveybot|baiduspider|yahoo|sohu-search|yisou|3721|qihoo|daqi|ia_archiver|p.arthur|fast-webcrawler|java|microsoft-atl-native|turnitinbot|webgather|sleipnir|msn';
        if (preg_match("/($browsers)/i", $_SERVER['HTTP_USER_AGENT'])) {
            $is_spider = FALSE;
        } elseif (preg_match("/($spiders)/i", $_SERVER['HTTP_USER_AGENT'])) {
            $is_spider = TRUE;
        }
        return $is_spider;
    }


    static public function base($is_full = true)
    {
        static $base;
        if (!isset($base)) {
            $uri            = static::i();
            $base['prefix'] = $uri->toString(['scheme', 'host', 'port']);
            if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI'])) {
                $base['path'] = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            } else {
                $base['path'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            }
        }
        return $is_full ? $base['prefix'] . $base['path'] . '/' : $base['path'];
    }

    static public function root($pathonly = false, $path = null)
    {
        static $root;
        if (!isset($root)) {
            $uri            = static::i(static::base());
            $root['prefix'] = $uri->toString(['scheme', 'host', 'port']);
            $root['path']   = rtrim($uri->toString(array('path')), '/\\');
        }

        if (isset($path)) {
            $root['path'] = $path;
        }
        return $pathonly === false ? $root['prefix'] . $root['path'] . '/' : $root['path'];
    }

    static public function current()
    {
        static $current;
        if (is_null($current)) {
            $uri     = static::i();
            $current = $uri->toString(['scheme', 'host', 'port', 'path']);
        }
        return $current;
    }

    static public function is_internal($url)
    {
        $uri  = static::i($url);
        $base = $uri->toString(['scheme', 'host', 'port', 'path']);
        $host = $uri->toString(['scheme', 'host', 'port']);
        if (stripos($base, static::base()) !== 0 && !empty($host)) {
            return false;
        }
        return true;
    }


    public function parse($uri)
    {
        $this->uri = $uri;
        if ($parts = parse_url($uri)) {
            $ret_val = true;
        } else {
            $ret_val = false;
        }

        if (isset($parts['query']) && strpos($parts['query'], '&amp;')) {
            $parts['query'] = str_replace('&amp;', '&', $parts['query']);
        }
        $this->scheme   = isset ($parts['scheme']) ? $parts['scheme'] : 'http';
        $this->user     = isset ($parts['user']) ? $parts['user'] : null;
        $this->pass     = isset ($parts['pass']) ? $parts['pass'] : null;
        $this->host     = isset ($parts['host']) ? $parts['host'] : null;
        $this->port     = isset ($parts['port']) ? $parts['port'] : null;
        $this->_path    = isset ($parts['path']) ? $parts['path'] : null;
        $this->_query   = isset ($parts['query']) ? $parts['query'] : null;
        $this->fragment = isset ($parts['fragment']) ? $parts['fragment'] : null;
        if (isset($parts['query'])) {
            parse_str($parts['query'], $this->query_vars);
        }
        return $ret_val;
    }

    public function toString($parts = ['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'])
    {
        $query = $this->query_get();
        $uri   = '';
        $uri   .= in_array('scheme', $parts) ? (!empty($this->scheme) ? $this->scheme . '://' : '') : '';
        $uri   .= in_array('user', $parts) ? $this->user : '';
        $uri   .= in_array('pass', $parts) ? (!empty($this->pass) ? ':' : '') . $this->pass . (!empty($this->user) ? '@' : '') : '';
        $uri   .= in_array('host', $parts) ? $this->host : '';
        $uri   .= in_array('port', $parts) ? (!empty($this->port) ? ':' : '') . $this->port : '';
        $uri   .= in_array('path', $parts) ? $this->_path : '';
        $uri   .= in_array('query', $parts) ? (!empty($query) ? '?' . $query : '') : '';
        $uri   .= in_array('fragment', $parts) ? (!empty($this->fragment) ? '#' . $this->fragment : '') : '';
        return $uri;
    }

    public function query_vars_set($name, $value)
    {
        $this->query_vars[$name] = $value;
        $this->_query            = null;
    }

    public function query_vars_get($name, $default = null)
    {
        return isset($this->query_vars[$name]) ? $this->query_vars[$name] : $default;
    }

    public function query_vars_del($name)
    {
        if (isset($this->query_vars[$name])) {
            unset($this->query_vars[$name]);
            $this->_query = null;
        }
    }

    public function query_set($query)
    {
        if (!is_array($query)) {
            if (strpos($query, '&amp;') !== false) {
                $query = str_replace('&amp;', '&', $query);
            }
            parse_str($query, $this->query_vars);
        } else {
            $this->query_vars = $query;
        }
        $this->_query = null;
    }

    public function query_get()
    {
        if (is_null($this->_query)) {
            $this->_query = http_build_query($this->query_vars);
        }
        return $this->_query;
    }

    public function path_get()
    {
        return $this->_path;
    }

    public function path_set($path)
    {
        $this->_path = $this->_clean_path($path);
    }

    public function https_is()
    {
        return $this->scheme === 'https';
    }

    /**
     * Returns the server protocol in use on the current server.
     *
     * @return string  The HTTP server protocol version.
     */
    public function http_protocol_get()
    {
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            if (($pos = strrpos($_SERVER['SERVER_PROTOCOL'], '/'))) {
                return substr($_SERVER['SERVER_PROTOCOL'], $pos + 1);
            }
        }
        return null;
    }

    public function _clean_path($path)
    {
        $path = preg_replace('#(/+)#', '/', $path);
        if (strpos($path, '.') === false) return $path;
        $path = explode('/', $path);
        for ($i = 0; $i < count($path); $i++) {
            if ($path[$i] == '.') {
                unset($path[$i]);
                $path = array_values($path);
                $i--;
            } elseif ($path[$i] == '..') {
                if ($i == 1 and $path[0] == '') {
                    unset ($path[$i]);
                    $path = array_values($path);
                    $i--;
                } elseif ($i > 1 or ($i == 1 and $path[0] != '')) {
                    unset($path[$i]);
                    unset($path[$i - 1]);
                    $path = array_values($path);
                    $i    -= 2;
                }
            } else {
                continue;
            }
        }
        return implode('/', $path);
    }

    /**
     * 初始化
     *
     * @param array $data
     */
    protected static function _initialize(array $data = [])
    {

    }
}
