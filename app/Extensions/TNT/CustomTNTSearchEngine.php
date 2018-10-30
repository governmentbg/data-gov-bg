<?php

namespace App\Extensions\TNT;

use Laravel\Scout\Builder;
use TeamTNT\TNTSearch\TNTSearch;
use Laravel\Scout\Engines\Engine;
use App\Extensions\TNT\CustomTNTSearch;
use TeamTNT\Scout\Engines\TNTSearchEngine;
use Illuminate\Database\Eloquent\Collection;
use TeamTNT\TNTSearch\Exceptions\IndexNotFoundException;

class CustomTNTSearchEngine extends TNTSearchEngine
{
    /**
     * Update the given model in the index.
     *
     * @param Collection $models
     *
     * @return void
     */
    public function update($models)
    {
        $this->initIndex($models->first());
        $indexName = $models->first()->searchableAs();
        $this->tnt->selectIndex($indexName);
        $index = $this->tnt->getIndex();
        $index->setPrimaryKey($models->first()->getKeyName());

        $index->indexBeginTransaction();

        $models->each(function ($model) use ($index, $indexName) {
            $array = $model->toSearchableArray();

            if (empty($array)) {
                return;
            }

            if ($model->getKey()) {
                $index->update($model->getKey(), $array, $indexName);
            } else {
                $index->insert($array, $indexName);
            }
        });

        $index->indexEndTransaction();
    }

    /**
     * Remove the given model from the index.
     *
     * @param Collection $models
     *
     * @return void
     */
    public function delete($models)
    {
        $this->initIndex($models->first());

        $models->each(function ($model) {
            $indexName = $model->searchableAs();
            $this->tnt->selectIndex($indexName);
            $index = $this->tnt->getIndex();
            $index->setPrimaryKey($model->getKeyName());
            $index->delete($model->getKey(), $indexName);
        });
    }

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
        $this->tnt->selectIndex($index);

        $this->builder = $builder;

        if (isset($builder->model->asYouType)) {
            $this->tnt->asYouType = $builder->model->asYouType;
        }

        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $this->tnt,
                $builder->query,
                $options
            );
        }

        if (isset($this->tnt->config['searchBoolean']) ? $this->tnt->config['searchBoolean'] : false) {
            return $this->tnt->searchBoolean($builder->query, $limit);
        } else {
            return $this->tnt->search($builder->query, $limit);
        }
    }

    public function initIndex($model)
    {
        $indexName = $model->searchableAs();
        $db = $model->getConnection()->getPdo();
        $result = $db->query("SELECT * FROM information_schema.tables
            WHERE table_schema = '". config('app.TNT_DATABASE') ."'
            AND table_name = '". CustomTNTSearch::getTNTName($indexName) ."'
            LIMIT 1;
        ");

        if (empty($result->fetchAll())) {
            $indexer = $this->tnt->createIndex($indexName);
            $indexer->setDatabaseHandle($model->getConnection()->getPdo());
            $indexer->setPrimaryKey($model->getKeyName());
        }
    }
}
