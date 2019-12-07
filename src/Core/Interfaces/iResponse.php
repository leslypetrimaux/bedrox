<?php

namespace Bedrox\Core\Interfaces;

use Bedrox\Core\Render;
use Bedrox\Core\Response;

interface iResponse
{
    /**
     * @param string $format
     * @param Render $data
     * @param array|null $error
     * @return null|string
     */
    public function renderView(string $format, Render $data, ?array $error): ?string;

    /**
     * @param Render $data
     * @param array|null $error
     * @return string|null
     */
    public function renderJSON(Render $data, ?array $error): ?string;

    /**
     * @param Render $data
     * @param array|null $error
     * @return string|null
     */
    public function renderXML(Render $data, ?array $error): ?string;

    /**
     * @param Render $data
     * @param array|null $error
     * @return string|null
     */
    public function renderCSV(Render $data, ?array $error): ?string;

    /**
     * @param Render $render
     * @param array|null $error
     * @return array|null
     */
    public function renderResult(Render $render, ?array $error): ?array;

    /**
     * void
     */
    public function clear(): void;

    /**
     * @param Response $response
     */
    public function terminate(Response $response): void;
}
