<?php
namespace BL\Slumen\Factory;

trait CoroutineConnectionTrait
{
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
