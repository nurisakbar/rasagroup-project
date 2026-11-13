<?php

namespace App\Support\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Pencarian per kolom dari ekstensi DataTables ColumnControl (mode server-side).
 *
 * ColumnControl mengirim `columns[i][columnControl][search]` = {value, logic, type}. Kolom dipetakan lewat
 * `data` DataTables ke kolom tabel (string) atau ke relasi (array relasi => kolom; beberapa relasi = OR).
 *
 * Contoh peta: ['name' => 'name', 'hub' => ['hub' => 'name'], 'sales_name' => ['salesPerson' => 'name', 'referringDistributor' => 'name']]
 *
 * Setelah diterapkan, parameter columnControl dihapus dari request agar dukungan bawaan Yajra (yang belum benar
 * untuk kolom relasi/filterColumn, mis. "Kosong" dan "Tidak mengandung") tidak memfilter ulang.
 */
class ColumnControlFilter
{
    private const TEXT_LOGICS = ['contains', 'notContains', 'equal', 'notEqual', 'starts', 'ends', 'empty', 'notEmpty'];

    /**
     * @param array<string, string|array<string, string>> $map
     */
    public static function apply(Builder $query, Request $request, array $map): Builder
    {
        foreach ((array) $request->input('columns', []) as $column) {
            $key = $column['data'] ?? null;
            $search = $column['columnControl']['search'] ?? null;
            if (! is_string($key) || ! isset($map[$key]) || ! is_array($search)) {
                continue;
            }

            $logic = (string) ($search['logic'] ?? 'contains');
            $value = trim((string) ($search['value'] ?? ''));
            if (! in_array($logic, self::TEXT_LOGICS, true)) {
                continue;
            }
            if ($value === '' && ! in_array($logic, ['empty', 'notEmpty'], true)) {
                continue;
            }

            $target = $map[$key];
            is_array($target)
                ? self::applyRelations($query, $target, $logic, $value)
                : self::applyColumn($query, $target, $logic, $value);
        }

        self::consume($request);

        return $query;
    }

    /** Hapus parameter columnControl dari request (dan request global yang dibaca Yajra). */
    private static function consume(Request $request): void
    {
        $targets = request() === $request ? [$request] : [$request, request()];
        foreach ($targets as $target) {
            $columns = $target->input('columns');
            if (! is_array($columns)) {
                continue;
            }
            foreach ($columns as $i => $column) {
                if (is_array($column)) {
                    unset($columns[$i]['columnControl']);
                }
            }
            $target->merge(['columns' => $columns]);
            if ($target->query->has('columns')) {
                $target->query->set('columns', $columns);
            }
        }
    }

    private static function applyColumn(Builder $query, string $column, string $logic, string $value): void
    {
        $column = $query->qualifyColumn($column);

        match ($logic) {
            'empty' => $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, '')),
            'notEmpty' => $query->whereNotNull($column)->where($column, '!=', ''),
            'notContains' => $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, 'not like', self::pattern('notContains', $value))),
            'notEqual' => $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, '!=', $value)),
            'equal' => $query->where($column, $value),
            default => $query->where($column, 'like', self::pattern($logic, $value)),
        };
    }

    /**
     * @param array<string, string> $relations
     */
    private static function applyRelations(Builder $query, array $relations, string $logic, string $value): void
    {
        // Kondisi positif: salah satu relasi cocok. Kondisi negatif: tidak ada relasi yang cocok.
        $negative = in_array($logic, ['notContains', 'notEqual', 'empty'], true);
        $positiveLogic = match ($logic) {
            'notContains' => 'contains',
            'notEqual' => 'equal',
            'empty' => 'notEmpty',
            default => $logic,
        };

        $matches = function (Builder $q, string $column) use ($positiveLogic, $value) {
            self::applyColumn($q, $column, $positiveLogic, $value);
        };

        if ($negative) {
            foreach ($relations as $relation => $column) {
                $query->whereDoesntHave($relation, fn ($q) => $matches($q, $column));
            }

            return;
        }

        $query->where(function ($outer) use ($relations, $matches) {
            foreach ($relations as $relation => $column) {
                $outer->orWhereHas($relation, fn ($q) => $matches($q, $column));
            }
        });
    }

    private static function pattern(string $logic, string $value): string
    {
        $escaped = addcslashes($value, '\\%_');

        return match ($logic) {
            'starts' => $escaped . '%',
            'ends' => '%' . $escaped,
            default => '%' . $escaped . '%',
        };
    }
}
