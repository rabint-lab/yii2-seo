<?php
namespace rabint\seo\services;


use app\modules\account\models\Site;
use PHPUnit\Runner\Exception;
use rabint\helpers\str;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\WebSite;

class SeoService
{
    /**
     * @var self
     */
    private static $schema = null;

    public static function factory(){
        return new static();
    }

    /**
     * @param $object
     * @return self
     */
    public function setSchema($object)
    {
        if((new \ReflectionObject($object))->getNamespaceName()!=='Spatie\SchemaOrg'){
            throw new Exception(
                \sprintf(
                    'this object is not schema'
                )
            );
        }
        self::$schema = $object;
        return $this;
    }

    /**
     * @return SeoService
     */

    public function getSchema()
    {
        return self::$schema;
    }

    /**
     * @param array $data
     * @return SeoService
     */
    public function addData($data=[]){
        $object = self::getSchema();
        foreach ($data as $key=>$value){
            $object->$key($value);
        }
        self::setSchema($object);
        return $this;
    }

    /**
     * @return mixed
     */
    public function renderSchema(){
        if(self::$schema===null)
            return null;
        return self::$schema->toScript();
    }

//    public function test(){
//        $object=\Spatie\SchemaOrg\Schema::webSite();
//        $object->url();
//        $object->name();
//        $object->description();
//        $object->about();
//        $object->comment();
//    }

}