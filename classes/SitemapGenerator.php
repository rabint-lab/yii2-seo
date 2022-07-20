<?php

namespace rabint\seo\classes;

use Yii;

/**
 *    $sitemap = new ArticlesSitemap(); // must implement a SitemapInterface
 *    $sitemapGenerator = new Sitemap([
 *        'sitemaps' => [$sitemap],
 *        'dir' => '@webRoot',
 *    ]);
 *    $sitemapGenerator->generate();
 * ```
 *
 * @package common\components
 */
class SitemapGenerator extends \yii\base\BaseObject
{

    /**
     * @var string directory for recording the site map file. You can use aliases.
     */
    public $dir = '';
    public $generateIndex = true;
    public $neetSort = true;

    /**
     * @var string name index sitemap file
     */
    public $indexFilename = 'sitemap.xml';

    /**
     * @var string Recording page was last modified format
     */
    public $lastmodFormat = 'Y-m-d';

    /**
     * @var SitemapInterface[] set of objects sitemap
     */
    public $sitemaps = [];

    /**
     * @var int the maximum number of addresses in a single card.
     *       * If the site map of addresses greater than a predetermined value,
     *       * The sitemap will break into multiple site maps in such a way
     *       * To each address was no longer than a predetermined value.
     *       * If there is "0", the card will not break up into several and one card may be
     *       * Unlimited number of addresses.
     */
    public $maxUrlsCount = 45000;

    /**
     * @var array stores information about the created site maps
     */
    protected $items = [];
    protected $createdSitemaps = [];

    function addItem($sitemap, $item)
    {
        $this->items[$sitemap][] = $item;
        return TRUE;
    }

    function addItemToGeneratedSitemap($sitemap, $item)
    {
//            $siteMapName = 'sitemap.' . $sitemap . '.xml';
        $siteMapName = 'sitemap';
        $multipleSitemapFlag = false;
        $entity = static::generateEntity($item) . PHP_EOL;
        $filename = $multipleSitemapFlag ? "{$siteMapName}-{$i}.xml" : "{$siteMapName}.xml";
        $fullFilename = Yii::getAlias($this->dir) . '/' . $filename;

//        var_dump($fullFilename);

        $lines = file($fullFilename);
//        var_dump($lines);
        $output = '';
        foreach ($lines as $line => $data) {
            if ($line == 7) {
                $output .= $entity;
            }
            $output .= $data;
        }

//        var_dump($output);
//        


        file_put_contents($fullFilename, $output);

//                        die('=====');

        $this->ping(\rabint\helpers\uri::home() . '/' . $this->indexFilename);

        return TRUE;
    }

    /**
     * Creating a sitemap
     */
    public function generate()
    {
        foreach ($this->sitemaps as $sitemap => $filename) {
            $this->createSitemap($sitemap, $filename);
        }
        if ($this->generateIndex) {
            $this->createIndexSitemap();
        }
        $this->ping(\rabint\helpers\uri::home() . '/' . $this->indexFilename);
    }

    /**
     * create a sitemap index
     *
     * @return string
     */
    protected function createIndexSitemap()
    {
        $sitemapIndex = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemapIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        $baseUrl = \rabint\helpers\uri::home();

        $sitemaps = $this->createdSitemaps;
        self::sortByLastmod($sitemaps);

        foreach ($sitemaps as $sitemap) {
            $sitemapIndex .= '    <sitemap>' . PHP_EOL;
            $sitemapIndex .= "        <loc>$baseUrl/$sitemap[loc]</loc>" . PHP_EOL;

            if (!empty($sitemap['lastmodTimestamp'])) {
                $lastmod = date($this->lastmodFormat, $sitemap['lastmodTimestamp']);
                $sitemapIndex .= "        <lastmod>$lastmod</lastmod>" . PHP_EOL;
            }

            $sitemapIndex .= '    </sitemap>' . PHP_EOL;
        }

        $sitemapIndex .= '</sitemapindex>';
        $this->createSitemapFile($this->indexFilename, $sitemapIndex);

        return $sitemapIndex;
    }

    /**
     * Create a site map of the object $ a sitemap and writes information on the created site map
     * In the array $ this-> createdSitemaps
     *
     * @return boolean
     */
    protected function createSitemap($sitemap, $siteMapName = '')
    {
        if (empty($siteMapName)) {
            $siteMapName = 'sitemap.' . $sitemap . '.xml';
        }

        $urls = $this->items[$sitemap];
        if ($this->neetSort) {
            self::sortByLastmod($urls);
        }
        $chunkUrls = $this->chunkUrls($urls);
        $multipleSitemapFlag = count($chunkUrls) > 1;
        $i = 1;

        $header = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
			    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">

EOT;

        $footer = "</urlset>";

        foreach ($chunkUrls as $urlsData) {
            $freshTimestamp = 0;
            $urlset = $header;

            foreach ($urlsData as $url) {
                $urlset .= static::generateEntity($url) . PHP_EOL;

                if ($freshTimestamp < strtotime($url['lastmod'])) {
                    $freshTimestamp = strtotime($url['lastmod']);
                }
            }

            $urlset .= $footer;
            $currentSitemapFilename = $multipleSitemapFlag ? "{$siteMapName}-{$i}.xml" : "{$siteMapName}.xml";

            $this->createdSitemaps[] = [
                'loc' => $currentSitemapFilename,
                'lastmodTimestamp' => $freshTimestamp,
            ];
            if (!$this->createSitemapFile($currentSitemapFilename, $urlset)) {
                return false;
            }
            $i++;
        }

        return true;
    }

