<?php

namespace BL\Slumen\Http;

class Logger
{
    const FILE_PER_DAY   = 'Y-m-d';
    const FILE_PER_MONTH = 'Y-m';
    const FILE_PER_YEAR  = 'Y';

    protected $stream    = null;
    protected $path      = null;
    protected $file_name = null;

    protected $single = null;

    protected $prefix   = '';
    protected $date     = '';
    protected $file_per = '';

    private $is_opened = false;

    public function __construct($path, $file_name, $single)
    {
        $this->path      = $path;
        $this->file_name = $file_name;
        $this->single    = $single;
    }

    public function initialize(array $config)
    {
        if (array_key_exists('file_per', $config)) {
            $this->file_per = $config['file_per'];
            $this->date     = date($this->file_per);
        }
        if (array_key_exists('prefix', $config)) {
            $this->prefix = $config['prefix'];
        }
    }

    public function open()
    {
        if ($this->is_opened) {
            $this->is_opened = true;

            $file = $this->path . '/' . $this->prefix . $this->date . $this->file_name;
            $this->single ||
            $this->stream = fopen($file, 'a');
        }
    }

    public function addAccessInfo(array $data)
    {
        if (!$this->is_opened) {
            return;
        }
        if ($this->stream) {
            if ($this->date) {
                $now = date($this->file_per);
                if ($this->date !== $now) {
                    fclose($this->stream);
                    $this->date = $now;

                    $file = $this->path . '/' . $this->prefix . $this->date . $this->file_name;
                    $this->single ||
                    $this->stream = fopen($file, 'a');
                }
            }
            fwrite($this->stream, json_encode($data) . "\n");
        } else {
            error_log(json_encode($data) . "\n", 3, $this->path . $this->file);
        }
    }
}
