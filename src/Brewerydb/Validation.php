<?php
/**
 * Created by PhpStorm.
 * User: lucio
 * Date: 25/09/17
 * Time: 18:21
 */

namespace Brewerydb;

use Silex\Application;
use Symfony\Component\Security\Csrf\CsrfToken;

class Validation
{
    public static function textValidation($text)
    {

        if (preg_match('/^[a-z0-9-\s]+$/i', $text) or empty($text)) {
            return true;
        } else {
            return false;

        }

    }

    public static function checkToken(Application $app, $_csrf_token, &$body)
    {

        if (!$app['csrf.token_manager']->isTokenValid(new CsrfToken('token_id', $_csrf_token))) {
            $body[] = [
                'name' => 'Token not recognized. You can not access the data.',
                'description' => '',
                'image' => ''
            ];
            $data['data'] = $body;  // Mounts the API return data

            return false;
        } else {
            return true;
        }

    }

}