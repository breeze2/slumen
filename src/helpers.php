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

if (! function_exists('str_to_upper')) {
	function str_to_upper($subject)
	{
	    static $search  = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '-'];
	    static $replace = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '_'];
	    return str_replace($search, $replace, $subject);
	}
}
