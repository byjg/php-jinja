<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Template;
use ByJG\JinjaPhp\Undefined\DefaultUndefined;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Evaluator for filter expressions using the pipe (|) operator
 */
class FilterEvaluator extends AbstractEvaluator
{
    /**
     * @var Template Reference to the template for filter application
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
    public function canEvaluate(string $content): bool
    {
        return str_contains($content, ' | ');
    }

    /**
     * {@inheritdoc}
     * @throws TemplateParseException
     */
    public function evaluate(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed
    {
        return $this->applyFilter(explode(" | ", $content), $variables, $undefined);
    }

    /**
     * Applies filters to content
     *
     * @param array $values The filter chain values
     * @param array $variables The variables context
     * @param UndefinedInterface|null $undefined The undefined strategy
     * @return mixed The result after applying filters
     * @throws TemplateParseException
     */
    protected function applyFilter(array $values, array $variables, ?UndefinedInterface $undefined): mixed
    {
        $content = trim(array_shift($values) ?? "");
        $firstTime = true;
        
        do {
            $filterCommand = $this->extractFilterValues(array_shift($values));
            
            if ($firstTime) {
                $firstTime = false;
                if ($filterCommand[0] == "default") {
                    $default = isset($filterCommand[1][0]) 
                        ? $this->evaluateValue($filterCommand[1][0], $variables, $undefined) 
                        : "";
                    $content = $this->evaluateValue($content, $variables, new DefaultUndefined($default));
                    continue;
                } else {
                    $content = $this->evaluateValue($content, $variables, $undefined);
                }
            }

            $content = $this->applyFilterFunction($filterCommand[0], $content, $filterCommand[1] ?? [], $variables);
            
        } while (count($values) > 0);
        
        return $content;
    }
    
    /**
     * Extracts filter name and parameters
     *
     * @param string $filterCommand The filter command
     * @return array The filter name and parameters
     */
    protected function extractFilterValues(string $filterCommand): array
    {
        $regex = '/([a-zA-Z0-9_-]+)\s*(\((.*)\))?/';
        preg_match($regex, $filterCommand, $matches);
        
        $filterName = $matches[1];
        
        // Split the parameters by comma, but not if it is inside quotes
        if (isset($matches[3])) {
            if (preg_match_all("~'[^']++'|\([^)]++\)|[^,]++~", $matches[3], $filterParams)) {
                $filterParams = $filterParams[0];
            } else {
                $filterParams = [];
            }
        } else {
            $filterParams = [];
        }
        
        return [$filterName, $filterParams];
    }

    /**
     * Applies a specific filter function
     *
     * @param string $filterName The name of the filter
     * @param mixed $content The content to filter
     * @param array $params The filter parameters
     * @param array $variables The variables context
     * @return mixed The filtered content
     * @throws TemplateParseException
     */
    protected function applyFilterFunction(string $filterName, mixed $content, array $params, array $variables): mixed
    {
        switch ($filterName) {
            case "upper":
                return strtoupper($content);
                
            case "lower":
                return strtolower($content);
                
            case "join":
                $delimiter = isset($params[0]) ? $this->evaluateValue($params[0], $variables) : "";
                return implode($delimiter, (array)$content);
                
            case "replace":
                $search = isset($params[0]) ? $this->evaluateValue($params[0], $variables) : "";
                $replace = isset($params[1]) ? $this->evaluateValue($params[1], $variables) : "";
                return str_replace($search, $replace, $content);
                
            case "split":
                $delimiter = isset($params[0]) ? $this->evaluateValue($params[0], $variables) : "";
                return explode($delimiter, $content);
                
            case "trim":
                $chars = isset($params[0]) ? $this->evaluateValue($params[0], $variables) : null;
                return is_null($chars) ? trim($content) : trim($content, $chars);
                
            case "length":
                return is_array($content) ? count($content) : strlen($content);
                
            case "capitalize":
                return ucwords($content);
                
            default:
                // Return unmodified if filter not found
                return $content;
        }
    }
} 