<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * Cleans SVG markup an admin typed in, so it can be printed on the design page as-is.
 * Only shape elements and drawing attributes survive: no scripts, event handlers, links,
 * styles or external references. Anything not on the lists below is dropped with its children.
 */
class SvgSanitizer
{
    public const NAMESPACE = 'http://www.w3.org/2000/svg';

    public const ELEMENTS = [
        'g', 'defs', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'linearGradient', 'radialGradient', 'stop',
    ];

    public const ATTRIBUTES = [
        'id', 'd', 'x', 'y', 'width', 'height', 'rx', 'ry', 'cx', 'cy', 'r', 'x1', 'y1', 'x2', 'y2',
        'points', 'transform', 'opacity', 'fill', 'fill-opacity', 'fill-rule',
        'stroke', 'stroke-width', 'stroke-opacity', 'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray',
        'offset', 'stop-color', 'stop-opacity', 'gradientUnits', 'gradientTransform', 'fx', 'fy',
    ];

    /** The characters an SVG path's d attribute can hold. */
    public const PATH_PATTERN = '/^\s*[Mm][MmLlHhVvCcSsQqTtAaZz0-9eE\s,.+-]*$/';

    public static function isPathData(string $d): bool
    {
        return preg_match(self::PATH_PATTERN, $d) === 1;
    }

    /** Null when the markup can't be read as SVG at all. */
    public function sanitize(string $markup): ?string
    {
        if (trim($markup) === '') {
            return '';
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        // No DTD loading and no network: entities and external files are never fetched.
        $loaded = $document->loadXML(
            '<svg xmlns="'.self::NAMESPACE.'">'.$markup.'</svg>',
            LIBXML_NONET | LIBXML_NOBLANKS,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || $document->documentElement === null || $document->doctype !== null) {
            return null;
        }

        $root = $document->documentElement;
        $this->clean($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveXML($child);
        }

        return $output;
    }

    private function clean(DOMElement $element): void
    {
        // Copy the list first: removing while iterating a live NodeList skips nodes.
        foreach (iterator_to_array($element->childNodes) as $child) {
            if (! $child instanceof DOMElement || ! $this->isAllowedElement($child)) {
                $element->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                if (! $this->isAllowedAttribute($attribute->namespaceURI, $attribute->localName, $attribute->value)) {
                    $child->removeAttributeNode($attribute);
                }
            }

            $this->clean($child);
        }
    }

    private function isAllowedElement(DOMElement $node): bool
    {
        return $node->namespaceURI === self::NAMESPACE
            && in_array($node->localName, self::ELEMENTS, true);
    }

    private function isAllowedAttribute(?string $namespace, ?string $name, string $value): bool
    {
        if ($namespace !== null || ! in_array($name, self::ATTRIBUTES, true)) {
            return false;
        }

        if ($name === 'd' && ! self::isPathData($value)) {
            return false;
        }

        // url() may only point at a gradient in this same drawing, e.g. fill="url(#shine)".
        if (stripos($value, 'url(') !== false) {
            return preg_match('/^\s*url\(\s*#[A-Za-z_][\w.-]*\s*\)\s*$/', $value) === 1;
        }

        return ! preg_match('/javascript:|data:|expression\s*\(|[<>]/i', $value);
    }
}
