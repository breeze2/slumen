<?php
namespace BL\Slumen;

interface ServiceHook
{
    public function startedHandle($server);

    public function stoppedHandle($server);

    public function workerStartedHandle($server, $worker_id);

    public function workerStoppedHandle($server, $worker_id);

    public function requestedHandle($request, $response);

    public function respondedHandle($request, $response);

    public function workerErrorHandle($server, $worker_id, $worker_pid, $exit_code, $signal);

    public function serverErrorHandle($error);
}
