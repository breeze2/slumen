<?php
namespace BL\Slumen\Factory;

trait CoroutineConnectionTrait
{
    /**
     * The name of Provider
     * @var string
     */
    protected $provider_name;

    /**
     * The last time used connection.
     * @var int
     */
    protected $last_used_at;

    /**
     * Is connection destroyed?
     * @var boolean
     */
    protected $is_destroyed = false;

    public function getProviderName()
    {
        return $this->provider_name;
    }

    public function setProviderName($name)
    {
        $this->provider_name = $name;
    }

    public function getLastUsedAt()
    {
        return $this->last_used_at;
    }

    public function setLastUsedAt($time)
    {
        $this->last_used_at = $time;
    }

    public function isDestroyed()
    {
        return $this->is_destroyed;
    }

    public function destroy()
    {
        $this->is_destroyed = true;
    }
}
