<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\middleware;


use Closure;
use ounun\interfaces\middleware_interface;

class allow_cross_domain implements middleware_interface
{
    protected string $cookie_domain;

    protected array $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
    ];

    public function __construct($config)
    {
        $this->cookie_domain = $config['cookie']['domain'] ?? '';
    }


    public function handle(Closure $next, string $origin = '', ?array $header = null)
    {
        $header = $header ? $this->header : array_merge($this->header, $header);

        if (!isset($header['Access-Control-Allow-Origin'])) {
            if ($origin && ('' == $this->cookie_domain || strpos($origin, $this->cookie_domain))) {
                $header['Access-Control-Allow-Origin'] = $origin;
            } else {
                $header['Access-Control-Allow-Origin'] = '*';
            }
        }
        return $next();
    }
}
