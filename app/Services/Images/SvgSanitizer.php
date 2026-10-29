<?php

namespace App\Services\Images;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMText;
use InvalidArgumentException;

/**
 * Cleans SVG before it is saved and drawn on the design page, so it can't run script or load anything
 * from elsewhere.
 *
 * - sanitize(): markup typed by an admin in the shape editor. Only plain shapes, groups and gradients.
 * - sanitizeFile(): a whole .svg file uploaded as a sponge or cream image. Also keeps what drawing apps
 *   export (style sheets and classes, clip paths, masks, patterns, filters, <use>, text and embedded
 *   PNG/JPEG/WebP/GIF images), but only references inside the file itself.
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

    /** Extra elements an uploaded file may use. */
    private const FILE_ELEMENTS = [
        'svg', 'symbol', 'use', 'clipPath', 'mask', 'pattern', 'image', 'style', 'title', 'desc', 'text', 'tspan',
        'filter', 'feBlend', 'feColorMatrix', 'feComponentTransfer', 'feComposite', 'feDisplacementMap',
        'feDropShadow', 'feFlood', 'feFuncA', 'feFuncB', 'feFuncG', 'feFuncR', 'feGaussianBlur', 'feMerge',
        'feMergeNode', 'feMorphology', 'feOffset', 'feTurbulence',
    ];

    /** Extra attributes an uploaded file may use. */
    private const FILE_ATTRIBUTES = [
        'class', 'style', 'viewBox', 'preserveAspectRatio', 'version', 'visibility', 'display', 'color',
        'clip-path', 'clip-rule', 'clipPathUnits', 'mask', 'maskUnits', 'maskContentUnits',
        'patternUnits', 'patternContentUnits', 'patternTransform', 'stroke-dashoffset', 'paint-order', 'mix-blend-mode',
        'filter', 'filterUnits', 'primitiveUnits', 'in', 'in2', 'result', 'stdDeviation', 'dx', 'dy', 'mode', 'type',
        'values', 'operator', 'k1', 'k2', 'k3', 'k4', 'flood-color', 'flood-opacity', 'radius', 'baseFrequency',
        'numOctaves', 'seed', 'stitchTiles', 'scale', 'xChannelSelector', 'yChannelSelector', 'tableValues',
        'slope', 'intercept', 'amplitude', 'exponent', 'color-interpolation-filters',
        'font-family', 'font-size', 'font-style', 'font-weight', 'text-anchor', 'dominant-baseline',
        'letter-spacing', 'word-spacing', 'text-decoration', 'href',
    ];

    /** Elements whose text content is kept (everywhere else stray text is dropped). */
    private const TEXT_ELEMENTS = ['style', 'title', 'desc', 'text', 'tspan'];

    /** The characters an SVG path's d attribute is made of; it must start with a move command. */
    private const PATH_PATTERN = '/^[Mm][MmLlHhVvCcSsQqTtAaZz0-9eE\s,.+\-]*$/';

    private const SVG_NS = 'http://www.w3.org/2000/svg';

    private const XLINK_NS = 'http://www.w3.org/1999/xlink';

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

        [$document, $root] = $this->load('<svg xmlns="'.self::SVG_NS.'">'.$markup.'</svg>');
        $this->cleanChildren($root, false);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveXML($child);
        }

        // Children inherit the default namespace from the wrapper; drop any redeclarations.
        return trim(str_replace(' xmlns="'.self::SVG_NS.'"', '', $output));
    }

    /**
     * A whole uploaded .svg file, cleaned and ready to save.
     *
     * @throws InvalidArgumentException when it is not a well-formed SVG file
     */
    public function sanitizeFile(string $contents): string
    {
        // Entity declarations are how XML bombs and file disclosure work; drawing apps don't need them.
        if (stripos($contents, '<!ENTITY') !== false) {
            throw new InvalidArgumentException('The file declares XML entities, which are not allowed.');
        }

        [$document, $root] = $this->load($contents);

        if ($root->namespaceURI !== self::SVG_NS || $root->localName !== 'svg' || $root->prefix !== '') {
            throw new InvalidArgumentException('The file is not an SVG image.');
        }

        $this->cleanAttributes($root, true);
        $this->cleanChildren($root, true);

        if (! $root->hasChildNodes()) {
            throw new InvalidArgumentException('Nothing is left to draw once unsupported parts are removed.');
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".$document->saveXML($root)."\n";
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

    /** @return array{0: DOMDocument, 1: DOMElement} */
    private function load(string $xml): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        // No network access, no DTD loading and no entity expansion: the markup is parsed as data only.
        $loaded = $document->loadXML($xml, LIBXML_NONET);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || ! $document->documentElement instanceof DOMElement) {
            throw new InvalidArgumentException('The markup is not well-formed SVG.');
        }

        return [$document, $document->documentElement];
    }

    private function cleanChildren(DOMElement $parent, bool $file): void
    {
        if ($file && $parent->localName === 'style') {
            $this->cleanStyleSheet($parent);

            return;
        }

        $keepsText = $file && in_array($parent->localName, self::TEXT_ELEMENTS, true);

        // Copy first: removing nodes while iterating a live list skips siblings.
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMElement && $this->isAllowedElement($node, $file)) {
                $this->cleanAttributes($node, $file);
                $this->cleanChildren($node, $file);
            } elseif ($node->nodeType === XML_TEXT_NODE && ($keepsText || trim((string) $node->nodeValue) === '')) {
                continue;
            } else {
                // Unknown elements, comments, CDATA, processing instructions and stray text.
                $parent->removeChild($node);
            }
        }
    }

    /** A <style> element keeps its CSS only when it is safe; otherwise the whole element goes. */
    private function cleanStyleSheet(DOMElement $style): void
    {
        $css = (string) $style->textContent;

        if (! $this->isSafeCss($css)) {
            $style->parentNode?->removeChild($style);

            return;
        }

        // Replace CDATA sections and anything else with one plain text node.
        while ($style->firstChild) {
            $style->removeChild($style->firstChild);
        }
        $style->appendChild(new DOMText($css));

        foreach (iterator_to_array($style->attributes, false) as $attribute) {
            if (! in_array($attribute->name, ['type', 'id'], true)) {
                $style->removeAttributeNode($attribute);
            }
        }
    }

    private function isAllowedElement(DOMElement $element, bool $file): bool
    {
        $allowed = $file ? [...self::ELEMENTS, ...self::FILE_ELEMENTS] : self::ELEMENTS;

        return $element->namespaceURI === self::SVG_NS
            && $element->prefix === ''
            && in_array($element->localName, $allowed, true);
    }

    private function cleanAttributes(DOMElement $element, bool $file): void
    {
        $allowed = $file ? [...self::ATTRIBUTES, ...self::FILE_ATTRIBUTES] : self::ATTRIBUTES;

        /** @var list<DOMAttr> $attributes */
        $attributes = iterator_to_array($element->attributes, false);

        foreach ($attributes as $attribute) {
            $isHref = $file && $attribute->localName === 'href'
                && ($attribute->namespaceURI === null || $attribute->namespaceURI === self::XLINK_NS);

            $keep = match (true) {
                $isHref => $this->isSafeHref($element, $attribute->value),
                $file && $attribute->name === 'style' => $this->isSafeCss($attribute->value),
                default => $attribute->namespaceURI === null
                    && in_array($attribute->name, $allowed, true)
                    && $this->isSafeValue($attribute->value),
            };

            if (! $keep) {
                $element->removeAttributeNode($attribute);
            }
        }
    }

    /** Links stay inside the file (#id); an <image> may also embed a PNG, JPEG, WebP or GIF. */
    private function isSafeHref(DOMElement $element, string $value): bool
    {
        $value = trim($value);

        if (preg_match('/^#[A-Za-z_][\w.:-]*$/', $value) === 1) {
            return true;
        }

        return $element->localName === 'image'
            && preg_match('#^data:image/(png|jpe?g|webp|gif);base64,[A-Za-z0-9+/=\s]+$#', $value) === 1;
    }

    /** CSS from a style sheet or style attribute: no imports, outside urls, script or legacy bindings. */
    private function isSafeCss(string $css): bool
    {
        $compact = strtolower((string) preg_replace('/\s+/', '', $css));

        foreach (['@import', '@font-face', 'behavior', '-moz-binding', '</', '\\'] as $needle) {
            if (str_contains($compact, $needle)) {
                return false;
            }
        }

        return $this->isSafeValue($css);
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
            if (preg_match('/^["\']?#[a-z0-9_.:-]+["\']?$/', $target) !== 1) {
                return false;
            }
        }

        return ! str_contains($compact, 'url(') || count($matches[1]) === substr_count($compact, 'url(');
    }
}
