<?php

namespace Bedrox\Core;

use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\Core\Interfaces\iRequest;
use Bedrox\Skeleton;

/**
 * @property  route
 * @property Route|null route
 */
class Request implements iRequest
{
    protected const X_RESPONSE_TYPE = 'X-Response-Type';

    public $files;
    public $get;
    public $post;

    /**
     * Create the Application request from PHP globals.
     *
     * @return Request
     */
    public static function createFromGlobals(): self
    {
        $request = new self();
        $request->get = !empty($_GET) ? $_GET : null;
        $request->post = !empty($_POST) ? $_POST : null;
        $request->files = !empty($_FILES) ? $_FILES : null;
        /** @noinspection PhpComposerExtensionStubsInspection */
        $headers = getallheaders();
        if (!empty($headers[self::X_RESPONSE_TYPE])) {
            $format = $request->parseResponseType($headers[self::X_RESPONSE_TYPE]);
            if (!empty($format) && !$request->getResponseType($format)) {
                BedroxException::render(
                    'ERR_URI_FORMAT',
                    'Erreur lors de la récupération de l\'encodage de la page dans l\'en-tête. Vérifiez votre route ou la configuration de votre application.'
                );
            }
        } else {
            $format = null;
        }
        $request->route = (new Router())->getCurrentRoute(!empty($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : Skeleton::BASE, $format);
        return $request;
    }

    public function parseResponseType(string $format): ?string
    {
        if (in_array($format, Response::TYPE_JSON, true)) {
            return Response::FORMAT_JSON;
        }
        if (in_array($format, Response::TYPE_XML, true)) {
            return Response::FORMAT_XML;
        }
        return null;
    }

    /**
     * Check if the response type asked is valid
     *
     * @param string $format
     * @return bool
     */
    public function getResponseType(string $format): bool
    {
        return in_array($format, array(
            Response::FORMAT_JSON,
            Response::FORMAT_XML,
            Response::FORMAT_CSV
        ), true);
    }

    /**
     * Handle the user's Request and return the wanted Response.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $response = new Response();
        if ($request !== null) {
            $response->request = new self();
            $response->request->get = !empty($request->get) ? $request->get : null;
            $response->request->post = !empty($request->post) ? $request->post : null;
            $response->request->files = !empty($request->files) ? $request->files : null;
            $response->route = $request->route;
        } else {
            BedroxException::render(
                'ERR_REQUEST',
                'Une erreur s\'est produite lors de la lecture de la requête.'
            );
        }
        return $response;
    }
}
