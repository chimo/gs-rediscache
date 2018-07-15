# Redis cache for GNU social

Status: early dev proof of concept

## Installation

1. Navigate to your `/local/plugins` directory (create it if it doesn't exist)
1. `git clone https://github.com/chimo/gs-rediscache.git RedisCache`
1. Run `composer install` in the `RedisCache` folder to install the dependencies

## Configuration

Tell `/config.php` to use it with (replace `127.0.0.1:6379` with the address/port of your Redis backend server):

```
    $config['rediscache']['connection'] = [ 'tcp://127.0.0.1:6379' ];
    addPlugin('RedisCache');
```

You can also use a unix socket instead of a tcp connection:

```
    $config['rediscache']['connection'] = [ 'unix:/var/run/redis/redis.sock' ];
    addPlugin('RedisCache');
```

