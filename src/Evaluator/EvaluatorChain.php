<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Chain of responsibility for evaluators
 */
class EvaluatorChain
{
    /**
     * @var EvaluatorInterface[] Array of evaluators
     */
    private array $evaluators = [];

    /**
     * Adds an evaluator to the chain
     *
     * @param EvaluatorInterface $evaluator The evaluator to add
     * @return self
     */
    public function addEvaluator(EvaluatorInterface $evaluator): self
    {
        $this->evaluators[] = $evaluator;
        return $this;
    }

    /**
     * Evaluates content by finding the first evaluator that can handle it
     *
     * @param string $content The content to evaluate
     * @param array $variables The variables context
     * @param UndefinedInterface|null $undefined The strategy for handling undefined variables
     * @return mixed The evaluated result
     * @throws TemplateParseException If no evaluator can handle the content
     */
    public function evaluate(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed
    {
        $trimmedContent = trim($content);
        
        foreach ($this->evaluators as $evaluator) {
            if ($evaluator->canEvaluate($trimmedContent)) {
                return $evaluator->evaluate($trimmedContent, $variables, $undefined);
            }
        }
        
        throw new TemplateParseException("No evaluator found for content: $content");
    }
} 