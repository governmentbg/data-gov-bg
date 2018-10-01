<div class="org m-t-lg side-info">
    <img src="{{ $group->logo }}">
    <h2>{{ $group->name }}</h2>
    @if (isset($group->descript))
        <h4>{!! nl2br(truncate(e($group->descript), 150)) !!}</h4>
    @elseif (isset($group->description))
        <h4>{!! nl2br(truncate(e($group->description), 150)) !!}</h4>
    @endif
    <p class="text-right show-more">
        @if (\Auth::user()->is_admin)
            <a href="{{ url('/admin/groups/view/'. $group->uri) }}" class="view-profile text-right">{{ __('custom.see_more') }}</a>
        @else
            <a href="{{ url('/user/groups/view/'. $group->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
        @endif
    </p>
</div>
