<?php
/**
 * Helps with holding the affected tags list and their classes, in order to limit the JS load.
 * User: simon
 * Date: 31.07.2020
 */

namespace ShortPixel\AI;

class AffectedTags {

    const SRC_ATTR = 1;
    const CSS_ATTR = 2;
    const SCRIPT_ATTR = 4;
    const INNER_CSS = 8;

    private $affectedTags;
    private $classMap;
    protected $logger = false;

    public function __construct() {
        $this->affectedTags = $this->getRecorded();
        $this->classMap = [];
        $this->logger = \ShortPixelAILogger::instance();
        //(SHORTPIXEL_AI_DEBUG & \ShortPixelAILogger::DEBUG_AREA_HTML) && $this->logger->log("NEW AFFECTED TAGS - type " . gettype($this->affectedTags));
    }

    public function get($tag = false) {
        if($tag) {
            return isset($this->affectedTags[$tag]) ? $this->affectedTags[$tag] : false;
        }
        else return $this->affectedTags;
    }

    public function getSelectors() {
        return $this->group($this->classMap);
    }

    public function add($tag, $type) {
        if(ctype_alnum($tag)) {
            if(!isset($this->affectedTags[$tag])) {
                $this->affectedTags[$tag] = 0;//(object)['srcAttrs' => [], 'bgClasses' => [], 'srcsetAttrs' => [], 'innerCss' => false];
            }
            $this->affectedTags[$tag] = $type | $this->affectedTags[$tag];
            (SHORTPIXEL_AI_DEBUG & \ShortPixelAILogger::DEBUG_AREA_HTML) && $this->logger->log("ADDED AFFECTED TAG " . $tag . " AS " . $type);
        }
    }

    public function remove($tag) {
        unset($this->affectedTags[$tag]);
        (SHORTPIXEL_AI_DEBUG & \ShortPixelAILogger::DEBUG_AREA_HTML) && $this->logger->log("REMOVED AFFECTED TAG " . $tag);
    }

    public function record() {
        $affectedTags = $this->getRecorded();
        foreach($this->affectedTags as $tag => $val) {
            if(!isset($affectedTags[$tag]) || ($affectedTags[$tag] !== $val)) {
                //this makes sure that we don't run the update if all the affected tags are already recorded.
                update_option('spai_settings_lazy_ajax_tags', $this->mergeTags($this->affectedTags, $affectedTags));
                return;
            }
        }
    }

    public function filter($flag) {
        $filtered = [];
        foreach ($this->affectedTags as $tag => $flags) {
            if($flags | $flag) {
                $filtered[$tag] = $flags;
            }
        }
        return $filtered;
    }

    public function getRecorded() {
        return get_option('spai_settings_lazy_ajax_tags', array());
    }

    public function getAll() {
        return $this->mergeTags($this->affectedTags, get_option('spai_settings_lazy_ajax_tags', array()));
    }

    protected function mergeTags($tags, $moreTags) {
        foreach($tags as $key => $val) {
            $moreTags[$key] = (isset($moreTags[$key]) ? $moreTags[$key] : 0) | $val;
        }
        return $moreTags;
    }


}