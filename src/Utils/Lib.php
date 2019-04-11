<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 5/17/2018
 * Time: 1:35 PM
 */

namespace App\Utils;

class Lib
{

    public static function gzip($content)
    {
        return gzencode($content, 5);
    }

    public static function gunzip($contentGzip)
    {
        return gzdecode($contentGzip, 5);
    }

    /**
     * Cache encode object
     * @param $value object
     * @return string
     */
    public static function cacheEncode($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Cache decode string
     * @param $value : string
     * @return mixed
     */
    public static function cacheDecode($value)
    {
        return json_decode($value, true);
    }

    /**
     * Check valid page url of music-news
     * author: ThanhDT
     * date:   2017-11-28 01:26 PM
     * @param $url
     * @return bool
     */
    public static function checkValidPageUrl($url)
    {
        if ($url == "/") {
            return true;
        }
        if (strpos($url, '/ajax/') !== false) {
            return false;
        }
        if (strpos($url, '.gif') !== false) {
            return false;
        }
        if (strpos($url, '.html') !== false || strpos($url, '.xml') !== false) {
            return true;
        }
        return true;
    }

    /**
     * Format datetime to string
     * @param $datetime
     * @return mixed
     */
    public static function fomartDate($datetime)
    {
//        $strTime = $datetime->format('M d, Y - g:ia') . ' IST';
//        return $strTime;
        $strTime = $datetime->format('d/m/Y - H:i');
        return $strTime;
    }

    /**
     * Format datetime to string in meta article:publish
     * author: ThanhDT
     * date:   2018-05-22 09:46 AM
     * @param $datetime
     * @return mixed
     */
    public static function fomartMetaDate($datetime)
    {
        $strTime = $datetime->format('c');
        return $strTime;
    }

    /**
     * Format Date in Rss feed to GMT
     * @param \DateTime $datetime
     * @return string
     */
    public static function fomartRssDate(\DateTime $datetime)
    {
        $strTime = $datetime->setTimezone(new \DateTimeZone('GMT'))->format('D, d M Y H:i:s O');
        return $strTime;
    }

    const UNICODE_ENCODING = 'utf-8';

    /**
     * Substring by word in utf-8 mode
     * author: ThanhDT
     * date:   2017-03-29 9:15 AM
     * @param $str
     * @param $limit
     * @param string $end
     * @return string
     */
    public static function subString($str, $limit, $end = '..')
    {
        $str_new = trim(strip_tags($str));
        // process special content
        $str_new = mb_substr($str_new, 0, $limit + 100, self::UNICODE_ENCODING);
        $str_new = self::removeSpecialContent($str_new);

        if (mb_strlen($str_new, self::UNICODE_ENCODING) > $limit) {
            $str_new = mb_substr($str_new, 0, $limit, self::UNICODE_ENCODING);
            if ($str[$limit] == ' ') {
                return $str_new . $end;
            }
            $pos = mb_strrpos($str_new, ' ', 0, self::UNICODE_ENCODING);
            if ($pos > 0) {
                $str_new = mb_substr($str_new, 0, $pos, self::UNICODE_ENCODING);
            }

            return preg_replace('/\\s{2,}/', ' ', $str_new . $end);
        }

        return preg_replace('/\\s{2,}/', ' ', $str_new);
    }

    /**
     * Remove WP special tag in content
     * @param $str
     * @return mixed
     */
    public static function removeSpecialContent($str)
    {
        $str = preg_replace('/\\[caption[^\\]+](.*)[\\/caption]/i', '', $str);
        return $str;
    }

    /**
     * Process caption tag in WP content
     * author: ThanhDT
     * date:   2018-05-19 11:44 AM
     * @param $content
     * @param $postId
     * @param int $isAmpPage
     * @return mixed
     */
    public static function processArticleContent($content, $postId, $isAmpPage = 0)
    {
        if ($isAmpPage == 1) {
            $content = preg_replace('/(\\<[a-z]+[^>]+)(style|type)=".*?"(.*?\\>)/', '$1$3', $content);
            // Process Image tag
            $content = preg_replace_callback_array([
                Constants::IMAGE_TAG_PATTERN => function ($match) {
                    $imgHtml = self::processImage($match[1]);
                    return $imgHtml;
                },
                Constants::IFRAME_TAG_PATTERN => function ($match) {
                    $src = '';
                    $width = 640;
                    $height = 360;
                    if (preg_match_all(Constants::ATTRIBUTE_PATTERN, trim($match['attr']), $matches)) {
                        $count = count($matches[0]);
                        for ($i = 0; $i < $count; $i++) {
                            switch ($matches[1][$i]) {
                                case 'src':
                                    $src = str_replace('http://', 'https://', $matches[2][$i]);
                                    break;
                                case 'width':
                                    $width = $matches[2][$i];
                                    break;
                                case 'height':
                                    $height = $matches[2][$i];
                                    break;
                            }
                        }
                    }
                    $imgHtml = sprintf(Constants::EMBED_YOUTUBE_URL_AMP_FORMAT, $src, $width, $height);

                    return $imgHtml;
                },
                Constants::OBJECT_TAG_PATTERN => function ($match) {
                    return '';
                },
            ], $content);
        }
        // Process WP Caption tag
        $content = preg_replace_callback(Constants::CAPTION_TAG_PATTERN, function ($match) use ($postId, $isAmpPage) {
            $attributes = explode(' ', trim($match[1]));
            $captionTag = "<figure";
            $caption = '';
            foreach ($attributes as $attribute) {
                $arrAtributeValue = explode('=', $attribute);
                if (count($arrAtributeValue) == 2) {
                    switch ($arrAtributeValue[0]) {
                        case 'id':
                            $captionTag .= ' ' . $attribute;
                            break;
                        case 'align':
                            $captionTag .= ' class="wp-caption ' . trim($arrAtributeValue[1], '"') . '"';
                            break;
                        case 'width':
                            if ($isAmpPage == 0) {
                                $captionTag .= ' style="width: ' . trim($arrAtributeValue[1], '"') . 'px"';
                            }
                            break;
                        case 'caption':
                            $caption = trim($arrAtributeValue[1], '"');
                            break;
                    }
                }
            }
            $captionTag .= '>';
            if (empty($caption)) {
                $caption = trim($match[5]);
            }
            if ($isAmpPage == 0) {
                $anchorHtml = $match[2];
            } else {
                if (empty($match[3])) {
                    $anchorHtml = self::processImage($match[2], $postId);
                } else {
                    $imgHtml = self::processImage($match[4], $postId);
                    $anchorHtml = $match[3] . $imgHtml . '</a>';
                }
            }
            //$anchorHtml = $type == 1 ? $match[2] : self::removeSize($match[2]);
            $captionTag .= $anchorHtml;
            if (!empty($caption)) {
                $captionTag .= '<figcaption class="wp-caption-text">' . $caption . '</figcaption>';
            }
            $captionTag .= '</figure>';
            return $captionTag;
        }, $content);

        // Process youtube embed
        $content = preg_replace_callback(Constants::EMBED_YOUTUBE_PATTERN, function ($match) use ($isAmpPage) {
            $embed = sprintf(($isAmpPage == 1 ? Constants::EMBED_YOUTUBE_AMP_FORMAT : Constants::EMBED_YOUTUBE_FORMAT), $match[1], $match[2], $match[3]);
            return $embed;
        }, $content);

        return $content;
    }

    private static function processImage($imgHtml, $postId = 0)
    {
        if (preg_match_all(Constants::ATTRIBUTE_PATTERN, $imgHtml, $matches)) {
            $captionTag = '';
            $width = 0;
            $height = 0;
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                switch ($matches[1][$i]) {
                    case 'src':
                    case 'alt':
                        $captionTag .= ' ' . $matches[0][$i];
                        break;
                    case 'width':
                        $width = $matches[2][$i];
                        break;
                    case 'height':
                        $height = $matches[2][$i];
                        break;
                }
            }
            $html = '<amp-img class="' . ($postId == 0 ? '' : 'wp-image-' . $postId . ' ') . 'amp-wp-enforced-sizes"' . $captionTag . ' width="' . $width . '" height="' . $height . '" sizes="(min-width: 640px) 640px, 100vw"></amp-img>';

            return $html;
        }

        return $imgHtml;
    }

