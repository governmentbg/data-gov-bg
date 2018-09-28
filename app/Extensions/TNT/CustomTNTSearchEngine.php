<?php

namespace App\Extensions\TNT;

use Laravel\Scout\Builder;
use TeamTNT\TNTSearch\TNTSearch;
use Laravel\Scout\Engines\Engine;
use TeamTNT\Scout\Engines\TNTSearchEngine;
use Illuminate\Database\Eloquent\Collection;
use TeamTNT\TNTSearch\Exceptions\IndexNotFoundException;

class CustomTNTSearchEngine extends TNTSearchEngine
{
    /**
     * Perform the given search on the engine.
     *
     * @param Builder $builder
     *
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $index = $builder->index ?: $builder->model->searchableAs();
        $limit = $builder->limit ?: 10000;
        $this->tnt->selectIndex("{$index}.index");

        $this->builder = $builder;

        if (isset($builder->model->asYouType)) {
            $this->tnt->asYouType = $builder->model->asYouType;
        }

        $fuzzy = $builder->callback;

        if (isset($this->tnt->config['searchBoolean']) ? $this->tnt->config['searchBoolean'] : false) {
            return $this->tnt->searchBoolean($builder->query, $limit, $fuzzy);
        } else {
            return $this->tnt->search($builder->query, $limit, $fuzzy);
        }
    }
}
