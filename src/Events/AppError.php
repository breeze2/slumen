<?php

namespace BL\Slumen\Events;

use ErrorException;

class AppError
{
    public $error;
    public function __construct(ErrorException $error)
    {
        $this->$error = $error;
    }
}
