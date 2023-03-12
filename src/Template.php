<?php

namespace ByJG\JinjaPhp;

use ByJG\JinjaPhp\Undefined\DefaultUndefined;
use ByJG\JinjaPhp\Undefined\StrictUndefined;

class Template
{

    protected $template = null;
    protected $undefined = null;
    protected $variables = [];

    public function __construct($template)
    {
        $this->template = $template;
        $this->undefined = new StrictUndefined();
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

    protected function getVar($varName, $variables, $undefined = null) {
        $varAr = explode('.', trim($varName));
        $varName = $varAr[0];
        if (isset($variables[$varName])) {
            if (count($varAr) > 1) {
                return $this->getVar(implode(".", array_slice($varAr, 1)), $variables[$varName], $undefined);
            }
            return $variables[$varName];
        } else {
            if (is_null($undefined)) {
                $undefined = $this->undefined;
            }
            return $undefined->render($varName);
        }
    }
    
    protected function extractFilterValues($filterCommand) {
        $regex = '/([a-zA-Z0-9_-]+)\s*(\((.*)\))?/';
        preg_match($regex, $filterCommand, $matches);
        $filterName = $matches[1];
        // Split the parameters by comma, but not if it is inside quotes
        if (isset($matches[3])) {
            // get filter parameters without parenthesis and delimited by , (comma) not (inside quotes or single quotes)
            // $filterParams = preg_split('/(?<=[^\'"]),(?=[^\'"])/', $matches[3]);
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
    
    protected function applyFilter($values, $variables) {
        $content = trim(array_shift($values));
        $firstTime = true;
        do {
            $filterCommand = $this->extractFilterValues(array_shift($values));
            if ($firstTime) {
                $firstTime = false;
                if ($filterCommand[0] == "default") {
                    $default = isset($filterCommand[1][0]) ? $this->evaluateVariable($filterCommand[1][0], $variables) : "";
                    $content = $this->evaluateVariable($content, $variables, new DefaultUndefined($default));
                    continue;
                } else {
                    $content = $this->evaluateVariable($content, $variables);
                }
            }

            switch ($filterCommand[0]) {
                case "upper":
                    $content = strtoupper($content);
                    break;
                case "lower":
                    $content = strtolower($content);
                    break;
                case "join":
                    $delimiter = isset($filterCommand[1][0]) ? $this->evaluateVariable($filterCommand[1][0], $variables) : "";
                    $content = implode($delimiter, (array)$content);
                    break;
                case "replace":
                    $search = isset($filterCommand[1][0]) ? $this->evaluateVariable($filterCommand[1][0], $variables) : "";
                    $replace = isset($filterCommand[1][1]) ? $this->evaluateVariable($filterCommand[1][1], $variables) : "";
                    $content = str_replace($search, $replace, $content);
                    break;
            }
        } while (count($values) > 0);
        return $content;
    }
    
    protected function evaluateVariable($content, $variables, $undefined = null) {
        if (strpos($content, ' | ') !== false) {
            return $this->applyFilter(explode(" | ", $content), $variables);
        // } else if (strpos($content, ' is ') !== false) {
        //     $content = "{{ " . str_replace(' is ', '}}{{', $content) . " }}";
        //     return $this->parseVariables($content, $variables);
        // } else if (strpos($content, ' in ') !== false) {
        //     $content = "{{ " . str_replace(' in ', '}}{{', $content) . " }}";
        //     return $this->parseVariables($content, $variables);       
        } else if (preg_match('/\s*~\s*/', $content) ) {
            $array = preg_split('/\s*~\s*/', $content);
            for ($i = 0; $i < count($array); $i++) {
                $array[$i] = $this->evaluateVariable($array[$i], $variables);
            }
            return implode("", $array);
        } else if (preg_match('/^["\'].*["\']$/', trim($content)) || is_numeric(trim($content)) || trim($content) == "true" || trim($content) == "false") {
            $valueToEvaluate = $content;
        // parse variables inside parenthesis
        } else if (preg_match('/\((.*)\)/', $content, $matches)) {
            $content = preg_replace_callback('/\((.*)\)/', function($matches) use (&$valueToEvaluate, $variables) {
                return $this->evaluateVariable($matches[1], $variables);
            }, $content);
            $valueToEvaluate = $this->evaluateVariable($content, $variables);
        // match content with the array representation
        } else if (preg_match('/^\[.*\]$/', trim($content))) {
            $array = preg_split('/\s*,\s*/', trim($content, "[]"));
            for ($i = 0; $i < count($array); $i++) {
                $array[$i] = $this->evaluateVariable($array[$i], $variables);
            }
            return $array;
        } else if (preg_match('/(<=|>=|==|!=|<>|\*\*|&&\|\||[\+\-\/\*\%\<\>])/', $content) ) {
            $array = preg_split('/(<=|>=|==|!=|<>|\*\*|&&\|\||[\+\-\/\*\%\<\>])/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
            for ($i = 0; $i < count($array); $i=$i+2) {
                $array[$i] = $this->evaluateVariable($array[$i], $variables);
                if (is_string($array[$i])) {
                    $array[$i] = "'" . $array[$i] . "'";
                }
            }
            $valueToEvaluate = implode("", $array);
        } else {
            $var = $this->getVar($content, $variables, $undefined);
            if (is_array($var)) {
                return $var;
            }
            $valueToEvaluate = "'" . $this->getVar($content, $variables, $undefined) . "'";
        }
    
        $evalResult = "";
        eval("\$evalResult = $valueToEvaluate;");
        return $evalResult;
    }
    
    protected function parseIf($partialTemplate, $variables = [])
    {
        // Find {%if%} and {%endif%} and replace the content between them
        $regex = '/\{%\s*if(.*)\%}(.*)\{%\s*endif\s*\%}/sU';
        $result = preg_replace_callback($regex, function ($matches) use ($variables) {
            $condition = trim($matches[1]);
            if ($this->evaluateVariable($condition, $variables)) {
                return $matches[2];
            };
            return "";
        }, $partialTemplate);
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
    
    
    protected function parseVariables($partialTemplate, $variables) {
        // Find {{}} and replace the content between them
        $regex = '/\{\{(.*)\}\}/U';
        $result = preg_replace_callback($regex, function ($matches) use ($variables) {
            // if contains any math operation, evaluate it
            return $this->evaluateVariable($matches[1], $variables);
        }, $partialTemplate);
        return $result;
    }

}