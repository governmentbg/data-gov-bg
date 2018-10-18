<div class="org m-t-lg side-info col-xs-12 p-r-none">
    <img src="{{ $group->logo }}">
    <h3>{{ $group->name }}</h3>
    @if (isset($group->descript))
        <h5>{!! nl2br(truncate(e($group->descript), 150)) !!}</h5>
    @elseif (isset($group->description))
        <h5>{!! nl2br(truncate(e($group->description), 150)) !!}</h5>
    @endif
    <p class="text-right show-more">
        @if (\Auth::user()->is_admin)
            <a href="{{ url('/admin/groups/view/'. $group->uri) }}" class="view-profile text-right">{{ __('custom.see_more') }}</a>
        @else
            <a href="{{ url('/user/groups/view/'. $group->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
        @endif
    </p>
</div>
