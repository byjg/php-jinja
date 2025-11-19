<?php

namespace ByJG\JinjaPhp\Undefined;

use ByJG\JinjaPhp\Exception\TemplateParseException;

class StrictUndefined implements UndefinedInterface
{
        protected string $message = 'NOT_FOUND';

    /**
     * @throws TemplateParseException
     */
    #[\Override]
    public function render(string $varName): string
        {
            throw new TemplateParseException("Variable $varName not defined.");
        }
}

