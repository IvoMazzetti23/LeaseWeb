<?php

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Service\ServerService;
use App\Http\Requests\ServerFilterRequest;
use App\Repository\ServerRepository;

class ServerController
{
    private ServerService $service;

    public function __construct()
    {
        $this->service = new ServerService(new ServerRepository());
    }

    public function index(Request $request): Response
    {
        $filterRequest = new ServerFilterRequest($request);

        if ($filterRequest->hasErrors()) {
            return Response::json($filterRequest->getErrors(), 400);
        }

        $filters = $filterRequest->getFilters();
        $servers = $this->service->getServers($filters);

        return Response::json($servers);
    }
}
