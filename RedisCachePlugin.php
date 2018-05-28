<?php

if (!defined('GNUSOCIAL')) {
    exit(1);
}

// composer
require 'vendor/autoload.php';

class RedisCachePlugin extends Plugin
{
    const VERSION = '0.0.1';

    private $client = null;
    public $defaultExpiry = 86400; // 24h

    function onInitializePlugin()
    {
        $this->_ensureConn();

        return true;
    }

    private function _ensureConn()
    {
        if ($this->client === null) {
            $connection = common_config('rediscache', 'connection');

            $this->client = new Predis\Client($connection);
        }
    }

    function onStartCacheGet(&$key, &$value)
    {
        $this->_ensureConn();

        $ret = $this->client->get($key);

        // Hit, overwrite "value" and return false
        // to indicate we took care of this
        if ($ret !== null) {
            $value = unserialize($ret);

            Event::handle('EndCacheGet', array($key, &$value));
            return false;
        }

        // Miss, let GS do its thing
        return true;
    }

    // TODO: look into flag and expiry values we get from GS
    function onStartCacheSet(&$key, &$value, &$flag, &$expiry, &$success)
    {
        $this->_ensureConn();

        if ($expiry === null) {
            $expiry = $this->defaultExpiry;
        }

        $ret = $this->client->set($key, serialize($value));

        if ($ret->getPayload() === "OK") {
            $success = true;

            Event::handle('EndCacheSet', array($key, $value, $flag, $expiry));

            return false;
        }

        return true;
    }

    function onStartCacheDelete($key)
    {
        $this->_ensureConn();

        $this->client->del($key);

        Event::handle('EndCacheDelete', array($key));

        // Let other Caches delete stuff if they want to
        return true;
    }

    function onStartCacheIncrement(&$key, &$step, &$value)
    {
        $this->_ensureConn();

        // TODO: handle when this fails
        $this->client->incrby($key, $step);

        Event::handle('EndCacheIncrement', array($key, $step, $value));

        return false;
    }

    function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'RedisCache',
                            'version' => self::VERSION,
                            'author' => 'chimo',
                            'homepage' => 'https://github.com/chimo/gs-rediscache',
                            'description' =>
                            // TRANS: Plugin description.
                            _m('')); // TODO
        return true;
    }
}

