<?php

namespace ByJG\JinjaPhp;

use ByJG\JinjaPhp\Undefined\DebugUndefined;

class Template
{

    protected $template = null;
    protected $undefined = null;
    protected $variables = [];

    public function __construct($template)
    {
        $this->template = $template;
        $this->undefined = new DebugUndefined();
    }

    public function withUndefined($undefined)
    {
        $this->undefined = $undefined;
        return $this;
    }

    public function render($variables = [])
    {
        $this->variables = $variables;
        return $this->renderTemplate($this->template);
    }

    protected function renderTemplate($template, $variables = [])
    {
        $variables = $this->variables + $variables;
        return $this->parseVariables(
            $this->parseIf(
                $this->parseFor($variables),
                $variables),
            $variables
        );
    }

    protected function getVar($varName, $variables) {
        $varAr = explode('.', trim($varName));
        $varName = $varAr[0];
        if (isset($variables[$varName])) {
            if (count($varAr) > 1) {
                return $this->getVar(implode(".", array_slice($varAr, 1)), $variables[$varName]);
            }
            return $variables[$varName];
        } else {
            return $this->undefined->render($varName);
        }
    }
    
    protected function extractFilterValues($filterCommand) {
        $regex = '/([a-zA-Z0-9_-]+)\s*(\((.*)\))?/';
        preg_match($regex, $filterCommand, $matches);
        $filterName = $matches[1];
        // Split the parameters by comma, but not if it is inside quotes
        if (isset($matches[2])) {
            $filterParams = preg_split('/,(?=(?:[^"\']*[\'"][^\'"]*\'")*[^\'"]*$)/', $matches[2]);
        } else {
            $filterParams = [];
        }
        return [$filterName, $filterParams];
    }
    
    protected function applyFilter($values, $variables) {
        $content = $this->evaluateVariable(array_shift($values), $variables);
        do {
            $filterCommand = $this->extractFilterValues(array_shift($values));
            switch ($filterCommand[0]) {
                case "upper":
                    $content = strtoupper($content);
                    break;
                case "lower":
                    $content = strtolower($content);
                    break;
                case "join":
                    $content = implode($this->evaluateVariable($filterCommand[1][0], $variables), (array)$content);
                    break;
            }
        } while (count($values) > 0);
        return $content;
    }
    
    protected function evaluateVariable($content, $variables) {
        if (strpos($content, ' | ') !== false) {
            return $this->applyFilter(explode(" | ", $content), $variables);
        } else if (strpos($content, ' ~ ') !== false) {
            $content = "{{ " . str_replace(' ~ ', '}}{{', $content) . " }}";
            return $this->parseVariables($content, $variables);
        } else if (preg_match('/["\'\+\-\*\/\]\[]/', $content)) {
            $valueToEvaluate = $content;
        } else {
            $var = $this->getVar($content, $variables);
            if (is_array($var)) {
                return $var;
            }
            $valueToEvaluate = "'" . $this->getVar($content, $variables) . "'";
        }
    
        $evalResult = "";
        eval("\$evalResult = $valueToEvaluate;");
        return $evalResult;
    }
    
    protected function parseIf($texto, $variables = [])
    {
        // Find {%if%} and {%endif%} and replace the content between them
        $regex = '/\{%\s*if(.*)\%}(.*)\{%\s*endif\s*\%}/sU';
        $result = preg_replace_callback($regex, function ($matches) use ($variables) {
            $condition = trim($matches[1]);
            $regexCond = '/([^"\'\w\d])([\w_-]+)[^"\'\w\d]/';
            $condition = preg_replace_callback($regexCond, function ($matches) use ($variables) {
                return $matches[1] . "\"" . $variables[trim($matches[2])] . "\"";
            }, $condition);
            if ($this->evaluateVariable($condition, $variables)) {
                return $matches[2];
            };
            return "";
        }, $texto);
        return $result;
    }
    
    protected function parseFor($variables)
    {
        // Find {%for%} and {%endfor%} and replace the content between them
        $regex = '/\{%\s*for(.*)\s*\%}(.*)\{%\s*endfor\s*\%}/sU';
        $result = preg_replace_callback($regex, function ($matches) use ($variables) {
    
            $content = "";
            $regexFor = '/\s*([\w\d_-]+)\s+in\s+([\w\d_-]+)\s*/';
            if (preg_match($regexFor, $matches[1], $matchesFor)) {
                $array = $this->evaluateVariable($matchesFor[2], $variables);
                foreach ($array as $key => $value) {
                    $content .= $this->parseVariables($matches[2], $variables + [trim($matchesFor[1]) => $value]);
                }
            }
    
            return $content;
        }, $this->template);
        return $result;
    }
    
    
    protected function parseVariables($texto, $variables) {
        // Find {{}} and replace the content between them
        $regex = '/\{\{(.*)\}\}/U';
        $result = preg_replace_callback($regex, function ($matches) use ($variables) {
            // if contains any math operation, evaluate it
            return $this->evaluateVariable($matches[1], $variables);
        }, $texto);
        return $result;
    }

}