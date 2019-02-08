<?php

namespace Bedrox\Core\Functions;

use Exception;
use SimpleXMLElement;
use Bedrox\Core\Response;
use App\Kernel;

class Parsing
{
    /**
     * Parse recursive Arrays or Objects to convert every object in array.
     * 
     * @param mixed $arrays
     * @return array|null
     */
    public function parseRecursiveToArray($arrays): ?array
    {
        $array = array();
        if (is_array($arrays) || is_object($arrays)) {
            foreach ($arrays as $key => $value) {
                $item = array();
                if (is_object($value)) {
                    foreach ($value as $keyObj => $valueObj) {
                        if (is_object($valueObj)) {
                            foreach ($valueObj as $keySubObj => $valueSubObj) {
                                $item[$keyObj][$keySubObj] = $valueSubObj;
                            }
                        } elseif (is_array($valueObj)) {
                            $item[$keyObj] = $this->parseRecursiveToArray($valueObj);
                        } else {
                            $item[$keyObj] = $valueObj;
                        }
                    }
                } elseif (is_array($value)) {
                    $item[$key] = $this->parseRecursiveToArray($value);
                } else {
                    $item = $value;
                }
                $array[$key] = $item;
                unset($item);
            }
        } else {
            $array = null;
        }
        return $array;
    }

    /**
     * Parse Arrays to convert them to SimpleXMLElement.
     * 
     * @param array $arrays
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    public function parseArrayToXml(array $arrays, SimpleXMLElement $xml): SimpleXMLElement
    {
        foreach ($arrays as $key => $value) {
            $key = htmlspecialchars(preg_replace('/[^A-Za-z0-9\-]/', '', $key), ENT_XML1);
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item-' . $key;
                }
                $subnode = $key !== $xml->getName() ? $xml->addChild($key) : $xml;
                $this->parseArrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, $value);
            }
        }
        return $xml;
    }

    /**
     * Parse an XML file, encode/decode through JSON to convert to an array.
     * Call parseRecursiveToArray() to return an array.
     * 
     * @param string $file
     * @return array
     */
    public function parseXmlToArray(string $file): array
    {
        libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string(file_get_contents($file));
            if ($xml === false) {
                throw new Exception();
            }
            $object = json_decode(json_encode($xml));
            return $this->parseRecursiveToArray($object);
        } catch (Exception $e) {
            $error = $this->parseRecursiveToArray(libxml_get_last_error());
            $encode = $this->parseAppFormat();
            http_response_code(500);
            exit((new Response())->renderView($encode, null, array(
                'code' => 'ERR_XML_FILE',
                'message' => 'La classe "libXMLError" ressort l\'erreur n° ' . $error['code'] . '. Echec lors de la lecture du fichier XML "' . $file . '". Veuillez vérifier la configuration de l\'application.'
            )));
        }
    }

    /**
     * @return string|null
     */
    public function parseAppFormat(): ?string
    {
        return $_SERVER['APP']['FORMAT'] ?? Kernel::DEFAULT_FORMAT;
    }

    /**
     * @return string|null
     */
    public function parseAppEncode(): ?string
    {
        return $_SERVER['APP']['ENCODAGE'] ?? Kernel::DEFAULT_ENCODE;
    }
}