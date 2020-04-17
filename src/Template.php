<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Template Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use Exception;

use function is_readable;
use function is_string;

class Template extends Base
{
    /**
     * @var string - path to template directory, with trailing slash
     */
    private $templateDirectory = '..' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;

    /**
     * @var array - variables for use in templates
     */
    private $vars = [];

    /**
     * include a template
     * @param string $name
     * @return bool
     */
    public function include(string $name)
    {
        $template = $this->templateDirectory . $name . '.php';
        if (!is_readable($template)) {
            return false;
        }
        try {
            include($template);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
    
            return false;
        }

        return true;
    }
    
    /**
     * set a single var
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function set(string $name, $value)
    {
        if (!is_string($name)) {
            return false;
        }
        $this->vars[$name] = $value;

        return true;
    }

    /**
     * get a var
     * @param string $name
     * @return string|mixed
     */
    public function get($name)
    {
        if (!isset($this->vars[$name])) {
            return '';
        }

        return $this->vars[$name];
    }
}
