<?php

namespace nsqphp\RequeueStrategy;

use nsqphp\Message\MessageInterface;

/**
 * Fixed delay requeue strategy
 * 
 * Retry all failed messages N times with X delay.
 * If failed finally, run a callback
 */
class FixedDelayCallback extends FixedDelay
{
    /**
     * callback called when hitting maxAttempts
     *
     * @var callable
     */
    private $_callback;

    /**
     * Constructor
     *
     * @param callable $callback
     * @param integer $maxAttempts
     * @param integer $delay
     */
    public function __construct($callback, $maxAttempts = 10, $delay = 50)
    {
        parent::__construct($maxAttempts, $delay);
        $this->_callback = $callback;
    }
    
    /**
     * Test if should requeue and with what delay
     * 
     * The message will contain how many attempts had been made _before_ we
     * made our attempt (which must have failed).
     * 
     * @param MessageInterface $msg
     * 
     * @return integer|NULL The number of milliseconds to delay for, if we 
     *      want to retry, or NULL to drop it on the floor
     */
    public function shouldRequeue(MessageInterface $msg)
    {
        $result = parent::shouldRequeue($msg);
        if ( !$result && $this->_callback) {
            call_user_func($this->_callback, $msg);
        }
        return $result;
    }
}