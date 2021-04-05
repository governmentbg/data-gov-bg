<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\NewsController as ApiNews;
use Illuminate\Support\Facades\Cache;

class MsgController extends Controller
{

  public function display()
  {
    //cache()->forget('alertNews');
    $newsRequest = Request::create('/api/listNews', 'POST', [
      'records_per_page'  => 1,
      'criteria'         => [
        'active'   => true,
        'home_page'   => 2,
      ]
    ]);
    $apiNews = new ApiNews($newsRequest);
    $result = $apiNews->listNews($newsRequest)->getData();
    $alertNewsCache = $result->news;

    $response[0] = 0;
    if(!empty($alertNewsCache)) {
      $response[0] = 1;
      $response[] = $alertNewsCache[0];
    }
    //dd($response);
    return $response;
  }
}