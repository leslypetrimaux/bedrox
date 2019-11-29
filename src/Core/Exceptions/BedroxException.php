<?php

namespace Bedrox\Core\Exceptions;

use Bedrox\Core\Render;
use Bedrox\Core\Response;
use Exception;

class BedroxException extends Exception
{
    public static function render(string $tag, string $message = "", int $responseCode = 500, string $format = null)
    {
        $format = !empty($format) ? $format : $_SERVER['APP']['FORMAT'];
        http_response_code($responseCode);
        exit((new Response)->renderView($format, new Render(), array(
            'code' => $tag,
            'message' => $message
        )));
    }
}
