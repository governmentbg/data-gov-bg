<?php

namespace App\Http\Controllers;

use App\Organisation;
use App\ActionsHistory;
use App\DataSet;
use App\Module;
use App\Page;

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

            $history = Dataset::select(
                'actions_history.id AS ahId',
                'actions_history.occurrence',
                'actions_history.user_id',
                'actions_history.module_name',
                'actions_history.action',
                'actions_history.action_object',
                'actions_history.action_msg',
                'data_sets.id',
                'data_sets.org_id',
                'data_sets.uri',
                'data_sets.name',
                'data_sets.descript',
                'data_sets.visibility',
                'data_sets.status',
                'data_sets.deleted_at'
            )
                ->leftJoin('actions_history', 'actions_history.action_object', '=', 'data_sets.id')
                ->withTrashed()
                ->where('data_sets.org_id', $organisation->id)
                ->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC)
                ->where('data_sets.status', DataSet::STATUS_PUBLISHED)
                ->where('actions_history.action', '!=', ActionsHistory::TYPE_SEE)
                ->where('actions_history.module_name', Module::getModuleName(Module::DATA_SETS))
                ->orderBy('occurrence', 'desc')
                ->limit(1000)
                ->get();

            return response()
                ->view('feeds/orgDatasetFeed', compact('history', 'organisation'))
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
        $history = Dataset::select(
            'actions_history.id AS ahId',
            'actions_history.occurrence',
            'actions_history.user_id',
            'actions_history.module_name',
            'actions_history.action',
            'actions_history.action_object',
            'actions_history.action_msg',
            'data_sets.id',
            'data_sets.uri',
            'data_sets.name',
            'data_sets.descript',
            'data_sets.visibility',
            'data_sets.status',
            'data_sets.deleted_at'
        )
            ->leftJoin('actions_history', 'actions_history.action_object', '=', 'data_sets.id')
            ->withTrashed()
            ->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC)
            ->where('data_sets.status', DataSet::STATUS_PUBLISHED)
            ->where('actions_history.action', '!=', ActionsHistory::TYPE_SEE)
            ->where('actions_history.module_name', Module::getModuleName(Module::DATA_SETS))
            ->orderBy('occurrence', 'desc')
            ->limit(1000)
            ->get();

        if ($history) {
            return response()
                ->view('feeds/datasetFeed', compact('history'))
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
            ->where('type', Page::TYPE_NEWS)
            ->where(function($a) {
                $a->where('valid_from', null)
                    ->where('valid_to', null);
            })->orWhere(function($b) {
                $b->where('valid_from', null)
                    ->where('valid_to', '>=', date(now()));
            })->orWhere(function($c) {
                $c->where('valid_from', '<=', date(now()))
                    ->where('valid_to', null);
            })->orWhere(function ($d) {
                $d->where('valid_from', '<=', date(now()))
                    ->where('valid_to', '>=', date(now()));
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
