<?php
/**
 * Created by PhpStorm.
 * User: felipe.rigo
 * Date: 26/12/2017
 * Time: 15:20
 */

namespace Fvr\Database\Schema;

use Cake\Database\Schema\PostgresSchema;

/**
 * Schema management/reflection features for PostgreSQL by AGETIC-UFMS.
 */
class NewPostgresSchema extends PostgresSchema
{

    /**
     * {@inheritDoc}
     */
    public function listTablesSql($config)
    {
        $sql = 'SELECT table_name as name FROM information_schema.tables WHERE table_schema = :schema';
        $sql .= " UNION SELECT table_name as name FROM INFORMATION_SCHEMA.views WHERE table_schema = :schema";
        $sql .= " UNION SELECT c.oid::regclass::text FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
WHERE ( relkind = 'm' or relkind = 'v' ) AND n.nspname = :schema";
        $sql .= ' ORDER BY 1';
        $schema = empty($config['schema']) ? 'public' : $config['schema'];

        return [$sql, [$schema]];
    }

    /**
     * {@inheritDoc}
     */
    public function describeColumnSql($tableName, $config)
    {
        $sql = 'SELECT DISTINCT table_schema AS schema,
            column_name AS name,
            data_type AS type,
            is_nullable AS null, column_default AS default,
            character_maximum_length AS char_length,
            c.collation_name,
            d.description as comment,
            ordinal_position,
            c.numeric_precision as column_precision,
            c.numeric_scale as column_scale,
            pg_get_serial_sequence(attr.attrelid::regclass::text, attr.attname) IS NOT NULL AS has_serial
        FROM information_schema.columns c
        INNER JOIN pg_catalog.pg_namespace ns ON (ns.nspname = table_schema)
        INNER JOIN pg_catalog.pg_class cl ON (cl.relnamespace = ns.oid AND cl.relname = table_name)
        LEFT JOIN pg_catalog.pg_index i ON (i.indrelid = cl.oid AND i.indkey[0] = c.ordinal_position)
        LEFT JOIN pg_catalog.pg_description d on (cl.oid = d.objoid AND d.objsubid = c.ordinal_position)
        LEFT JOIN pg_catalog.pg_attribute attr ON (cl.oid = attr.attrelid AND column_name = attr.attname)
        WHERE table_name = ? AND table_schema = ? AND table_catalog = ?
        UNION
        SELECT s.nspname as schema, a.attname as name,
           pg_catalog.format_type(a.atttypid, a.atttypmod) as type,
           CASE WHEN a.attnotnull IS TRUE THEN \'NO\' ELSE \'YES\' END as null,
           null as default,
           null AS char_length,
                null as collation_name,
                null as comment,
                a.attnum as ordinal_position,     
                null as column_precision,
                null as column_scale,
                false AS has_serial 
        FROM pg_attribute a
        JOIN pg_class t on a.attrelid = t.oid
        JOIN pg_namespace s on t.relnamespace = s.oid
        WHERE a.attnum > 0 AND NOT a.attisdropped AND t.relkind = \'m\'
        AND t.relname = ? AND s.nspname = ? AND current_catalog = ?
        ORDER BY ordinal_position';

        $schema = empty($config['schema']) ? 'public' : $config['schema'];

        return [$sql, [$tableName, $schema, $config['database'], $tableName, $schema, $config['database']]];
    }

}
