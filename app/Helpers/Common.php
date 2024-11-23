<?php
namespace App\Helpers;
    /* 
    helper to compare a phrase with an array of keywords, and returns true if it matches any of the keywords
    */

    class KeywordMatcher
    {
        public static function matchKeywords($phrase, $keywords)
        {
            foreach ($keywords as $keyword) {
                if (stripos($phrase, $keyword) !== false) {
                    return true;
                }
            }
            
            return false;
        }
    }

    
