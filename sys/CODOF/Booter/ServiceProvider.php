<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Booter;

class ServiceProvider {

    /**
     *
     * Services required
     * @var array 
     */
    protected $providers = array(
        "immediate" => array(),
        "deferred" => array()
    );

    //Registers all required services for codoforum
    public function register(Load $container) {

        $container->bindShared('db', function() {

            return new \CODOF\Database\Connector();
        });
        
        $container->bindShared('i', function() {

            return new \CODOF\User\CurrentUser\CurrentUser();
        });
        
    }

}
