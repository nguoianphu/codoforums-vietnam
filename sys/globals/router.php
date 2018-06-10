<?php

class Klein {
    
    function respond($a,$b) {}
    
}

$klein = new Klein();

function dispatch_get($route, $func) {

    global $klein; //or use singleton like below
    //$klein = CODOF\Router\Router::get_instance();
    $klein->respond('GET', $route, $func);
}

function dispatch_post($route, $func) {

    global $klein;
    $klein->respond('POST', $route, $func);
}

function dispatch($route, $func) {

    global $klein;
    $klein->respond(array('POST', 'GET'), $route, $func);
}

function run() {

    global $klein;
 //   $klein->dispatch();
}


$klein->respond('/codoforum/', function ($req, $resp) {
    
    //var_dump($req);
    //var_dump($resp);
    return 'Hello World!';
});
/*
$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg);
        $klein->service()->back();
    });
});
*/