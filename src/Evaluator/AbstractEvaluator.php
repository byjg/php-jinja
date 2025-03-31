<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Base abstract evaluator with common functionality
 */
abstract class AbstractEvaluator implements EvaluatorInterface
{
    /**
     * @var EvaluatorChain The evaluator chain
     */
    protected EvaluatorChain $evaluatorChain;

    /**
     * @param EvaluatorChain $evaluatorChain The evaluator chain
     */
    public function __construct(EvaluatorChain $evaluatorChain)
    {
        $this->evaluatorChain = $evaluatorChain;
    }

    protected function getWorkingContent(string $content): string
    {
        $workingContent = trim($content);
        $quoteMap = [];
        return preg_replace_callback('/([\'"])(.*?)\1/', function($matches) use (&$quoteMap) {
            $placeholder = '___QUOTED_' . count($quoteMap) . '___';
            $quoteMap[$placeholder] = $matches[0];
            return $placeholder;
        }, $workingContent);
    }

    /**
     * Helper method to evaluate a value through the chain
     *
     * @param string $content The content to evaluate
     * @param array $variables The variables context
     * @param UndefinedInterface|null $undefined The strategy for handling undefined variables
     * @return mixed The evaluated result
     * @throws TemplateParseException
     */
    protected function evaluateValue(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed
    {
        return $this->evaluatorChain->evaluate($content, $variables, $undefined);
    }

    /**
     * Evaluates the final PHP expression
     *
     * @param mixed $valueToEvaluate The value to evaluate
     * @return mixed The evaluated result
     */
    protected function evalPhpExpression(mixed $valueToEvaluate): mixed
    {
        // Return boolean values directly
        if (is_bool($valueToEvaluate)) {
            return $valueToEvaluate;
        }
        
        // Arrays should be returned as is
        if (is_array($valueToEvaluate)) {
            return $valueToEvaluate;
        }

        // Use PHP's eval function to evaluate the final expression
        $evalResult = null;
        eval("\$evalResult = $valueToEvaluate;");
        return $evalResult;
    }

    /**
     * Adds slashes to a string
     *
     * @param string $value The value to escape
     * @return string The escaped value
     */
    protected function addSlashes(string $value): string
    {
        return preg_replace('/([\'\\\\])/', '\\\\$1', $value);
    }
} 