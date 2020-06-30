<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Interfaces\IPropertyGenerator;

class DatabasePropertyGenerator implements IPropertyGenerator
{
    /**
     * @var array
     */
    private const STRING_TYPES = ['char', 'varchar', 'binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'tinytext', 'text', 'mediumtext', 'longtext', 'enum', 'set'];

    /**
     * @var array
     */
    private const NUMBER_TYPES = ['bit', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double'];

    /**
     * @var array
     */
    private const BOOL_TYPES = ['tinyint'];

    /**
     * @var array
     */
    private const DATE_TYPES = ['date', 'time', 'datetime', 'timestamp', 'year'];

    /**
     * {@inheritDoc}
     */
    public function getPropertyDefinition(Model $model): array
    {
        $propertyDefinition = [];
        $fields = DB::select('SHOW FIELDS FROM ' . $model->getTable());

        foreach ($fields as $field) {
            $propertyDefinition[$field->Field] = $this->formatMysqlType($field);
        }

        return $propertyDefinition;
    }

    /**
     * Format the given mysql field
     *
     * @param  \stdClass $field
     * @return array
     */
    public function formatMysqlType(\stdClass $field): array
    {
        $type = 'any';
        $typesToCheck = [
            'string' => self::STRING_TYPES,
            'number' => self::NUMBER_TYPES,
            'boolean' => self::BOOL_TYPES,
            'string /* Date */' => self::DATE_TYPES
        ];

        foreach ($typesToCheck as $tsType => $typesToCheck) {
            foreach ($typesToCheck as $mysqlType) {
                if (false !== strpos($field->Type, $mysqlType)) {
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
            'value' => $type . ('YES' === $field->Null ? ' | null' : '') . ('any' === $type ? ' // NOT FOUND' : ''),
        ];
    }
}
