<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LinkedDataController extends ApiController
{
  
    public function getLinkedData(Request $request)
    { 
        return $this->successResponse();
    }
}
