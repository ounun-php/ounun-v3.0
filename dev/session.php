<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun;

use ounun\session\storage;

class session
{
    protected array $options = [];

    protected bool $started = false;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public function start()
    {
        if (!$this->started) {
            ini_set('session.gc_maxlifetime', $this->options['maxlifetime']);
            //session_cache_limiter($this->options['cache_limiter']);
            session_cache_expire($this->options['cache_expire']);
            session_set_cookie_params($this->options['cookie_lifetime'], $this->options['cookie_path'], $this->options['cookie_domain']);

            $storage = storage::i($this->options['storage'], $this->options);
            session_start();
            $this->started = true;
        }
    }
}
