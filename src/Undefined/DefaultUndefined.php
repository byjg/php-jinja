<?php

namespace ByJG\JinjaPhp\Undefined;

class DefaultUndefined implements UndefinedInterface
{
    protected string $default = '';

    public function __construct(string $default = '')
    {
        $this->default = $default;
    }

    public function render(string $varName): string
    {
        return $this->default;
    }
}