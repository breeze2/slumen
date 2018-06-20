<?php

namespace BL\Slumen\Xhgui;

use MongoDate;

class Collector
{
    protected $options;
    protected $config;
    protected $save_handler;
    protected $server;

    public function __construct($config_path)
    {
        Config::load($config_path);
        $this->options      = Config::read('profiler.options');
        $this->save_handler = Config::read('save.handler');

        $config = Config::all();
        $config += array('db.options' => array());
        $this->config = $config;
    }

    public function collectorDisable()
    {
        $server = $this->server;
        $uri    = $this->uri;
        $get    = $this->get;
        $data   = [];
        $time   = array_key_exists('request_time', $server)
        ? $server['request_time']
        : time();

        $delimiter        = (strpos($server['request_time_float'], ',') !== false) ? ',' : '.';
        $requestTimeFloat = explode($delimiter, $server['request_time_float']);
        if (!isset($requestTimeFloat[1])) {
            $requestTimeFloat[1] = 0;
        }

        if ($this->save_handler === 'mongodb') {
            $requestTs      = new MongoDate($time);
            $requestTsMicro = new MongoDate($requestTimeFloat[0], $requestTimeFloat[1]);
        } else {
            $requestTs      = array('sec' => $time, 'usec' => 0);
            $requestTsMicro = array('sec' => $requestTimeFloat[0], 'usec' => $requestTimeFloat[1]);
        }

        if (extension_loaded('uprofiler')) {
            $data['profile'] = uprofiler_disable();
        } else if (extension_loaded('tideways')) {
            $data['profile'] = tideways_disable();
        } elseif (extension_loaded('tideways_xhprof')) {
            $data['profile'] = tideways_xhprof_disable();
        } else {
            $data['profile'] = xhprof_disable();
        }

        // ignore_user_abort(true);
        // flush();
        $data['meta'] = array(
            'url'              => $uri,
            'SERVER'           => $server,
            'get'              => $get,
            'env'              => [],
            'simple_url'       => Util::simpleUrl($uri),
            'request_ts'       => $requestTs,
            'request_ts_micro' => $requestTsMicro,
            'request_date'     => date('Y-m-d', $time),
        );
        return $this->saveData($data);
    }

    public function saveData($data)
    {
        try {
            $saver = Saver::factory($this->config);
            $saver && $saver->save($data);
            unset($saver);
        } catch (Exception $e) {
            var_dump('xhgui - ' . $e->getMessage());
        }
    }

    public function collectorEnable()
    {
        $options = $this->options;
        if (extension_loaded('uprofiler')) {
            uprofiler_enable(UPROFILER_FLAGS_CPU | UPROFILER_FLAGS_MEMORY, $options);
        } else if (extension_loaded('tideways')) {
            tideways_enable(TIDEWAYS_FLAGS_CPU | TIDEWAYS_FLAGS_MEMORY | TIDEWAYS_FLAGS_NO_SPANS, $options);
        } elseif (extension_loaded('tideways_xhprof')) {
            tideways_xhprof_enable(TIDEWAYS_XHPROF_FLAGS_CPU | TIDEWAYS_XHPROF_FLAGS_MEMORY);
        } else {
            if (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 4) {
                xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY | XHPROF_FLAGS_NO_BUILTINS, $options);
            } else {
                xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY, $options);
            }
        }
    }

    public function setInfo($uri, $get, $server)
    {
        $this->uri    = $uri;
        $this->get    = $get;
        $this->server = $server;
    }

}
