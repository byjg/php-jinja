<?php

namespace ByJG\JinjaPhp;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Internal\PartialDocument;
use ByJG\JinjaPhp\Undefined\DefaultUndefined;
use ByJG\JinjaPhp\Undefined\StrictUndefined;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

class Template
{

    protected string $template;
    protected UndefinedInterface|null $undefined = null;
    protected array $variables = [];

    public function __construct(string $template)
    {
        $this->template = $template;
        $this->undefined = new StrictUndefined();
    }

    public function withUndefined(UndefinedInterface $undefined): static
    {
        $this->undefined = $undefined;
        return $this;
    }

    /**
     * @throws TemplateParseException
     */
    public function render(array $variables = []): string|array|null
    {
        $variables = $this->variables + $variables;
        return $this->parseVariables(
            $this->parseIf(
                $this->parseFor($variables),
                $variables),
            $variables
        );
    }

    /**
     * @throws TemplateParseException
     */
    protected function getVar(string $varName, array $variables, ?UndefinedInterface $undefined = null) {
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
    
    protected function extractFilterValues(string $filterCommand): array
    {
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

    /**
     * @throws TemplateParseException
     */
    protected function applyFilter(array $values, array $variables): mixed
    {
        $content = trim(array_shift($values)) ?? "";
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
                case "split":
                    $delimiter = isset($filterCommand[1][0]) ? $this->evaluateVariable($filterCommand[1][0], $variables) : "";
                    $content = explode($delimiter, $content);
                    break;
                case "trim":
                    $chars = isset($filterCommand[1][0]) ? $this->evaluateVariable($filterCommand[1][0], $variables) : null;
                    $content = is_null($chars) ? trim($content) : trim($content, $chars);
                    break;
                case "length":
                    /** @var array|string $content */
                    $content = is_array($content) ? count($content) : strlen($content);
                    break;
                case "capitalize":
                    $content = ucwords($content);
                    break;
            }
        } while (count($values) > 0);
        return $content;
    }

    protected function addSlashes(string $value): array|string
    {
        return preg_replace('/([\'\\\\])/', '\\\\$1', $value);
    }

