<?php
declare(strict_types = 1);

namespace Attogram\Justrefs;

class Mediawiki
{
    const VERSION = '0.0.2';

    public $verbose = false;

    private $userAgent = 'JustRefsBot/' . self::VERSION;

    private $api = 'https://en.wikipedia.org/w/api.php';

    private $apiLinks = '?action=parse&prop=externallinks|links&format=json&page=';

    private $apiSearch = '?action=query&list=search&format=json&srprop=&srlimit=50&srsearch=';

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
        if (empty($data['parse'])
            || !isset($data['parse']['title'])
            || !strlen($data['parse']['title'])
            || !isset($data['parse']['links'])
            || !is_array($data['parse']['links'])
            || !isset($data['parse']['externallinks'])
            || !is_array($data['parse']['externallinks'])
        ) {
            $this->verbose('getLink: missing elements');
            return false;
        }
        
        return $data;
    }

    /**
     * @param string $query
     * @return array|false
     */
    public function search($query)
    {
        $url = $this->api . $this->apiSearch . urlencode($query);
        $this->verbose("getSearch: query: $query url: $url");
        $json = $this->getApi($url);
        if (!$json) {
            return false;
        }
        //$this->verbose($json);
        $data = @json_decode($json, true);
        if (empty($data)
            || !is_array($data)
            || empty($data['query'])
            || empty($data['query']['search'])
            || !is_array($data['query']['search'])
        ) {
            return false;
        }
        //$this->verbose($data);
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

    /**
     * @param string $message
     * @return void
     */
    private function verbose($message)
    {
        if ($this->verbose) {
            print '<pre>' . gmdate('Y-m-d H:i:s') . ': Mediawiki: ' . htmlentities(print_r($message, true)) . '</pre>';
        }
    }
}
