<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ListsController
{
    protected $listsService;

    public function __construct($service)
    {
        $this->listsService = $service;
    }

    public function getAll()
    {
        return new JsonResponse($this->listsService->getAll());
    }

    public function save(Request $request)
    {
        $list = $this->getDataFromRequest($request);

        return new JsonResponse(['id' => $this->listsService->save($list)]);
    }

    public function update($id, Request $request)
    {
        $list = $this->getDataFromRequest($request);
        $this->listsService->update($id, $list);

        return new JsonResponse($list);
    }

    public function delete($id)
    {
        return new JsonResponse($this->listsService->delete($id));
    }

    public function getDataFromRequest(Request $request)
    {
        return $list = [
            'list' => $request->request->get('list'),
        ];
    }
}
