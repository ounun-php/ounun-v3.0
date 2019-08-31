<?php

namespace ounun\spl;

class subject implements \SplSubject
{
    /** @var \SplObjectStorage */
    protected $_storage;

    /** @var array 当前事件 */
    public $event_paras;

    /**
     * subject constructor.
     */
    public function __construct()
    {
        $this->_storage = new \SplObjectStorage();
    }

    /**
     * @param \SplObserver $observer
     */
    public function attach(\SplObserver $observer)
    {
        $this->_storage->attach($observer);
    }

    /**
     * @param \SplObserver $observer
     */
    public function detach(\SplObserver $observer)
    {
        $this->_storage->detach($observer);
    }

    /**
     * @param string $event
     * @param array $paras
     */
    public function notify(string $event = '',array $paras = [])
    {
        if ($event) {
            $this->event_paras = [$event,$paras];
        }
        $this->_storage->rewind();
        while ($this->_storage->valid()) {
            $this->_storage->current()->update($this);
            $this->_storage->next();
        }
    }
}
