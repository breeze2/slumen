<?php

namespace BL\Slumen\Redis\Connections;

use Illuminate\Redis\Connections\PredisConnection as BasePredisConnection;

/**
 * @mixin \Predis\Client
 */
class PredisConnection extends BasePredisConnection
{
    use ConnectionTrait;
}
