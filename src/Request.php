<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BtcRelax;

class Request
{   
    public static function getHeaderByKey($keyName)
    {
        $result = false; 
        $headers = \BtcRelax\Utils::getHeaders();
        if (\property_exists($keyName, $headers)) {
            $result = $headers[$keyName];
        }
        return $result;
    }

    public static function getParamByKey($keyName)
    {
        $result = false;
        $params = \BtcRelax\Utils::getRequestParams();
        if (\property_exists($keyName, $params)) {
            $result = $params[$keyName];
        }
        return $result;
    }

    public static function getUserAgent()
    {
        return \filter_input(\INPUT_SERVER, 'HTTP_USER_AGENT');
    }
}