    /**
     * @throws TemplateParseException
     */
    /**
     * Evaluates the given content string based on template variables and syntax rules.
     * This method handles variable interpolation, filter application, string concatenation,
     * array definition, and expression evaluation.
     *
     * @param string $content The template content to evaluate
     * @param array $variables The variables available in the template context
     * @param UndefinedInterface|null $undefined The strategy for handling undefined variables
     * @throws TemplateParseException If parsing or evaluation fails
     * @return mixed The evaluated result (string, array, boolean, etc.)
     */
    protected function evaluateVariable(string $content, array $variables, UndefinedInterface|null $undefined = null): mixed
    {
        $trimmedContent = trim($content);

        // Handle pipe filters (e.g. "var | upper | join(' ')")
        if (str_contains($content, ' | ')) {
            return $this->applyFilter(explode(" | ", $content), $variables);
        }

        // Handle string concatenation with ~ operator (e.g. "hello" ~ name ~ "!")
        if (preg_match('/\s*~\s*/', $content) ) {
            $array = preg_split('/\s*~\s*/', $content);
            for ($i = 0; $i < count($array); $i++) {
                $array[$i] = $this->evaluateVariable($array[$i], $variables);
            }
            return implode("", $array);
        }

        // Handle literals (strings, numbers, boolean values)
        if (!str_contains($trimmedContent, ' in ') && (preg_match('/^["\'].*["\']$/', $trimmedContent) || is_numeric($trimmedContent) || $trimmedContent == "true" || $trimmedContent == "false")) {
            return $this->evaluateValue($content);
        }

        // Handle expressions in parentheses
        if (preg_match('/\((.*)\)/', $content)) {
            $content = preg_replace_callback('/\((.*)\)/', function($matches) use ($variables) {
                return $this->evaluateVariable($matches[1], $variables);
            }, $content);
            return $this->evaluateValue($this->evaluateVariable($content, $variables));
        }

        // Handle array declarations with brackets (e.g. [1, 2, 'key': 'value'])
        if (preg_match('/^\[.*\]$/', $trimmedContent)) {
            $array = preg_split('/\s*,\s*/', trim($trimmedContent, "[]"));
            $retArray = [];
            for ($i = 0; $i < count($array); $i++) {
                $arData = preg_split('/\s*:\s*/', $array[$i]);
                // Handle key:value pairs
                if (count($arData) == 2) {
                    $retArray[trim($arData[0], "\"'")] = $this->evaluateVariable($arData[1], $variables);
                }
                // Handle regular array items
                else {
                    $retArray[$i] = $this->evaluateVariable($array[$i], $variables);
                }
            }
            return $retArray;
        }

        // Handle expressions with operators (math, comparison, logic, 'in' check)
        if (preg_match('/( in |<=|>=|==|!=|<>|\*\*|&&|\|\||[\+\-\/\*\%\<\>])/', $content) ) {
            $quotedTextMap = [];
            // Step 1: Temporarily replace content inside quotes with placeholders
            $parsedContent = preg_replace_callback('/([\'"])(.*?)\1/', function($matches) use (&$quotedTextMap) {
                static $i = 0;
                $placeholder = "___QUOTED_TEXT_" . $i++ . "___";
                $quotedTextMap[$placeholder] = $matches[0];
                return $placeholder;
            }, $content);

            if (preg_match('/( in |<=|>=|==|!=|<>|\*\*|&&|\|\||[\+\-\/\*\%\<\>])/', $parsedContent))
            {
                // Step 2: Now split by operators
                $array = preg_split('/( in |<=|>=|==|!=|<>|\*\*|&&|\|\||[\+\-\/\*\%\<\>])/', $parsedContent, -1, PREG_SPLIT_DELIM_CAPTURE);

                // Step 3: Restore quoted text in the result
                foreach ($array as &$part) {
                    foreach ($quotedTextMap as $placeholder => $original) {
                        $part = str_replace($placeholder, $original, $part);
                    }
                }

                // Evaluate each part of the expression
                for ($i = 0; $i < count($array); $i=$i+2) {
                    $array[$i] = trim($array[$i]);
                    $array[$i] = $this->evaluateVariable($array[$i], $variables);
                    if (is_string($array[$i]) && !str_starts_with($array[$i], "'")) {
                        $array[$i] = "'" . $this->addSlashes($array[$i]) . "'";
                    } else if (is_bool($array[$i])) {
                        $array[$i] = $array[$i] ? "true" : "false";
                    }
                    // Special handling for array merging with + operator
                    else if ($i > 0 && is_array($array[$i-2]) && is_array($array[$i]) && trim($array[$i-1]) == "+") {
                        $array[$i-2] = json_encode(array_merge($array[$i-2], $array[$i]));
                        $array[$i-1] = "";
                        $array[$i] = "";
                    }
                }

                // Special handling for 'in' operator
                $inIndex = array_search(" in ", $array);
                if ($inIndex !== false) {
                    // Check if value exists in array
                    if (is_array($array[$inIndex+1])) {
                        $valueToCompare = $this->evaluateVariable($array[$inIndex-1], $variables);
                        $array[$inIndex] = in_array($valueToCompare, (array)$array[$inIndex+1]) ? "true" : "false";
                        $array[$inIndex+1] = "";
                        $array[$inIndex-1] = "";
                    }
                    // Check if substring exists in string
                    elseif (is_string($array[$inIndex+1])) {
                        $array[$inIndex] = str_contains($this->evaluateVariable($array[$inIndex + 1], $variables), $this->evaluateVariable($array[$inIndex - 1], $variables)) ? "true" : "false";
                        $array[$inIndex+1] = "";
                        $array[$inIndex-1] = "";
                    }
                }
                /** @var array $array */
                $valueToEvaluate = implode(" ", $array);
                return $this->evaluateValue($valueToEvaluate);
            } else {
                return $trimmedContent;
            }
        }

        // Handle negation operator
        if (str_starts_with($trimmedContent, "!")) {
            return $this->evaluateValue($content);
        }

        // Handle variable access (treats as a string)
        $var = $this->getVar($content, $variables, $undefined);
        if (is_array($var)) {
            return $var;
        }
        $valueToEvaluate = "'" . $this->addSlashes($var) . "'";

        return $this->evaluateValue($valueToEvaluate);
    }

