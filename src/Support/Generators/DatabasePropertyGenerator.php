<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

use PDO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Interfaces\IPropertyGenerator;

class DatabasePropertyGenerator implements IPropertyGenerator
{
    /**
     * {@inheritDoc}
     */
    public function getPropertyDefinition(Model $model): array
    {
        $propertyDefinition = [];
        $table = $model->getTable();
        $connectionName = $model->getConnection()->getName();
        $connection = DB::connection($connectionName);
        $driverName = $model
            ->getConnection()
            ->getPDO()
            ->getAttribute(PDO::ATTR_DRIVER_NAME);

        if (!Schema::connection($connectionName)->hasTable($table)) {
            error_log("Tried to get columns of '{$table}' but the table was not found in the database.");

            return [];
        }

        // Determine fields of table depending on the driver name
        switch ($driverName) {
            case 'mysql':
                $fields = array_map(function ($field) {
                    return [
                        'name' => $field->Field,
                        'type' => $field->Type,
                        'isNullable' => 'YES' === $field->Null,
                    ];
                }, $connection->select("SHOW FIELDS FROM `{$table}`"));

                break;

            case 'pgsql':
                $fields = array_map(function ($field) {
                    return [
                        'name' => $field->column_name,
                        'type' => $field->data_type,
                        'isNullable' => 'YES' === $field->is_nullable,
                    ];
                }, $connection->select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = '{$table}'"));

                break;

            case 'sqlite':
                $fields = array_map(function ($field) {
                    return [
                        'name' => $field->name,
                        'type' => $field->type,
                        'isNullable' => 0 === $field->notnull,
                    ];
                }, $connection->select("PRAGMA table_info(`{$table}`)"));

                break;

            default:
                throw new \Exception('Driver not supported.');

                break;
        }

        foreach ($fields as $field) {
            $propertyDefinition[$field['name']] = $this->formatDatabaseType($field);
        }

        return $propertyDefinition;
    }

    /**
     * Format the given database field.
     *
     * @param  array $field
     * @return array
     */
    public function formatDatabaseType(array $field): array
    {
        $type = 'any';
        $typesToCheck = [
            'boolean' => MysqlConsts::BOOL_TYPES,
            'string' => MysqlConsts::STRING_TYPES,
            'number' => MysqlConsts::NUMBER_TYPES,
            'string /* Date */' => MysqlConsts::DATE_TYPES,
        ];

        foreach ($typesToCheck as $tsType => $typesToCheck) {
            foreach ($typesToCheck as $databaseType) {
                if (false !== strpos($field['type'], $databaseType)) {
                    $type = $tsType;

                    break;
                }
            }

            if ('any' !== $type) {
                break;
            }
        }

        return [
            'operator' => ':',
            'value' => $type .
                ($field['isNullable'] ? ' | null' : '') .
                ('any' === $type ? ' // NOT FOUND' : ''),
        ];
    }
}
