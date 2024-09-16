<?php

namespace ByJG\JinjaPhp\Loader;

use ByJG\JinjaPhp\Template;

class StringLoader implements LoaderInterface
{
    /**
     * @param string $template
     * @return Template
     */
    public function getTemplate(string $template): Template
    {
        return new Template($template);
    }
}