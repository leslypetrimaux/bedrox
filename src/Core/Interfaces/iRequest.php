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
     * @param array $items
     * @return array
     */
    public static function xssFilter(array $items): array;

    /**
     * @param string $format
     * @return string|null
     */
    public function parseResponseType(string $format): ?string;

    /**
     * @param string $format
     * @return bool
     */
    public function getResponseType(string $format): bool;

    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response;
}
