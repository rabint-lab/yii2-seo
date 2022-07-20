<?php
namespace rabint\seo\behaviors;


use rabint\seo\models\Option;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use Yii;

class seoSiteMapBehavior extends Behavior
{
    public $query;
    public $baseArray = [];
    public $formatProperty;
    public $formatArrays = [];
    public $thumbnailProperty;
    public $thumbnailArray;
    public $events;
    public $limit;
    public $name;

    public function events()
    {
        if ($this->events == NULL) {
            return [
                ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
                ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
                ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            ];
        }
        $ret = [];
        foreach ($this->events as $k => $evnt) {
            $ret[$evnt] = 'afterSave';
        }
        return $ret;
    }

    public function getSitemapParams() {
        $model = $this->owner;
        $baseArray = $this->converCluser($this->baseArray,$this->owner);
        $formatProperty = $this->formatProperty;
        $formatArrays = $this->converCluser($this->formatArrays,$this->owner);
        $thumbnailProperty = $this->thumbnailProperty;
        $thumbnailArray = $this->converCluser($this->thumbnailArray,$this->owner);
        if(!(empty($formatProperty)||empty($formatArrays[$model->$formatProperty]))) {
            $attach = $formatArrays[$model->$formatProperty];
            $FORMAT = $attach['FORMAT'];
            unset($attach['property']);
            $baseArray[$FORMAT]=$attach;
        }else if(isset($thumbnailProperty)&&!empty($model->$thumbnailProperty)) {
            $baseArray['image:image'][] = $thumbnailArray;
        }

        return $baseArray;
    }

    public function getLock($name,$key, $default = 0)
    {
        $path = Yii::getAlias('@runtime/seo/' .$name.".". $key . '.state');
        if (!file_exists($path)) {
            return $default;
        }
        return file_get_contents($path);
    }

    public function setLock($name,$key, $value = 1)
    {
        $path = Yii::getAlias('@runtime/seo/' .$name.".". $key . '.state');
        return file_put_contents($path,$value);
    }

    public function afterSave(){
        $model = $this->owner;
        if ($this->query !== null) {
            $query = $this->query;
            if (is_callable($query)) {
                $query = $query();
            }
            $sitemapGenerator = new \rabint\seo\classes\SitemapGeneratorNew([
                'sitemaps' => [$this->name => 'sitemap'],
                'generateIndex' => true,
                'neetSort' => FALSE,
                'dir' => '@app/web',
                'name' => $this->name
            ]);

            $status = $this->getLock($this->name,'refreshSitemap', '1');

            if ($status == '1') {
                if(empty($this->limit)){
                    $items = $query->all();
                }
                $items = $query->limit($this->limit)->all();

                foreach ($items as $item) {
                    $sitemapGenerator->addItem($this->name, $item->getSitemapParams());
                }
                $sitemapGenerator->generate();
                $this->setLock($this->name,'refreshSitemap', '0');
            } else {
                $sitemapGenerator->addItemToGeneratedSitemap($this->name, $model->getSitemapParams());
            }
            //set Ping Back
            $config = Option::getConfigArray();
            if($config['pingBack']){
                $sitemapGenerator->googlePing;
            }
            // end Ping back
        }
    }

    public function converCluser($array,$param){
        if(!is_array($array)){
            if(is_string($array)){
                return $array;
            }
            if(is_callable($array)){
                return $this->converCluser($array($param),$param);
            }
        }else
        foreach ($array as $key=>$value){
            if(is_array($value)){
                $array[$key] = $this->converCluser($value,$param);
            }else if(is_callable($value)){
                $array[$key] = $value($param);
                $array[$key] = $this->converCluser($array[$key],$param);
            }
        }
        return $array;
    }

    public function afterDelete(){
        if ($this->query !== null) {
            $query = $this->query;
            if (is_callable($query)) {
                $query = $query();
            }
            $sitemapGenerator = new \rabint\seo\classes\SitemapGeneratorNew([
                'sitemaps' => [$this->name => 'sitemap'],
                'generateIndex' => false,
                'neetSort' => FALSE,
                'dir' => '@app/web',
                'name' => $this->name
            ]);
            if(empty($this->limit)){
                $items = $query->all();
            }
            $items = $query->limit($this->limit)->all();

            foreach ($items as $item) {
                $sitemapGenerator->addItem($this->name, $item->getSitemapParams());
            }
            $sitemapGenerator->generate();
            //set Ping Back
            $config = Option::getConfigArray();
            if($config['pingBack']){
                $sitemapGenerator->googlePing;
            }
            // end Ping back
        }
    }

}