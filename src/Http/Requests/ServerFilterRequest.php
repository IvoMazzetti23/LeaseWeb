<?php

namespace App\Http\Requests;

use App\Http\Request;

class ServerFilterRequest
{
    private array $errors = [];

    public function __construct(private Request $request)
    {
    }

    public function getFilters(): array
    {
        $filters = [];

        $ram = $this->getMinRam();
        if ($ram !== null) {
            $filters['ram_min'] = $ram;
        }

        $location = $this->getLocation();
        if ($location !== null) {
            $filters['location'] = $location;
        }

        $price = $this->getMaxPrice();
        if ($price !== null) {
            $filters['price_max'] = $price;
        }

        return $filters;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMinRam(): ?int
    {
        $value = $this->request->query('ram_min');
        if ($value === null) {
            return null;
        }

        $valid = filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($valid === false) {
            $this->errors['ram_min'] = 'ram_min must be a positive integer.';
            return null;
        }

        return (int) $value;
    }

    public function getLocation(): ?array
    {
        $value = $this->request->query('location');
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = explode(',', $value);
            }
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $locations = [];
        foreach ($value as $loc) {
            $loc = trim(strip_tags((string) $loc));
            if ($loc !== '') {
                $locations[] = $loc;
            }
        }

        if (empty($locations)) {
            return null;
        }

        return $locations;
    }

    public function getMaxPrice(): ?float
    {
        $value = $this->request->query('price_max');
        if ($value === null) {
            return null;
        }

        $valid = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($valid === false || (float) $value <= 0) {
            $this->errors['price_max'] = 'price_max must be a positive number.';
            return null;
        }

        return (float) $value;
    }

    public function getCursor(): ?string
    {
        $value = $this->request->query('cursor');
        if ($value === null) {
            return null;
        }

        $value = trim(strip_tags((string) $value));
        if ($value === '') {
            return null;
        }

        return $value;
    }

    public function getLimit(int $default = 20): int
    {
        $value = $this->request->query('limit');
        if ($value === null) {
            return $default;
        }

        $valid = filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 100]]);
        if ($valid === false) {
            $this->errors['limit'] = 'limit must be a positive integer between 1 and 100.';
            return $default;
        }

        return (int) $value;
    }
}
