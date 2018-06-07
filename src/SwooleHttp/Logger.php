<?php

namespace BL\Slumen\SwooleHttp;

class Logger
{
	protected $stream = null;
	protected $file = null;

	public function __construct($file)
	{
		$this->file = $file;
		$this->stream = fopen($this->file, 'a');
	}

	public function accessJSON(array $data)
	{
		fwrite($this->stream, json_encode($data) . "\n");
	}
}