    protected function evaluateValue(mixed $valueToEvaluate): mixed
    {
        // Return boolean values directly
        if (is_bool($valueToEvaluate)) {
            return $valueToEvaluate;
        }

        // Use PHP's eval function to evaluate the final expression
        // This handles all the math, boolean logic, and other expressions
        $evalResult = "";
        eval("\$evalResult = $valueToEvaluate;");
        return $evalResult;
    }

    /**
     * @throws TemplateParseException
     */
    protected function prepareDocumentToParse(string $partialTemplate, string $startTag, string $endTag): PartialDocument
    {
        // count the number of {% $startTag %} and {% $endTag %} tags using regex
        $regex = '/\{%[+-]?\s*' . $startTag . '(.*)\%}/sU';
        preg_match_all($regex, $partialTemplate, $matches);
        $startTagCount = count($matches[0]);
        $regex = '/\{%\s*' . $endTag . '\s*\%}/sU';
        preg_match_all($regex, $partialTemplate, $matches);
        $endTagCount = count($matches[0]);
        if ($startTagCount != $endTagCount) {
            throw new TemplateParseException("The number of {% $startTag %} and {% $endTag %} tags does not match");
        }

        if ($startTagCount == 0) {
            return new PartialDocument(0, $partialTemplate);
        }

        // find all {% $startTag %} and replace then with {% $startTag00%} where 00 can be 01, 02, 03, etc.
        $iStartTag = 0;
        $iEndTag = [];
        $result = $partialTemplate;

        // Close the closest {% $endTag %} tags before opening a new {% $startTag %} tag
        $fixArray = /**
         * @return (mixed|null|string|string[])[]
         *
         * @psalm-return list{mixed, array<string>|mixed|null|string}
         */
        function ($iEndTag, $endTag, $result) use ($startTag): array {
            while (!empty($iEndTag)) {
                $i = array_pop($iEndTag);

                $iPosStartTag = strpos($result, ' ' . $startTag . str_pad($i, 2, "0", STR_PAD_LEFT) . " ");
                $iPosStartTagAfter = preg_match('/\{%[+-]?\s*' . $startTag . ' /sU', $result, $matchesTmpStartTag, PREG_OFFSET_CAPTURE, $iPosStartTag);
                $iPosEndTag = preg_match('/\{%[+-]?\s*' .  $endTag . '\s*[+-]?\%}/sU', $result, $matchesTmpEndTag, PREG_OFFSET_CAPTURE, $iPosStartTag);
            
                if (($iPosStartTagAfter && $iPosEndTag) && $matchesTmpStartTag[0][1] < $matchesTmpEndTag[0][1]) {
                    $iEndTag[] = $i;
                    break;
                }

                $regex = '/\{%(?<left>[+-])?\s*' .  $endTag . '\s*(?<right>[+-])?\%}/sU';
                $result = preg_replace_callback($regex, function ($matches) use ($i, $endTag) {
                    $left = $matches['left'] ?? '';
                    $right = $matches['right'] ?? '';

                    return "{%$left " .  $endTag . str_pad($i, 2, "0", STR_PAD_LEFT) . " $right%}";
                }, $result, 1);
            }

            return [$iEndTag, $result];
        };

        while ($iStartTag < $startTagCount) {
            $regex = '/\{%(?<left>[+-])?\s*' . $startTag . ' /sU';
            $iStartTag++;
            $result = preg_replace_callback($regex, function ($matches) use ($iStartTag, $startTag) {
                $left = $matches['left'] ?? '';

                return "{%$left " . $startTag . str_pad((string)$iStartTag, 2, "0", STR_PAD_LEFT) . " ";
            }, $result, 1);

            $iPosStartTag = strpos($result, ' ' . $startTag . str_pad((string)$iStartTag, 2, "0", STR_PAD_LEFT) . " ");
            $iPosStartTagAfter = preg_match('/\{%[+-]?\s*' . $startTag . ' /sU', $result, $matchesTmpStartTag, PREG_OFFSET_CAPTURE, $iPosStartTag);
            $iPosEndTag = preg_match('/\{%[+-]?\s*' .  $endTag . '\s*[+-]?\%}/sU', $result, $matchesTmpEndTag, PREG_OFFSET_CAPTURE, $iPosStartTag);

            if ($iPosStartTagAfter && $iPosEndTag && $matchesTmpEndTag[0][1] < $matchesTmpStartTag[0][1]) {
                $result = preg_replace_callback('/\{%(?<left>[+-])?\s*' .  $endTag . '\s*(?<right>[+-])?\%}/sU', function ($matches) use ($iStartTag, $endTag) {
                    $left = $matches['left'] ?? '';
                    $right = $matches['right'] ?? '';

                    return "{%$left " .  $endTag . str_pad((string)$iStartTag, 2, "0", STR_PAD_LEFT) . " $right%}";
                }, $result, 1);

                list($iEndTag, $result) = $fixArray($iEndTag, $endTag, $result);
            } else {
                $iEndTag[] = $iStartTag;
            }
        }

        list($iEndTag, $result) = $fixArray($iEndTag, $endTag, $result);

        return new PartialDocument($startTagCount, $result);
    }

