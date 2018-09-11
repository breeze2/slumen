<?php

namespace BL\Slumen\Redis\Connections;

use Illuminate\Redis\Connections\PhpRedisConnection as BasePhpRedisConnection;
use Redis;

/**
 * @mixin \Redis
 */
class PhpRedisConnection extends BasePhpRedisConnection
{
    use ConnectionTrait;
}
