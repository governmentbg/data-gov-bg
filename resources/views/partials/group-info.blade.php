<div class="org m-t-lg">
    <img src="{{ $group->logo }}">
    <h2>{{ $group->name }}</h2>
    @if (isset($group->descript))
        <h4>{!! nl2br(truncate(e($group->descript), 150)) !!}</h4>
    @elseif (isset($group->description))
        <h4>{!! nl2br(truncate(e($group->description), 150)) !!}</h4>
    @endif
    <p class="text-right show-more">
        <a href="{{ url('/user/groups/view/'. $group->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
    </p>
</div>
