# Postgresql Database Driver Extension  
  
An extension driver to the postgresql Database to support materialized views.   

Requirements
------------

* CakePHP 4.0+
* PHP 7.2+


Install
-------

` composer require felipevr/postgresql-ext`


Use
___

Edit your config/app.php

``` 
use Fvr\Database\Driver\NewPostgres;
...
return [
...
'Datasources' => [

        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => `'Fvr\Database\Driver\NewPostgres'`,
            ...
        ],

        'test' => [
            'className' => Connection::class,
            'driver' => NewPostgres::class,
            ...
        ],
    ],
...
];
```