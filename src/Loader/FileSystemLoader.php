<?php

namespace ByJG\JinjaPhp\Loader;

use ByJG\JinjaPhp\Exception\LoaderException;
use ByJG\JinjaPhp\Template;
use Override;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var string
     */
    protected string $path;

    /**
     * @var string
     */
    protected mixed $extension;

    /**
     * FileSystemLoader constructor.
     * @param string $path
     * @param string $extension
     */
    public function __construct(string $path, string $extension = '.jinja')
    {
        $this->path = $path;
        $this->extension = $extension;

        if (!is_dir($this->path)) {
            throw new LoaderException("The path '$this->path' is not a valid directory.");
        }

        if (!is_readable($this->path)) {
            throw new LoaderException("The path '$this->path' is not readable.");
        }

        if (!file_exists($this->path)) {
            throw new LoaderException("The path '$this->path' does not exist.");
        }
    }

    /**
     * @param string $template
     * @return Template
     */
    #[Override]
    public function getTemplate(string $template): Template
    {
        $filename = $this->path . DIRECTORY_SEPARATOR . $template;
        if (!str_ends_with($filename, $this->extension)) {
            $filename .= $this->extension;
        }

        // The file has to be inside the templates directory once the path is resolved, so a name
        // with "../" cannot reach anything else. A missing file gives the same answer.
        $realFilename = realpath($filename);
        $realPath = rtrim((string)realpath($this->path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($realFilename === false || !str_starts_with($realFilename, $realPath)) {
            throw new LoaderException("The template '$filename' does not exist.");
        }

        return new Template((string)file_get_contents($realFilename));
    }
}