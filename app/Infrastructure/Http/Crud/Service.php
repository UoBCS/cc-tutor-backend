<?php

namespace App\Infrastructure\Http\Crud;

use App\Infrastructure\Exceptions as ResourceException;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;
//use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception as SymfonyException;

/**
 * Base service class for CRUD business logic
 */
abstract class Service
{
    /**
     * CRUD events array
     *
     * @var array
     */
    protected $events;

    /**
     * Exceptions array
     *
     * @var array
     */
    protected $exceptions;

    /**
     * Repository that handles the database operations
     *
     * @var \App\Infrastructure\Http\Crud
     */
    protected $repository;

    /**
     * Get all resources
     *
     * @param  array  $options See Bruno package
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll($options = [])
    {
        try {
            return $this->repository->get($options);
        } catch (RelationNotFoundException $e) {
            throw new SymfonyException\UnprocessableEntityHttpException('The `includes` array contains invalid relationships.');
        }
    }

    /**
     * Get one or many resources given a field and its corresponding value
     *
     * @param  string  $column
     * @param  mixed   $value
     * @param  boolean $unique
     * @param  array   $options
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     */
    public function getBy($column, $value, $unique = false, $options = [])
    {
        $resource = $this->repository->getBy($column, $value, $unique, $options);

        if (is_null($resource)) {
            throw resolve($this->exceptions['resourceNotFound']);
        }

        return $resource;
    }

    /**
     * Get a resource by its ID
     *
     * @param  string|integer $id
     * @param  array          $options
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getById($id, $options = [])
    {
        return $this->getRequestedResource($id, $options);
    }

    public function create($data)
    {
        $this->repository->beginTransaction();

        try {
            $resource = $this->repository->create($data);
        } catch (Exception $e) {
            $this->repository->rollbackTransaction();
            throw resolve($this->exceptions['resourceAlreadyExists']);
        }

        event(app()->makeWith($this->events['resourceWasCreated'], ['resource' => $resource, 'data' => $data]));

        return $resource;
    }

    /**
     * Update a resource
     *
     * @param  string|integer $id
     * @param  array          $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, $data)
    {
        $this->repository->beginTransaction();

        $resource = $this->getRequestedResource($id);

        $this->repository->update($resource, $data);

        event(app()->makeWith($this->events['resourceWasUpdated'], ['resource' => $resource, 'data' => $data]));

        return $resource;
    }

    /**
     * Delete a resource
     *
     * @param  string|integer $id
     * @return void
     */
    public function delete($id)
    {
        $this->repository->beginTransaction();

        $resource = $this->getRequestedResource($id);

        if (isset($this->events['resourceWillBeDeleted'])) {
            event(app()->makeWith($this->events['resourceWillBeDeleted'], ['resource' => $resource, 'data' => null]));
        }

        $this->repository->delete($id);

        event(app()->makeWith($this->events['resourceWasDeleted'], ['resource' => $resource, 'data' => null]));
    }

    /**
     * Get a resource
     *
     * @param  string|integer $id
     * @param  array  $options
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getRequestedResource($id, $options = [])
    {
        try {
            $resource = $this->repository->getById($id, $options);
        } catch (RelationNotFoundException $e) {
            throw new SymfonyException\UnprocessableEntityHttpException('The `includes` array contains invalid relationships.');
        }

        if (is_null($resource)) {
            throw resolve($this->exceptions['resourceNotFound']);
        }

        return $resource;
    }
}
