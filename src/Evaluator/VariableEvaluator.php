<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Template;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Evaluator for variable references
 */
class VariableEvaluator extends AbstractEvaluator
{
    /**
     * @var Template Reference to the template for variable access
     */
    private Template $template;
    
    /**
     * @param EvaluatorChain $evaluatorChain The evaluator chain
     * @param Template $template The template instance
     */
    public function __construct(EvaluatorChain $evaluatorChain, Template $template) 
    {
        parent::__construct($evaluatorChain);
        $this->template = $template;
    }
    
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function canEvaluate(string $content): bool
    {
        // This evaluator is a fallback, returns true for any content
        // that wasn't handled by other evaluators
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function evaluate(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed
    {
        // Handle negation operator
        if (str_starts_with($content, "!")) {
            return !$this->evaluateValue(substr($content, 1), $variables, $undefined);
        }
        
        // Handle variable access
        $var = $this->template->getVar($content, $variables, $undefined);
        
        if (is_array($var)) {
            return $var;
        }
        
        $valueToEvaluate = "'" . $this->addSlashes($var) . "'";
        return $this->evalPhpExpression($valueToEvaluate);
    }
} 