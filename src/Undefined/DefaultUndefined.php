<?php

namespace ByJG\JinjaPhp\Undefined;

class DefaultUndefined implements UndefinedInterface
{
    protected $default = '';

    public function __construct($default = '')
    {
        $this->default = $default;
    }

    public function render($varName)
    {
        return $this->default;
    }
}