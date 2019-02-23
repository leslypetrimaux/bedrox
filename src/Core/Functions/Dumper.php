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
        $d = new self();
        $dumps = $d->getData($strings);
        $debugTrace = $d->getMethod();
        http_response_code(200);
        print_r((new Response())->renderView($_SERVER['APP']['FORMAT'], array(
            'file' => $debugTrace['file'],
            'line' => $debugTrace['line'],
            'dumps' => $dumps
        ), null));
    }

    /**
     * @return array|null
     */
    protected function getMethod(): ?array
    {
        $debug = debug_backtrace();
        return $debug[2];
    }

    /**
     * @param mixed ...$strings
     * @return array|null
     */
    protected function getData(... $strings): ?array
    {
        $responses = array();
        foreach ($strings as $arrayString) {
            $response = array();
            foreach ($arrayString as $string) {
                $response[$this->getClassOrType($string)] = $string;
            }
            $responses[] = $response;
        }
        return $responses[0];
    }

    /**
     * @param mixed $object
     * @return string|null
     */
    protected function getClassOrType($object): ?string
    {
        return !empty(is_object($object)) ? get_class($object) : gettype($object);
    }
}