    /**
     * @throws TemplateParseException
     */
    protected function parseIf(string $partialTemplate, array $variables = []): string
    {
        $partial = $this->prepareDocumentToParse($partialTemplate, "if", "endif");

        // Find {%if%} and {%endif%} and replace the content between them
        for ($i=1; $i <= $partial->startTagCount; $i++) {
            $position = str_pad((string)$i, 2, "0", STR_PAD_LEFT);

            $regex = '/\{%([+-])?\s*if' . $position . '(.*)([+-])?\%}(.*)\{%\s*endif' . $position . '\s*\%}/sU';
            $partial->result = preg_replace_callback($regex, function ($matches) use ($variables) {
                $leftWhiteSpace = trim($matches[1]);
                $condition = trim($matches[2]);
                $rightWhiteSpace = trim($matches[3]);
                $ifContent = $matches[4];
                
                // First split by 'else' to get the main parts
                $mainParts = preg_split('/\{%\s*else\s*\%}/', $ifContent);
                
                // Check if there are 'elif' or 'elseif' tags in the first part
                $elifPattern = '/\{%\s*(elif|elseif)\s+(.*?)\s*\%}/';
                if (preg_match_all($elifPattern, $mainParts[0], $elifMatches, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER)) {
                    // Extract the if, elif and else parts
                    $parts = [];
                    $conditions = [];
                    
                    // Start with the 'if' condition
                    $conditions[] = $condition;
                    
                    // Get positions of all elif tags
                    $positions = array_column($elifMatches[0], 1);
                    
                    // Extract the 'if' content (until first elif)
                    $parts[] = substr($mainParts[0], 0, $positions[0]);
                    
                    // Extract the 'elif' conditions
                    foreach ($elifMatches[2] as $match) {
                        $conditions[] = trim($match[0]);
                    }
                    
                    // Extract content between 'elif' tags
                    for ($j = 0; $j < count($positions) - 1; $j++) {
                        $startPos = $positions[$j] + strlen($elifMatches[0][$j][0]);
                        $length = $positions[$j+1] - $startPos;
                        $parts[] = substr($mainParts[0], $startPos, $length);
                    }
                    
                    // Add last 'elif' part (to the end)
                    if (!empty($positions)) {
                        $lastPos = end($positions) + strlen(end($elifMatches[0])[0]);
                        $parts[] = substr($mainParts[0], $lastPos);
                    }
                    
                    // Add 'else' part if it exists
                    $elsePart = isset($mainParts[1]) ? $mainParts[1] : "";
                    
                    // Evaluate conditions one by one
                    $return = "";
                    $foundMatch = false;
                    
                    for ($j = 0; $j < count($conditions); $j++) {
                        if ($this->evaluateVariable($conditions[$j], $variables)) {
                            $return = $parts[$j];
                            $foundMatch = true;
                            break;
                        }
                    }
                    
                    // If no condition was met and there's an else part
                    if (!$foundMatch && isset($mainParts[1])) {
                        $return = $elsePart;
                    }
                } else {
                    // Original behavior for if/else
                    $ifParts = $mainParts;
                    $return = "";
                    if ($this->evaluateVariable($condition, $variables)) {
                        $return = $ifParts[0];
                    } else if (isset($ifParts[1])) {
                        $return = $ifParts[1];
                    }
                }

                if ($leftWhiteSpace == "-") {
                    $return = ltrim($return);
                }
                if ($rightWhiteSpace == "-") {
                    $return = rtrim($return);
                }
                return $return;
            }, $partial->result);
        }
        return $partial->result;
    }

