<?php
declare(strict_types=1);
namespace App\Services;
/** Rebuild allowed markup instead of trusting editor/client HTML. */
final class RichText
{
    public const COLORS = ['red','gold','green','blue','purple','white','black'];
    public static function clean(string $html, bool $inline = false): string
    {
        // Quill exports spaces as NBSP; normalize them so saved prose wraps on phones.
        $html = str_replace(["&nbsp;", "&#160;", "&#xA0;", "\u{00A0}"], ' ', $html);
        if (!str_contains($html, '<')) return nl2br(htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $old = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors(); libxml_use_internal_errors($old);
        $walk = function (\DOMNode $node) use (&$walk, $inline): string {
            if ($node instanceof \DOMText) return htmlspecialchars($node->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if (!$node instanceof \DOMElement) return '';
            $tag = strtolower($node->tagName);
            if (in_array($tag, ['script','style','iframe','object','svg','math','template','form','input','button','textarea','noscript'])) return '';
            $content = ''; foreach ($node->childNodes as $child) $content .= $walk($child);
            if ($tag === 'br') return '<br>';
            if (in_array($tag, ['b','i'])) $tag = $tag === 'b' ? 'strong' : 'em';
            $allowed = ['p','strong','em','u','s','span','ul','ol','li','blockquote'];
            if (!in_array($tag, $allowed, true)) return $content;
            $class = '';
            foreach (preg_split('/\s+/', $node->getAttribute('class')) as $candidate) {
                if (preg_match('/^ql-color-(red|gold|green|blue|purple|white|black)$/', $candidate)) $class .= ' ' . $candidate;
            }
            if ($inline && in_array($tag, ['p','ul','ol','li','blockquote'])) return $content . ($tag === 'p' || $tag === 'li' ? '<br>' : '');
            return '<' . $tag . ($class ? ' class="' . trim($class) . '"' : '') . '>' . $content . '</' . $tag . '>';
        };
        $body = $doc->getElementsByTagName('body')->item(0); $out = '';
        if ($body) foreach ($body->childNodes as $node) $out .= $walk($node);
        return $inline ? preg_replace('/(?:<br>)+$/', '', $out) : $out;
    }
    public static function plain(string $html): string { return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
}
