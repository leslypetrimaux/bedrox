<?php

namespace Bedrox\Core\Interfaces;

use Bedrox\Core\Request;
use Bedrox\Core\Response;

interface iKernel
{
    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response;

    /**
     * @param Response $response
     */
    public function terminate(Response $response): void;

    /**
     * @return array
     */
    public static function getCustomCmd(): array;
}
