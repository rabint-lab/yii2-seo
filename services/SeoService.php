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

    public function setSeoMetaArray($array){
        self::$meta = array_merge(self::$meta,$array);
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

    public function renderHeadSeo($url=null){
        if(empty($url)){
            $url = \rabint\helpers\uri::current();
        }
        $option = Option::getConfigArray();
        $return = '';
        $return .= $this->renderSchema();
        $return .= $this->renderMeta();
        $return .= $this->renderOptions($url, \rabint\seo\models\Option::LOCATION_HEAD);
        if(isset($option['index'])&&!$option['index']){
            $return .= "<meta name=\"robots\" content=\"[noindex, nofollow]\">";
        }
        return $return;
    }

    public function renderFooterSeo($url=null){
        if(empty($url)){
            $url = \rabint\helpers\uri::current();
        }
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
            if($item->route == '*'||strpos($route,$item->route)||(Url::current()==config('defaultRoute')&&$item->route=='__HOME__'))
                $return .= Option::MakeTag($item);
        }
        return $return;
    }

}
