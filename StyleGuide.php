<?php
/**
 * Style guide for Stationer PHP development
 * Included code serves only as a demonstration of coding style conventions
 *
 * Spaces (4) MUST be used for indentation
 * Files MUST be saved with no trailing spaces
 *
 * PHP version 7.3
 *
 * @category StyleGuide
 * @package  Stationer\Graphite
 * @author   Tyler Uebele
 * @license  MIT https://github.com/stationer/Graphite/blob/master/LICENSE
 * @link     https://github.com/stationer/Graphite/blob/master/StyleGuide.php
 * @see      https://www.ietf.org/rfc/rfc2119.txt
 */

// There MUST be no parens in require/include statements
require_once 'StyleGuide.php';

/**
 * Example Class for reference
 * There MUST be exactly one space before the opening brace
 * The opening brace MUST be on the same line as the class declaration
 * The closing brace MUST be on its own line
 *
 * @category StyleGuide
 * @package  Stationer\Graphite
 * @author   Tyler Uebele
 * @license  MIT https://github.com/stationer/Graphite/blob/master/LICENSE
 * @link     https://github.com/stationer/Graphite/blob/master/StyleGuide.php
 */
class StyleGuide {
    /** @var string $name Class Variables SHOULD have explanations in doc comments */
    public $name = 'bar';

    /**
     * Example class method for reference
     * There MUST be no space before the parameter list parenthesis
     * There MUST be exactly one space before the opening brace
     * The opening brace MUST be on the same line as the function declaration
     * The closing brace MUST be on its own line
     *
     * @param string $bar Parameters MUST be documented
     * @param string $baz Parameters MUST be documented
     *
     * @return mixed Returns MUST be documented
     */
    public function __toString($bar = 'bing', $baz = null) {
        /*
         * Example Control block for reference
         * The following applies to all IFs, FORs, FOREACHs, WHILEs, SWITCHs
         * There MUST be exactly one space before the opening parenthesis
         * There MUST be exactly one space after the closing parenthesis
         * Literal values SHOULD be on the left side of boolean comparators
         * Multi-line Controls MUST have boolean operators at beginning of lines
         * All Control blocks MUST use braces
         * The opening brace MUST be on the same line as the closing paren
         * Enclosed statements MUST be indented one level deeper
         * The closing brace MUST be on its own line
         */
        if ('bing' == $bar) {
            /*
             * PHP control statements like return, break, continue SHOULD NOT
             * use parens
             */
            return false;
        }

        /*
         * Example Assignment statements
         * Assignment equals MUST have at least one space before and after
         * Assignment equals MAY have extra space to increase readability
         */
        $len = 10;
        $i   = 4;

        /*
         * Example While loop, demonstrating multiline conditional
         * MUST obey rules for Control structures above
         */
        while ('bing' == $baz
            && 1 < $len--
        ) {
            /*
             * PHP non-functions like echo and print SHOULD NOT use parens
             */
            echo $i;
        }

        /*
         * Example Array
         * Array definitions MUST follow commas with at least one space
         * Associative arrays MUST surround `=>` with at least one space
         * Array variable names SHOULD be pluralized
         */
        $numbers = [1 => 'one', 'two', 'three'];

        /*
         * Example Multi-line Array
         * Multi-line arrays SHOULD have a comma after last element
         * Multi-line arrays SHOULD have closing paren/brace and semicolon on own line
         * Multi-line arrays MAY include extra space for readability
         */
        $numbers += [
            'yi'  => 'one',
            'er'  => 'two',
            'san' => 'three',
        ];

        /*
         * Example Foreach
         * MUST obey rules for Control structures above
         * Associative operation `=>` must be surrounded by one space
         */
        foreach ($numbers as $key => $value) {
            echo 'whatever';
        }

        /*
         * Example For
         * MUST obey rules for Control structures above
         * Semicolon separators MUST be followed by a spoce
         */
        for ($i = 0; $i < 10; $i++) {
            echo 'whatever';
        }

        /*
         * Example function call
         * Function name MUST NOT be followed by a space
         * Function parameters MUST be separated by a comma and space
         */
        $this->__toString('bar', 'bing');

        /*
         * Example Switch/case
         * MUST obey rules for Control structures above, where applicable
         * Case lines MUST be indented one level
         * Case blocks SHOULD NOT use curly braces
         * Case breaks MUST line with content of case, not with case
         * Switch MUST include `default` case, even if empty
         */
        switch ($bar) {
            case 'bing':
                echo 'whatever';
                break;
            case 2:
                echo 'whenever';
                break;
            default:
                break;
        }

        /*
         * Example Long String / Query
         * Long queries MAY be broken into concatenated strings to prevent
         *  inclusion of newlines and indents
         * SQL keywords MUST be all capitalized
         * Field names MUST be enclosed in `back-tics`
         * Table aliases SHOULD precede all fields
         * Multi-line Strings MAY have semicolon on own line
         * Multi-line queries SHOULD be un-indented for better logging
         */
        $query = "
SELECT t.`thing_id`, t.`created_uts`, t.`updated_dts`,
    s.`stuffValue`
FROM `Thing` t
    LEFT JOIN `Stuff` s ON t.`thing_id` = s.`stuff_id`
WHERE t.`thing_id` > 4
    AND t.`thingValue` IS NOT NULL
ORDER BY t.`created_uts`
";

        /*
         * Example Ternary operator
         * The ? SHOULD be followed by a space
         * The : SHOULD be followed by a space
         * Ternaries in a concatenated string MUST be enclosed in (parens)
         * Ternaries nested MUST be enclosed in (parens)
         */
        $someVariable = true || !true ? 'Obviously' : 'Wait, what?';

        /*
         * Example Multi-Line ternary operator
         * The ? MUST start its own line
         * The ? MUST be followed by a space
         * The : MUST start its own line
         * The : MUST be followed by a space
         * Multi-line Ternaries MAY have semicolon on own line
         */
        $someOtherVariable = true || !true
            ? 'Obviously'
            : 'Wait, what?';

        // Returns SHOULD have a blank line above them, unless they are alone
        return $query.$someVariable.$someOtherVariable;
    }
}

/* Object variables SHOULD be capitalized */
/* Object variables SHOULD have doc comments for IDE type hinting */
/** @var StyleGuide $SomeVariable */
$SomeVariable = new StyleGuide();
echo $SomeVariable;

/*
 * End of file MUST have an empty line
 * End of file MUST NOT have a close-php tag `?>`
 */
