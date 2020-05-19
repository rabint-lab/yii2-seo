<?php

namespace rabint\seo;

use Yii;
use yii\base\Behavior;
use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @package rabint\seo
 */
class SeoViewBehavior extends Behavior {

    public $sitename;
    public $subject;
    public $locale;
    public $description;
    public $homeurl;
    public $author;
    public $keywords;
    public $noIndexNoFollow = 0;

    /**
     * @var SeoMetaObject
     */
    public $metaObject;
    private $_noIndex = false;

    public function init() {
        $this->noIndex($this->noIndexNoFollow);
        $this->metaObject = new SeoMetaObject;
        return parent::init();
    }

    public function events() {
        return [
//active record create , for generate sitemap and ping back
//active record update , for generate sitemap and ping back
        ];
    }

    /**
     * @param SeoMetaObject $metaObject
     * @return \rabint\seo\SeoViewBehavior
     */
    public function setSeoMeta($metaObject) {
        $this->metaObject = $metaObject;
        return $this;
    }

    public function renderMetaTags() {
        $view = $this->owner;
        $this->fillMetaObject();
        $meta = $this->metaObject;
        /* ------------------------------------------------------ */
        //$title = !empty($meta->title) ? $meta->title . ' | ' . $this->sitename . ': ' . $this->subject : $this->sitename . ': ' . $this->subject;

        if (!empty($this->subject)) {
            $title = !empty($meta->title) ? $meta->title . ' | ' . $this->subject : $this->sitename . ': ' . $this->subject;
        } else {
            $title = !empty($meta->title) ? $meta->title . ' | ' . $this->sitename : $this->sitename;
        }
        echo '<title>' . $this->normalizeStr($title) . '</title>' . PHP_EOL;
        /* ------------------------------------------------------ */
        if (!empty($meta->author)) {
            $view->registerMetaTag(['name' => 'author', 'content' => $this->normalizeStr($meta->author)]);
        }
        if (!empty($meta->description)) {
            $view->registerMetaTag(['name' => 'description', 'content' => $this->normalizeStr($meta->description)]);
        }
        if (!empty($meta->keywords)) {
            $meta->keywords = (is_array($meta->keywords)) ? implode(', ', $meta->keywords) : $meta->keywords;
            $view->registerMetaTag(['name' => 'keywords', 'content' => $this->normalizeStr($meta->keywords)]);
            $view->registerMetaTag(['name' => 'DC.Subject', 'content' => $this->normalizeStr($meta->keywords)]);
        }

//        Yii::$app->response->headers->add('X-Powered-By', 'RabintCMF/3.1.3 (rabint.ir)');
//        $view->registerMetaTag(['name' => 'Designer', 'content' => $this->normalizeStr('Design By Rabint intelligent solution, website: www.rabint.ir, Tel: 09151038085, telegram: www.t.me/rabint , e-mail: info@rabint.ir, support{at}rabint.ir , Rabint')]);
//        $view->registerMetaTag(['name' => 'generator', 'content' => $this->normalizeStr('RabintCMF 3.1.3, besed on Yii2')]);

        if (!empty($this->_noIndex)) {
            $view->registerMetaTag(['name' => 'robots', 'content' => $this->_noIndex]);
        }

        /* =================================================================== */

        if (!empty($meta->title)) {
            $view->registerMetaTag(['property' => 'og:title', 'content' => $this->normalizeStr($meta->title)]);
        }
        if (!empty($this->sitename)) {
            $view->registerMetaTag(['property' => 'og:site_name', 'content' => $this->normalizeStr($this->sitename)]);
        }
        if (!empty($meta->description)) {
            $view->registerMetaTag(['property' => 'og:description', 'content' => $this->normalizeStr($meta->description)]);
        }
        if (!empty($meta->updated_time)) {
            if (is_numeric($meta->updated_time)) {
                $meta->updated_time = date('c', $meta->updated_time);
            }
            $view->registerMetaTag(['property' => 'og:updated_time', 'content' => $this->normalizeStr($meta->updated_time)]);
        }
        if (!empty($meta->locale)) {
            $view->registerMetaTag(['property' => 'og:locale', 'content' => $this->normalizeStr($meta->locale)]);
        }

        $view->registerMetaTag(['property' => 'og:url', 'content' => $this->normalizeStr(Url::canonical())]);
        if (!empty($meta->type)) {
            $view->registerMetaTag(['property' => 'og:type', 'content' => $this->normalizeStr($meta->type)]);
        } elseif (Url::canonical() == $this->homeurl) {
            $meta->type = 'website';
            $view->registerMetaTag(['property' => 'og:type', 'content' => 'website']);
        }
        /* ------------------------------------------------------ */
        if (!empty($meta->image)) {
            if (!is_array($meta->image)) {
                $view->registerMetaTag(['property' => 'og:image', 'content' => $this->normalizeStr($meta->image)]);
            } else {
                foreach ($meta->image as $img) {
                    /**
                     * todo add width and height...
                     */
                    $view->registerMetaTag(['property' => 'og:image', 'content' => $this->normalizeStr($img['url'])]);
                    $view->registerMetaTag(['property' => 'og:image:type', 'content' => $this->normalizeStr($img['type'])]);
                }
            }
        }
        /* ------------------------------------------------------ */
        if (!empty($meta->video)) {
            if (!is_array($meta->video)) {
                $view->registerMetaTag(['property' => 'og:video', 'content' => $this->normalizeStr($meta->video)]);
            } else {
                foreach ($meta->video as $video) {
                    $view->registerMetaTag(['property' => 'og:video', 'content' => $this->normalizeStr($video['url'])]);
                    $view->registerMetaTag(['property' => 'og:video:type', 'content' => $this->normalizeStr($video['type'])]);
                }
            }
        }
        /* ------------------------------------------------------ */
        if (!empty($meta->audio)) {
            if (!is_array($meta->audio)) {
                $view->registerMetaTag(['property' => 'og:audio', 'content' => $this->normalizeStr($meta->audio)]);
            } else {
                foreach ($meta->audio as $audio) {
                    $view->registerMetaTag(['property' => 'og:audio', 'content' => $this->normalizeStr($audio['url'])]);
                    $view->registerMetaTag(['property' => 'og:audio:type', 'content' => $this->normalizeStr($audio['type'])]);
                }
            }
        }
        /* ------------------------------------------------------ */
        if ($meta->type == 'article' and ! empty($meta->article)) {
            if (!empty($meta->article['published_time'])) {
                if (is_numeric($meta->article['published_time'])) {
                    $meta->article['published_time'] = date('c', $meta->article['published_time']);
                }
                $view->registerMetaTag(['property' => 'article:published_time', 'content' => $this->normalizeStr($meta->article['published_time'])]);
            }
            if (!empty($meta->article['modified_time'])) {
                if (is_numeric($meta->article['modified_time'])) {
                    $meta->article['modified_time'] = date('c', $meta->article['modified_time']);
                }
                $view->registerMetaTag(['property' => 'article:modified_time', 'content' => $this->normalizeStr($meta->article['modified_time'])]);
            }
            if (!empty($meta->article['expiration_time'])) {
                if (is_numeric($meta->article['expiration_time'])) {
                    $meta->article['expiration_time'] = date('c', $meta->article['expiration_time']);
                }
                $view->registerMetaTag(['property' => 'article:expiration_time', 'content' => $this->normalizeStr($meta->article['expiration_time'])]);
            }
            if (!empty($meta->article['author'])) {
                $view->registerMetaTag(['property' => 'article:author', 'content' => $this->normalizeStr($meta->article['author'])]);
            }
            if (!empty($meta->article['section'])) {
                $meta->article['section'] = (is_array($meta->article['section'])) ? implode(', ', $meta->article['section']) : $meta->article['section'];
                $view->registerMetaTag(['property' => 'article:section', 'content' => $this->normalizeStr($meta->article['section'])]);
            }
            if (!empty($meta->article['tag'])) {
                $meta->article['tag'] = (is_array($meta->article['tag'])) ? implode(', ', $meta->article['tag']) : $meta->article['tag'];
                $view->registerMetaTag(['property' => 'article:tag', 'content' => $this->normalizeStr($meta->article['tag'])]);
            }
        }
        if ($meta->type == 'profile' and ! empty($meta->profile)) {
            if (!empty($meta->profile['first_name'])) {
                $view->registerMetaTag(['property' => 'profile:first_name', 'content' => $this->normalizeStr($meta->profile['first_name'])]);
            }
            if (!empty($meta->profile['last_name'])) {
                $view->registerMetaTag(['property' => 'profile:last_name', 'content' => $this->normalizeStr($meta->profile['last_name'])]);
            }
            if (!empty($meta->profile['username'])) {
                $view->registerMetaTag(['property' => 'profile:username', 'content' => $this->normalizeStr($meta->profile['username'])]);
            }
            if (!empty($meta->profile['gender'])) {
                $view->registerMetaTag(['property' => 'profile:gender', 'content' => $this->normalizeStr($meta->profile['gender'])]);
            }
        }
        $view->registerLinkTag(['rel' => 'canonical', 'href' => $this->normalizeStr(Url::canonical())]);
        $view->registerLinkTag(['rel' => 'profile', 'href' => "http://gmpg.org/xfn/11"]);
    }

