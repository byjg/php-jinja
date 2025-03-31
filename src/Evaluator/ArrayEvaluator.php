<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Evaluator for array declarations using [key: value] syntax
 */
class ArrayEvaluator extends AbstractEvaluator
{
    /**
     * {@inheritdoc}
     */
    public function canEvaluate(string $content): bool
    {
        return preg_match('/^\[.*\]$/', $content) === 1;
    }
    
    /**
     * {@inheritdoc}
     * @throws TemplateParseException
     */
    public function evaluate(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed
    {
        $array = preg_split('/\s*,\s*/', trim($content, "[]"));
        $result = [];
        
        foreach ($array as $i => $item) {
            $arData = preg_split('/\s*:\s*/', $item);
            
            // Handle key:value pairs
            if (count($arData) == 2) {
                $key = trim($arData[0], "\"'");
                $result[$key] = $this->evaluateValue($arData[1], $variables, $undefined);
            }
            // Handle regular array items
            else {
                $result[$i] = $this->evaluateValue($item, $variables, $undefined);
            }
        }
        
        return $result;
    }
} 