<?php
namespace ounun\mvc\patterns\command;
use ounun\mvc\interfaces\icommand;
use ounun\mvc\interfaces\inotification;
use ounun\mvc\patterns\observer\notifier;
/**
 * PureMVC Multicore Port to PHP
 */

/**
 * A base <b>icommand</b> implementation.
 *
 * Your subclass should override the <b>execute</b>
 * method where your business logic will handle the <b>inotification</b>.
 *
 * @see controller
 * @see notification
 * @see macro_command
 */
class simple_command extends notifier implements icommand
{

    /**
     * Constructor.
     *
     * Your subclass MUST define a constructor, be
     * sure to call <b>parent::__construct();</b> to
     * have PHP instanciate the whole parent/child chain.
     *
     * @return void
     */
//    public function __construct()
//    {
//        parent::__construct();
//    }

    /**
     * Fulfill the use-case initiated by the given <b>inotification</b>.
     *
     * In the Command Pattern, an application use-case typically
     * begins with some user action, which results in an <b>inotification</b> being broadcast, which
     * is handled by business logic in the <b>execute</b> method of an
     * <b>icommand</b>.
     *
     * @param inotification $notification the <b>inotification</b> to handle.
     * @return void
     */
    public function execute( inotification $notification )
    {

    }

}
