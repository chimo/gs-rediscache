<?php

if (!defined('GNUSOCIAL')) {
    exit(1);
}

// composer
require 'vendor/autoload.php';

class RedisCachePlugin extends Plugin
{
    const VERSION = '0.0.1';

    private $client;


    function initialize()
    {
        $connection = common_config('rediscache', 'connection');

        Predis\Autoloader::register();
        $this->client = new Predis\Client($connection);

        return true;
    }

    function onStartCacheGet(&$key, &$value)
    {
        // Apparently we can catch this event before `initialize()` is called
        // so check if we have a client yet and return early if we don't
        if ($this->client === null) {
            return true;
        }

        $ret = $this->client->get($key);

        // Hit, overwrite "value" and return false
        // to indicate we took care of this
        if ($ret !== null) {
            $value = unserialize($ret);

            return false;
        }

        // Miss, let GS do its thing
        return true;
    }

    function onStartCacheSet(&$key, &$value, &$flag, &$expiry, &$success)
    {
        // Apparently we can catch this event before `initialize()` is called
        // so check if we have a client yet and return early if we don't
        if ($this->client === null) {
            return true;
        }

        $ret = $this->client->set($key, serialize($value));

        if ($ret->getPayload() === "OK") {
            $success = true;
            return false;
        }

        return true;
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