    /**
     * Splits an array of URLs in accordance with the $ this-> maxUrlsCount.
     *      * Wrapping for array_chunk () function.
     *
     * @param array $urls
     *
     * @return array
     */
    protected function chunkUrls(array $urls)
    {
        if (empty($this->maxUrlsCount)) {
            $result[] = $urls;

            return $result;
        }

        return array_chunk($urls, $this->maxUrlsCount);
    }

    /**
     * Create a site map file
     *
     * @param $filename
     * @param $data
     *
     * @return int
     */
    protected function createSitemapFile($filename, $data)
    {
        $fullFilename = Yii::getAlias($this->dir) . '/' . $filename;

        return file_put_contents($fullFilename, $data);
    }

    /**
     * Sort by lastmod URLs in descending order
     *
     * @param array $urls
     */
    protected static function sortByLastmod(array &$urls)
    {
        $lastmod = [];

        foreach ($urls as $key => $row) {
            $lastmod[$key] = !empty($row['lastmodTimestamp']) ? $row['lastmodTimestamp'] : 0;
        }

        array_multisort($lastmod, SORT_DESC, $urls);
    }

    protected static function generateEntity($item, $level = 1, $parentKey = 'url')
    {
        $url = '';
        $sep = str_repeat("\t", $level);
        if (!empty($parentKey)) {
            $url .= $sep . "<$parentKey>" . PHP_EOL;
            $parentClose = strpos($parentKey, ' ') ? substr($parentKey, 0, strpos($parentKey, ' ')) : $parentKey;
        }
        $last_key = key(array_slice($item, -1, 1, TRUE));
        foreach ($item AS $attr => $value) {
            if (is_array($value)) {
                if (is_int($attr)) {
                    $url .= static::generateEntity($value, $level, '');
                    if ($attr !== $last_key) {
                        $url .= $sep . "</$parentClose>" . PHP_EOL;
                        $url .= $sep . "<$parentKey>" . PHP_EOL;
                    }
                } else {
                    $url .= static::generateEntity($value, $level + 1, $attr);
                }
            } else {
                $close = strpos($attr, ' ') ? substr($attr, 0, strpos($attr, ' ')) : $attr;
                $url .= $sep . "\t" . "<$attr>" . $value . "</$close>\n";
            }
        }
        if (!empty($parentKey)) {
            $url .= $sep . "</$parentClose>" . PHP_EOL;
        }
        return $url;
    }

    /* =================================================================== */

    function pingSitemap($sitemaps = null)
    {
        $baseUrl = \rabint\helpers\uri::home();
        if ($sitemaps === NULL) {
            $this->ping($baseUrl . '/' . $this->indexFilename);
            //sitemapindex
        } else {
            foreach ($sitemaps as $sitemap => $filename) {
                $this->ping($sitemap);
            }
        }
    }

    function ping($url_xml, $search_engines = NULL)
    {
        $statuses = array();
        if (is_array($search_engines)) {
            foreach ($search_engines AS $engine) {
                $status = 0;
                if ($fp = @fsockopen($engine['host'], 80)) {
                    $engine['url'] = empty($engine['url']) ? "/ping?sitemap=" : $engine['url'];

                    $req = 'GET ' . $engine['url'] .
                        urlencode($url_xml) . " HTTP/1.1\r\n" .
                        "Host: " . $engine['host'] . "\r\n" .
                        config('SEO.sitemap.sitemaps_user_agent') .
                        "Connection: Close\r\n\r\n";
                    fwrite($fp, $req);
                    while (!feof($fp)) {
                        if (@preg_match('~^HTTP/\d\.\d (\d+)~i', fgets($fp, 128), $m)) {
                            $status = intval($m[1]);
                            break;
                        }
                    }
                    fclose($fp);
                }
                $statuses[] = array("host" => $engine['host'], "status" => $status, "request" => $req);
            }
        }

//        if (config('SEO.sitemap.sitemaps_log_http_responses') OR config('SEO.sitemap.sitemaps_debug')) {
//            foreach ($statuses AS $reponse) {
//                $message = "Sitemaps: " . $reponse['host'] . " responded with HTTP status " . $reponse['status'];
//
//                if (config('SEO.sitemap.sitemaps_log_http_responses')) {
//                    $level = $reponse['status'] == 200 ? 'debug' : 'error';
//                    log_message($level, $message);
//                }
//
//                if (config('SEO.sitemap.sitemaps_debug')) {
//                    echo "<p>" . $message . " after request:</p>\n<pre>" . $reponse['request'] . "</pre>\n\n";
//                }
//            }
//        }

        return $statuses;
    }

}
 
