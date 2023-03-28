<?php

namespace Csoellinger\SilverStripe\LimitCharactersWithHtml;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBHTMLVarchar;
use Minifier\TinyMinify;

/**
 * A simple SilverStripe extension to limit DBHtmlText and DBHtmlVarChar
 * without loosing html tags. HTML will be minified to ignore white spaces
 * between tags and/or html comments. Options can be set at private static
 * $html_min_options variable or via config file.
 *
 * @property DBHTMLText|DBHTMLVarchar $owner
 *
 * @example
 * <code>
 * <?php
 *
 * DBHTMLText::create('Test')
 *   ->setValue($htmlText)
 *   ->LimitCharactersWithHtml(150)
 *
 * </code>
 */
class LimitCharactersWithHtmlExtension extends DataExtension
{
    /** @var bool */
    private static bool $html_min = true;

    /** @var array */
    private static array $html_min_options = [
        'collapse_whitespace' => true,
        'disable_comments' => false,
    ];

    /** {@inheritDoc} */
    private static $casting = [
        'LimitCharactersWithHtml' => 'HTMLText',
        'LimitCharactersWithHtmlToClosestWord' => 'HTMLText',
    ];

    /**
     * Limit this field's content by a number of characters. It can consider
     * html and limit exact or at word ending.
     *
     * @param int           $limit        Number of characters to limit by.
     * @param string|false  $add          Ellipsis to add to the end of truncated string.
     * @param bool          $exact        Truncate exactly or at word ending.
     *
     * @return string HTML text with limited characters.
     */
    public function LimitCharactersWithHtml($limit = 20, $add = false, $exact = true): string
    {
        $truncate = '';
        $text = $this->owner->getValue();
        // Force cast to bool values
        $exact = (bool) $exact;
        // If $add is character "0" we define it as false. Needed by SilverStripe cause we could not send false params
        // within templates.
        $add = $add === '0' ? false : $add;
        // Use default ellipsis if not set
        $add = $add === false ? $this->owner->defaultEllipsis() : $add;
        // Minify html to get better results
        $text = TinyMinify::html($text, ['collapse_whitespace' => true]);

        // if the plain text is shorter than the maximum length, return the whole text
        if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $limit) {
            return $text;
        }

        // splits all html-tags to scannable lines
        preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

        $total_length = mb_strlen($add);
        $open_tags = [];
        $truncate = '';

        foreach ($lines as $line_matchings) {
            // if there is any html-tag in this line, handle it and add it (uncounted) to the output
            if (!empty($line_matchings[1])) {
                // if it's an "empty element" with or without xhtml-conform closing slash
                if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) { //phpcs:ignore
                    // do nothing
                    // if tag is a closing tag
                } elseif (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                    // delete tag from $open_tags list
                    $pos = array_search($tag_matchings[1], $open_tags);
                    if ($pos !== false) {
                        unset($open_tags[$pos]);
                    }
                    // if tag is an opening tag
                } elseif (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                    // add tag to the beginning of $open_tags list
                    array_unshift($open_tags, strtolower($tag_matchings[1]));
                }

                // add html-tag to $truncate'd text
                $truncate .= $line_matchings[1];
            }

            // calculate the length of the plain text part of the line; handle entities as one character
            $content_length = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));

            if ($total_length + $content_length > $limit) {
                // the number of characters which are left
                $left = $limit - $total_length;
                $entities_length = 0;

                // search for html entities
                if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) { //phpcs:ignore
                    // calculate the real length of all entities in the legal range
                    foreach ($entities[0] as $entity) {
                        if ($entity[1] + 1 - $entities_length <= $left) {
                            $left--;
                            $entities_length += mb_strlen($entity[0]);
                        } else {
                            // @codeCoverageIgnoreStart
                            // no more characters left
                            break;
                            // @codeCoverageIgnoreEnd
                        }
                    }
                }

                $truncate .= mb_substr($line_matchings[2], 0, $left + $entities_length);
                // maximum length is reached, so get off the loop
                break;
            } else {
                $truncate .= $line_matchings[2];
                $total_length += $content_length;
            }
            // if the maximum length is reached, get off the loop
            if ($total_length >= $limit) {
                // @codeCoverageIgnoreStart
                break;
                // @codeCoverageIgnoreEnd
            }
        }

        if (!$exact) {
            // ...search the last occurrence of a space...
            $spacePosition = mb_strrpos($truncate, ' ');

            if (isset($spacePosition)) {
                // ...and cut the text in this position
                $truncate = mb_substr($truncate, 0, $spacePosition);
            }
        }

        // add the defined ending to the text
        $truncate .= $add;

        // close all unclosed html-tags
        foreach ($open_tags as $tag) {
            $truncate .= '</' . $tag . '>';
        }

        return $truncate;
    }

    /**
     * Limit this field's content by a number of characters and truncate the
     * field to the closest complete word.
     *
     * @param int          $limit        Number of characters to limit by.
     * @param string|false $add          Ellipsis to add to the end of truncated string.
     *
     * @return string HTML text value with limited characters truncated to the closest word.
     */
    public function LimitCharactersWithHtmlToClosestWord(int $limit = 20, $add = false): string
    {
        return $this->LimitCharactersWithHtml($limit, $add, false);
    }

    /**
     * Check if a string is longer than a number of characters. It excludes html
     * by default.
     *
     * @param bool $excludeHtml Default is true
     *
     * @return bool
     */
    public function LongerThan(int $limit, bool $excludeHtml = true): bool
    {
        $text = $this->owner->getValue();

        if ((bool) $excludeHtml === true) {
            return mb_strlen(strip_tags($text)) > $limit;
        }

        return mb_strlen($text) > $limit;
    }
}
