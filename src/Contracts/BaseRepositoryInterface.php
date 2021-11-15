<?php

namespace ab\LaraveRepo\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    // CRUD
    public function create(array $attributes): Model;
    public function read(string $id, array $columns = ['*']): Model;
    public function update(string $id, array $attributes): Model;
    public function delete(string ...$id): bool;

    // Query execution
    public function all(): Collection;
    public function paginate(int $page, int $perPage): array;

    // Query modification
    public function withRelations(string ...$relations): self;
    public function hasRelation(string $relation): self;
    public function withFilters(array ...$conditions): self;
    public function withRelationFilters(string $relation, array $conditions): self;
    public function withInFilters(string $column, array $conditions): self;
    public function withInRelationFilters(string $relation, string $column, array $conditions): self;
    public function withScope(string $scope): self;
    public function withLimitAndOffset(int $limit = 250, int $offset = 0): self;
}
