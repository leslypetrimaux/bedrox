<?php

namespace Bedrox\Core\Annotations;

use Bedrox\Core\Exceptions\BedroxException;

class AnnotationsTypes
{
    /**
     * SGBD/Entities for EntityManager
     */
    public const LABEL_DB = '@Database';

    public const LABEL_DB_TABLE = 'table';
    public const LABEL_DB_PRIMARY_KEY = 'primary';
    public const LABEL_DB_FOREIGN_KEY = 'foreign';
    public const LABEL_DB_COLUMN = 'column';

    public const DB_TABLE = 'EDR\\Table';
    public const DB_PRIMARY_KEY = 'EDR\\PrimaryKeys';
    public const DB_FOREIGN_KEY = 'EDR\\ForeignKeys';
    public const DB_COLUMN = 'EDR\\Column';
    public const DB_STRATEGY = 'EDR\\PKStrategy';

    /**
     * AnnotationsTypes usable by other classes
     */
    public $dbTable;
    public $dbPrimaryKey;
    public $dbForeignKey;
    public $dbColumn;

    public $ctrlRoute;

    /**
     * AnnotationsTypes public constructor
     * Create object depending on $type.
     *
     * @param string $type
     */
    public function __construct(?string $type)
    {
        if ($type !== null) {
            if ($type === self::LABEL_DB) {
                $this->dbTable = self::DB_TABLE;
                $this->dbPrimaryKey = self::DB_PRIMARY_KEY;
                $this->dbForeignKey = self::DB_FOREIGN_KEY;
                $this->dbColumn = self::DB_COLUMN;
            }
        } else {
            BedroxException::render(
                'ERR_ANNOTATIONS_TYPE',
                'Unable to access annotations type for your application.'
            );
        }
    }

    /**
     * Return the "Annotation" name
     * 
     * @param string $type
     * @return string|null
     */
    public function get(?string $type): ?string
    {
        $annotation = null;
        if ($type !== null) {
            switch ($type) {
                case self::LABEL_DB_TABLE:
                    $annotation = $this->dbTable;
                    break;
                case self::LABEL_DB_PRIMARY_KEY:
                    $annotation = $this->dbPrimaryKey;
                    break;
                case self::LABEL_DB_FOREIGN_KEY:
                    $annotation = $this->dbForeignKey;
                    break;
                case self::LABEL_DB_COLUMN:
                    $annotation = $this->dbColumn;
                    break;
            }
        } else {
            BedroxException::render(
                'ERR_ANNOTATIONS_TYPE',
                'Unable to read annotations type for your application.'
            );
        }
        return $annotation;
    }
}
