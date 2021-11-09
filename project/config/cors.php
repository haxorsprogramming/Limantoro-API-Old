<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*','*','/img/*'],

    'allowed_methods' => ['*'],
    // 'allowed_methods' => ['POST','GET','OPTIONS','PUT',"DELETE"],

    // 'allowed_origins' => ['*'],
    // 'allowed_origins' => ['http://127.0.0.1:3000'],
    'allowed_origins' => ['http://limantoroagungproperty.com'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],
    // 'allowed_headers' => ['Accept','Content-Type','X-Auth-Token','Origin','Authorization'],

    // 'exposed_headers' => ['Access-Control-Allow-Origin'],
    'exposed_headers' => ['*'],

    'allowed_hosts'=>['*'],

    'max_age' => 0,

    'supports_credentials' => true,

];
