<?php


namespace rabint\seo\behaviors;


use yii\base\Behavior;

class seoMetaBehavior extends Behavior
{
    public $map;

    public function getSeoMeta(){
        foreach ($this->map as $key => $value){
            if(is_callable($value))
                $val = $value($this->owner);
                \rabint\seo\services\SeoService::factory()->setSeoMeta($key,$val);
            else{
                \rabint\seo\services\SeoService::factory()->setSeoMeta($key,$this->owner->$value);
            }
        }
    }

}
