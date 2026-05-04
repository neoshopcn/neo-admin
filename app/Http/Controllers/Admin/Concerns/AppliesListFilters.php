<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AppliesListFilters
{
    protected function applyKeyword(Builder $query, Request $request, array $columns): void
    {
        $kw = trim((string) $request->input('keyword', ''));
        if ($kw === '') {
            return;
        }

        $query->where(function (Builder $q) use ($kw, $columns) {
            foreach ($columns as $col) {
                $q->orWhere($col, 'like', '%'.$kw.'%');
            }
        });
    }

    protected function applyExact(Builder $query, Request $request, string $param, string $column): void
    {
        if ($request->filled($param)) {
            $query->where($column, $request->input($param));
        }
    }

    protected function applyDateRange(Builder $query, Request $request, string $fromKey, string $toKey, string $column): void
    {
        if ($request->filled($fromKey)) {
            $query->whereDate($column, '>=', $request->input($fromKey));
        }
        if ($request->filled($toKey)) {
            $query->whereDate($column, '<=', $request->input($toKey));
        }
    }
}
