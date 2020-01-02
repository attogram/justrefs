<?php
declare(strict_types = 1);

namespace Attogram\Justrefs;

class Mediawiki extends Base
{
    private $userAgent = 'JustRefsBot/' . self::VERSION;
    private $api = 'https://en.wikipedia.org/w/api.php';
    private $apiLinks = '?action=parse&prop=externallinks|links|templates&format=json&page=';
    private $apiSearch = '?action=query&list=search&format=json&srprop=&srlimit=50&srsearch=';

    /**
     * @param string $query
     * @return array|false
     */
    public function links($query)
    {
        $url = $this->api . $this->apiLinks . urlencode($query);
        $this->verbose("getLink: query: $query url: $url");
        $jsonData = $this->getApi($url);
        if (!$jsonData) {
            return false;
        }
        $data = @json_decode($jsonData, true);
        if (empty($data) || !is_array($data)) {
            $this->verbose('getLink: decode failed');
            return false;
        }

        //$this->verbose($data);
        $result = [];

        // set title
        $result['title'] = isset($data['parse']['title'])
            ? $data['parse']['title']
            : '';

        // set related topics 
        $result['topics'] = isset($data['parse']['links'])
            ? $data['parse']['links']
            : [];

        // set reference links
        $result['refs'] = isset($data['parse']['externallinks'])
            ? $data['parse']['externallinks']
            : [];
        
        // set templates
        $result['templates'] = isset($data['parse']['templates'])
            ? $data['parse']['templates']
            : [];

        //$this->verbose($result);
        return $result;
    }

    /**
     * @param string $query
     * @return array|false
     */
    public function search($query)
    {
        $url = $this->api . $this->apiSearch . urlencode($query);
        $this->verbose("getSearch: query: $query url: $url");
        $jsonData = $this->getApi($url);
        if (!$jsonData) {
            return false;
        }
        $data = @json_decode($jsonData, true);
        if (empty($data)
            || !is_array($data)
            || empty($data['query'])
            || empty($data['query']['search'])
            || !is_array($data['query']['search'])
        ) {
            return false;
        }
        $results = [];
        foreach ($data['query']['search'] as $topic) {
            if (!isset($topic['title']) || !is_string($topic['title'])) {
                continue;
            }
            $results[] = $topic['title'];
        }
        return $results;
    }

    /**
     * @param string $url
     * @return string|false
     */
    private function getApi($url)
    {
        $this->verbose('getApi: url: ' . $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent); 
        $result = curl_exec($ch);
        curl_close($ch);
        if (empty($result)) {
            $this->verbose('getApi: Error: empty result');
            return false;
        }
        $this->verbose('getApi: strlen.output: ' . strlen($result));
        return $result;
    }
}
