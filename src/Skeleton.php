<?php

namespace Bedrox;

use Bedrox\Core\Functions\Parsing;
use Bedrox\Core\Request;
use Bedrox\Core\Response;
use Bedrox\Core\Session;

class Skeleton
{
    public const BASE = '/';

    public $request;
    public static $entityManager;
    public $auth;

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
        $this->cmd = $cmd;
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
    public function handle(Request $request): Response
    {
        return $request->handle($request);
    }

    /**
     * @param Response $response
     */
    public function terminate(Response $response): void
    {
        $response->terminate($response);
    }

    /**
     * Return the current user.
     *
     * @return bool
     */
    public function getAuth(): bool
    {
        return $this->auth;
    }

    /**
     * Set the current authentication.
     *
     * @param bool|null $auth
     * @return Skeleton
     */
    public function setAuth(?bool $auth): self
    {
        $this->auth = $auth ?? false;
        return $this;
    }

    /**
     * Get dumps to display
     *
     * @return array|null
     */
    public function getDumps(): ?array
    {
        return $this->session->get('DUMPS') ?? null;
    }
}

require_once __DIR__ . '/Core/Functions/Globals.php';
