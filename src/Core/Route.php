<?php

namespace Bedrox\Core;

class Route
{
    /** @var string $name */
    public $name;

    /** @var string $url */
    public $url;

    /** @var string $controller */
    public $controller;

    /** @var string $function */
    public $function;

    /** @var Entity $params */
    public $params;

    /** @var int $paramsCount */
    public $paramsCount;

    /** @var string $render */
    public $render;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Route
     */
    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Route
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     * @return Route
     */
    public function setController(string $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFunction(): ?string
    {
        return $this->function;
    }

    /**
     * @param string $function
     * @return Route
     */
    public function setFunction(string $function): self
    {
        $this->function = $function;
        return $this;
    }

    /**
     * @return Entity|null
     */
    public function getParams(): ?Entity
    {
        return $this->params;
    }

    /**
     * @param Entity $params
     * @return Route
     */
    public function setParams(Entity $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return int
     */
    public function getParamsCount(): int
    {
        return $this->paramsCount;
    }

    /**
     * @param int $paramsCount
     * @return Route
     */
    public function setParamsCount(int $paramsCount): self
    {
        $this->paramsCount = $paramsCount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRender(): ?string
    {
        return $this->render;
    }

    /**
     * @param string $render
     * @return Route
     */
    public function setRender(string $render): self
    {
        $this->render = $render;
        return $this;
    }
}