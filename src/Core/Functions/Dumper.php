<?php

namespace Bedrox\Core\Functions;

use Bedrox\Core\Controller;
use Bedrox\Core\Render;
use Bedrox\Core\Response;
use Bedrox\Skeleton;

class Dumper extends Skeleton
{
    /**
     * @param mixed ...$strings
     */
    public static function dump(... $strings): void
    {
        if (!isset($_SESSION['DUMPS_COUNT'])) {
            $_SESSION['DUMPS_COUNT'] = 0;
        } else {
            $_SESSION['DUMPS_COUNT']++;
        }
        if ($_SESSION['APP_DEBUG']) {
            $d = new self();
            $dumps = $d->getData($strings);
            $debugTrace = $d->getMethod();
            $d->setDumpResult(array(
                'file' => $debugTrace[2]['file'],
                'function' => $debugTrace[3]['function'] === Controller::CONSTRUCTOR ? Controller::PHP_CONSTRUCTOR : $debugTrace[3]['function'],
                'line' => $debugTrace[2]['line'],
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
        $format = null;
        if (!empty($_SESSION['URI_FORMAT'])) {
            $format = $_SESSION['URI_FORMAT'];
        } else {
            if (!empty($_SERVER['APP']['FORMAT'])) {
                $format = $_SERVER['APP']['FORMAT'];
            } else {
                $format = $_SESSION['APP_FORMAT'];
            }
        }
        exit((new Response())->renderView($format, new Render(), null));
    }

    /**
     * @param array $dump
     */
    public function setDumpResult(array $dump): void
    {
        $_SESSION['DUMPS'][$_SESSION['DUMPS_COUNT']] = $dump;
    }

    /**
     * @return array|null
     */
    protected function getMethod(): ?array
    {
        return debug_backtrace();
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
