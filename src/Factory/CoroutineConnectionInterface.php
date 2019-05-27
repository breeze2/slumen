<?php
namespace BL\Slumen\Factory;

interface CoroutineConnectionInterface
{
    /**
     * @return string The provider name.
     */
    public function getProviderName();

    /**
     * @param string $name
     * @return void
     */
    public function setProviderName($name);

    /**
     * @return float|int The time connection last was used at.
     */
    public function getLastUsedAt();

    /**
     * @param float|int $time
     * @return void
     */
    public function setLastUsedAt($time);

    /**
     * @return boolean
     */
    public function isDestroyed();

    /**
     * @return void
     */
    public function destroy();
}
