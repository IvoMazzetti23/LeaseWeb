<?php

namespace App\Http;

class Response
{
    public function __construct(
        private mixed $data,
        private int $status = 200
    ) {}

    public static function json(mixed $data, int $status = 200): self
    {
        return new self($data, $status);
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->data);
    }
}