    /**
     * It normalizes the line, preparing it for display
     *
     * @param string $str
     *
     * @return string
     */
    protected function normalizeStr($str, $htmlEncode = TRUE) {
// Remove the tags from the text
        $str = strip_tags($str);
// Replace all spaces, line breaks, and tabs with a single space
        $str = trim(preg_replace('/[\s]+/is', ' ', $str));
        if ($htmlEncode) {
            return Html::encode($str);
        }
        return $str;
    }

    /**
     * Set meta noindex tag to the current page
     *
     * @param int $type 0=index, follow | 1=noindex, follow | 2=noindex, nofollow
     */
    public function noIndex($type = 2) {
        switch ($type) {
            case 0:
                $this->_noIndex = false;
                break;
            case 1:
                $this->_noIndex = 'noindex, follow';
                break;
            case 2:
            default :
                $this->_noIndex = 'noindex, nofollow';
                break;
        }
    }

    protected function fillMetaObject() {

        if (empty($this->metaObject->description)) {
            $this->metaObject->description = $this->description;
        }

        if (empty($this->metaObject->author)) {
            $this->metaObject->author = $this->author;
        }

        if (empty($this->metaObject->keywords)) {
            $this->metaObject->keywords = $this->keywords;
        }

        if (empty($this->metaObject->locale)) {
            $this->metaObject->locale = $this->locale;
        }

        if (empty($this->metaObject->title) and ! empty($this->owner->title)) {
            $this->metaObject->title = $this->owner->title;
        }
    }

}