    /**
     * Get image gallery info
     * author: ThanhDT
     * date:   2018-05-19 11:52 AM
     * @param $galleryAttribute
     * @return array|null
     */
    public static function getGalleryInfo($galleryAttribute)
    {
        if ($galleryAttribute == '') {
            return null;
        }
        $attrubutes = explode(' ', $galleryAttribute);
        $galleryInfo = [];
        foreach ($attrubutes as $attrubute) {
            $arrAtributeValue = explode('=', $attrubute);
            if (count($arrAtributeValue) == 2) {
                switch ($arrAtributeValue[0]) {
                    case 'columns':
                        $galleryInfo['columns'] = trim($arrAtributeValue[1], '"');
                        break;
                    case 'ids':
                        $galleryInfo['ids'] = explode(',', trim($arrAtributeValue[1], '"'));
                        break;
                }
            }
        }
        if (!isset($galleryInfo['ids'])) {
            return null;
        }
        if (!isset($galleryInfo['columns'])) {
            $galleryInfo['columns'] = 5;
        }

        return $galleryInfo;
    }

    /**
     * Wrap text by paragrah tag
     * author: ThanhDT
     * date:   2018-05-21 09:40 AM
     * @param $html
     * @return string
     */
    public static function wrapTextToParagraph($html)
    {
        //var_dump($html);
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED);
        //echo(self::getInnerHTML($doc->documentElement));die;
        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query('text()');
        foreach ($nodes as $node) {
            $content = htmlspecialchars(trim($node->textContent));
            if (!empty($content)) {
                $arrContent = explode(PHP_EOL, $content);
                $newNode = $doc->createDocumentFragment();
                foreach ($arrContent as $childContent) {
                    $childContent = trim($childContent);
                    if (empty($childContent)) {
                        continue;
                    }
                    $p = $doc->createElement('p', $childContent);
                    $newNode->appendChild($p);
                }
                //$p = $doc->createElement('p', $content);
                //var_dump($content);
                $doc->documentElement->replaceChild($newNode, $node);
            } else {
                //echo 'EMPTY' . PHP_EOL;
            }
        }
        $html = self::getInnerHTML($doc->documentElement);

