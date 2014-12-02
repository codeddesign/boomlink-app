<?php

class CurlController
{
    public function indexAction()
    {
        exit('nothing to do here.');
    }

    public function getResponse($link)
    {
        $con = curl_init();
        curl_setopt_array($con, array(
                CURLOPT_URL => $link,
                // defaults:
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_USERAGENT => "boomlink-app",

                // don't change:
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FRESH_CONNECT => true,
                CURLOPT_SSL_VERIFYPEER => false,
            )
        );
        $content = curl_exec($con);
        curl_close($con);

        return $content;

    }
}