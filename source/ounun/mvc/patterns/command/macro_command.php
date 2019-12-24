<?php
namespace ounun\mvc\patterns\command;

use ounun\mvc\interfaces\icommand;
use ounun\mvc\interfaces\inotification;
use ounun\mvc\patterns\observer\notifier;

/**
 * OununMVC Multicore Port to PHP
 */

/**
 * A base <b>icommand</b> implementation that executes other <b>icommand</b>s.
 *
 * A <b>MacroCommand</b> maintains an list of
 * <b>icommand</b> Class references called <i>SubCommands</i>.
 *
 * When <b>execute</b> is called, the <b>MacroCommand</b>
 * instantiates and calls <b>execute</b> on each of its <i>SubCommands</i> turn.
 * Each <i>sub_command</i> will be passed a reference to the original
 * <b>inotification</b> that was passed to the <b>MacroCommand</b>'s
 * <b>execute</b> method.
 *
 * Unlike <b>SimpleCommand</b>, your subclass
 * should not override <b>execute</b>, but instead, should
 * override the <b>initializeMacroCommand</b> method,
 * calling <b>addSubCommand</b> once for each <i>sub_command</i>
 * to be executed.
 *
 * @see controller
 * @see notification
 * @see macro_command
 * @package org.OununMVC.php.multicore
 */
class macro_command extends notifier implements icommand
{

    private $_sub_commands;

    /**
     * Constructor.
     *
     * Your subclass MUST define a constructor, be
     * sure to call <b>parent::__construct();</b> to
     * have PHP instanciate the whole parent/child chain.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_sub_commands = [];
        $this->initialize_command();
    }

    /**
     * Initialize the <b>MacroCommand</b>.
     *
     * In your subclass, override this method to
     * initialize the <b>MacroCommand</b>'s <i>sub_command</i>
     * list with <b>icommand</b> class references like
     * this:
     *
     * <code>
     *      // Initialize MyMacroCommand
     *      protected function initializeMacroCommand( ) : void
     *      {
     *          $this->addSubCommand( 'FirstCommand' );
     *          $this->addSubCommand( 'SecondCommand' );
     *          $this->addSubCommand( 'ThirdCommand' );
     *      }
     * </code>
     *
     * Note that <i>sub_command</i>s may be any <b>icommand</b> implementor,
     * <b>MacroCommand</b>s or <b>SimpleCommands</b> are both acceptable.
     *
     * @return void
     */
    protected function initialize_command()
    {
    }

    /**
     * Add a <i>sub_command</i>.
     *
     * The <i>SubCommands</i> will be called in First In/First Out (FIFO)
     * order.
     *
     * @param string $command_class_name The <b>Class name</b> of the <b>icommand</b>.
     * @return void
     */
    protected function sub_command_add(string $command_class_name )
    {
        array_push($this->_sub_commands,$command_class_name);
    }

    /**
     * Execute this <b>macro_command</b>'s <i>sub_commands</i>.
     *
     * The <i>sub_commands</i> will be called in First In/First Out (FIFO)
     * order.
     *
     * @param inotification $notification The <b>inotification</b> object to be passsed to each <i>sub_command</i>.
     * @return void
     */
    public final function execute( inotification $notification )
    {
        while ( count($this->_sub_commands) > 0) {
            $command_class_name = array_shift( $this->_sub_commands );
            /** @var icommand $command_instance */
            $command_instance = new $command_class_name();
            $command_instance->initialize( $this->_core_tag );
            $command_instance->execute( $notification );
        }
    }

}
