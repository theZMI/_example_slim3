<?php

    // Autoloader for myself models and libs
    function _AutoLoad($className)
    {
        if (is_readable(BASEPATH . "_private/models/{$className}.php"))
        {
            require_once BASEPATH . "_private/models/{$className}.php";
        }
        elseif (is_readable(BASEPATH . "_private/libs/{$className}.php"))
        {
            require_once BASEPATH . "_private/libs/{$className}.php";
        }
    }
    spl_autoload_register('_AutoLoad');

    // Simple debug
    function Xmp($a)
    {
        printf("<xmp>%s</xmp>", print_r($a, true));
    }

    // Call url and get answer
    function CallUrl($url)
    {
        $options = array
        (
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
        );
    
        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );
    
        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        
        if ($header['errno'])
        {
            return NULL;
        }
        
        return $header['content'];
    }