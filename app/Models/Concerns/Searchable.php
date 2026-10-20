<?php

namespace App\Models\Concerns;

trait Searchable
{
    public function scopeSearch($query, ?string $term, array $columns)
    {
        $term = trim((string) $term);

        if ($term === '' || $columns === []) {
            return $query;
        }


        $like = '%' . addcslashes($term, '%_\\') . '%';

        return $query->where(function ($group) use ($columns, $like) {
            foreach ($columns as $column) {
                $group->orWhere($column, 'like', $like);
            }
        });
    }
}
