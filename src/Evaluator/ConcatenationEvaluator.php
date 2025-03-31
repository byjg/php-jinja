<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Evaluator for string concatenation using the tilde (~) operator
 */
class ConcatenationEvaluator extends AbstractEvaluator
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function canEvaluate(string $content): bool
    {
        return preg_match('/\s*~\s*/', $content) === 1;
    }
    
    /**
     * {@inheritdoc}
     * @throws TemplateParseException
     */
    #[\Override]
    public function evaluate(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed
    {
        $array = preg_split('/\s*~\s*/', $content);
        
        $result = [];
        foreach ($array as $part) {
            $result[] = $this->evaluateValue($part, $variables, $undefined);
        }
        
        return implode("", $result);
    }
} 