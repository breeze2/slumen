<?php
namespace BL\Slumen\Xhgui;

use MongoCollection;
use MongoId;
use Xhgui_Saver_Mongo;

class SaverMongo extends Xhgui_Saver_Mongo
{
    /**
     * @var MongoCollection
     */
    private $_collection;

    /**
     * @var MongoId lastProfilingId
     */
    private static $lastProfilingId;

    public function __construct(MongoCollection $collection)
    {
        $this->_collection = $collection;
    }

    public function save(array $data)
    {
        $data['_id'] = self::getLastProfilingId();
        // var_dump($data['_id']);
        return $this->_collection->insert($data, array('w' => 0));
    }

    /**
     * Return profiling ID
     * @return MongoId lastProfilingId
     */
    public static function getLastProfilingId()
    {
        return new MongoId();
    }
}
