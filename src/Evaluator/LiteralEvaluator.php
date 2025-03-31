<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Evaluator for literal values (strings, numbers, booleans)
 */
class LiteralEvaluator extends AbstractEvaluator
{
    /**
     * {@inheritdoc}
     */
    public function canEvaluate(string $content): bool
    {
        // Detect strings, numbers, and boolean literals
        return !str_contains($this->getWorkingContent($content), ' in ') &&
            (preg_match('/^["\'].*["\']$/', $content) || 
             is_numeric($content) || 
             $content === "true" || 
             $content === "false");
    }
    
    /**
     * {@inheritdoc}
     */
    public function evaluate(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed
    {
        return $this->evalPhpExpression($content);
    }
} 