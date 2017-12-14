<?php

namespace nsqphp\RequeueStrategy;

use nsqphp\Message\MessageInterface;

/**
 * Fixed delay requeue strategy
 *
 * Retry all failed messages N times with "start * (growFactor^N)" delay.
 */
class GrowingDelayCallback extends DelaysList
{
    private $_callback;

    /**
     * Constructor
     *
     * @param callable $callback
     * @param int $maxAttempts
     * @param int $start ms
     * @param int $maxDelay ms
     * @param float $growFactor
     * @throws \InvalidArgumentException
     */
    public function __construct($callback, $maxAttempts = 10, $start = 1000, $maxDelay = 10000, $growFactor = 2)
    {
        $delays = array();
        $current = $start;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $delays[] = (int)$current;
            $current *= $growFactor;
            if ($current >= $maxDelay) {
                $current = $maxDelay;
            }
        }
        parent::__construct($maxAttempts, $delays);
        $this->_callback = $callback;
    }

    public function shouldRequeue(MessageInterface $msg)
    {
        $result = parent::shouldRequeue($msg);
        if ( !$result && $this->_callback) {
            call_user_func($this->_callback, $msg);
        }
        return $result;
    }
}
