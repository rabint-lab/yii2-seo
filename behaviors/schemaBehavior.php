<?php


namespace rabint\seo\behaviors;


use \yii\base\Behavior;

class schemaBehavior extends Behavior
{
    public $schemaObject;
    public $map;

    public function getSchema(){
        $object = '\Spatie\SchemaOrg\Schema::'.$this->schemaObject;
        $schima = \rabint\seo\services\SeoService::factory()->setSchema($object());
        foreach ($this->map as $key => $value){
            if(is_callable($value))
                $val = $value($this->owner);
                $schima->setSchemaMeta([$key=>$val]);
            else{
                $schima->setSchemaMeta([$key=>$this->owner->$value]);
            }
        }
    }
}
