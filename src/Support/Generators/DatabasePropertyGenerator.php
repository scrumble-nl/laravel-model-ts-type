<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PDO;
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
    $connection = DB::connection($model->getConnection()->getName());
    $driverName = $model
      ->getConnection()
      ->getPDO()
      ->getAttribute(PDO::ATTR_DRIVER_NAME);

    // Determine fields of table depending on the driver name
    switch ($driverName) {
      case 'mysql':
        $fields = array_map(function ($field) {
          return [
            'name' => $field->Field,
            'type' => $field->Type,
            'isNullable' => $field->Null === 'YES',
          ];
        }, $connection->select("SHOW FIELDS FROM `$table`"));
        break;

      case 'pgsql':
        $fields = array_map(function ($field) {
            return [
                'name' => $field->name,
                'type' => $field->type,
                'isNullable' => $field->is_nullable === 'YES',
              ];
        }, $connection->select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = `$table`"));
        break;

      case 'sqlite':
        $fields = array_map(function ($field) {
          return [
            'name' => $field->name,
            'type' => $field->type,
            'isNullable' => $field->notnull === 0,
          ];
        }, $connection->select("PRAGMA table_info(`$table`)"));
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
   * Format the given database field
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
      'value' =>
        $type .
        ('YES' === $field['isNullable'] ? ' | null' : '') .
        ('any' === $type ? ' // NOT FOUND' : ''),
    ];
  }
}
