<?php

namespace ByJG\JinjaPhp\Internal;

class PartialDocument
{
    public int $startTagCount;
    public string $result;

    public function __construct(int $startTagCount, string $result)
    {
        $this->startTagCount = $startTagCount;
        $this->result = $result;
    }
}