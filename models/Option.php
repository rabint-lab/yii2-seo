<?php

namespace rabint\seo\models;

use GuzzleHttp\Psr7\Uri;
use phpDocumentor\Reflection\Types\Self_;
use Yii;
use common\models\User;
use yii\helpers\Url;

/**
* This is the model class for table "seo_option".
*
    * @property integer $id
    * @property string $route
    * @property integer $type
    * @property string $name
    * @property string $content
    * @property integer $location
    * @property string $linked
    * @property integer $created_at
    * @property integer $updated_at
    * @property integer $created_by
    * @property integer $updated_by
*/
class Option extends \common\models\base\ActiveRecord     /* \yii\db\ActiveRecord */
{
const SCENARIO_CUSTOM = 'custom';
/* statuses */
const STATUS_DRAFT = 0;
const STATUS_PENDING = 1;
const STATUS_PUBLISH = 2;
//metatag و schema و script
const META_TYPE_TAG = 1;
const META_TYPE_SCHEMA = 2;
const META_TYPE_SCRIPT = 3;

const LOCATION_NO = 0;
const LOCATION_HEAD = 1;
const LOCATION_FOOTER = 2;

const BASE_RUNTIME_DIR = "../runtime/seo";

const CONFIG_DIR = "../runtime/seo/config.php";
const CONFIG_DEFAULT = [
    'seo'=>true,
    'pingBack'=>true,
    'compressAssets'=>true,
    'index'=>true,
];

    /**
* @inheritdoc
*/
public static function tableName()
{
return 'seo_option';
}


public function behaviors() {
return [
[
'class' => \yii\behaviors\TimestampBehavior::class,
'createdAtAttribute' => 'created_at',
'updatedAtAttribute' => 'updated_at',
'value' => time(),
],
[
'class' => \yii\behaviors\BlameableBehavior::class,
'createdByAttribute' => 'created_by',
'updatedByAttribute' => 'updated_by',
],
// [
//     'class' =>\rabint\behaviors\SoftDeleteBehavior::class,
//     'attribute' => 'deleted_at',
//     'attribute' => 'deleted_by',
// ],
/*[
'class' => \rabint\behaviors\Slug::class,
'sourceAttributeName' => 'title', // If you want to make a slug from another attribute, set it here
'slugAttributeName' => 'slug', // Name of the attribute containing a slug
],*/
];
}

public function scenarios() {
$scenarios = parent::scenarios();
// $scenarios[self::SCENARIO_CUSTOM] = ['status'];
return $scenarios;
}


/* ====================================================================== */

public static function statuses() {
return [
static::STATUS_DRAFT => ['title' => \Yii::t('rabint', 'draft')],
static::STATUS_PENDING => ['title' => \Yii::t('rabint', 'pending')],
static::STATUS_PUBLISH => ['title' => \Yii::t('rabint', 'publish')],
];
}

