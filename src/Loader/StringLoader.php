<?php

namespace ByJG\JinjaPhp\Loader;

use ByJG\JinjaPhp\Template;
use Override;

class StringLoader implements LoaderInterface
{
    /**
     * @param string $template
     * @return Template
     */
    #[Override]
    public function getTemplate(string $template): Template
    {
        return new Template($template);
    }
}