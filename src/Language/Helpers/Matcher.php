<?php

namespace SRL\Language\Helpers;

use SRL\Exceptions\SyntaxException;
use SRL\Interfaces\Method;
use SRL\Language\Methods;

class Matcher
{
    /** @var static */
    protected static $instance;

    /** @var string[] Contains all possible commands. */
    protected $mapper = [
        'any letter' => ['class' => Methods\SimpleMethod::class, 'method' => 'anyLetter'],
        'no letter' => ['class' => Methods\SimpleMethod::class, 'method' => 'noLetter'],
        'multi line' => ['class' => Methods\SimpleMethod::class, 'method' => 'multiLine'],
        'single line' => ['class' => Methods\SimpleMethod::class, 'method' => 'singleLine'],
        'case insensitive' => ['class' => Methods\SimpleMethod::class, 'method' => 'caseInsensitive'],
        'all lazy' => ['class' => Methods\SimpleMethod::class, 'method' => 'allLazy'],
        'starts with' => ['class' => Methods\SimpleMethod::class, 'method' => 'startsWith'],
        'begin with' => ['class' => Methods\SimpleMethod::class, 'method' => 'startsWith'],
        'must end' => ['class' => Methods\SimpleMethod::class, 'method' => 'mustEnd'],
        'once or more' => ['class' => Methods\SimpleMethod::class, 'method' => 'onceOrMore'],
        'never or more' => ['class' => Methods\SimpleMethod::class, 'method' => 'neverOrMore'],
        'new line' => ['class' => Methods\SimpleMethod::class, 'method' => 'newLine'],
        'whitespace' => ['class' => Methods\SimpleMethod::class, 'method' => 'whitespace'],
        'no whitespace' => ['class' => Methods\SimpleMethod::class, 'method' => 'noWhitespace'],
        'all' => ['class' => Methods\SimpleMethod::class, 'method' => 'all'],
        'anything' => ['class' => Methods\SimpleMethod::class, 'method' => 'any'],
        'tab' => ['class' => Methods\SimpleMethod::class, 'method' => 'tab'],
        'unicode' => ['class' => Methods\SimpleMethod::class, 'method' => 'unicode'],
        'number' => ['class' => Methods\SimpleMethod::class, 'method' => 'number'],
        'letter' => ['class' => Methods\SimpleMethod::class, 'method' => 'letter'],
        'uppercase letter' => ['class' => Methods\SimpleMethod::class, 'method' => 'uppercaseLetter'],
        'once' => ['class' => Methods\SimpleMethod::class, 'method' => 'once'],
        'twice' => ['class' => Methods\SimpleMethod::class, 'method' => 'twice'],
        'first match' => ['class' => Methods\SimpleMethod::class, 'method' => 'firstMatch'],

        'literally' => ['class' => Methods\DefaultMethod::class, 'method' => 'literally'],
        'either of' => ['class' => Methods\DefaultMethod::class, 'method' => 'eitherOf'],
        'if already had' => ['class' => Methods\DefaultMethod::class, 'method' => 'ifAlreadyHad'],
        'if not already had' => ['class' => Methods\DefaultMethod::class, 'method' => 'ifNotAlreadyHad'],
        'if followed by' => ['class' => Methods\DefaultMethod::class, 'method' => 'ifFollowedBy'],
        'if not followed by' => ['class' => Methods\DefaultMethod::class, 'method' => 'ifNotFollowedBy'],
        'optional' => ['class' => Methods\DefaultMethod::class, 'method' => 'optional'],
        'to' => ['class' => Methods\DefaultMethod::class, 'method' => 'to'],
        'raw' => ['class' => Methods\DefaultMethod::class, 'method' => 'raw'],
        'one of' => ['class' => Methods\DefaultMethod::class, 'method' => 'oneOf'],
        'at least' => ['class' => Methods\DefaultMethod::class, 'method' => 'atLeast'],
        'exactly' => ['class' => Methods\DefaultMethod::class, 'method' => 'exactly'],

        'number between' => ['class' => Methods\AndMethod::class, 'method' => 'number'],
        'letter between' => ['class' => Methods\AndMethod::class, 'method' => 'letter'],
        'uppercase letter between' => ['class' => Methods\AndMethod::class, 'method' => 'uppercaseLetter'],
        'between' => ['class' => Methods\AndMethod::class, 'method' => 'between'],
        'capture' => ['class' => Methods\AsMethod::class, 'method' => 'capture'],
    ];

    /**
     * Get matcher instance. Since this matcher contains static functionality, we'll use a singleton.
     *
     * @return Matcher
     */
    public static function getInstance() : self
    {
        return static::$instance ?: static::$instance = new static();
    }

    /**
     * Match a string part to a method. Please note that the string must start with a method.
     *
     * @param string $part
     * @return Method
     * @throws SyntaxException If no method was found, a SyntaxException will be thrown.
     */
    public function match(string $part) : Method
    {
        $maxMatchCount = 0;

        // Go through each mapper and check if the name matches. Then, take the highest match to avoid matching
        // 'any', if 'any letter' was given, and so on.
        foreach ($this->mapper as $key => $value) {
            $matches = [];
            preg_match_all('/^(' . str_replace(' ', ') (', $key) . ')/i', $part, $matches, PREG_SET_ORDER);
            $count = empty($matches) ? 0 : count($matches[0]);

            if ($count > $maxMatchCount) {
                $maxMatchCount = $count;
                $maxMatch = $key;
            }
        }

        if (isset($maxMatch)) {
            // We've got a match. Create the desired object and populate it.
            $method = $this->mapper[$maxMatch];

            return new $method['class']($maxMatch, $method['method']);
        }

        throw new SyntaxException("Invalid method: `$part`");
    }
}