    public static function locations() {
        return [
            static::LOCATION_NO => ['title' => \Yii::t('rabint', 'عدم جایگاه')],
            static::LOCATION_HEAD => ['title' => \Yii::t('rabint', 'سر صفحه')],
            static::LOCATION_FOOTER => ['title' => \Yii::t('rabint', 'پاورقی')],
        ];
    }

public static function defultItems(){
    return[
        'home-meta-keywords' => [
            'title' => Yii::t('rabint','کلمات کلیدی'),
            'name' => 'home-meta-keywords',
            'type' => static::META_TYPE_TAG,
            'location'=> self::LOCATION_HEAD,
            'route'=>'*',
            'default' => [
                'meta'=>'keywords',
                'content'=>'',
            ],
            'target' => 'content'
        ],
        'home-meta-description' => [
            'title' => Yii::t('rabint','توضیحات'),
            'name' => 'home-meta-description',
            'type' => static::META_TYPE_TAG,
            'location'=> self::LOCATION_HEAD,
            'route'=>'*',
            'default' => [
                'meta'=>'description',
                'content'=>'',
            ],
            'target' => 'content'
        ],
        'home-schema-json-id' => [
            'title' => Yii::t('rabint','جیسان آی دی'),
            'name' => 'home-schema-json-id',
            'type'=>static::META_TYPE_SCHEMA,
            'location'=> self::LOCATION_HEAD,
            'route'=>'__HOME__',
            'default' => [],
            'target' => null
        ]
    ];
}

public static function metaTagTypes(){
    return [
        static::META_TYPE_TAG => ['title' => Yii::t('rabint','تگ')],
        static::META_TYPE_SCHEMA => ['title'=>Yii::t('rabint','اسکیما')],
        static::META_TYPE_SCRIPT => ['title'=>Yii::t('rabint','اسکریپت')],
    ];
}

/* ====================================================================== */

/**
* @inheritdoc
*/
public function rules()
{
return [
            [['type', 'location', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['content'], 'string'],
            [['route', 'name', 'linked'], 'string', 'max' => 255],
        ];
}

/**
* @inheritdoc
*/
public function attributeLabels()
{
return [
    'id' => 'شناسه',
    'route' => Yii::t('rabint','بخشی از مسیر صفحه'),
    'type' => Yii::t('rabint','نوع متا'),
    'name' => Yii::t('rabint','نام'),
    'content' => Yii::t('rabint','محتوا'),
    'location' => Yii::t('rabint','محل اضافه شدن (هدر یا فوتر)'),
    'linked' => Yii::t('rabint','لینک شده'),
    'created_at' => Yii::t('rabint','Created At'),
    'updated_at' => Yii::t('rabint','Updated At'),
    'created_by' => Yii::t('rabint','Created By'),
    'updated_by' => Yii::t('rabint','Updated By'),
];
}

/**
* @inheritdoc
*/
public function beforeSave($insert)
{
//if(!empty($this->publish_at)){
//    $this->publish_at = \rabint\helpers\locality::anyToGregorian($this->publish_at);
//    $this->publish_at = strtotime($this->publish_at);// if timestamp needs
//}
return parent::beforeSave($insert);
}


    /**
     * @inheritdoc
     * @param $object
     * @return string
     */
    //public static function find()
    //{
    //    $publishQuery = new \rabint\models\query\PublishQuery(get_called_class());
    //    $publishQuery->statusField="status";
    //    $publishQuery->activeStatusValue=self::STATUS_PUBLISH;
    //    $publishQuery->ownerField="creator_id";
    //    $publishQuery->showNotActiveToOwners=true;
    //    return $publishQuery;
    //}

    public static function MakeTag($object){
        $content = json_decode($object->content);
        switch ($object->type){
            case self::META_TYPE_TAG:
                    return '<meta name="'.$content->meta.'" content="'.$content->content.'">'.PHP_EOL;
                break;
            case self::META_TYPE_SCHEMA:
                    return '<script type="application/ld+json">'.$object->content.'</script>'.PHP_EOL;
                break;
            case self::META_TYPE_SCRIPT:
                    return $object->content;
                break;
        }
    }

    public static function setConfigArray($array){
        ob_start();
        var_export($array);
        $data = ob_get_contents();
        ob_end_clean();
        if(!file_exists(self::BASE_RUNTIME_DIR)){
            mkdir(self::BASE_RUNTIME_DIR,0777,true);
        }
        file_put_contents(self::CONFIG_DIR,'<?php return '.$data.';');
        chmod(self::CONFIG_DIR,0777);
        return true;
    }

    public static function getConfigArray(){
        if(file_exists(self::CONFIG_DIR)){
            return include self::CONFIG_DIR;
        }else{
            self::setConfigArray(self::CONFIG_DEFAULT);
        }
        return self::CONFIG_DEFAULT;
    }

    public static function checkIsExist($route){
        $re = self::find()
            ->where(['route'=>$route])
            ->one();
        if($re==null)
            return null;
        else
            return Url::to(['/seo/admin-option/update','id'=>$re->id]);
    }

}