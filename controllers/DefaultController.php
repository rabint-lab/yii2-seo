<?php

namespace rabint\seo\controllers;

use rabint\seo\models\Option;
use Yii;
use rabint\attachment\models\Attachment;
use rabint\attachment\models\search\attachmentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DefaultController implements the CRUD actions for Attachment model.
 */
class DefaultController extends \rabint\controllers\DefaultController {

    public function actionSitemap($id) {
        $CI = &get_instance();
        $CI->load->library('Sitemaps');
        $CI->load->model('post_model');

        $item = array(
            "loc" => site_url(),
            "lastmod" => date("c", strtotime(current_datetime())),
            "changefreq" => "hourly",
            "priority" => "1.0"
        );

        $CI->sitemaps->add_item($item);
        $posts = $CI->post_model->get_Posts_List(null, null);
        foreach ($posts AS $post) {
            $item = array(
                "loc" => site_url("post/" . $post['post_id'] . "/" . $post['post_uri']),
                "lastmod" => date("c", strtotime($post['post_datetime'])),
                "changefreq" => "hourly",
                "priority" => "0.8",
                "title" => $post['post_title'],
                "category_id" => $post['category_id'],
                "image" => $post['post_image']
            );

            $CI->sitemaps->add_item($item);
        }
        $file_name = $CI->sitemaps->build("sitemap.xml", false);
        $reponses = $CI->sitemaps->ping(site_url($file_name));
    }

    public function pingback_send($title, $url) {
        $config = Option::getConfigArray();
        if(!$config['pingBack'])
            return false;
        $CI = &get_instance();
        $CI->load->library('xmlrpc');

        $pingback_servers = $CI->config->item('pingback_servers');

        foreach ($pingback_servers as $pingback_server) {
            $CI->xmlrpc->server($pingback_server['url'], $pingback_server['port']);
            $CI->xmlrpc->method($pingback_server['method']);

            $request = array($title, $url);
            $CI->xmlrpc->request($request);

            if (!$CI->xmlrpc->send_request()) {
                $error = $CI->xmlrpc->display_error();
            }
        }

        if ($error)
            return $error;
        else
            return true;
    }

}
