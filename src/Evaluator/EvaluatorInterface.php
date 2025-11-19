<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Interface for variable expression evaluators
 */
interface EvaluatorInterface
{
    /**
     * Evaluates if this evaluator can handle the given content
     *
     * @param string $content The content to check
     * @return bool True if this evaluator can handle the content
     */
    public function canEvaluate(string $content): bool;

    /**
     * Evaluates the content and returns the result
     *
     * @param string $content The content to evaluate
     * @param array $variables The variables context
     * @param UndefinedInterface|null $undefined The strategy for handling undefined variables
     * @return mixed The evaluated result
     */
    public function evaluate(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed;
} 