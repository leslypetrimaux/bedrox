<?php

namespace Bedrox;

use Bedrox\Core\Functions\Parsing;
use Bedrox\Core\Request;
use Bedrox\Core\Response;
use Bedrox\Core\Session;

class Skeleton
{
    public const BASE = '/';

    protected static $response;
    public static $entityManager;

    protected $session;
    protected $parsing;
    protected $cmd;

    /**
     * Skeleton constructor.
     * Used by the Application Kernel. Is currently the Framework Kernel.
     * Handle every actions on the Application.
     * @param bool $cmd
     */
    public function __construct(bool $cmd = false)
    {
        $this->session = new Session();
        $this->parsing = new Parsing();
        if ($cmd) {
            $this->cmd = $cmd;
            self::setResponse(new Response());
        }
    }

    public function __debugInfo()
    {
        return array(
            '_class' => static::class,
            '_this' => $this
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function handle(Request $request): Response
    {
        return $request->handle($request);
    }

    /**
     * @param Response $response
     */
    protected function terminate(Response $response): void
    {
        $response->terminate($response);
    }

    /**
     * Return the current user.
     *
     * @return bool|null
     */
    protected function getAuth(): ?bool
    {
        return $this->session->get('APP_AUTH') ?? false;
    }

    /**
     * Return the Application's Token
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->session->get('APP_TOKEN');
    }

    /**
     * Get dumps to display
     *
     * @return array|null
     */
    protected function getDumps(): ?array
    {
        return $this->session->get('DUMPS') ?? null;
    }

    /**
     * @return Response|null
     */
    protected static function getResponse(): ?Response
    {
        return self::$response;
    }

    /**
     * @param Response $response
     * @return Skeleton
     */
    protected function setResponse(Response $response): ?self
    {
        self::$response = $response;
        return $this;
    }

    /**
     * @return Request|null
     */
    protected static function getRequest(): ?Request
    {
        $response = self::getResponse();
        return $response->request;
    }
}

require_once __DIR__ . '/Core/Functions/Globals.php';
