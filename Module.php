<?php

# THIS FILE ISDISABLED 

//EXIT;
namespace rabint\seo;

/**
 * notify module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'rabint\seo\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }


    public static function adminMenu()
    {
        return [
            [
                'label' => \Yii::t('rabint', 'تنظیمات سئو'),
                'options' => ['class' => 'nav-main-heading'],
                'visible' => \rabint\helpers\user::can('manager'),
                'items' => [
                    [
                        'label' => \Yii::t('rabint', 'تنظیمات کلی'),
                        'url' => ['/seo/admin-option/index'],
                        'visible' => \rabint\helpers\user::can('manager'),
                        'icon' => '<i class="far fa-circle"></i>',
                    ],
                    [
                        'label' => \Yii::t('rabint', 'تنظیمات اختصاصی'),
                        'url' => ['/seo/admin-option/special-options'],
                        'visible' => \rabint\helpers\user::can('manager'),
                        'icon' => '<i class="far fa-circle"></i>',
                    ],
                    [
                        'label' => \Yii::t('rabint', 'تنظیمات ماژول'),
                        'url' => ['/seo/admin-option/module-option'],
                        'visible' => \rabint\helpers\user::can('manager'),
                        'icon' => '<i class="far fa-circle"></i>',
                    ],
                ]
            ],
        ];
    }

    public static function getConfig($key)
    {
        $defaultConfigs = [
            'pingback_servers' => [
                ['url' => 'http://rpc.pingomatic.com/', "port" => 80, "method" => "weblogUpdates.ping"],
                ['url' => 'http://rpc.pingomatic.com/', "port" => 80, "method" => "pingback.ping"],
                ['url' => 'https://codex.wordpress.org/Update_Services', "port" => 80, "method" => "pingback.ping"]
            ],
            'sitemap_ping_servers' => [
                ["host" => "www.google.de", "url" => "/webmasters/tools/ping?sitemap="],
                ["host" => "search.yahooapis.com", "url" => "/SiteExplorerService/V1/ping?sitemap="],
                ["host" => "submissions.ask.com"],
                ["host" => "www.bing.com", "url" => "/webmaster/ping.aspx?siteMap="],
                ["host" => "www.sitemapwriter.com", "url" => "/notify.php?crawler=all&amp;url="],
            ],
            'sitemaps_gzip' => true,
            'sitemaps_gzip_path' => '{file_name}.gz',
            'sitemaps_index_gzip' => false,
            'sitemaps_index_gzip_path' => '{file_name}.gz',
            'sitemaps_debug' => true,
            'sitemaps_filesize_error' => true,
            'sitemaps_log_http_responses' => true,
            'sitemaps_user_agent' => "User-Agent: Mozilla/5.0 (compatible; " . PHP_OS . ") PHP/" . PHP_VERSION . "\r\n",
        ];
        return config('SERVICE.SEO.' . $key, $defaultConfigs[$key]);
    }
}
