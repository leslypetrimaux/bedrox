<?php

namespace Bedrox\Core\Annotations;

use Bedrox\Core\Response;

class AnnotationsTypes
{
    /**
     * SGBD/Entities for EntityManager
     */
    public const LABEL_DB = '@Database';

    public const LABEL_DB_TABLE = 'table';
    public const LABEL_DB_PRIMARY_KEY = 'primary';
    public const LABEL_DB_COLUMN = 'column';

    public const DB_TABLE = 'EDR\\Table';
    public const DB_PRIMARY_KEY = 'EDR\\PrimaryKeys';
    public const DB_COLUMN = 'EDR\\Column';
    public const DB_STRATEGY = 'DB\\Strategy';

    /**
     * AnnotationsTypes usable by other classes
     */
    public $dbTable;
    public $dbPrimaryKey;
    public $dbColumn;

    public $ctrlRoute;

    protected $response;

    /**
     * AnnotationsTypes public constructor
     * Create object depending on $type.
     *
     * @param string $type
     */
    public function __construct(?string $type)
    {
        $this->response = new Response();
        if ($type !== null) {
            if ($type === self::LABEL_DB) {
                $this->dbTable = self::DB_TABLE;
                $this->dbPrimaryKey = self::DB_PRIMARY_KEY;
                $this->dbColumn = self::DB_COLUMN;
            }
        } else {
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_ANNOTATIONS_TYPE',
                'message' => 'Impossible d\'écrire les types d\'annotations disponibles pour cette Application.'
            )));
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
                case self::LABEL_DB_COLUMN:
                    $annotation = $this->dbColumn;
                    break;
            }
        } else {
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_ANNOTATIONS_TYPE',
                'message' => 'Impossible de lire les types d\'annotations disponibles pour cette Application.'
            )));
        }
        return $annotation;
    }
}