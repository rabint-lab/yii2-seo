<?php
namespace rabint\seo\services;


use app\modules\account\models\Site;
use rabint\seo\models\Option;
use PHPUnit\Runner\Exception;
use rabint\helpers\str;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\WebSite;
use yii\helpers\Url;

class SeoService
{
    /**
     * @var self
     */
    private static $schema = null;

    private static $meta = [];

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
    public function setSchemaMeta($data=[]){
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

    /**
     * @return array
     */

    public function getAllMeta(){
        return self::$meta;
    }

    /**
     * @param $key
     * @return mixed|null
     */

    public static function getSeoMeta($key){
        if(isset(self::$meta[$key]))
            return self::$meta[$key];
        return null;
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */

    public function setSeoMeta($key,$value){
        self::$meta[$key] = $value;
        return true;
    }

    /**
     * @return string
     */

    public function renderMeta(){
        $return = '';
        foreach (self::$meta as $key=>$value){
            $return .='<meta name="'.$key.'" content="'.$value.'">';
        }
        return $return;
    }

    public function renderHeadSeo($url){
        $return = '';
        $return .= $this->renderSchema();
        $return .= $this->renderMeta();
        $return .= $this->renderOptions($url, \rabint\seo\models\Option::LOCATION_HEAD);
        return $return;
    }

    public function renderFooterSeo($url){
        $return = '';
        $return .= $this->renderOptions($url, \rabint\seo\models\Option::LOCATION_FOOTER);
        return $return;
    }

    public function renderOptions($route,$location){
        $config = Option::getConfigArray();
        if($config['seo']!=true) return '';
        $options = Option::find()
            ->AndWhere(['location'=>$location])
            ->AndWhere(['not in','route',['']])
            ->all();
        $return = '';
        foreach ($options as $item){
            if($item->route == '*'||strpos($route,$item->route)||(Url::canonical()==Url::base(true).$route&&$item->route=='__HOME__'))
                $return .= static::MakeTag($item);
        }
        return $return;
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
