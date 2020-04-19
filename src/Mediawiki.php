<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Mediawiki Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function curl_close;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function is_array;
use function is_string;
use function json_decode;
use function print_r;
use function urlencode;

class Mediawiki extends Base
{
    /**
     * @var string - user agent for API requests
     */
    private $userAgent = 'JustRefsBot/' . self::VERSION;

    /**
     * @var string - api endpoint
     */
    private $api = 'https://en.wikipedia.org/w/api.php';

    /**
     * @var string - url params to get page links
     */
    private $apiLinks = '?action=parse&prop=externallinks|links|templates&format=json&page=';

    /**
     * @var string - url params for search request
     */
    private $apiSearch = '?action=query&list=search&format=json&srprop=&srlimit=50&srsearch=';

    /**
     * @param string $query
     * @return array|false
     */
    public function links($query)
    {
        $data = $this->getApi($this->api . $this->apiLinks . urlencode($query));
        if (!$data || !is_array($data)) {
            $this->error('links: decode failed: ' . $query);

            return false;
        }
        $result = [];
        if (isset($data['error'])
            || !isset($data[self::PARSE][self::TITLE])
            || empty($data[self::PARSE][self::TITLE])
        ) {
            $result[self::TITLE] = $query;
            $result['error'] = true;

            return $result; // 404 Not Found
        }
        // set title
        $result[self::TITLE] = $data[self::PARSE][self::TITLE];
        // set reference links
        $result[self::REFS] = isset($data[self::PARSE][self::EXTERNALLINKS]) ? $data[self::PARSE][self::EXTERNALLINKS]: [];
        // set related topics
        $result[self::TOPICS] = isset($data[self::PARSE][self::LINKS]) ? $data[self::PARSE][self::LINKS] : [];
        // set templates
        $result[self::TEMPLATES] = isset($data[self::PARSE][self::TEMPLATES]) ? $data[self::PARSE][self::TEMPLATES] : [];

        return $result;
    }

    /**
     * @param string $query
     * @return array|false
     */
    public function search($query)
    {
        $data = $this->getApi($this->api . $this->apiSearch . urlencode($query));
        if (!$data || !is_array($data) || empty($data[self::QUERY])
            || empty($data[self::QUERY][self::SEARCH]) || !is_array($data[self::QUERY][self::SEARCH])
        ) {
            $this->error('search: query failed');

            return false;
        }
        $results = [];
        foreach ($data[self::QUERY][self::SEARCH] as $topic) {
            if (!isset($topic[self::TITLE]) || !is_string($topic[self::TITLE])) {
                continue;
            }
            $results[] = $topic[self::TITLE];
        }

        return $results;
    }

    /**
     * @param string $url
     * @return array|false
     */
    private function getApi($url)
    {
        if (empty($url) || !is_string($url)) {
            $this->error('getApi: invalid url: ' . print_r($url, true));
            return false;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        $jsonData = curl_exec($curl);
        curl_close($curl);
        if (empty($jsonData)) {
            $this->error("getApi: EMPTY RESULT: $url");

            return false;
        }
        $data = @json_decode($jsonData, true);
        if (!is_array($data)) {
            $this->error("getApi: DECODE FAILED: url: $url jsonData: $jsonData");

            return false;
        }
        $this->verbose("getApi: got: $url");

        return $data;
    }
}
