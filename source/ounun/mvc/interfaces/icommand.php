<?php

namespace ounun\mvc\interfaces;

/**
 * OununMVC Multicore Port to PHP
 */

/**
 * The interface definition for a OununMVC Command.
 *
 * @see inotifier
 * @see inotification
 */
interface icommand extends inotifier
{
    /**
     * Execute Command
     *
     * Execute the <kbd>icommand</kbd>'s logic to handle a given <kbd>inotification</kbd>.
     *
     * @param inotification $notification The <b>inotification</b> to handle.
     * @return void
     */
    public function execute( inotification $notification );
}
