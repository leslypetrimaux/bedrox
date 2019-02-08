<?php

namespace Bedrox\Core;

use SimpleXMLElement;
use DOMDocument;
use Bedrox\Skeleton;
use Bedrox\Core\Interfaces\iResponse;

class Response extends Skeleton implements iResponse
{
    public const FORMAT_JSON = 'json';
    public const FORMAT_XML = 'xml';

    public $request;
    public $route;

    /**
     * @param string $format
     * @param array $data
     * @param array $error
     * @return null|string
     */
    public function renderView(string $format, ?array $data, ?array $error): ?string
    {
        switch ($format) {
            case self::FORMAT_JSON:
                return $this->renderJSON($data, $error);
            case self::FORMAT_XML:
                return $this->renderXML($data, $error);
            default:
                return $this->renderJSON(null, array(
                    'code' => 'ERR_OUTPUT',
                    'message' => 'Le format d\'écriture de l\'application n\'est pas spécifié. Veuillez vérifier votre fichier "./environnement.xml".'
                ));
        }
    }

    /**
     * Render JSON Format.
     *
     * @param array $data
     * @param array $error
     * @return string|null
     */
    public function renderJSON(?array $data, ?array $error): ?string
    {
        $result = $this->renderResult($data, $error);
        header('Content-Type: application/json; charset=' . $this->parsing->parseAppEncode());
        $this->clear();
        return json_encode(
            $result,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Render XML Format.
     *
     * @param array $data
     * @param array $error
     * @return string|null
     */
    public function renderXML(?array $data, ?array $error): ?string
    {
        $result = $this->renderResult($data, $error);
        $xml = new SimpleXMLElement('<Response/>');
        $result = $this->parsing->parseRecursiveToArray($result);
        $xml = $this->parsing->parseArrayToXml($result, $xml);
        $domXml = new DOMDocument('1.0', $this->parsing->parseAppEncode());
        $domXml->preserveWhiteSpace = false;
        $domXml->formatOutput = true;
        $domXml->loadXML($xml->asXML());
        header('Content-Type: application/xml; charset=' . $this->parsing->parseAppEncode());
        $this->clear();
        return $domXml->saveXML();
    }

    /**
     * Render result Array.
     *
     * @param array $data
     * @param array $error
     * @return array|null
     */
    public function renderResult(?array $data, ?array $error): ?array
    {
        return array(
            'status' => !is_array($error) ? 'success' : 'error',
            'statusCode' => http_response_code(),
            'execTime' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2),
            'data' => $data,
            'error' => $error
        );
    }

    /**
     * Clear _SESSION & all globals/variables
     */
    public function clear(): void
    {
        unset($_SESSION, $_GET, $_POST, $_FILES);
        session_unset();
        session_destroy();
        session_write_close();
    }

    /**
     * Render the view depending on the route/controller/function.
     * Parameters defined in the Router file configuration.
     *
     * @param Response $response
     */
    public function terminate(Response $response): void
    {
        $render = empty($response->route->render) ? $this->session['APP_FORMAT'] : $response->route->render;
        if ( !empty($response->route) && !empty($response->route->url) && !empty($response->route->controller) && !empty($response->route->function) && !empty($render) ) {
            $class = new $response->route->controller($response);
            $functionStr = $response->route->function;
            if ($response->route->paramsCount > 0) {
                if (!empty($response->route->params)) {
                    switch ($response->route->paramsCount) {
                        case 1:
                            $function = $class->$functionStr($response->route->params);
                            break;
                        default:
                            http_response_code(500);
                            exit($this->renderView($render, null, array(
                                'code' => 'ERR_URI_PARAMS',
                                'message' => 'La route "' . $response->route->url . '" ne possède pas le bon nombre de paramètres. Veuillez vérifier votre fichier "./routes.xml".'
                            )));
                    }
                } else {
                    http_response_code(500);
                    exit($this->renderView($_SERVER['APP']['FORMAT'], null, array(
                        'code' => 'ERR_URI_NOTFOUND_PARAMS',
                        'message' => 'Le paramètre de cette route n\'existe pas dans la base de données. Veuillez vérifier votre requête.'
                    )));
                }
            } else {
                $function = $class->$functionStr();
            }
            http_response_code(200);
            exit($this->renderView($render, $function, null));
        }
        http_response_code(404);
        exit($this->renderView($render, null, array(
            'code' => 'ERR_URI_NOTFOUND',
            'message' => 'La route "' . $_SERVER['REQUEST_URI'] . '" n\'existe pas OU n\'est pas configurée correctement dans votre Application. Veuillez vérifier votre fichier "./routes.xml".'
        )));
    }
}