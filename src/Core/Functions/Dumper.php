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
        if ($_SESSION['APP_DEBUG']) {
            $d = new self();
            $dumps = $d->getData($strings);
            $debugTrace = $d->getMethod();
            http_response_code(200);
            print_r((new Response())->renderView($_SERVER['APP']['FORMAT'] ?? $_SESSION['APP_FORMAT'], array(
                'file' => $debugTrace['file'],
                'line' => $debugTrace['line'],
                'dumps' => $dumps
            ), null));
        } else {
            $_SESSION['APP_DEBUG'] = false;
        }
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
        foreach ($strings[0][0] as $string) {
            $responses[] = array(
                $this->getClassOrType($string) => $string
            );
        }
        return $responses;
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