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
    public function getOrganisationDatasetHistory($orgId)
    {
        $organisation = Organisation::where('id', $orgId)->first();
        if ($organisation) {

            $datasetsList = Dataset::where('org_id', $organisation->id)
                ->where('visibility', DataSet::VISIBILITY_PUBLIC)
                ->where('status', DataSet::STATUS_PUBLISHED)
                ->get();

            $history = [];

            foreach ($organisation->dataSet as $dataset){
                $datasetIds[] = $dataset->id;
            }

            if (!empty($datasetIds)) {
                $history = ActionsHistory::where('module_name', 'Dataset')
                    ->whereIn('action_object', $datasetIds)
                    ->where('action', '!=', 1)->get();
            }

            return response()
                ->view('feeds.orgDatasetFeed', compact('history', 'datasetsList', 'organisation'))
                ->header('Content-Type', 'text/xml');
        }
    }
}
