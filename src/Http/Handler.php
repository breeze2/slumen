<?php

namespace BL\Slumen\Http;

use Closure;

class Handler
{
    const METHOD_HANDLE = 'handle';

    protected $onServerStarted;

    protected $onServerStopped;

    protected $onWorkerStarted;

    protected $onWorkerStopped;

    protected $onWorkerError;

    protected $onRequested;

    protected $onResponded;

    protected $onAppError;

    public function register($hook_name, $callback)
    {
        if ($callback instanceof Closure) {
            $this->$hook_name = $callback;
        } else if (class_exists($callback) && method_exists($callback, self::METHOD_HANDLE)) {
            $this->$hook_name = new $callback;
        }
    }

    public function handle($hook_name, array $parameter = [])
    {
        if (isset($this->$hook_name)) {
            $hook = $this->$hook_name;

            if ($hook instanceof Closure) {
                call_user_func_array($hook, $parameter);
            } else {
                call_user_func_array([$hook, self::METHOD_HANDLE], $parameter);
            }
        }
    }
}
