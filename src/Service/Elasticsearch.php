<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 10/29/2018
 * Time: 11:30 AM
 */

namespace App\Service;

class Elasticsearch
{
    private $elasticUrl;
    private $timeout;
    public const INDIA_POSTS_INDEX = "indianautosblog_posts/post_publish";
    const TOTAL_PATTERN = '"hits":{"total":';

    /**
     * Construct for Elasticsearch
     * author: ThanhDT
     * date:   2018-10-29 05:20 PM
     * ElasticsearchUtils constructor.
     * @param string $elasticUrl
     * @param int $timeout
     */
    public function __construct(string $elasticUrl, int $timeout)
    {
        $this->elasticUrl = $elasticUrl;
        $this->timeout = $timeout;
    }

    /**
     * Search data from elastic search
     * author: ThanhDT
     * date:   2018-10-29 05:21 PM
     * @param $index
     * @param $query: String, Ex: {"match": {"title":"August"}}
     * @param $offset
     * @param $limit
     * @param $total
     * @param $error
     * @return bool|mixed
     */
    public function search($index, $query, $offset, $limit, &$total, &$error)
    {
        $esUrl = $this->elasticUrl . $index . '/_search?filter_path=hits.total,hits.hits._source';
        $query = "{
            \"from\" : $offset, \"size\" : $limit,
            \"query\" : $query
        }";
        $content = $this->requestApi($esUrl, $query, $this->timeout, $error);
        if ($content === false) {
            $total = 0;
            return false;
        }

        $pos = strpos($content, self::TOTAL_PATTERN);
        if ($pos === false) {
            $total = 0;
            return false;
        }

        $content = substr($content, $pos);//var_dump($content);die;
        $total = str_replace(self::TOTAL_PATTERN, '', substr($content, 0, strpos($content, ',')));
        $startIndex = strpos($content, '[');
        $endIndex = strrpos($content, ']');
        $content = substr($content, $startIndex, $endIndex - $startIndex + 1);
        $data = json_decode($content, true);

        return $data;
    }

    /**
     * Request api by CURL
     * author: ThanhDT
     * date:   2018-10-29 05:22 PM
     * @param $url
     * @param $query
     * @param $timeout
     * @param $error
     * @return bool|mixed
     */
    private function requestApi($url, $query, $timeout, &$error)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //Set to zero to switch to the default built-in connection timeout - 300 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        // Append data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        // Set headers
        $headers = ['content-type: application/json'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        try {
            // Exec request
            $content = curl_exec($ch);

            //Return a string containing the last error for the current session
            if (curl_error($ch)) {
                $error = curl_error($ch);
                return false;
            }

            // Check if any error occurred - Returns the error number or 0 (zero) if no error occurred.
            // view result : https://curl.haxx.se/libcurl/c/libcurl-errors.html
            if (curl_errno($ch)) {
                $error = curl_errno($ch);
                return false;
            }
            // Close handle
            curl_close($ch);

            return $content;
        } catch (\Exception $ex) {
            $error = $ex->getMessage() . PHP_EOL . $ex->getTraceAsString();
            return false;
        }
    }

    public function searchPosts($index, $query, $offset, $limit, &$error)
    {
        $data = [];
        $esUrl = $this->elasticUrl . $index . '/_search?filter_path=hits.total,hits.hits._source';
        $query = "{
            \"from\" : $offset, \"size\" : $limit,
            \"query\" : $query
        }";
        $content = $this->requestApi($esUrl, $query, $this->timeout, $error);
        if ($content != null) {
            $data = json_decode($content, true);
        }
        return $data;
    }
}
