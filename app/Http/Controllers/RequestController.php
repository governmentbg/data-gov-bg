<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\DataRequestController as ApiDataRequest;
use Illuminate\Support\Facades\Input;

class RequestController extends Controller {
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

    }

    public function sendDataRequest(Request $request)
    {
        if ($request->has('save')) {
            $recaptchaResp = $request->get('g-recaptcha-response');

            if (empty($recaptchaResp)) {
                Input::flash();
                return back()->with('alert-danger', __('custom.recaptcha_confirm_err'));
            } else {
                $recaptcha = new \ReCaptcha\ReCaptcha(config('app.CAPTCHA_SECRET'));
                $resp = $recaptcha->verify($recaptchaResp);

                if (!$resp->isSuccess()) {
                    Input::flash();
                    return back()->with('alert-danger', __('custom.recaptcha_wrong_resp'));
                }
            }

            $requestData['description'] = $request['description'];
            $requestData['published_url'] = isset($request['published_url']) ? $request['published_url'] : null;
            $requestData['contact_name'] = isset($request['contact_name']) ? $request['contact_name'] : null;
            if (!is_null($request['email'])) {
                $requestData['email'] = $request['email'];
            }
            $requestData['notes'] = isset($request['notes']) ? $request['notes'] : null;
            $requestData['org_id'] = $request['org_id'];

            $sendDataRequest = Request::create('/api/sendDataRequest', 'POST', ['data' => $requestData]);
            $apiDataRequest = new ApiDataRequest($sendDataRequest);
            $requestResponse = $apiDataRequest->sendDataRequest($sendDataRequest)->getData();

            if ($requestResponse->success) {
               return back()->with('alert-success', __('custom.success_data_request'));
            } else{
               return back()->with('alert-danger', __('custom.send_request_fail'));
            }
        }

        return view(
            'request/dataRequest',
            [
                'class'          => 'request'
            ]
        );
    }
}
