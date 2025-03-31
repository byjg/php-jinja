<?php

namespace ByJG\JinjaPhp;

use ByJG\JinjaPhp\Evaluator\ArrayEvaluator;
use ByJG\JinjaPhp\Evaluator\ConcatenationEvaluator;
use ByJG\JinjaPhp\Evaluator\EvaluatorChain;
use ByJG\JinjaPhp\Evaluator\FilterEvaluator;
use ByJG\JinjaPhp\Evaluator\LiteralEvaluator;
use ByJG\JinjaPhp\Evaluator\OperatorEvaluator;
use ByJG\JinjaPhp\Evaluator\ParenthesizedEvaluator;
use ByJG\JinjaPhp\Evaluator\VariableEvaluator;
use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Internal\PartialDocument;
use ByJG\JinjaPhp\Undefined\DefaultUndefined;
use ByJG\JinjaPhp\Undefined\StrictUndefined;
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

/**
 * Template engine with Jinja-like syntax
 */
class Template
{
    /**
     * @var string The template content
     */
    protected string $template;
    
    /**
     * @var UndefinedInterface|null Strategy for handling undefined variables
     */
    protected UndefinedInterface|null $undefined = null;
    
    /**
     * @var array Template variables
     */
    protected array $variables = [];
    
    /**
     * @var EvaluatorChain|null The evaluator chain
     */
    protected ?EvaluatorChain $evaluatorChain = null;

    /**
     * Creates a new template instance
     *
     * @param string $template The template content
     */
    public function __construct(string $template)
    {
        $this->template = $template;
        $this->undefined = new StrictUndefined();
    }

    /**
     * Sets the undefined variable handler
     *
     * @param UndefinedInterface $undefined The undefined handler
     * @return static
     */
    public function withUndefined(UndefinedInterface $undefined): static
    {
        $this->undefined = $undefined;
        return $this;
    }

    /**
     * Gets the evaluator chain, creating it if necessary
     *
     * @return EvaluatorChain The evaluator chain
     */
    protected function getEvaluatorChain(): EvaluatorChain
    {
        if ($this->evaluatorChain === null) {
            $this->evaluatorChain = new EvaluatorChain();
            
            // Add evaluators in order of specificity (most specific first)
            $this->evaluatorChain->addEvaluator(new FilterEvaluator($this->evaluatorChain, $this));
            $this->evaluatorChain->addEvaluator(new ConcatenationEvaluator($this->evaluatorChain));
            $this->evaluatorChain->addEvaluator(new LiteralEvaluator($this->evaluatorChain));
            $this->evaluatorChain->addEvaluator(new ParenthesizedEvaluator($this->evaluatorChain));
            $this->evaluatorChain->addEvaluator(new ArrayEvaluator($this->evaluatorChain));
            $this->evaluatorChain->addEvaluator(new OperatorEvaluator($this->evaluatorChain));
            $this->evaluatorChain->addEvaluator(new VariableEvaluator($this->evaluatorChain, $this));
        }
        
        return $this->evaluatorChain;
    }

    /**
     * Renders the template with the given variables
     *
     * @param array $variables The variables to use in the template
     * @return string|array|null The rendered template
     * @throws TemplateParseException If an error occurs during rendering
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
     * Gets a variable from the variables array, supporting dot notation and bracket notation
     *
     * @param string $varName The variable name with dot notation or bracket notation
     * @param array $variables The variables context
     * @param UndefinedInterface|null $undefined The strategy for handling undefined variables
     * @return mixed The variable value
     * @throws TemplateParseException If an error occurs during variable resolution
     */
    public function getVar(string $varName, array $variables, ?UndefinedInterface $undefined = null): mixed 
    {
        // Check for bracket notation like var[0] or var['key']
        $bracketPattern = '/^([\w\d_-]+)\[(.*?)\](.*)/';
        if (preg_match($bracketPattern, $varName, $matches)) {
            $mainVar = $matches[1];
            $index = trim($matches[2], "'\""); // Remove quotes if present
            $remaining = $matches[3];
            
            if (isset($variables[$mainVar])) {
                if (!isset($variables[$mainVar][$index])) {
                    if (is_null($undefined)) {
                        $undefined = $this->undefined;
                    }
                    return $undefined->render($index);
                }
                
                $result = $variables[$mainVar][$index];
                
                // Handle any remaining path components (could be dot notation or more brackets)
                if (!empty($remaining)) {
                    // If the next character is a bracket, it's another bracket notation
                    if (str_starts_with($remaining, '[')) {
                        // Create a temporary array with the result as the first element
                        return $this->getVar('temp' . $remaining, ['temp' => $result], $undefined);
                    } 
                    // If the next character is a dot, it's dot notation
                    else if (str_starts_with($remaining, '.')) {
                        return $this->getVar(substr($remaining, 1), is_array($result) ? $result : ['value' => $result], $undefined);
                    }
                }
                
                return $result;
            } else {
                if (is_null($undefined)) {
                    $undefined = $this->undefined;
                }
                return $undefined->render($mainVar);
            }
        }
        
        // Handle dot notation (existing implementation)
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

    /**
     * Evaluates a variable expression
     *
     * @param string $content The content to evaluate
     * @param array $variables The variables context
     * @param UndefinedInterface|null $undefined The strategy for handling undefined variables
     * @return mixed The evaluated result
     * @throws TemplateParseException If evaluation fails
     */
    protected function evaluateVariable(string $content, array $variables, UndefinedInterface|null $undefined = null): mixed
    {
        return $this->getEvaluatorChain()->evaluate($content, $variables, $undefined);
    }

    /**
     * Prepare a document for parsing by numbering the tags
     *
     * @param string $partialTemplate The template to parse
     * @param string $startTag The starting tag
     * @param string $endTag The ending tag
     * @return PartialDocument The prepared document
     * @throws TemplateParseException If tag mismatch is found
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

                $iPosStartTag = strpos($result, ' ' . $startTag . str_pad((string)$i, 2, "0", STR_PAD_LEFT) . " ");
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

                    return "{%$left " .  $endTag . str_pad((string)$i, 2, "0", STR_PAD_LEFT) . " $right%}";
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
     * Parse if/elseif/else conditions in the template
     *
     * @param string $partialTemplate The template to parse
     * @param array $variables The variables context
     * @return string The parsed template
     * @throws TemplateParseException If parsing fails
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
     * Parse for loops in the template
     *
     * @param array $variables The variables context
     * @param int $forStart The starting for loop index
     * @param int|null $forCount The number of for loops
     * @param string|null $partialTemplate The template to parse
     * @return string The parsed template
     * @throws TemplateParseException If parsing fails
     */
    protected function parseFor(array $variables, int $forStart = 1, ?int $forCount = null, string $partialTemplate = null): string
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
            $position = str_pad((string)$i, 2, "0", STR_PAD_LEFT);

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
     * Parse variables in the template
     *
     * @param string $partialTemplate The template to parse
     * @param array $variables The variables context
     * @return array|string|null The parsed template
     * @throws TemplateParseException If parsing fails
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
