<?php

namespace ByJG\JinjaPhp\Undefined;

use Exception;

class StrictUndefined
{
        protected $message = 'NOT_FOUND';

        public function render($varName)
        {
            throw new Exception("Variable $varName not defined.");
        }
}

