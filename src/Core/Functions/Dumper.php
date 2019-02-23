<?php

namespace Bedrox\Core\Functions;

use Bedrox\Core\Response;
use Bedrox\Skeleton;

class Dumper extends Skeleton
{
    /**
     * @param mixed ...$strings
     */
    public static function dump(... $strings): void
    {
        // TODO: Get class/line source
        $d = new self();
        $responses = array();
        foreach ($strings as $arrayString) {
            $response = array();
            foreach ($arrayString as $string) {
                $response[$d->getClass($string)] = $string;
            }
            $responses[] = $response;
        }
        http_response_code(200);
        print_r((new Response())->renderView($_SERVER['APP']['FORMAT'], $responses[0], null));
    }

    /**
     * @param mixed $object
     * @return string|null
     */
    protected function getClass($object): ?string
    {
        return !empty(is_object($object)) ? get_class($object) : gettype($object);
    }
}