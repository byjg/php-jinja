<?php

namespace ByJG\JinjaPhp\Undefined;

class DebugUndefined implements UndefinedInterface
{
        protected $message = 'NOT_FOUND';

        public function render($varName)
        {
            return "{{ {$this->message}: $varName }}";
        }
}

