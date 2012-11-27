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

    protected $actionReads = array(
        'noticia.exibir'
    );

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

    public function setToCache($key, $value, $ttl = 0) {
        return $this->cache->set($key, $value, $ttl);
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

    public function curlLoad($url, $reads=false) {
        if ($reads) $url .= '&leituras=' . $reads;
        $ch = curl_init($url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
        curl_setopt ($ch, CURLOPT_TIMEOUT, 5) ;
        $contents = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
            $contents = unserialize($contents);
            $contents->tmp_leituras = 1;
            return $contents;
        }
        throw new CommunicationException('Unable to load the content');
    }

    private function validateCacheReads($contents) {
        $ttl = strtotime($contents->generated) + $contents->ttl - time();
        return $contents->parameters['acao'] == 'noticia.exibir' && $ttl > 0;
    }

    private function getContentAction($contents) {
        if (isset($contents->parameters['acao'])){
            return $contents->parameters['acao'];
        }
        return false;
    }

    private function contentCountsReads($contents){
        $action = $this->getContentAction($contents);
        return in_array($action, $this->actionReads);
    }

    private function loadUrl($url) {
        $url = $this->fixUrl($url);
        $cacheKey = $this->generateCacheKey($url);
        $contents = $this->getFromCache($cacheKey);
        $reads = 1;
        if (false !== $contents) {
            $reads = ++$contents->tmp_leituras;
            if ($this->validateCacheReads($contents)){
                $this->setToCache($cacheKey, $contents);
                return $contents;
            }
        }

        $contents = $this->curlLoad($url, $reads);

        if (!$this->cache) {
            return $contents;
        }

        $ttl = strtotime($contents->generated) + $contents->ttl - time();
        if (!isset($contents->status) || $contents->status == 0) {
            $ttl = 10;
        }elseif ($this->contentCountsReads($contents)) {
            $ttl = 0;
        }
        $this->setToCache($cacheKey, $contents, $ttl);
        return $contents;

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
