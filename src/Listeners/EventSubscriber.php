<?php

namespace BL\Slumen\Listeners;

class EventSubscriber
{
    public function onAppError($event) {}

    public function onServerRequested($event) {}

    public function onServerResponded($event) {}

    public function onServerStarted($event) {}

    public function onServerStopped($event) {}

    public function onWorkerError($event) {}

    public function onWorkerStarted($event) {}

    public function onWorkerStopped($event) {}

    public function subscribe($events)
    {
        $events->listen(
            'BL\Slumen\Events\AppError',
            'BL\Slumen\Listeners\EventSubscriber@onAppError'
        );

        $events->listen(
            'BL\Slumen\Events\ServerRequested',
            'BL\Slumen\Listeners\EventSubscriber@onServerRequested'
        );

        $events->listen(
            'BL\Slumen\Events\ServerResponded',
            'BL\Slumen\Listeners\EventSubscriber@onServerResponded'
        );

        $events->listen(
            'BL\Slumen\Events\ServerStarted',
            'BL\Slumen\Listeners\EventSubscriber@onServerStarted'
        );

        $events->listen(
            'BL\Slumen\Events\ServerStopped',
            'BL\Slumen\Listeners\EventSubscriber@onServerStopped'
        );

        $events->listen(
            'BL\Slumen\Events\WorkerError',
            'BL\Slumen\Listeners\EventSubscriber@onWorkerError'
        );

        $events->listen(
            'BL\Slumen\Events\WorkerStarted',
            'BL\Slumen\Listeners\EventSubscriber@onWorkerStarted'
        );

        $events->listen(
            'BL\Slumen\Events\WorkerStopped',
            'BL\Slumen\Listeners\EventSubscriber@onWorkerStopped'
        );
    }
}
