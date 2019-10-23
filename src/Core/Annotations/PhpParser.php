<?php

namespace Bedrox\Core\Annotations;

use Bedrox\EDR\Entity;
use Bedrox\Core\Exceptions\BedroxException;
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
            BedroxException::render(
                'ERR_ANNOTATIONS_' . $e->getCode(),
                $e->getMessage()
            );
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
            BedroxException::render(
                'ERR_ANNOTATIONS_' . $e->getCode(),
                $e->getMessage()
            );
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
                    'Unable to load class properties.',
                    'REFLECTION_PROPERTIES'
                );
            }
        } catch (ReflectionException $e) {
            BedroxException::render(
                'ERR_ANNOTATIONS_' . $e->getCode(),
                $e->getMessage()
            );
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
                    $match = str_replace(AnnotationsTypes::DB_COLUMN, '', $column);
                    $propName = $property->getName();
                    if (strpos($match, 'name')) {
                        $name  = preg_replace('/\({(.*)?name=\"([a-zA-Z0-9]+[_[a-zA-Z0-9]+]?)\"(.*)?}\)/', '$2', $match);
                    } else {
                        $name = null;
                    }
                    if (strpos($match, 'type')) {
                        $type  = preg_replace('/\({(.*)?type=\"([a-zA-Z0-9]+[_[a-zA-Z0-9]+]?)\"(.*)?}\)/', '$2', $match);
                    } else {
                        $type = null;
                    }
                    if (strpos($match, 'length')) {
                        $length  = preg_replace('/\({(.*)?length=\"([a-zA-Z0-9]+[_[a-zA-Z0-9]+]?)\"(.*)?}\)/', '$2', $match);
                    } else {
                        $length = null;
                    }
                    $columns[$propName] = array($name, $type, $length);
                }
                if (empty($columns)) {
                    throw new ReflectionException(
                        'Unable to load the class columns. Please check your entities.',
                        'REFLECTION_COLUMNS'
                    );
                }
            } else {
                throw new ReflectionException(
                    'Unable to load the class columns properties. Please check your entities.',
                    'REFLECTION_PROPERTIES'
                );
            }
        } catch (ReflectionException $e) {
            BedroxException::render(
                'ERR_ANNOTATIONS_' . $e->getCode(),
                $e->getMessage()
            );
        }
        return $columns;
    }

    public function getFKeysFromProperties(?array $properties): ?array
    {
        $columns = array();
        try {
            if ($properties !== null) {
                foreach ($properties as $property) {
                    $document = $this->propertiesComment($property);
                    $matches = $this->matchesAnnotations($document);
                    $column = $this->getAnnotationValue(AnnotationsTypes::DB_FOREIGN_KEY, $matches);
                    $match = str_replace(AnnotationsTypes::DB_FOREIGN_KEY, '', $column);
                    $propName = $property->getName();
                    $columns[$propName] = $match;
                }
                if (empty($columns)) {
                    throw new ReflectionException(
                        'Unable to load the class columns. Please check your entities.',
                        'REFLECTION_COLUMNS'
                    );
                }
            } else {
                throw new ReflectionException(
                    'Unable to load the class columns properties. Please check your entities.',
                    'REFLECTION_PROPERTIES'
                );
            }
        } catch (ReflectionException $e) {
            BedroxException::render(
                'ERR_ANNOTATIONS_' . $e->getCode(),
                $e->getMessage()
            );
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
                        'Unable to load the class columns.',
                        'REFLECTION_COLUMNS'
                    );
                }
            } else {
                throw new ReflectionException(
                    'Unable to load the class columns properties.',
                    'REFLECTION_PROPERTIES'
                );
            }
        } catch (ReflectionException $e) {
            BedroxException::render(
                'ERR_ANNOTATIONS_' . $e->getCode(),
                $e->getMessage()
            );
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
                    $value = str_replace('@', '', trim($line));
                }
            }
        } else {
            BedroxException::render(
                'ERR_ANNOTATIONS_VALUE',
                'Unable to retrieve the value of annotation type "' . $annotation . '".'
            );
        }
        return $value;
    }
}
