<?php

namespace ByJG\JinjaPhp\Undefined;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use Exception;

class StrictUndefined implements UndefinedInterface
{
        protected $message = 'NOT_FOUND';

        public function render($varName)
        {
            throw new TemplateParseException("Variable $varName not defined.");
        }
}

