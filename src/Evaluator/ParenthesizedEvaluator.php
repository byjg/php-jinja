<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;
use Override;

/**
 * Evaluator for parenthesized expressions like (x + y)
 */
class ParenthesizedEvaluator extends AbstractEvaluator
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function canEvaluate(string $content): bool
    {
        return preg_match('/\((.*)\)/', $this->getWorkingContent($content)) === 1;
    }
    
    /**
     * {@inheritdoc}
     * @throws TemplateParseException
     */
    #[Override]
    public function evaluate(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed
    {
        // Evaluate the expression inside parentheses first
        $result = preg_replace_callback('/\((.*)\)/', function($matches) use ($variables, $undefined) {
            return $this->evaluateValue($matches[1], $variables, $undefined);
        }, $content);
        
        // Then evaluate the whole resulting expression
        return $this->evaluateValue($result, $variables, $undefined);
    }
} 