<?php

return array(
    'parameters' => array(
        'database_host' => getenv('DB_SERVER'),
        'database_port' => getenv('DB_PORT'),
        'database_name' => getenv('DB_NAME'),
        'database_user' => getenv('DB_USER'),
        'database_password' => getenv('DB_PASSWORD'),
        'database_prefix' => 'ps_',
        'database_engine' => 'InnoDB',
        'mailer_transport' => 'smtp',
        'mailer_host' => '127.0.0.1',
        'mailer_user' => null,
        'mailer_password' => null,
        'secret' => getenv('NFS_SECRET'),
        'ps_caching' => 'CacheMemcache',
        'ps_cache_enable' => false,
        'ps_creation_date' => '2017-04-28',
        'locale' => 'en-US',
        'cookie_key' => getenv('NFS_COOKIE_KEY'),
        'cookie_iv' => getenv('NFS_COOKIE_IV'),
        'new_cookie_key' => getenv('NFS_NEW_COOKIE_KEY'),
        'webpack_server' => false,
    ),
);
