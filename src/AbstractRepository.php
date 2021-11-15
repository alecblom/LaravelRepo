<?php

namespace ab\LaravelRepo;

use Exception;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    /** 
     * Empty model instance, private because it should never be modified
     * @var Model
     */
    private $model;

    /** 
     * Repository constructor, resolves model using the abstract function or custom model if passed as param (mostly used for mocking tests) 
     * @param Model $model
     */
    public function __construct(Model $model = null)
    {
        $this->model = $model ?? $this->resolveModel();
    }

    /**
     * The name of the model class (return Model::class)
     * @return string
     */
    abstract static public function model(): string;

    /**
     * Empty model instance getter, use to reset or start a new query
     * @return Model
     */
    final protected function getModelInstance(): Model
    {
        return $this->model;
    }

    /**
     * Resolve the model class using the abstract function and make sure it's an instance of Illuminate\Database\Eloquent\Model
     * @return Model
     * @throws Exception
     */
    final private function resolveModel(): Model
    {
        $model = app()->make($this->model());

        if (!$model instanceof Model) {
            throw new Exception(
                "Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model"
            );
        }
        return $model;
    }
}