    /**
     * @throws TemplateParseException
     */
    protected function parseFor($variables, $forStart = 1, $forCount = null, $partialTemplate = null): string
    {
        if (empty($partialTemplate)) {
            $partialTemplate = $this->template;
        }
        if (empty($forCount)) {
            $partial = $this->prepareDocumentToParse($partialTemplate, "for", "endfor");
        } else {
            $partial = new PartialDocument($forCount, $partialTemplate);
        }

        // Find {%for%} and {%endfor%} and replace the content between them
        for ($i=$forStart; $i <= $partial->startTagCount; $i++) {
            $position = str_pad($i, 2, "0", STR_PAD_LEFT);

            $regex = '/\{%([-+])?\s*for' . $position . '(.*)\s*([-+])?\%}(.*)\{%\s*endfor' . $position . '\s*\%}/sU';
            $partial->result = preg_replace_callback($regex, function ($matches) use ($variables) {
        
                $content = "";
                $regexFor = '/\s*(?<key1>[\w\d_-]+)(\s*,\s*(?<key2>[\w\d_-]+))?\s+in\s+(?<array>.*)\s*/';
                $leftWhiteSpace = trim($matches[1]);
                $forExpression = trim($matches[2]);
                $rightWhiteSpace = trim($matches[3]);
                if (preg_match($regexFor, $forExpression, $matchesFor)) {
                    $array = $this->evaluateVariable($matchesFor["array"], $variables);
                    if (!empty($matchesFor["key2"])) {
                        $forKey = $matchesFor["key1"];
                        $forValue = $matchesFor["key2"];
                    } else {
                        $forKey = "__index";
                        $forValue = $matchesFor["key1"];
                    }
                    $index = 0;
                    $loop = [];
                    foreach ($array as $key => $value) {
                        $loop["first"] = $index == 0;
                        $loop["last"] = $index == count($array) - 1;
                        $loop["index"] = $index + 1;
                        $loop["index0"] = $index;
                        $loop["revindex"] = count($array) - $index;
                        $loop["revindex0"] = count($array) - $index - 1;
                        $loop["length"] = count($array);
                        $loop["even"] = $index % 2 == 0;
                        $loop["odd"] = $index % 2 == 1;

                        $loopControl = [
                            $forKey => $key, 
                            $forValue => $value
                        ];

                        // Find {% for00 %} and get the array with 00 pattern
                        $regexNestedFor = '/\{%\s*for(\d{2}).*\%}/sU';
                        if (preg_match_all($regexNestedFor, $matches[4], $matchesNestedFor)) {
                            foreach ($matchesNestedFor[1] as $matchNested) {
                                $matchNested = intval($matchNested);
                                $matches[4] = $this->parseFor($variables + $loopControl, $matchNested, $matchNested, $matches[4]);
                            }
                        }
                        
                        $forVariables = $variables + $loopControl + ["loop" => $loop];
                        $resultContent = $this->parseVariables($this->parseIf($matches[4], $forVariables), $forVariables);
                        if ($leftWhiteSpace == "-") {
                            $resultContent = ltrim($resultContent);
                        }
                        if ($rightWhiteSpace == "-") {
                            $resultContent = rtrim($resultContent);
                        }
                        $content .= $resultContent;
                        
                        $index++;
                    }
                }
        
                return $content;
            }, $partial->result);
        }

        return $partial->result;
    }

    /**
     * @param string $partialTemplate
     * @param array $variables
     * @return array|string|null
     * @throws TemplateParseException
     */
    protected function parseVariables(string $partialTemplate, array $variables): array|string|null
    {
        // Find {{}} and replace the content between them
        $regex = '/\{\{(.*)\}\}/U';
        return preg_replace_callback($regex, function ($matches) use ($variables) {
            // if contains any math operation, evaluate it
            return (string) $this->evaluateVariable($matches[1], $variables);
        }, $partialTemplate);
    }

}
