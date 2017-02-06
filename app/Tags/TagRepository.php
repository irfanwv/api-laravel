<?php

namespace App\Tags;

use Dingo\Api\Exception\ResourceException;

class TagRepository
{
    protected $model;

    public function __construct (Tag $tag)
    {
        $this->model = $tag;
    }

    public function find ($id)
    {
    	return $this->model->findOrFail($id);
    }

    public function findByName ($name)
    {
        return $this->model->where('name', $name)->first();
    }

    public function search ($include = [], $filter = [])
    {
    	return $this->model
            ->search($include, $filter)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function create ($params)
    {
        return $this->model->fill($params);
    }

    public function save (Tag $tag)
    {
        return $tag->save();
    }

    public function delete (Tag $tag)
    {
        return $tag->delete();
    }

    public function listCities ()
    {
        return $this->model
            ->cities()
            ->orderBy('name', 'asc')
            ->get();
    }

    public function disableCity (Tag $city)
    {
        if ($city->isChild() || $city->type != 'App\Studios\Studio') {
            throw new ResourceException ('This method only supports top level city tags.');
        }

        if ($city->hasActivePassports()) {
            throw new ResourceException ('This area still has ' . $city->activePassportCount() . ' active passports.');
        }
        
        $city->children
            ->each (function ($area)
            {
                $area->studios()->delete();
                $area->delete();
            });

        return $city->delete();
    }
}
