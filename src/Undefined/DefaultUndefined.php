<?php

namespace ByJG\JinjaPhp\Undefined;

class DefaultUndefined
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