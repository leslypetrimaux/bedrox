<?php

namespace Bedrox\Core\Interfaces;

use Bedrox\Core\Response;

interface iResponse
{
    /**
     * @param string $format
     * @param array $data
     * @param array $error
     * @return null|string
     */
    public function renderView(string $format, ?array $data, ?array $error): ?string;

    /**
     * @param array $data
     * @param array $error
     * @return string|null
     */
    public function renderJSON(?array $data, ?array $error): ?string;

    /**
     * @param array $data
     * @param array $error
     * @return string|null
     */
    public function renderXML(?array $data, ?array $error): ?string;

    /**
     * @param array|null $data
     * @param array|null $error
     * @return string|null
     */
    public function renderCSV(?array $data, ?array $error): ?string;

    /**
     * @param array $data
     * @param array $error
     * @return array|null
     */
    public function renderResult(?array $data, ?array $error): ?array;

    /**
     * void
     */
    public function clear(): void;

    /**
     * @param Response $response
     */
    public function terminate(Response $response): void;
}
