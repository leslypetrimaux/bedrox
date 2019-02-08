<?php

namespace Bedrox\Core\Interfaces;

use Bedrox\Core\Request;
use Bedrox\Core\Response;

interface iRequest
{
    /**
     * @return Request
     */
    public static function createFromGlobals(): Request;

    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response;
}