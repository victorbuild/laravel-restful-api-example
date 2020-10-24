<?php

namespace App\Services;

use App\Models\Animal;

class AnimalService
{
    protected function filterAnimals($query, $filters)
    {
        // 篩選欄位條件
        if (isset($filters)) {
            $filtersArray = explode(',', $filters);
            foreach ($filtersArray as $key => $filter) {
                list($key, $value) = explode(':', $filter);
                $query->where($key, 'like', "%$value%");
            }
        }
        return $query;
    }

    protected function sortAnimals($query, $sorts)
    {
        // 排列順序
        if (isset($sorts)) {
            $sortArray = explode(',', $sorts);
            foreach ($sortArray as $key => $sort) {
                list($key, $value) = explode(':', $sort);
                if ($value == 'asc' || $value == 'desc') {
                    $query->orderBy($key, $value);
                }
            }
        } else {
            $query->orderBy('id', 'desc');
        }
        return $query;
    }

    public function getListData($request)
    {
        $limit = $request->limit ?? 10;

        $query = Animal::query()->with('type');

        $query = $this->filterAnimals($query, $request->filters);
        $query = $this->sortAnimals($query, $request->sorts);

        $animals = $query->paginate($limit)->appends($request->query());

        // 返回查詢結果
        return $animals;
    }
}
