<?php
/**
 * Created by : simon
 * Date: 26.01.2021
 */

namespace ShortPixel\AI;

class TagRule
{
    public $tag;
    public $attr;
    public $classFilter;
    public $attrFilter;
    public $attrValFilter;
    public $mergeAttr;
    public $eager;
    public $type;
    public $callback;
    public $quickMatch; //this is a faster regex that checks if we go further for that particular
    public $frontEager;
    public $noFront; // this indicates not to send the rule to the (Vanilla)JS as it should remain as it is. For example a lazy img href that is used for a lightbox, it doesn't need to be replaced until it's in the final img.
    public $frontResize;

    //Only works with eager true
    private $customCompression = '';
    //this is a function that if set, is called with the tag contents (example TagRules::addCrossOrigin)
    private $postProcessor = false;

    public $used = [];

    public function __construct($tag, $attr, $classFilter = false, $attrFilter = false, $attrValFilter = false, $mergeAttr = false, $eager = false,
                                $type = 'url', $callback = false, $quickMatch = false, $frontEager = false, $noFront = false, $frontResize = true) {
        $this->tag = $tag;
        $this->attr = $attr;
        $this->classFilter = $classFilter;
        $this->attrFilter = $attrFilter;
        $this->attrValFilter = $attrValFilter;
        $this->mergeAttr = $mergeAttr;
        $this->eager = $eager;
        $this->type = $type;
        $this->callback = $callback;
        $this->quickMatch = $quickMatch;
        $this->frontEager = $frontEager;
        $this->noFront = $noFront;
        $this->frontResize = $frontResize;
    }

    /**
     * @return bool|string returns the custom compression if set, otherwise returns false
     */
    public function getCustomCompression()
    {
        return $this->customCompression;
    }

    /**
     * @param string $customCompression
     */
    public function setCustomCompression($customCompression)
    {
        $this->customCompression = $customCompression;
    }

    public function setPostProcessor(array $func)
    {
        $this->postProcessor = $func;
    }

    public function getPostProcessor() {
        return $this->postProcessor;
    }
}