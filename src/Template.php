<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Template Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function is_readable;
use function is_string;

class Template extends Base
{
    /**
     * @param string $templateDirectory - path to template directory, with trailing slash
     */
    private $templateDirectory = '..' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;

    /**
     * @param array $vars - variables for use in templates
     */
    private $vars = [];

    /**
     * include a template
     * @param string $name
     * @return bool
     */
    public function include(string $name): bool
    {
        $template = $this->templateDirectory . $name . '.php';
        if (!is_readable($template)) {
            return false;
        }
        try {
            include($template);
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }
    
    /**
     * set a single var
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, $value): bool
    {
        if (!is_string($name)) {
            return false;
        }
        $this->vars[$name] = $value;
        $this->verbose("set: $name: " . print_r($value, true));

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
