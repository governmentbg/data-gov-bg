<?php


namespace App\Http\Controllers;

use App\Page;

class MsgController extends Controller
{

  public function display()
  {
    $locale = \LaravelLocalization::getCurrentLocale();
    $alertNewsCache = \DB::select("SELECT p.active, t.label as msg 
                                    FROM `pages` p,`translations` t 
                                    WHERE p.title = t.group_id AND p.news_type = ".Page::NEWS_TYPE_ALERT." AND p.active = 1 
                                            AND p.type = ".Page::TYPE_NEWS." AND t.locale = '$locale' 
                                    LIMIT 1");

    $response['msg'] = (!empty($alertNewsCache)) ? mb_substr($alertNewsCache[0]->msg, 0, 2500, "utf-8") : "";

    return $response;
  }
}