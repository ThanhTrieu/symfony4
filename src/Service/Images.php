<?php
/**
 * Created by JetBrains PhpStorm.
 * User: hoandoanviet
 * Date: 11/14/15
 * Time: 9:45 AM
 * To change this template use File | Settings | File Templates.
 */

namespace App\Service;

use App\Utils\Lib;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Images
{
    protected $mediaUrl;
    const WEB_DOMAIN_SSL = 'https://indianautosblog.com/wp-content/uploads';
    const WEB_DOMAIN = 'http://indianautosblog.com/wp-content/uploads';
    const FILE_DOMAIN = 'https://img.indianautosblog.com';
    const NO_IMAGE = '/no-image.jpg';
    const IMAGE_SERVICE = 'ImageUtils';

    /*
     * ThanhDT add for common image lib
     * */
    public function __construct(string $mediaUrl)
    {
        $this->mediaUrl = $mediaUrl;
    }

    /**
     * Get image by thumb size
     * author: ThanhDT
     * date:   2018-05-18 09:24 AM
     * @param $url
     * @param $imageSize
     * @return string
     */
    public function getImageSize($url, $imageSize)
    {
        if (empty($url)) {
            $image = self::NO_IMAGE;
        } else {
            $image = self::trimDomain($url);
            if (strpos('http', $image) === 0) {
                return $image;
            }
            //$url = preg_replace('/\\.(jpg|png|gif)/i', $thumbSize . '\1', str_replace(self::WebDomain, $this->mediaUrl, $url));
        }
        $thumbUrl = sprintf('%s/crop/%s%s', $this->mediaUrl, $imageSize, $image);

        return $thumbUrl;
    }

    /**
     * Get full size
     * author: ThanhDT
     * date:   2018-05-18 09:27 AM
     * @param $url
     * @return mixed|string
     */
    public function getFullImage($url)
    {
        if (empty($url)) {
            return $this->mediaUrl . self::NO_IMAGE;
        }
        $url = $this->mediaUrl . self::trimDomain($url);
        return $url;
    }

    private function trimDomain($url)
    {
        if (strpos($url, self::WEB_DOMAIN_SSL) !== false) {
            return str_replace(self::WEB_DOMAIN_SSL, '', $url);
        }

        if (strpos($url, self::FILE_DOMAIN) !== false) {
            return str_replace(self::FILE_DOMAIN, '', $url);
        }

        return str_replace(self::WEB_DOMAIN, '', $url);
    }
}
