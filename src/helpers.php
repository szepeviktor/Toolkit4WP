<?php

/**
 * Useful functions.
 *
 * @author  Viktor Szépe <viktor@szepe.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

use Traversable;

/**
 * Create an HTML element with pure PHP.
 *
 * @see https://www.w3.org/TR/html/syntax.html#void-elements
 *
 * @param string $name Tag name.
 * @param array<string, string|null> $attrs HTML attributes.
 * @param string|\Traversable<int, string> $content Raw HTML content.
 * @return string
 * @throws \Exception
 */
function tag(string $name = 'div', array $attrs = [], $content = ''): string
{
    $voids = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img',
        'input', 'link', 'meta', 'param', 'source', 'track', 'wbr', ];

    // Void elements.
    $name = \sanitize_key($name);
    $isVoid = \in_array($name, $voids, true);
    if ($content instanceof Traversable) {
        $content = \implode(\iterator_to_array($content));
    }
    if ($isVoid && $content !== '') {
        throw new \Exception('Void HTML element with content.');
    }

    // Attributes.
    $attrString = '';
    foreach ($attrs as $attrName => $attrValue) {
        $attrName = \strtolower($attrName);
        $attrName = \preg_replace('/[^a-z0-9-]/', '', $attrName);
        // Boolean Attributes.
        if ($attrValue === null) {
            $attrString .= \sprintf(' %s', $attrName);
            continue;
        }
        $attrString .= \sprintf(' %s="%s"', $attrName, esc_attr($attrValue));
    }

    // Element.
    if ($isVoid) {
        return \sprintf('<%s%s>', $name, $attrString);
    }

    return \sprintf('<%s%s>%s</%s>', $name, $attrString, $content, $name);
}

/**
 * Create an HTML list.
 *
 * @param string $name Parent tag name.
 * @param array<string, string> $attrs HTML attributes of the parent.
 * @param array<int, string> $childrenContent Raw HTML content of children.
 * @param string $childTagName Name of children tags.
 * @return string
 */
function tagList(
    string $name = 'ul',
    array $attrs = [],
    array $childrenContent = [],
    string $childTagName = 'li'
): string {
    $content = \array_map(
        static function (string $child) use ($childTagName): string {
            return \sprintf('<%s>%s</%s>', $childTagName, $child, $childTagName);
        },
        $childrenContent
    );

    return tag($name, $attrs, \implode('', $content));
}

/**
 * Create a DIV element with classes.
 *
 * @param string $classes
 * @param string $htmlContent
 * @return string
 */
function tagDivClass(string $classes, string $htmlContent = ''): string
{
    return tag('div', ['class' => $classes], $htmlContent);
}

/**
 * Create an H3 element with classes.
 *
 * @param string $classes
 * @param string $htmlContent
 * @return string
 */
function tagH3Class(string $classes, string $htmlContent = ''): string
{
    return tag('h3', ['class' => $classes], $htmlContent);
}

/**
 * Create an HTML element from tag name and array of attributes.
 *
 * @param array{tag: string, attrs: array<string, string|null>} $skeleton
 * @param string $htmlContent
 * @return string
 */
function tagFromSkeleton(array $skeleton, string $htmlContent = ''): string
{
    return tag($skeleton['tag'], $skeleton['attrs'], $htmlContent);
}

/**
 * Validate a string with RFC 3629.
 *
 * @link https://tools.ietf.org/html/rfc3629
 */
function checkUTF8(string $string): bool
{
    // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
    // https://www.php.net/manual/en/function.mb-check-encoding.php
    //return \mb_check_encoding($string, 'UTF-8');
    // https://github.com/PCRE/pcre2/blob/master/src/pcre2_valid_utf.c#L99-L316
    //return \preg_match('//u', $string) === 1;

    $length = \strlen($string);
    for ($pos = 0; $pos < $length; $pos += 1) {
        $sequenceSize = 1;
        $octet = \ord($string[$pos]);
        /*
        | Char. number range  | UTF-8 octet sequence                |
        | ------------------- | ----------------------------------- |
        | 0000 0000-0000 007F | 0xxxxxxx                            |
        | 0000 0080-0000 07FF | 110xxxxx 10xxxxxx                   |
        | 0000 0800-0000 FFFF | 1110xxxx 10xxxxxx 10xxxxxx          |
        | 0001 0000-0010 FFFF | 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx |
        */
        // Test leading byte.
        switch (true) {
            // Only 1 octet.
            case $octet <= 0b01111111:
                continue 2;
            // Leading byte is too high: 0xF8-0xFF.
            case $octet > 0b11110111:
                return false;
            case $octet > 0b11101111:
                $sequenceSize = 4;
                break;
            case $octet > 0b11011111:
                $sequenceSize = 3;
                break;
            case $octet > 0b10111111:
                $sequenceSize = 2;
                break;
            // Leading byte is too low: 0x80-0xBF.
            default:
                return false;
        }

        // The string ending before the end of the character.
        if ($length < $pos + $sequenceSize) {
            return false;
        }

        // TODO https://en.wikipedia.org/wiki/UTF-8#Invalid_byte_sequences
        // See https://github.com/voku/portable-utf8/blob/b9cb9a51de8715db29a66c42d8f30b216957871f/src/voku/helper/UTF8.php#L14067-L14087
        // https://hsivonen.fi/php-utf8/php-utf8.tar.gz

        // TODO Add tests

        // Check further octets.
        while ($sequenceSize > 1) {
            $pos += 1;
            $octet = \ord($string[$pos]);
            // Octet is outside 0x80-0xBF.
            if ($octet < 0b10000000 || $octet > 0b10111111) {
                return false;
            }

            $sequenceSize -= 1;
        }
    }
    return true;
}
