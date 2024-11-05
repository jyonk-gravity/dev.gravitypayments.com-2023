<?php

namespace Perfmatters;

class HTML
{
    private static $offsets = array();

    public static function get_selector_elements($html, $selector)
    {
        //find tags with selector
        if(!preg_match_all("/<(?:div|section|figure|footer)[^>]*{$selector}[^>]*>/s", $html, $selector_tags, PREG_OFFSET_CAPTURE)) {
            return;
        }

        $elements = array();

        //get all tags
        preg_match_all('/<[^>]*>/', $html, $dom_tags, PREG_OFFSET_CAPTURE);

        //loop through selector tags
        foreach($selector_tags[0] as $selector_tag) {

            //skip if selector tag is inside an element we already matched
            if(!empty(self::$offsets)) {
                foreach(self::$offsets as $offset) {
                    if($selector_tag[1] > $offset[0] && $selector_tag[1] < $offset[1]) {
                        continue 2;
                    }
                }
            }

            $stack = array();

            //loop through dom tags
            foreach($dom_tags[0] as $dom_tag) {

                //wait until current tag position is after the selector tag
                if($dom_tag[1] < $selector_tag[1]) {
                    continue;
                }

                //skip some tags by default, self-closing, style, script, etc...
                if(preg_match('/^<\/?(area|base|br|col|command|embed|hr|img|input|keygen|link|meta|param|source|track|wbr|script|style|circle|rect|ellipse|line|path|poly|use|view|stop|set|image|animate|!--|!DOCTYPE)/i', $dom_tag[0])) {
                    continue;
                }

                //make sure it's not a closing tag
                if(strpos($dom_tag[0], '</') === false) {

                    //add current tag to stack
                    array_push($stack, $dom_tag[0]);
                }
                else {

                    //remove last tag from stack
                    array_pop($stack);
                }

                //end of element
                if(empty($stack)) {

                    //get the length of the entire element we need
                    $length = $dom_tag[1] - $selector_tag[1] + strlen($dom_tag[0]);

                    //save element string
                    $elements[] = substr($html, $selector_tag[1], $length);

                    //save the start and end positions of the element
                    self::$offsets[] = array($selector_tag[1], $selector_tag[1] + $length);

                    break;
                }
            }
        }

        return $elements;
    }
}