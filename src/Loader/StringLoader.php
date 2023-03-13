<?php

namespace ByJG\JinjaPhp\Loader;

use ByJG\JinjaPhp\Template;

class StringLoader implements LoaderInterface
{
    /**
     * @param string $template
     * @return Template
     */
    public function getTemplate($template)
    {
        return new Template($template);
    }
}