        return $html;
    }

    /**
     * Get dom html content
     * author: ThanhDT
     * date:   2018-05-21 09:40 AM
     * @param \DOMNode $element
     * @return string
     */
    public static function getInnerHTML(\DOMNode $element)
    {
        $innerHTML = "";
        $children = $element->childNodes;

        foreach ($children as $child) {
            $innerHTML .= trim($element->ownerDocument->saveHTML($child));
        }

        return $innerHTML;
    }

    public static function buildPagingMeta($domain, $mobile, $baseUrl, $title, $pageIndex, $pageCount, $suffix = '')
    {
        $seo = [];
        if ($pageIndex == 1) {
            $seo['title'] = $title;
        } else {
            $seo['title'] = sprintf(Constants::TITLE_SEO_PAGING_FORMAT, $title, $pageIndex, $pageCount);
        }
        if (!empty($suffix)) {
            $seo['title'] .= ' - ' . $suffix;
        }
        $seo['url'] = $domain . $baseUrl;
        $seo['mobile_url'] = $mobile . $baseUrl;
        /*$pageUrl = $baseUrl[strlen($baseUrl) - 1] == '/' ? $baseUrl . 'page/' : $baseUrl . '/page/';
        if ($pageIndex != 1) {
            if ($pageIndex != 2) {
                $seo['prev_url'] = $pageUrl . ($pageIndex - 1);
            } else {
                $seo['prev_url'] = $baseUrl;
            }
            $seo['url'] = $pageUrl . $pageIndex;
        } else {
            $seo['url'] = $baseUrl;
        }
        if ($pageCount != 1) {
            if ($pageIndex != $pageCount) {
                $seo['next_url'] = $pageUrl . ($pageIndex + 1);
            }
        }*/

        return $seo;
    }

    public static function sanitizeOutput($buffer)
    {
        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );
        $replace = array(
            '>',
            '<',
            '\\1',
            ''
        );
        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    }

    /*
     * Store html content of page to cache
     * Author: ThanhDT
     * Date: 2016-01-12 11:32 AM
     * */
    public static function addCachePage($request, $response, $cachParams, $cacheTime)
    {
        $key = $request->getRequestUri();
        $gzipContent = Lib::gzip(Lib::sanitizeOutput($response->getContent()));
        //$gzipContent = $response->getContent();
        //$redis = $container->get(Constants::SERVER_CACHE_FULL_PAGE);
        //$redis->setString($key, $gzipContent, $cacheTime);
        $serverCache = CacheProvider::createInstance($request, Constants::SERVER_CACHE_FULL_PAGE, $cachParams, CacheProvider::REDIS);
        $serverCache->setString($key, $gzipContent, $cacheTime);
    }

    /* Clear cache url */

    public static function clearAllCache($url = '')
    {
        if (empty($url)) {
            return false;
        }

        try {
            $header = [
                'X-Refresh-Cache:123456',
            ];
            $process = curl_init($url);
            curl_setopt($process, CURLOPT_HTTPHEADER, $header);
            curl_setopt($process, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36');
            curl_setopt($process, CURLOPT_TIMEOUT, 30);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
            $return = curl_exec($process);
            curl_close($process);
            return $return;
        } catch (\Exception $e) {
            //  Yii::log("\n".$e->getMessage());
            return false;
        }
    }


    /**
     * Convert title to slug
     * author: TrieuNT
     * create date: 2018-11-02 10:08 AM
     * @param $text
     * @return mixed|string
     */
    public static function convertToSlug($text)
    {
        $text = self::mapUnsignedString($text);
        $text = self::removeSpecialChar($text);
        // Convert space to -
        $text = str_replace(' ', '-', $text);
        // Re-format text
        $text = strtolower(preg_replace('/-{2,}/', '-', $text));
        return $text;
    }

    /**
     * Map string to unsigned
     * author: TrieuNT
     * create date: 2018-11-02 10:08 AM
     * @param $text
     * @return mixed
     */
    public static function mapUnsignedString($text)
    {
        $sourceChars = array("A", "Á", "À", "Ả", "Ã", "Ạ", "Â", "Ấ", "Ầ", "Ẩ", "Ẫ", "Ậ", "Ă", "Ắ", "Ằ", "Ẳ", "Ẵ", "Ặ", "E", "É", "È", "Ẻ", "Ẽ", "Ẹ", "Ê", "Ế", "Ề", "Ể", "Ễ", "Ệ", "I", "Í", "Ì", "Ỉ", "Ĩ", "Ị", "O", "Ó", "Ò", "Ỏ", "Õ", "Ọ", "Ô", "Ố", "Ồ", "Ổ", "Ỗ", "Ộ", "Ơ", "Ớ", "Ờ", "Ở", "Ỡ", "Ợ", "U", "Ú", "Ù", "Ủ", "Ũ", "Ụ", "Ư", "Ứ", "Ừ", "Ử", "Ữ", "Ự", "Y", "Ý", "Ỳ", "Ỷ", "Ỹ", "Ỵ", "Đ",
            "a", "á", "à", "ả", "ã", "ạ", "â", "ấ", "ầ", "ẩ", "ẫ", "ậ", "ă", "ắ", "ằ", "ẳ", "ẵ", "ặ", "e", "é", "è", "ẻ", "ẽ", "ẹ", "ê", "ế", "ề", "ể", "ễ", "ệ", "i", "í", "ì", "ỉ", "ĩ", "ị", "o", "ó", "ò", "ỏ", "õ", "ọ", "ô", "ố", "ồ", "ổ", "ỗ", "ộ", "ơ", "ớ", "ờ", "ở", "ỡ", "ợ", "u", "ú", "ù", "ủ", "ũ", "ụ", "ư", "ứ", "ừ", "ử", "ữ", "ự", "y", "ý", "ỳ", "ỷ", "ỹ", "ỵ", "đ", ")", "(", "%", "&", "/", "  ", "amp;", "*", "~", "- -", ".", ",", "#", "'", "°", "ö", "Ð", "¿a", "­", "ç‰ä„®â€¬æ½”æ±µç™©ç‰¥ - æ•”æ±¬ä´ â¥æ¡", "»", "«", "ñ", "ç", ".", "©", "Å", "́", "„", "œ", "ë", "°", "›", "§", "€", "́", "β", "ι", "", "ο", "ς", "Ü", "", "ộ", "ồ", "ầ", "039");
        $unsignedChars = array("A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "I", "I", "I", "I", "I", "I", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "Y", "Y", "Y", "Y", "Y", "Y", "D",
            "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "i", "i", "i", "i", "i", "i", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "y", "y", "y", "y", "y", "y", "d", " ", " ", "", "", "", " ", "", " ", " ", "-", "", " ", "", "", "", "o", "d", "", "", "", "", "", "n", "c", " ", "", "a", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "o", "o", "a", '');

        $new_string = str_replace($sourceChars, $unsignedChars, $text);
        return $new_string;
    }

    /**
     * Remove special character
     * author: TrieuNT
     * create date: 2018-11-02 10:08 AM
     * @param $text
     * @param string $replace_string
     * @return mixed
     */
    public static function removeSpecialChar($text, $replace_string = "")
    {
        return preg_replace('/[^a-zA-Z0-9\\s-]/', $replace_string, $text);
    }
}
