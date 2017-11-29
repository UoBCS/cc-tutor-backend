<?php

namespace App\Infrastructure\Http\Crud;

use App\Infrastructure\Database\Eloquent\Repository as BaseRepository;

/**
 * Base repository class for CRUD database operations
 */
abstract class Repository extends BaseRepository
{
    /**
     * Create a resource
     *
     * @param  array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($data)
    {
        $resource = $this->getModel();

        $resource->fill($data);

        $resource->save();

        return $resource;
    }

    /**
     * Update a resource
     *
     * @param  \Illuminate\Database\Eloquent\Model $resource
     * @param  array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($resource, $data)
    {
        $resource->fill($data);

        $resource->save();

        return $resource;
    }
}
