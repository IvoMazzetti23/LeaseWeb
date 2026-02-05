# Servers API

This is a REST API for filtering server data from an Excel file, built with PHP.

## Prerequisites

- **Docker** (recommended)
- Or **PHP 8.2+** and **Composer** installed locally

## Installation & Setup

### Using Docker (Recommended)

1. **Build and start the container:**
    ```bash
    docker-compose up -d --build
    ```

### Local Setup

1.  **Install dependencies:**
    ```bash
    composer install
2.  **Start the development server:**
    ```bash
    php -S 0.0.0.0:8000 -t public
    ```

## Usage

### UI Interface Tool
  `http://localhost:8000/servers.html`

### Endpoints

#### GET `/api/servers`

Get a list of servers, optionally filtered.

**Query Parameters:**

| Parameter   | Type   | Description                                 |
| :---------- | :----- | :------------------------------------------ |
| `ram_min`   | `int`  | Minimum RAM in GB (e.g., `16`, `64`).       |
| `location`  | `string`| Location substring (e.g., `Amsterdam`).    |
| `price_max` | `float`| Maximum price (e.g., `500.00`).             |

**Example Requests:**

- **Get all servers:**
  `GET http://localhost:8000/api/servers`

- **Filter by RAM (>= 64GB):**
  `GET http://localhost:8000/api/servers?ram_min=16`

- **Filter by Location (Amsterdam) and Price (< 500):**
  `GET http://localhost:8000/api/servers?location=Amsterdam&price_max=100`

## Testing

To run the automated tests:

```bash
vendor/bin/phpunit tests
```
