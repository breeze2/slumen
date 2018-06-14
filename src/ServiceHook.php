<?php
namespace BL\Slumen;

interface ServiceHook
{
    public function startedHandle($server);

    public function stoppedHandle($server);

    public function workStartedHandle($server, $worker_id);

    public function workStoppedHandle($server, $worker_id);

    public function requestedHandle($request, $response);
    
    public function respondedHandle($request, $response);

    public function serverErrorHandle($error);
}
