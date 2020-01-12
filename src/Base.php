<?php
/**
 * Just Refs
 * Base Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function get_class;
use function gmdate;
use function htmlentities;
use function microtime;
use function print_r;
use function round;

class Base
{
    const VERSION = '0.2.5';

    public $verbose;

    protected $basePath = '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    protected $siteName = 'Just Refs';
    protected $timer = [];

    /**
     * @param string $message (optional)
     */
    protected function verbose($message = '')
    {
        if ($this->verbose) {
            print '<pre>' . gmdate('Y-m-d H:i:s') . ': ' . get_class($this) 
                . ': ' . htmlentities(print_r($message, true)) . '</pre>';
        }
    }

    /**
     * @param string $name
     */
    protected function startTimer($name)
    {
        $this->timer[$name] = microtime(true);
    }

    /**
     * @param string $name
     * @return float
     */
    protected function endTimer($name)
    {
        if (empty($this->timer[$name])) {
            return 0.0;
        }
        return round(microtime(true) - $this->timer[$name], 4);
    }
}
