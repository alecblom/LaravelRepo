<?php

namespace ab\LaravelRepo;

use ab\LaravelRepo\Contracts\BaseRepositoryInterface;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /** 
     * Dynamic model object, used as query builder, should reset on execute
     * @var Model
     */
    protected $query;

    /** 
     * Extends abstract constructor to set query
     * @var Model
     */
    public function __construct(Model $model = null)
    {
        parent::__construct($model);
        $this->reset();
    }

    /**
     * The name of a class extending Illuminate\Database\Eloquent\Model (return Model::class)
     * @return string
     */
    abstract static function model(): string;
    // abstract static function validate(array $attributes): bool;

    /**
     * Reset the query using the empty model instance
     */
    final protected function reset(): void
    {
        $this->query = $this->getModelInstance();
    }

    /**
     * 
     * @param array $attributes
     *
     * @return Model
     */
    final public function create(array $attributes): Model
    {
        // $this->validate($attributes);
        return $this::model()::create($attributes);
    }

    /**
     * @param string $id
     * @param array $columns
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    final public function read(string $id, array $columns = ['*']): Model
    {
        return $this::model()::findOrFail($id, $columns);
    }

    /**
     * @param string $id
     * @param array $attributes
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    final public function update(string $id, array $attributes): Model
    {
        ($model = $this->read($id))->update($attributes);
        return $model;
    }

    /**
     * @param ...$id
     * 
     * @return bool
     */
    final public function delete(string ...$id): bool
    {
        return $this::model()::destroy($id);
    }

    /**
     * @return Collection
     */
    public function all(): Collection
    {
        $result = $this->query->get();

        $this->reset();

        return $result;
    }

    /**
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     * @return Collection
     */
    public function first(): ?Model
    {
        $result = $this->query->firstOrFail();

        $this->reset();

        return $result;
    }

    /**
     * @param array $relations
     *
     * @return self
     */
    public function withRelations(string ...$relations): self
    {
        $this->query = $this->query->with($relations);
        return $this;
    }

    /**
     * @param array $relations
     *
     * @return self
     */
    public function hasRelation(string $relation): self
    {
        $this->query = $this->query->whereHas($relation);
        return $this;
    }

    /**
     * @param array $relations
     *
     * @return self
     */
    public function withFilters(array ...$conditions): self
    {
        $this->query = $this->query->where($conditions);
        return $this;
    }

    /**
     * @param array $relations
     *
     * @return self
     */
    public function withRelationFilters(string $relation, array $conditions): self
    {
        $this->query = $this->query->whereHas(
            $relation,
            function ($query) use ($conditions) {
                return $query->where($conditions);
            }
        );

        return $this;
    }

    /**
     * @param string $column
     * @param array $values
     *
     * @return self
     */
    public function withInFilters(string $column, array $values): self
    {
        $this->query = $this->query->whereIn($column, $values);
        return $this;
    }

    /**
     * @param string $relation
     * @param string $column
     * @param array $conditions
     *
     * @return self
     */
    public function withInRelationFilters(string $relation, string $column, array $conditions): self
    {
        $this->query = $this->query->whereHas($relation, function ($query) use ($column, $conditions) {
            return $query->whereIn($column, $conditions);
        });

        return $this;
    }

    /**
     * Add a scope to the current query
     * 
     * @param string $scope
     *
     * @return self
     */
    public function withScope(string $scope): self
    {
        if (method_exists($this->model(), 'scope' . ucfirst($scope))) {
            $this->query = $this->query->{$scope}();
        }
        return $this;
    }

    public function withLimitAndOffset(int $limit = 250, int $offset = 0): self
    {
        $this->query = $this->query->limit($limit)->offset($offset);
        return $this;
    }

    /**
     * @param int $page
     * @param int $limit
     * 
     * @return array
     */
    public function paginate(int $page = 1, int $limit = 10): array
    {
        $paginator = $this->model->paginate($limit, ['*'], class_basename($this->model()), $page);

        $result = [
            'items' => $paginator->items(),
            'pagination' => [
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage()
            ]
        ];

        $this->reset();

        return $result;
    }
}
