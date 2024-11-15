<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 12.06.2018
 * Time: 14:23
 */

class ShortPixelLzw
{
    function compress($uncompressed)
    {
        $dictSize = 256;
        $dictionary = array();
        for ($i = 0; $i < 256; $i++) {
            $dictionary[chr($i)] = $i;
        }
        $w = "";
        $result = "";
        for ($i = 0; $i < strlen($uncompressed); $i++) {
            $c = $this->charAt($uncompressed, $i);
            $wc = $w . $c;
            if (isset($dictionary[$wc])) {
                $w = $wc;
            } else {
                $result .= chr($dictionary[$w]);
                $dictionary[$wc] = $dictSize++;
                $w = "" . $c;
            }
        }
        if ($w != "") {
            $result .= chr($dictionary[$w]);
            return $result;
        }
   }
    function decompress($compressed)
    {
        $compressed = explode(",", $compressed);
        $dictSize = 256;
        $dictionary = array();
        for ($i = 1; $i < 256; $i++) {
            $dictionary[$i] = chr($i);
        }
        $w = chr($compressed[0]);
        $result = $w;
        for ($i = 1; $i < count($compressed); $i++) {
            $entry = "";
            $k = $compressed[$i];
            if (isset($dictionary[$k])) {
                $entry = $dictionary[$k];
            } else if ($k == $dictSize) {
                $entry = $w . $this->charAt($w, 0);
            } else {
                return null;
            }
            $result .= $entry;
            $dictionary[$dictSize++] = $w . $this->charAt($entry, 0);
            $w = $entry;
        }
        return $result;
    }

    function charAt($string, $index)
    {
        if ($index < mb_strlen($string)) {
            return mb_substr($string, $index, 1);
        } else {
            return -1;
        }
    }
}