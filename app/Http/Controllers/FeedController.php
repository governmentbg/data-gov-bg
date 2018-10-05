<?php

namespace App\Http\Controllers;

use App\Organisation;
use App\ActionsHistory;
use App\DataSet;
use App\Module;

class FeedController extends Controller
{
    /**
     *getOrganisationDatasetHistory
     *
     * @param int $orgId
     *
     * @return xml view for rss feed
     * for the organisation's dataset history
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
                'data_sets.status')
                ->leftJoin('actions_history', 'actions_history.action_object', '=', 'data_sets.id')
                ->withTrashed()
                ->where('data_sets.org_id', $organisation->id)
                ->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC)
                ->where('data_sets.status', DataSet::STATUS_PUBLISHED)
                ->where('actions_history.action', '!=', ActionsHistory::TYPE_SEE)
                ->where('actions_history.module_name', Module::getModuleName(Module::DATA_SETS))
                ->orderBy('occurrence', 'desc')
                ->limit('1000')
                ->get();

            return response()
                ->view('feeds/orgDatasetFeed', compact('history', 'organisation'))
                ->header('Content-Type', 'text/xml');
        }
    }
}
