<?php
namespace BL\Slumen\Xhgui;

use MongoClient;
use Xhgui_Saver;
use Xhgui_Saver_File;
use Xhgui_Saver_Upload;

class Saver extends Xhgui_Saver
{
    public static function factory($config)
    {
        if (!array_key_exists('save.handler', $config)) {
            return null;
        }
        switch ($config['save.handler']) {

            case 'file':
                return new Xhgui_Saver_File($config['save.handler.filename']);

            case 'upload':
                $timeout = 3;
                if (isset($config['save.handler.upload.timeout'])) {
                    $timeout = $config['save.handler.upload.timeout'];
                }
                return new Xhgui_Saver_Upload(
                    $config['save.handler.upload.uri'],
                    $timeout
                );

            case 'mongodb':
            default:
                $mongo      = new MongoClient($config['db.host'], $config['db.options']);
                $collection = $mongo->{$config['db.db']}->results;
                $collection->findOne();
                return new SaverMongo($collection);
        }
    }
}
