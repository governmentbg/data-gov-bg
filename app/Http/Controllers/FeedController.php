<?php

namespace App\Http\Controllers;

use App\Organisation;
use App\ActionsHistory;
use App\DataSet;
use App\Module;
use App\Page;
use App\Translator\Translation;

class FeedController extends Controller
{
    /**
     * Gets a list of dataset history for a given organisation
     *
     * @param string $uri
     *
     * @return xml view for rss feed for the organisation's dataset history
     */
    public function getOrganisationDatasetHistory($uri)
    {
        $organisation = Organisation::where('uri', $uri)->first();

        if ($organisation) {
            $history = [];

            $locale = \LaravelLocalization::getCurrentLocale();
            $history = ActionsHistory::select(
                'actions_history.id AS ahId',
                'actions_history.occurrence',
                'actions_history.user_id',
                'actions_history.module_name',
                'actions_history.action',
                'actions_history.action_object',
                'actions_history.action_msg',
                'data_sets.id AS dataset_id',
                'data_sets.uri AS dataset_uri',
                'data_sets.name AS dataset_name',
                'data_sets.descript AS dataset_descript',
                'data_sets.visibility',
                'data_sets.status',
                'data_sets.deleted_at AS dataset_delete',
                'resources.id AS resource_id',
                'resources.uri AS resource_uri',
                'resources.name AS resource_name',
                'resources.descript AS resource_descript',
                'resources.data_set_id AS resource_dataset',
                'resources.deleted_at AS resource_delete'
            )
                ->leftJoin('data_sets', 'data_sets.id', '=', 'actions_history.action_object')
                ->leftJoin('resources', 'resources.uri', '=', 'actions_history.action_object')
                ->where('data_sets.org_id', $organisation->id)
                ->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC)
                ->where('data_sets.status', DataSet::STATUS_PUBLISHED)
                ->where('actions_history.action', '!=', ActionsHistory::TYPE_SEE)
                ->where('actions_history.module_name', Module::getModuleName(Module::DATA_SETS))
                ->orWhere('actions_history.module_name', Module::getModuleName(Module::RESOURCES))
                ->orderBy('occurrence', 'desc')
                ->limit(1000)
                ->get();

            $datasets = $history->pluck('dataset_id', 'dataset_id')->toArray();
            $moduleResource = Module::getModuleName(Module::RESOURCES);

            foreach ($history as $i => $row) {
                if ($row->module_name == $moduleResource && !in_array($row->resource_dataset, $datasets )) {
                    unset($history[$i]);
                    continue;
                }

                if (!empty($row->resource_name)) {
                    $translateGroups[$row->resource_name] = $row->resource_name;
                }

                if (!empty($row->resource_descript)) {
                    $translateGroups[$row->resource_descript] = $row->resource_descript;
                }

                if (!empty($row->dataset_name)) {
                    $translateGroups[$row->dataset_name] = $row->dataset_name;
                }

                if (!empty($row->dataset_descript)) {
                    $translateGroups[$row->dataset_descript] = $row->dataset_descript;
                }
            }

            $translationsCollection = Translation::select(
                'translations.group_id',
                'translations.text'
            )
                ->whereIn('translations.group_id', $translateGroups)
                ->where('translations.locale', $locale)
                ->get()
                ->toArray();

            foreach ($translationsCollection as $index => $data) {
                $translation[$data['group_id']] = !empty($data['text']) ? $data['text'] : '';
            }

            return response()
                ->view('feeds/orgDatasetFeed', compact('history', 'organisation', 'translation'))
                ->header('Content-Type', 'text/xml');
        }
    }

    /**
     * Returns an xml containing history of the datasets on the portal
     *
     * @return xml view for rss feed
     */
    public function getDatasetsHistory()
    {
        $locale = \LaravelLocalization::getCurrentLocale();
        $history = ActionsHistory::select(
            'actions_history.id AS ahId',
            'actions_history.occurrence',
            'actions_history.user_id',
            'actions_history.module_name',
            'actions_history.action',
            'actions_history.action_object',
            'actions_history.action_msg',
            'data_sets.id AS dataset_id',
            'data_sets.uri AS dataset_uri',
            'data_sets.name AS dataset_name',
            'data_sets.descript AS dataset_descript',
            'data_sets.visibility',
            'data_sets.status',
            'data_sets.deleted_at AS dataset_delete',
            'resources.id AS resource_id',
            'resources.uri AS resource_uri',
            'resources.name AS resource_name',
            'resources.descript AS resource_descript',
            'resources.deleted_at AS resource_delete'
        )
            ->leftJoin('data_sets', 'data_sets.id', '=', 'actions_history.action_object')
            ->leftJoin('resources', 'resources.uri', '=', 'actions_history.action_object')
            ->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC)
            ->where('data_sets.status', DataSet::STATUS_PUBLISHED)
            ->where('actions_history.action', '!=', ActionsHistory::TYPE_SEE)
            ->where('actions_history.module_name', Module::getModuleName(Module::DATA_SETS))
            ->orWhere('actions_history.module_name', Module::getModuleName(Module::RESOURCES))
            ->orderBy('occurrence', 'desc')
            ->limit(1000)
            ->get();

        foreach ($history as $row) {
            if (!empty($row->resource_name)) {
                $translateGroups[$row->resource_name] = $row->resource_name;
            }
            if (!empty($row->resource_descript)) {
                $translateGroups[$row->resource_descript] = $row->resource_descript;
            }
            if (!empty($row->dataset_name)) {
                $translateGroups[$row->dataset_name] = $row->dataset_name;
            }
            if (!empty($row->dataset_descript)) {
                $translateGroups[$row->dataset_descript] = $row->dataset_descript;
            }
        }

        $translationsCollection = Translation::select(
            'translations.group_id',
            'translations.text'
        )
            ->whereIn('translations.group_id', $translateGroups)
            ->where('translations.locale', $locale)
            ->get()
            ->toArray();

        foreach ($translationsCollection as $index => $data) {
            $translation[$data['group_id']] = !empty($data['text']) ? $data['text'] : '';
        }

        if ($history) {
            return response()
                ->view('feeds/datasetFeed', compact('history', 'translation'))
                ->header('Content-Type', 'text/xml');
        }
    }

    /**
     * Returns an xml containing active on the portal
     *
     * @return xml view for rss feed
     */
    public function getNewsHistory()
    {
        $history = Page::select(
            'id',
            'type',
            'title',
            'abstract',
            'active',
            'valid_from',
            'valid_to',
            'created_at'
        )
            ->where('active', '!=', Page::ACTIVE_FALSE)
            ->where('type', '=', Page::TYPE_NEWS)
            ->where(function($query) {
                $query->where(function($a) {
                    $a->where('valid_from', null)
                        ->where('valid_to', null);
                })
                ->orWhere(function($b) {
                    $b->where('valid_from', null)
                        ->where('valid_to', '>=', date(now()));
                })
                ->orWhere(function($c) {
                    $c->where('valid_from', '<=', date(now()))
                        ->where('valid_to', null);
                })
                ->orWhere(function ($d) {
                    $d->where('valid_from', '<=', date(now()))
                        ->where('valid_to', '>=', date(now()));
                });
            })
            ->limit(1000)
            ->get();

        if ($history) {
            return response()
                ->view('feeds/newsFeed', compact('history'))
                ->header('Content-Type', 'text/xml');
        }
    }
}
