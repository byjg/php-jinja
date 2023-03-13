<?php

namespace ByJG\JinjaPhp\Loader;

use ByJG\JinjaPhp\Template;

interface LoaderInterface
{
    /**
     * @param string $template
     * @return Template
     */
    public function getTemplate($template);
}