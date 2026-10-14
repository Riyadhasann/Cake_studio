<?php

namespace App\Services;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use InvalidArgumentException;

/**
 * Cleans SVG markup typed by an admin before it is saved and drawn on the design page.
 * Only plain shapes, groups and gradients survive: no scripts, event handlers, links, images,
 * styles or references to anything outside the drawing.
 */
class SvgSanitizer
{
    /** Elements kept (with their children). Anything else is removed together with its children. */
    public const ELEMENTS = [
        'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'defs', 'linearGradient', 'radialGradient', 'stop',
    ];

    /** Attributes kept on those elements. */
    public const ATTRIBUTES = [
        'id', 'd', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'fx', 'fy',
        'width', 'height', 'points', 'transform', 'opacity',
        'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width', 'stroke-opacity',
        'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray', 'stroke-miterlimit',
        'offset', 'stop-color', 'stop-opacity', 'gradientUnits', 'gradientTransform', 'spreadMethod',
    ];

    /** The characters an SVG path's d attribute is made of; it must start with a move command. */
    private const PATH_PATTERN = '/^[Mm][MmLlHhVvCcSsQqTtAaZz0-9eE\s,.+\-]*$/';

    private const SVG_NS = 'http://www.w3.org/2000/svg';

    /**
     * The cleaned markup, without the outer <svg> element.
     *
     * @throws InvalidArgumentException when the markup is not well-formed
     */
    public function sanitize(string $markup): string
    {
        if (trim($markup) === '') {
            return '';
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        // No network access and no entity expansion: the markup is parsed as data only.
        $loaded = $document->loadXML('<svg xmlns="'.self::SVG_NS.'">'.$markup.'</svg>', LIBXML_NONET);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || ! $document->documentElement instanceof DOMElement) {
            throw new InvalidArgumentException('The markup is not well-formed SVG.');
        }

        $root = $document->documentElement;
        $this->cleanChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveXML($child);
        }

        // Children inherit the default namespace from the wrapper; drop any redeclarations.
        return trim(str_replace(' xmlns="'.self::SVG_NS.'"', '', $output));
    }

    public function isValidPath(string $path): bool
    {
        return preg_match(self::PATH_PATTERN, trim($path)) === 1;
    }

    /** The path with surrounding space trimmed and runs of whitespace collapsed. */
    public function normalizePath(string $path): string
    {
        return (string) preg_replace('/\s+/', ' ', trim($path));
    }

    private function cleanChildren(DOMNode $parent): void
    {
        // Copy first: removing nodes while iterating a live list skips siblings.
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMElement && $this->isAllowedElement($node)) {
                $this->cleanAttributes($node);
                $this->cleanChildren($node);
            } elseif ($node->nodeType !== XML_TEXT_NODE || trim((string) $node->nodeValue) !== '') {
                // Unknown elements, comments, CDATA, processing instructions and stray text.
                $parent->removeChild($node);
            }
        }
    }

    private function isAllowedElement(DOMElement $element): bool
    {
        return $element->namespaceURI === self::SVG_NS
            && $element->prefix === ''
            && in_array($element->localName, self::ELEMENTS, true);
    }

    private function cleanAttributes(DOMElement $element): void
    {
        /** @var list<DOMAttr> $attributes */
        $attributes = iterator_to_array($element->attributes, false);

        foreach ($attributes as $attribute) {
            $allowed = $attribute->namespaceURI === null
                && in_array($attribute->name, self::ATTRIBUTES, true)
                && $this->isSafeValue($attribute->value);

            if (! $allowed) {
                $element->removeAttributeNode($attribute);
            }
        }
    }

    /** Only same-drawing references such as url(#shine) are allowed; no other urls or script. */
    private function isSafeValue(string $value): bool
    {
        $compact = strtolower((string) preg_replace('/\s+/', '', $value));

        if (str_contains($compact, 'javascript:') || str_contains($compact, 'data:') || str_contains($compact, 'expression(')) {
            return false;
        }

        preg_match_all('/url\(([^)]*)\)/', $compact, $matches);

        foreach ($matches[1] as $target) {
            if (preg_match('/^["\']?#[a-z0-9_-]+["\']?$/', $target) !== 1) {
                return false;
            }
        }

        return ! str_contains($compact, 'url(') || count($matches[1]) === substr_count($compact, 'url(');
    }
}
