<?php

namespace App\Http\Controllers;

use App\Organisation;
use App\ActionsHistory;
use App\DataSet;

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

            $history = Dataset::select('actions_history.id AS ahId', 'actions_history.*','data_sets.*')
                ->leftJoin('actions_history', 'actions_history.action_object', '=', 'data_sets.id')
                ->withTrashed()
                ->where('data_sets.org_id', $organisation->id)
                ->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC)
                ->where('data_sets.status', DataSet::STATUS_PUBLISHED)
                ->where('actions_history.action', '!=', 1)
                ->orderBy('data_sets.id', 'occurrence')
                ->limit('1000')
                ->get() ;

            return response()
                ->view('feeds/orgDatasetFeed', compact('history', 'organisation'))
                ->header('Content-Type', 'text/xml');
        }
    }
}
