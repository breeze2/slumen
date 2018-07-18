<?php

use BL\Slumen\Container;

if (! function_exists('slumen')) {
    /**
     * Get the available container instance.
     *
     * @param  string  $make
     * @return mixed|\Laravel\Lumen\Application
     */
    function slumen($make = null)
    {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make);
    }
}
