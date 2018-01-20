<?php

namespace App\Infrastructure\Http\Crud;

use App\Infrastructure\Http\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Base controller for CRUD operations
 */
abstract class Controller extends BaseController
{
    /**
     * Validation rules for creating a resource
     *
     * @var array
     */
    protected $createRules;

    /**
     * Validation rules for updating a resource
     *
     * @var array
     */
    protected $updateRules;

    /**
     * Request key (for creating and updating)
     *
     * @var string
     */
    protected $key;

    /**
     * Service that handles business logic
     *
     * @var object
     */
    protected $service;

    /**
     * Get all resources
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->service->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions);

        return $this->response($parsedData);
    }

    /**
     * Get a resource by ID
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function getById($id)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->service->getById($id, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions);

        return $this->response($parsedData);
    }

    /**
     * Create a resource
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $data = $request->validate($this->createRules);
        } catch (ValidationException $e) {
            throw new UnprocessableEntityHttpException(json_encode($e->errors()));
        }

        $data = getOnly($this->getFields($this->createRules), $data[$this->key]);
        $data = $this->processCreateData($data);

        $resultData = $this->processCreationResult($this->service->create($data));

        return $this->response($resultData, 201);
    }

    /**
     * Update a resource
     *
     * @param  string  $id
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        try {
            $data = $request->validate($this->updateRules);
        } catch (ValidationException $e) {
            throw new UnprocessableEntityHttpException(json_encode($e->errors()));
        }

        $data = getOnly($this->getFields($this->updateRules), $data[$this->key]);
        $data = $this->processUpdateData($data);

        return $this->response($this->service->update($id, $data));
    }

    /**
     * Delete a resource
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $this->service->delete($id);
        return $this->response('', 204);
    }

    /**
     * Process data for creation before passing it to the service
     *
     * @param  array $data
     * @return array
     */
    protected function processCreateData($data)
    {
        return $data;
    }

    protected function processCreationResult($data)
    {
        return $data;
    }

    /**
     * Process data for update before passing it to the service
     *
     * @param  array $data
     * @return array
     */
    protected function processUpdateData($data)
    {
        return $data;
    }

    /**
     * Get fields from rules array
     *
     * @param  array $rules
     * @return array
     */
    protected function getFields($rules)
    {
        $rules = array_keys($rules);

        $rules = array_filter($rules, function ($rule) {
            return $rule !== $this->key;
        });

        return array_map(function ($rule) {
            $parts = explode('.', $rule);
            return isset($parts[1]) ? $parts[1] : $parts[0];
        }, $rules);
    }
}
