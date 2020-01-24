<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Template Class
 *
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function is_array;
use function is_readable;

class Template
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
     * @param array $vars
     */
    public function setVars($vars)
    {
        if (!is_array($vars)) {
            $vars = [];
        }
        $this->vars = $vars;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function include($name)
    {
        $template = $this->templateDirectory . $name . '.php';
        if (!is_readable($template)) {
            return false;
        }
        include($template);

        return true;
    }

    /**
     * @param string $name
     * @return string|mixed
     */
    public function var($name)
    {
        if (!isset($this->vars[$name])) {
            return '';
        }

        return $this->vars[$name];
    }
}
