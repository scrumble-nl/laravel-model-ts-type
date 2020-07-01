<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

use Illuminate\Support\Facades\DB;
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
            'string' => MysqlConsts::STRING_TYPES,
            'number' => MysqlConsts::NUMBER_TYPES,
            'boolean' => MysqlConsts::BOOL_TYPES,
            'string /* Date */' => MysqlConsts::DATE_TYPES
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
