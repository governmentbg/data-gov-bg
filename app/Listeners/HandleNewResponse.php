<?php

namespace App\Listeners;

use DevDojo\Chatter\Events\ChatterBeforeNewResponse;
use Illuminate\Support\Facades\Auth;

class HandleNewResponse
{
    public function handle(ChatterBeforeNewResponse $event)
    {
        if (!Auth::user()) {
            throw new \App\Exceptions\ForumResponse(__('custom.create_response_error'));
        }
    }
}
