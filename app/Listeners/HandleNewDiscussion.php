<?php

namespace App\Listeners;

use DevDojo\Chatter\Events\ChatterBeforeNewDiscussion;
use Illuminate\Support\Facades\Auth;

class HandleNewDiscussion
{
    public function handle(ChatterBeforeNewDiscussion $event)
    {
        if (!Auth::user()->is_admin) {
            throw new \App\Exceptions\ForumDiscussion(__('custom.create_discussion_error'));
        }
    }
}
