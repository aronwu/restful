<?php
include '../src/Restful/Client.php';
include '../src/Restful/Exception.php';

$hostalias = [
    'order' => 'https://openapi.xxx.com/v1',
    'product' => 'https://openapi.xxx.com/v1',
];

class_alias('\Restful\Client', '\RestfulClient');
//set host alias
\RestfulClient::hostalias($hostalias);
//set http basic authorization
\RestfulClient::auth('oz4glwcM6IUy_pmViRfUR5MNms78', '0df5ae42fd6cc699bc27d43e45e759ee');
//GET method
try {
    echo \RestfulClient::host('order')->orders()->debug(true)->get();
} catch (Exception $ex) {
    print_r($ex);
}

try {
    echo \RestfulClient::host('order')->users('{user_id}')->addresses()->debug(true)->get([
        'user_id' => 8,
    ]);
} catch (Exception $ex) {
    print_r($ex);
}

try {
    echo \RestfulClient::host('order')->path('/products/{product_id}')->debug(true)->get([
        'product_id' => 8,
    ]);
} catch (Exception $ex) {
    print_r($ex);
}
