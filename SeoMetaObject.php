<?php

namespace rabint\seo;

class SeoMetaObject extends \yii\base\BaseObject {

    const META_TITLE = "title";
    const META_AUTHOR = 'author';
    const META_DESCRIPTION = 'description';
    const META_KEYWORDS = 'keywords';
    const META_LOCALE = 'locale';
    const META_TYPE = 'type';

    public $title = NULL;
    public $author = NULL;
    public $description = NULL;
    public $keywords = NULL;
    public $locale = NULL;
    public $type = NULL;
//    public $article = [
//        'published_time' => null,
//        'modified_time' => null,
//        'expiration_time' => null,
//        'author' => null,
//        'section' => null,
//        'tag' => null,
//    ];
//    public $profile = [
//        'first_name' => null,
//        'last_name' => null,
//        'username' => null,
//        'gender' => null,
//    ];
    public $image = [
//        ['url' => null, 'type' => null,'width'=>null, 'height'=>null]
    ];
    public $video = [
//        ['url' => null, 'type' => null]
    ];
    public $audio = [
//        ['url' => null, 'type' => null]
    ];
    public $updated_time = NULL;

}
