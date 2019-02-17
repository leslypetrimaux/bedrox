<?php

namespace Bedrox\Core\Annotations;

use Bedrox\Core\Entity;
use Bedrox\Core\Response;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class PhpParser
{
    /**
     * Return the class comment from $class into a string.
     * 
     * @param Entity $class
     * @return string|null
     */
    public function classComment(?Entity $class): ?string
    {
        $document = null;
        try {
            $document = (new ReflectionClass($class))->getDocComment();
        } catch (ReflectionException $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_ANNOTATIONS_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        return $document;
    }

    /**
     * Return class properties from $class into an array.
     * 
     * @param Entity $class
     * @return array|null
     */
    public function classProperties(?Entity $class): ?array
    {
        $properties = null;
        try {
            $properties = (new ReflectionClass($class))->getProperties(
                ReflectionProperty::IS_PUBLIC |
                ReflectionProperty::IS_PRIVATE |
                ReflectionProperty::IS_PROTECTED
            );
        } catch (ReflectionException $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_ANNOTATIONS_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        return $properties;
    }
    
    /**
     * Return properties comment from $properties into a string.
     * 
     * @param ReflectionProperty $properties
     * @return string|null
     */
    public function propertiesComment(?ReflectionProperty $properties): ?string
    {
        $document = null;
        try {
            if ($properties !== null) {
                $document = $properties->getDocComment();
            } else {
                throw new ReflectionException(
                    'Impossible de charger les propriétés de la classe.',
                    'REFLECTION_PROPERTIES'
                );
            }
        } catch (ReflectionException $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_ANNOTATIONS_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        return $document;
    }

    /**
     * Return an array of columns from $properties.
     * 
     * @param array $properties
     * @return array|null
     */
    public function getColumnsFromProperties(?array $properties): ?array
    {
        $columns = array();
        try {
            if ($properties !== null) {
                foreach ($properties as $property) {
                    $document = $this->propertiesComment($property);
                    $matches = $this->matchesAnnotations($document);
                    $column = $this->getAnnotationValue(AnnotationsTypes::DB_COLUMN, $matches);
                    $columns[$property->getName()] = $column;
                }
                if (empty($columns)) {
                    throw new ReflectionException(
                        'Impossible de charger les colonnes de la classe.',
                        'REFLECTION_COLUMNS'
                    );
                }
            } else {
                throw new ReflectionException(
                    'Impossible de charger les propriétés des colonnes de la classe.',
                    'REFLECTION_PROPERTIES'
                );
            }
        } catch (ReflectionException $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_ANNOTATIONS_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        return $columns;
    }

    /**
     * Return an array of columns from $properties.
     *
     * @param array|null $properties
     * @return array|null
     */
    public function getStrategyFromProperties(?array $properties): ?array
    {
        $columns = array();
        try {
            if ($properties !== null) {
                foreach ($properties as $property) {
                    $document = $this->propertiesComment($property);
                    $matches = $this->matchesAnnotations($document);
                    $column = $this->getAnnotationValue(AnnotationsTypes::DB_STRATEGY, $matches);
                    $columns[$property->getName()] = $column;
                }
                if (empty($columns)) {
                    throw new ReflectionException(
                        'Impossible de charger les colonnes de la classe.',
                        'REFLECTION_COLUMNS'
                    );
                }
            } else {
                throw new ReflectionException(
                    'Impossible de charger les propriétés des colonnes de la classe.',
                    'REFLECTION_PROPERTIES'
                );
            }
        } catch (ReflectionException $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_ANNOTATIONS_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        return $columns;
    }

    /**
     * Return an array of Annotations matching the pattern.
     *
     * @param string $document
     * @return array|null
     */
    public function matchesAnnotations(?string $document): ?array
    {
        preg_match_all("#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#", $document, $matches);
        return $matches[0];
    }

    /**
     * Return the value of an Annotation.
     * 
     * @param string $annotation
     * @param array $annotations
     * @return string|null
     */
    public function getAnnotationValue(?string $annotation, ?array $annotations): ?string
    {
        $value = null;
        if (is_array($annotations) && $annotation !== null) {
            foreach ($annotations as $line) {
                if (strpos($line, $annotation) !== false) {
                    $value = trim(str_replace($annotation . ' ', '', $line));
                }
            }
        } else {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_ANNOTATIONS_VALUE',
                'message' => 'Impossible de récupérer la value pour l\'annotation de type "' . $annotation . '".'
            )));
        }
        return $value;
    }
}