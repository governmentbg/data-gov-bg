<?php


namespace App\Http\Controllers;

class MsgController extends Controller
{

  public function display()
  {
    $alertNewsCache = \DB::select('SELECT p.active, t.text as msg FROM `pages` p,`translations` t Where p.body = t.group_id and p.home_page = 2 and p.active = 1 and p.type = 1 and t.locale = "bg" limit 1');
    //dd(\DB::getQueryLog());
    $response[0] = 0;
    if(!empty($alertNewsCache)) {
      $response[0] = 1;
      $response['msg'] = substr(strip_tags($alertNewsCache[0]->msg), 0, 255);
    }
    //dd($response);
    return $response;
  }
}