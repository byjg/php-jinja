<?php

namespace ByJG\JinjaPhp\Undefined;

class DebugUndefined implements UndefinedInterface
{
        protected string $message = 'NOT_FOUND';

        public function render(string $varName): string
        {
            return "{{ $this->message: $varName }}";
        }
}

