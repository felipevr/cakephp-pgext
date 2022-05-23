<?php
/**
 * Created by PhpStorm.
 * User: felipe.rigo
 * Date: 26/12/2017
 * Time: 15:26
 */

namespace Fvr\Database\Driver;

use App\Database\Schema\NewPostgresSchema;
use Cake\Database\Driver\Postgres;
use PDO;


class NewPostgres extends Postgres
{

    /**
     * Base configuration settings for Postgres driver
     *
     * @var array
     */
    protected $_baseConfig = [
        'persistent' => false,
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'cake',
        'schema' => 'public',
        'port' => 5432,
        'encoding' => 'utf8',
        'timezone' => null,
        'flags' => [],
        'init' => [],
        'application_name' => '',
    ];


    /**
     * Establishes a connection to the database server
     *
     * @return bool true on success
     */
    public function connect()
    {
        if ($this->_connection) {
            return true;
        }
        $config = $this->_config;
        $config['flags'] += [
            PDO::ATTR_PERSISTENT => $config['persistent'],
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        if (empty($config['unix_socket'])) {
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        } else {
            $dsn = "pgsql:dbname={$config['database']}";
        }

        $this->_connect($dsn, $config);
        $this->_connection = $connection = $this->getConnection();
        if (!empty($config['encoding'])) {
            $this->setEncoding($config['encoding']);
        }

        if (!empty($config['schema'])) {
            $this->setSchema($config['schema']);
        }

        if (!empty($config['timezone'])) {
            $config['init'][] = sprintf('SET timezone = %s', $connection->quote($config['timezone']));
        }

        if(!empty($config['application_name'])) {
            $this->setApplicationName($config['application_name']);
        }


        foreach ($config['init'] as $command) {
            $connection->exec($command);
        }

        return true;
    }


    /**
     * Sets the application_name variablein the begging of each transaction allows you to w
     * hich logical process blocks another one. It can be information which source code line starts
     * transaction or any other information that helps you to match application_name to your code.
     *
     * @param string $application_name The application name to set `application_name` to.
     * @return void
     */
    public function setApplicationName($application_name)
    {
        $this->connect();
        $this->_connection->exec('SET application_name TO ' . $this->_connection->quote($application_name));
    }


    /**
     * Get the schema dialect.
     *
     * Used by Cake\Database\Schema package to reflect schema and
     * generate schema.
     *
     * @return \Cake\Database\Schema\PostgresSchema
     */
    public function schemaDialect()
    {
        if (!$this->_schemaDialect) {
            $this->_schemaDialect = new NewPostgresSchema($this);
        }

        return $this->_schemaDialect;
    }
}
