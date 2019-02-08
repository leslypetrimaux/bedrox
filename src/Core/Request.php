<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;
use Bedrox\Core\Interfaces\iRequest;

/**
 * @property  route
 * @property Route|null route
 */
class Request implements iRequest
{
    public $get;
    public $post;
    public $files;

    /**
     * Create the Application request from PHP globals.
     *
     * @return Request
     */
    public static function createFromGlobals(): self
    {
        $_SESSION['APP_AUTH'] = false;
        $request = new self();
        $request->get = !empty($_GET) ? $_GET : null;
        $request->post = !empty($_POST) ? $_POST : null;
        $request->files = !empty($_FILES) ? $_FILES : null;
        $request->route = (new Router())->getCurrentRoute(!empty($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : Skeleton::BASE);
        return $request;
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
        $session = (new Session())->globals;
        if ($session) {
            $session['APP_TOKEN'] = $_SESSION['APP_TOKEN'];
            $response->session = $session;
        } else {
            http_response_code(500);
            exit($response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_SESSION',
                'message' => 'Une erreur s\'est produite lors de la lecture/écriture de la session courante. Merci de supprimer le cache de l\'Application.'
            )));
        }
        if ($request !== null) {
            $response->request = new self();
            $response->request->get = !empty($request->get) ? $request->get : null;
            $response->request->post = !empty($request->post) ? $request->post : null;
            $response->request->files = !empty($request->files) ? $request->files : null;
            $response->route = $request->route;
        } else {
            http_response_code(500);
            exit($response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_REQUEST',
                'message' => 'Une erreur s\'est produite lors de la lecture de la requête.'
            )));
        }
        return $response;
    }
}