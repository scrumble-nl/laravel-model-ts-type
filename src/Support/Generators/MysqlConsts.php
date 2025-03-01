<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

class MysqlConsts
{
    /**
     * @var array<int, string>
     */
    public const STRING_TYPES = ['char', 'varchar', 'binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'tinytext', 'text', 'mediumtext', 'longtext', 'enum', 'set'];

    /**
     * @var array<int, string>
     */
    public const NUMBER_TYPES = ['bit', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'INTEGER'];

    /**
     * @var array<int, string>
     */
    public const BOOL_TYPES = ['tinyint', 'boolean'];

    /**
     * @var array<int, string>
     */
    public const DATE_TYPES = ['date', 'time', 'datetime', 'timestamp', 'year'];
}
