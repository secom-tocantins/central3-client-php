<?php

namespace Secom\Central3;

use Secom\Cache\Cacheable,
    Secom\Central3\Client\Hydrator,
    Secom\Central3\Client\Exception\CommunicationException,
    \RuntimeException;

class Client
{

    protected $server = "http://central3.to.gov.br/";
    protected $site = '';
    protected $cache = null;
    protected $cacheTimeout = 300;
    protected $hydrator = null;

    public function __construct($site, Cacheable $cache = null) {
        $this->site = $site;
        $this->cache = $cache;
        $this->hydrator = new Hydrator;
    }

    public function getFromCache($key) {
        if ($this->cache) {
            return $this->cache->get($key);
        }
        return false;
    }

    public function setToCache($key, $value, $ttl = null) {
        if ($this->cache) {
            if (null == $ttl) $ttl = $this->cacheTimeout;
            return $this->cache->set($key, $value, $this->cacheTimeout);
        }
    }

    public function fixUrl($url) {
        $url = trim($url, '&');
        $url = str_replace(' ', '%20', $url);
        $url = preg_replace('@&+@', '&', $url);
        return $url;
    }

    public function generateCacheKey($url) {
        return md5($url);
    }

    public function curlLoad($url) {
        $ch = curl_init($url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
        curl_setopt ($ch, CURLOPT_TIMEOUT, 5) ;
        $contents = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
            return unserialize($contents);
        }
        return false;
    }

    private function loadUrl($url) {
        $url = $this->fixUrl($url);
        $cacheKey = $this->generateCacheKey($url);

        $contents = $this->getFromCache($cacheKey);
        if (false !== $contents) {
            return $contents;
        }

        $contents = $this->curlLoad($url);
        if (false !== $contents) {
            $ttl = $this->cacheTimeout;
            if (!isset($contents->status) || !$contents->status) {
                $ttl = 10;
            }
            $this->setToCache($cacheKey, $contents, $ttl);
            return $contents;
        }

        throw new CommunicationException('Unable to load the content');
    }

    public function query($acao, $pars = '', $site = null) {
        if ($site===null) $site = $this->site;
        $url = $this->server . "rpc/{$acao}?formato=serial&site={$site}&{$pars}";
        return $this->hydrator->hydrate($this->loadUrl($url));
    }

    public function byUri($uri,$pars='') {
        $url = $this->server . "rpc/?formato=serial&site={$this->site}&uri={$uri}&{$pars}";
        return $this->hydrator->hydrate($this->loadUrl($url));
    }
}
