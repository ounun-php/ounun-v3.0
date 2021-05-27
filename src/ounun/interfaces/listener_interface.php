<?php


namespace ounun\interfaces;


interface listener_interface
{
    public function handle($event): void;
}
