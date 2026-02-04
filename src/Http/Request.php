<?php

namespace App\Http;

class Request
{
    public function __construct(
        private string $method,
        private string $uri,
        private array $query
    ) {}

    public static function fromGlobals(): self
    {
        return new self(
            $_SERVER['REQUEST_METHOD'],
            strtok($_SERVER['REQUEST_URI'], '?'),
            $_GET
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(?string $key = null)
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? null;
    }
}
