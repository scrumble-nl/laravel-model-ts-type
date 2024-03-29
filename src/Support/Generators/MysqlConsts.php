<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

class MysqlConsts
{
    /**
     * @var array
     */
    public const STRING_TYPES = ['char', 'varchar', 'binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'tinytext', 'text', 'mediumtext', 'longtext', 'enum', 'set'];

    /**
     * @var array
     */
    public const NUMBER_TYPES = ['bit', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'INTEGER'];

    /**
     * @var array
     */
    public const BOOL_TYPES = ['tinyint'];

    /**
     * @var array
     */
    public const DATE_TYPES = ['date', 'time', 'datetime', 'timestamp', 'year'];
}
