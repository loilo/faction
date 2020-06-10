<?php

namespace App\Library\ResponseCache;

use Carbon\Carbon;
use DOMDocument;
use DOMNode;
use Spatie\ResponseCache\Replacers\Replacer;
use Symfony\Component\HttpFoundation\Response;

/**
 * This response cache replacer takes care of the "last updated" diff
 * in the package overview.
 */
class LatestChangeReplacer implements Replacer
{
    public function prepareResponseToCache(Response $response): void
    {
        if (!$response->getContent()) {
            return;
        }

        // Replace the content of all <time> elements holding the
        // "package__updated-time" class with an easy-to-replace token.
        $manipulatedHtml = $this->manipulateHtml(
            $response->getContent(),
            function (DOMNode $node) {
                /**
                 * @var DOMElement $node
                 */
                if (
                    $node->nodeType === XML_ELEMENT_NODE &&
                    $node->nodeName === 'time' &&
                    $node->getAttribute('class') === 'package__updated-time'
                ) {
                    $node->textContent = sprintf(
                        '{{diff-time::%s}}',
                        $node->getAttribute('datetime'),
                    );
                }
            },
        );

        $response->setContent($manipulatedHtml);
    }

    public function replaceInCachedResponse(Response $response): void
    {
        if (!$response->getContent()) {
            return;
        }

        $response->setContent(
            preg_replace_callback(
                '/\\{\\{diff-time::(.+)\\}\\}/U',
                fn(array $matches) => Carbon::parse(
                    $matches[1],
                )->diffForHumans(),
                $response->getContent(),
            ),
        );
    }

    private function walkDom(DOMNode $domNode, callable $callback): void
    {
        foreach ($domNode->childNodes as $node) {
            $callback($node);

            if ($node->hasChildNodes()) {
                $this->walkDom($node, $callback);
            }
        }
    }

    private function manipulateHtml(string $html, callable $callback): string
    {
        $dom = new DOMDocument();

        // Don't spread warnings when encountering malformed HTML
        $previousXmlErrorBehavior = libxml_use_internal_errors(true);

        // Use XML processing instruction to properly interpret document as UTF-8
        @$dom->loadHTML(
            '<?xml encoding="utf-8" ?>' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        foreach ($dom->childNodes as $item) {
            if ($item->nodeType === XML_PI_NODE) {
                $dom->removeChild($item);
            }
        }
        $dom->encoding = 'UTF-8';

        $this->walkDom($dom, $callback);

        // Turn DOM back into HTML and remove
        $result = trim($dom->saveHTML());

        // Restore previous XML error behavior
        libxml_use_internal_errors($previousXmlErrorBehavior);

        return $result;
    }
}
