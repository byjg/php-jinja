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
     * Get the appropriate undefined variable handler
     *
     * @param UndefinedInterface|null $undefined The provided undefined handler
     * @return UndefinedInterface The undefined handler to use
     */
    protected function getUndefinedHandler(?UndefinedInterface $undefined = null): UndefinedInterface
    {
        // If $undefined is null, use the default undefined handler
        // This ensures we never return null
        return $undefined ?? $this->undefined ?? new StrictUndefined();
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
                    return $this->getUndefinedHandler($undefined)->render($index);
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
                return $this->getUndefinedHandler($undefined)->render($mainVar);
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
            return $this->getUndefinedHandler($undefined)->render($varName);
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
     * Find positions of start and end tags in a template
     *
     * @param string $result The template being processed
     * @param string $startTag The start tag to find
     * @param string $endTag The end tag to find
     * @param int $iTag The tag number
     * @return array An array with start and end tag positions and match data
     */
    protected function findTagPositions(string $result, string $startTag, string $endTag, int $iTag): array
    {
        $paddedTag = str_pad((string)$iTag, 2, "0", STR_PAD_LEFT);
        $iPosStartTag = strpos($result, ' ' . $startTag . $paddedTag . " ");
        
        $matchesTmpStartTag = [];
        $iPosStartTagAfter = preg_match('/\{%[+-]?\s*' . $startTag . ' /sU', $result, $matchesTmpStartTag, PREG_OFFSET_CAPTURE, $iPosStartTag);
        
        $matchesTmpEndTag = [];
        $iPosEndTag = preg_match('/\{%[+-]?\s*' . $endTag . '\s*[+-]?\%}/sU', $result, $matchesTmpEndTag, PREG_OFFSET_CAPTURE, $iPosStartTag);
        
        return [
            'iPosStartTag' => $iPosStartTag,
            'iPosStartTagAfter' => $iPosStartTagAfter,
            'iPosEndTag' => $iPosEndTag,
            'matchesTmpStartTag' => $matchesTmpStartTag,
            'matchesTmpEndTag' => $matchesTmpEndTag
        ];
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
        $self = $this;
        $fixArray = /**
         * @return (mixed|null|string|string[])[]
         *
         * @psalm-return list{mixed, array<string>|mixed|null|string}
         */
        function ($iEndTag, $endTag, $result) use ($startTag, $self): array {
            while (!empty($iEndTag)) {
                $i = array_pop($iEndTag);

                $positions = $self->findTagPositions($result, $startTag, $endTag, $i);
                $iPosStartTagAfter = $positions['iPosStartTagAfter'];
                $iPosEndTag = $positions['iPosEndTag'];
                $matchesTmpStartTag = $positions['matchesTmpStartTag'];
                $matchesTmpEndTag = $positions['matchesTmpEndTag'];
            
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

            $positions = $this->findTagPositions($result, $startTag, $endTag, $iStartTag);
            $iPosStartTagAfter = $positions['iPosStartTagAfter'];
            $iPosEndTag = $positions['iPosEndTag'];
            $matchesTmpStartTag = $positions['matchesTmpStartTag'];
            $matchesTmpEndTag = $positions['matchesTmpEndTag'];

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
     * Apply whitespace control to content based on control flags
     *
     * @param string $content The content to process
     * @param string|null $leftWhiteSpace The left whitespace control character
     * @param string|null $rightWhiteSpace The right whitespace control character
     * @return string The processed content
     */
    protected function applyWhitespaceControl(string $content, ?string $leftWhiteSpace = null, ?string $rightWhiteSpace = null): string
    {
        if ($leftWhiteSpace == "-") {
            $content = ltrim($content);
        }
        if ($rightWhiteSpace == "-") {
            $content = rtrim($content);
        }
        return $content;
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
                    $elsePart = $mainParts[1] ?? "";
                    
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

                return $this->applyWhitespaceControl($return, $leftWhiteSpace, $rightWhiteSpace);
            }, $partial->result);
        }
        return $partial->result;
    }

    /**
     * Process nested for loops in content
     *
     * @param string $content The content to process
     * @param array $variables The variables context
     * @param int|null $specificLoopId Process only this specific loop ID if provided
     * @return string The processed content
     * @throws TemplateParseException
     */
    protected function processNestedForLoops(string $content, array $variables, ?int $specificLoopId = null): string
    {
        $processedContent = $content;
        $regexNestedFor = '/\{%\s*for(\d{2}).*\%}/sU';
        
        if (preg_match_all($regexNestedFor, $processedContent, $matchesNestedFor)) {
            foreach ($matchesNestedFor[1] as $matchNested) {
                $matchNested = intval($matchNested);
                // Skip if we're looking for a specific loop ID and this isn't it
                if ($specificLoopId !== null && $matchNested !== $specificLoopId) {
                    continue;
                }
                $processedContent = $this->parseFor($variables, $matchNested, $matchNested, $processedContent);
            }
        }
        
        return $processedContent;
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
    protected function parseFor(array $variables, int $forStart = 1, ?int $forCount = null, ?string $partialTemplate = null): string
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
            $partial->result = preg_replace_callback($regex, function ($matches) use ($variables): string {
        
                $content = "";
                $regexFor = '/\s*(?<key1>[\w\d_-]+)(\s*,\s*(?<key2>[\w\d_-]+))?\s+in\s+(?<array>.*)\s*/';
                $leftWhiteSpace = trim($matches[1]);
                $forExpression = trim($matches[2]);
                $rightWhiteSpace = trim($matches[3]);
                $loopContent = $matches[4] ?? '';
                
                // Check if there's an else block in the for loop
                $elseParts = preg_split('/\{%\s*else\s*\%}/', $loopContent, 2);
                $forContent = $elseParts[0];
                $elseContent = $elseParts[1] ?? "";
                
                if (preg_match($regexFor, $forExpression, $matchesFor)) {
                    $array = $this->evaluateVariable($matchesFor["array"], $variables);
                    
                    // If the array is empty and there's an else block, render the else content
                    if (empty($array) && !empty($elseContent)) {
                        // Process nested for loops in the else content first
                        $processedElseContent = $this->processNestedForLoops($elseContent, $variables);
                        
                        $elseResult = $this->parseVariables($this->parseIf($processedElseContent, $variables), $variables);
                        return $this->applyWhitespaceControl($elseResult, $leftWhiteSpace, $rightWhiteSpace);
                    }
                    
                    if (!empty($matchesFor["key2"])) {
                        $forKey = $matchesFor["key1"];
                        $forValue = $matchesFor["key2"];
                    } else {
                        $forKey = "__index";
                        $forValue = $matchesFor["key1"];
                    }
                    $index = 0;
                    
                    foreach ($array as $key => $value) {
                        // Create loop variable for this iteration
                        $loop = [
                            "first" => $index == 0,
                            "last" => $index == count($array) - 1,
                            "index" => $index + 1,
                            "index0" => $index,
                            "revindex" => count($array) - $index,
                            "revindex0" => count($array) - $index - 1,
                            "length" => count($array),
                            "even" => $index % 2 == 0,
                            "odd" => $index % 2 == 1
                        ];

                        // Create the local variables for this iteration
                        $loopVariables = [
                            $forKey => $key,
                            $forValue => $value,
                            "loop" => $loop
                        ];
                        
                        // Combine with parent variables but give precedence to the loop variables
                        $forVariables = array_merge($variables, $loopVariables);
                        
                        // Process the content for this iteration with nested for loops and if conditions
                        $iterationContent = $forContent;
                        
                        // Process nested for loops with the current scope's variables
                        $iterationContent = $this->processNestedForLoops($iterationContent, $forVariables);
                        
                        // Process if conditions and variables
                        $resultContent = $this->parseVariables($this->parseIf($iterationContent, $forVariables), $forVariables);
                        
                        // Apply whitespace control
                        $resultContent = $this->applyWhitespaceControl($resultContent, $leftWhiteSpace, $rightWhiteSpace);
                        
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
