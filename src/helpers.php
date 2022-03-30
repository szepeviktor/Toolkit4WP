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

use function esc_attr;
use function esc_html;
use function esc_url;
use function sanitize_key;

/**
 * Create an HTML attribute string from an array.
 *
 * @param array<string, string|null> $attrs HTML attributes.
 * @return string
 */
function tagAttrString(array $attrs = []): string
{
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

        $attrString .= \sprintf(
            ' %s="%s"',
            $attrName,
            \in_array($attrName, ['href', 'src'], true)
                ? esc_url($attrValue)
                : esc_attr($attrValue)
        );
    }

    return $attrString;
}

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
    $voids = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img',
        'input', 'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];

    $name = sanitize_key($name);
    if ($content instanceof Traversable) {
        $content = \implode(\iterator_to_array($content));
    }

    // Void elements.
    $isVoid = \in_array($name, $voids, true);
    if ($isVoid && $content !== '') {
        throw new \Exception('Void HTML element with content.');
    }

    $attrString = tagAttrString($attrs);

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
 * Create a select element.
 *
 * @param array<string, string> $attrs HTML attributes of the select.
 * @param array<string, string> $options Option elements value=>item.
 */
function tagSelect(array $attrs, array $options, string $currentValue = ''): string
{
    $optionElements = \array_map(
        static function (string $value, string $item) use ($currentValue): string {
            return tag(
                'option',
                \array_merge(
                    ['value' => $value],
                    $value === $currentValue ? ['selected' => null] : []
                ),
                esc_html($item)
            );
        },
        \array_keys($options),
        $options
    );

    return tag(
        'select',
        $attrs,
        \implode('', $optionElements)
    );
}
