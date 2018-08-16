<?php

namespace BL\Slumen\Http;

class EventPublisher
{
    protected $subscriber = null;

    public function __construct(EventSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function publish($event, array $params = [])
    {
        $events = $this->subscriber->getEvents();
        if (array_key_exists($event, $events) && method_exists($this, $events[$event])) {
            return call_user_func_array([$this, $events[$event]], $params);
        }
    }
}
