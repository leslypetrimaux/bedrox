<?php

namespace Bedrox\Core\Functions;

use Bedrox\Core\Response;
use Bedrox\Skeleton;

class Dumper extends Skeleton
{
    /**
     * @param bool $die
     * @param mixed ...$strings
     */
    public static function dump(bool $die, ... $strings): void
    {
        if ($_SESSION['APP_DEBUG']) {
            $d = new self();
            $dumps = $d->getData($strings);
            $debugTrace = $d->getMethod();
            $d->setDumpResult(array(
                'file' => $debugTrace['file'],
                'line' => $debugTrace['line'],
                'outputs' => $dumps
            ));
        } else {
            $_SESSION['APP_DEBUG'] = false;
        }
    }

    /**
     * Print dd() results
     */
    public static function printAndDie(): void
    {
        print_r((new Response())->renderView($_SERVER['APP']['FORMAT'] ?? $_SESSION['APP_FORMAT'], null, null));
        die;
    }

    /**
     * @param array $dump
     */
    public function setDumpResult(array $dump): void
    {
        $_SESSION['DUMPS'][] = $dump;
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