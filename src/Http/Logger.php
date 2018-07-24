<?php

namespace BL\Slumen\Http;

class Logger
{
    protected $stream = null;
    protected $file   = null;

    public function __construct($file, $single)
    {
        $this->file   = $file;
        $single || $this->stream = fopen($this->file, 'a');
    }

    public function accessJSON(array $data)
    {
        if($this->stream) {
            fwrite($this->stream, json_encode($data) . "\n");
        } else {
            error_log(json_encode($data) . "\n", 3, $this->file);
        }
    }
}
