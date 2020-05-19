<?php

namespace rabint\seo;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Behavior to work with SEO meta options
 *
 * @package rabint\seo
 *
 * @property ActiveRecord $owner
 */
class SeoModelBehavior extends Behavior {

    /**
     *
     * @var Query
     */
    public $query = NULL;
    public $events = NULL;
    public $limit = 45000;

    public function events() {
        if ($this->events == NULL) {
            return [
                ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
                ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ];
        }
        $ret = [];
        foreach ($this->events as $k => $evnt) {
            $ret[$evnt] = 'afterSave';
        }
        return $ret;
    }

    public function afterSave() {
        $model = $this->owner;
        if ($model->status != \app\modules\post\models\Post::STATUS_PUBLISH) {
            return;
        }
        /* sitemap ========================================================== */
        if (method_exists($model, 'getSitemapParams') and $this->query !== null) {
            $query = $this->query;
            if (is_callable($query)) {
                $query = $query();
            }
            
            $sitemapGenerator = new \rabint\seo\classes\SitemapGenerator([
                    'sitemaps' => ['post' => 'sitemap'],
                    'generateIndex' => FALSE,
                    'neetSort' => FALSE,
                    'dir' => '@app/web',
                ]);
            
            $status = Yii::$app->keyStorage->get('SEO.refreshSitemap');
            if ($status == '1') {
                $items = $query->limit($this->limit)->all();
                
                foreach ($items as $item) {
                    $sitemapGenerator->addItem('post', $item->getSitemapParams());
                }
                $sitemapGenerator->generate();
                Yii::$app->keyStorage->set('SEO.refreshSitemap', '0');
            }else{
                 $sitemapGenerator->addItemToGeneratedSitemap('post', $model->getSitemapParams());
            }
        }
        /* pingback ========================================================= */
        $pingback_servers = config('SEO.pingback.servers');
        $xmlrpc = new classes\Xmlrpc;
        foreach ($pingback_servers as $pingback_server) {
            $xmlrpc->server($pingback_server['url'], $pingback_server['port']);
            $xmlrpc->method($pingback_server['method']);
            $params = $model->getPingBackParams();
            $request = array($params['title'], $params['newUrl']);
            $xmlrpc->request($request);

//            if (!$xmlrpc->send_request()) {
//                $error = $xmlrpc->display_error();
//            }
        }
    }

}
