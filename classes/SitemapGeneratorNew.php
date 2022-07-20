<?php

namespace rabint\seo\classes;

use Yii;
use yii\helpers\Url;

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
class SitemapGeneratorNew extends \yii\base\BaseObject
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

    public $name;
    /**
     * @var array stores information about the created site maps
     */
    protected $items = [];
    public $createdSitemaps = [];

    function addItem($sitemap, $item)
    {
        $this->items[$sitemap][] = $item;
        return TRUE;
    }

    public function getFileName(){
        return Yii::getAlias($this->dir) . "/sitemap-". $this->name.".xml";
    }
    public function getIndexFile(){
        return \Yii::getAlias('@webroot')."/". $this->indexFilename;
    }

    function addItemToGeneratedSitemap($sitemap, $item)
    {
        $entity = static::generateEntity($item) . PHP_EOL;
        $lines = file($this->fileName);
        $output = '';
        foreach ($lines as $line => $data) {
            if ($line == 7) {
                $output .= $entity;
            }
            $output .= $data;
        }
        file_put_contents($this->fileName, $output);
        $this->updateIndexMod();
        return TRUE;
    }

    public function checkIsFile(){
        $file = Yii::getAlias($this->dir) . '/' . $this->name;
        if(!file_exists($file)){
            $this->createSitemap($this->name);
        }
        return true;
    }
    /**
     * Creating a sitemap
     */
    public function generate()
    {
        $this->checkIsFile();
        if ($this->generateIndex) {
            $this->createIndexSitemap();
        }
        $this->updateIndexMod();
    }

    /**
     * create a sitemap index
     *
     * @return string
     */
    protected function createIndexSitemap()
    {
        $indexFile = Yii::getAlias($this->dir) . '/' . $this->indexFilename;
        $baseUrl = \rabint\helpers\uri::home();
        if(file_exists($indexFile)){
            $lines = file($indexFile);

            $sitemaps = $this->createdSitemaps;

            self::sortByLastmod($sitemaps);
            foreach ($sitemaps as $sitemap) {
                $entry[] = '    <sitemap>' . PHP_EOL;
                $entry[] = "        <loc>$baseUrl$sitemap[loc]</loc>" . PHP_EOL;

                if (!empty($sitemap['lastmodTimestamp'])) {
                    $lastmod = date($this->lastmodFormat, $sitemap['lastmodTimestamp']);
                    $entry[] = "        <lastmod>$lastmod</lastmod>" . PHP_EOL;
                }

                $entry[] = '    </sitemap>' . PHP_EOL;
            }
            $output = '';
            foreach ($lines as $key=>$item){
                if($key==2){
                    foreach ($entry as $val){
                        $output.= $val;
                    }
                }
                $output.= $item;
            }
            file_put_contents($indexFile, $output);
            return $output;
        }else{

            $sitemapIndex = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $sitemapIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

            $sitemaps = $this->createdSitemaps;

            self::sortByLastmod($sitemaps);
            foreach ($sitemaps as $sitemap) {
                $sitemapIndex .= '    <sitemap>' . PHP_EOL;
                $sitemapIndex .= "        <loc>$baseUrl$sitemap[loc]</loc>" . PHP_EOL;

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
            $siteMapName = 'sitemap-' . $sitemap;
        }

        $urls = $this->items[$sitemap]??[];
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

    function getGooglePing(){
        $googleUrlRequest = 'https://www.google.com/webmasters/sitemaps/ping?sitemap=';
        $requestLink = $googleUrlRequest.Url::base(true)."/".$this->indexFilename;
        $response = file_get_contents($requestLink);
        $return = strpos($response,"Sitemap Notification Received")>0?true:false;
        //set log
        $path = Yii::getAlias('@runtime/seo/pingBackLogs');
        file_put_contents($path,date('Y/m/d H:i:s',time()).($return?" Status Success":" Status Error ".$requestLink).PHP_EOL,FILE_APPEND);
        if(!$return){
            Yii::$app->session->setFlash('warning',Yii::t('rabint','درخواست پینگ بک با مشکل مواجه شد.'));
            $path = Yii::getAlias('@runtime/seo/pingBackErrors');
            file_put_contents($path,$response.PHP_EOL,FILE_APPEND);
        }

        return $return;
    }

    public function updateIndexMod(){
        $indexUrl = $this->getIndexFile();
        chmod($indexUrl,0777);
        $pattern = "/".$this->name."\.xml(.(?!<\/lastmod>))*<lastmod>(([^\/])*)<\/lastmod>/sm";
        $date = date('Y-m-d',time());
        $replacement = $this->name.'.xml</loc>'.PHP_EOL.'<lastmod>'.$date.'</lastmod>';
        $content = preg_replace($pattern, $replacement, file_get_contents($indexUrl),1);
        return file_put_contents($indexUrl,$content);
    }


}
