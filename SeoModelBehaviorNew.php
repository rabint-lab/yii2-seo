<?php

namespace rabint\seo;

use rabint\seo\models\Option;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Behavior to work with SEO meta options
 * @package rabint\seo
 *
 * @property ActiveRecord $owner
 */
class SeoModelBehaviorNew extends Behavior
{
    public $events = NULL;

    public function events()
    {
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

    public function afterSave()
    {
        $model = $this->owner;
        /* pingback ========================================================= */
        $config = Option::getConfigArray();
        if(!$config['pingBack']||!method_exists($model,'getPingBackParams'))
            return false;
        $pingback_servers = \rabint\seo\Module::getConfig('pingback_servers');
        $xmlrpc = new classes\Xmlrpc;
        foreach ($pingback_servers as $pingback_server) {
            $xmlrpc->server($pingback_server['url'], $pingback_server['port']);
            $xmlrpc->method($pingback_server['method']);
            $params = $model->getPingBackParams();
            $request = array($params['title'], $params['newUrl']);
            $xmlrpc->request($request);
        }
    }
}
