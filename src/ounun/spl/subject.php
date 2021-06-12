<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\spl;

class subject implements \SplSubject
{
    /** @var \SplObjectStorage */
    protected \SplObjectStorage $_storage;

    /** @var array 当前事件 */
    public array $event_paras;

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
     * @param string $event_status
     * @param string $event_action
     * @param array $paras
     */
    public function notify(string $event_status = '',string $event_action = '',array $paras = [])
    {
        if ($event_status) {
            if($event_action){
                $event = "{$event_status}_{$event_action}";
            }else{
                $event =  $event_status;
            }
            $this->event_paras = [$event,$paras];
        }
        $this->_storage->rewind();
        while ($this->_storage->valid()) {
            $this->_storage->current()->update($this);
            $this->_storage->next();
        }
    }
}
