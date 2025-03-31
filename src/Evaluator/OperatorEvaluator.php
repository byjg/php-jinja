<?php

namespace ByJG\JinjaPhp\Evaluator;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Evaluator for expressions with operators (math, comparison, logic, 'in' check)
 */
class OperatorEvaluator extends AbstractEvaluator
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function canEvaluate(string $content): bool
    {
        return preg_match('/( in |<=|>=|==|!=|<>|\*\*|&&|\|\|| and | or |[\+\-\/\*\%\<\>])/', $content) === 1;
    }
    
    /**
     * {@inheritdoc}
     * @throws TemplateParseException
     */
    #[\Override]
    public function evaluate(string $content, array $variables, ?UndefinedInterface $undefined = null): mixed
    {
        // Replace content inside quotes with placeholders to avoid false matches
        $quoteMap = [];
        $workingContent = preg_replace_callback('/([\'"])(.*?)\1/', function($matches) use (&$quoteMap) {
            $placeholder = '___QUOTED_' . count($quoteMap) . '___';
            $quoteMap[$placeholder] = $matches[0];
            return $placeholder;
        }, $content);
        
        // Split by operators
        $array = preg_split('/( in |<=|>=|==|!=|<>|\*\*|&&|\|\|| and | or |[\+\-\/\*\%\<\>])/', $workingContent, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        // Restore quoted text in the result
        foreach ($array as &$part) {
            foreach ($quoteMap as $placeholder => $original) {
                $part = str_replace($placeholder, $original, $part);
            }
        }
        
        // Evaluate each part of the expression
        for ($i = 0; $i < count($array); $i = $i + 2) {
            $array[$i] = trim($array[$i]);
            $array[$i] = $this->evaluateValue($array[$i], $variables, $undefined);
            
            if (is_string($array[$i]) && !str_starts_with($array[$i], "'")) {
                $array[$i] = "'" . $this->addSlashes($array[$i]) . "'";
            } else if (is_bool($array[$i])) {
                $array[$i] = $array[$i] ? "true" : "false";
            }
            
            // Special handling for array merging with + operator
            if ($i > 0 && is_array($array[$i-2]) && is_array($array[$i]) && trim($array[$i-1]) == "+") {
                $array[$i-2] = json_encode(array_merge($array[$i-2], $array[$i]));
                $array[$i-1] = "";
                $array[$i] = "";
            }
        }
        
        // Convert 'and' to '&&' and 'or' to '||' for PHP evaluation
        for ($i = 1; $i < count($array); $i = $i + 2) {
            $operator = trim($array[$i]);
            if ($operator === "and") {
                $array[$i] = "&&";
            } else if ($operator === "or") {
                $array[$i] = "||";
            }
        }
        
        // Special handling for 'in' operator
        $inIndex = array_search(" in ", $array);
        if ($inIndex !== false) {
            $inIndex = intval($inIndex);
            // Check if value exists in array
            if (is_array($array[$inIndex+1])) {
                $valueToCompare = $this->evaluateValue($array[$inIndex-1], $variables, $undefined);
                $array[$inIndex] = in_array($valueToCompare, (array)$array[$inIndex+1]) ? "true" : "false";
                $array[$inIndex+1] = "";
                $array[$inIndex-1] = "";
            }
            // Check if substring exists in string
            elseif (is_string($array[$inIndex+1])) {
                $array[$inIndex] = str_contains(
                    $this->evaluateValue($array[$inIndex+1], $variables, $undefined), 
                    $this->evaluateValue($array[$inIndex-1], $variables, $undefined)
                ) ? "true" : "false";
                $array[$inIndex+1] = "";
                $array[$inIndex-1] = "";
            }
        }
        
        // Ensure all values in the array are strings before imploding
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = json_encode($value);
            } elseif (!is_scalar($value) && !is_null($value)) {
                $value = '';
            }
        }

        /** @psalm-suppress InvalidArgument */
        $valueToEvaluate = implode(" ", $array);
        return $this->evalPhpExpression($valueToEvaluate);
    }
} 