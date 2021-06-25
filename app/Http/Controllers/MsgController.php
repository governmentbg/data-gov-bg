<?php


namespace App\Http\Controllers;

class MsgController extends Controller
{

  public function display()
  {
    $locale = \LaravelLocalization::getCurrentLocale();
    $alertNewsCache = \DB::select("SELECT p.active, t.label as msg FROM `pages` p,`translations` t 
                                    Where p.title = t.group_id and p.home_page = 2 and p.active = 1 and p.type = 1 and t.locale = '$locale' limit 1");

    $response['msg'] = (!empty($alertNewsCache)) ? mb_substr($alertNewsCache[0]->msg, 0, 2500, "utf-8") : "";

    return $response;
  }
}