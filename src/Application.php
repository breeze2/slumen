<?php

namespace BL\Slumen;

use Laravel\Lumen\Application as LumenApplication;

class Application extends LumenApplication{

    public function getMiddleware(){
        return $this->middleware;
    }

    public function callTerminableMiddleware($response){
        parent::callTerminableMiddleware($response);
    }
}