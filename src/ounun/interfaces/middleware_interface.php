<?php


namespace ounun\interfaces;

interface middleware_interface
{
    public function handle(\Closure $next);
}
