<?php
/**
 * Created by PhpStorm.
 * User: lucio
 * Date: 25/09/17
 * Time: 18:21
 */

namespace Brewerydb;

class Validation
{
    public static function text_validation($text)
    {

        if (preg_match('/^[a-z0-9-\s]+$/i', $text) or empty($text)) {
            return true;
        } else {
            return false;

        }

    }

}