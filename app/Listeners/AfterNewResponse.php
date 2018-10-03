<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Auth;
use DevDojo\Chatter\Events\ChatterAfterNewResponse;

class AfterNewResponse
{
    public function handle(ChatterAfterNewResponse $after)
    {
        throw new \App\Exceptions\AfterForumResponse();
    }
}
