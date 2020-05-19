<?php

namespace rabint\seo;

interface SeoEntityInterface {

    /**
     * @return array An Array with this parameters:
     * [
     *   lastmod => as timestamp
     *   changefreq => as str sample daily, hourly ,...
     *   priority => as int
     *   loc => url as str 
     * ]
     */
    public function getSitemapParams();

    /**
     * @return SeoMetaObject
     */
    public function getSeoMeta();

    /**
     * @return array [oldUrl,newUrl]
     */
    public function getPingBackParams();
}
