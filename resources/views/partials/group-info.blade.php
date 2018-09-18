<div class="org m-t-lg">
    <img src="{{ $group->logo }}">
    <h2>{{ $group->name }}</h2>
    <h4>{{ isset($group->descript) ? truncate($group->descript, 150) : truncate($group->description, 150) }}</h4>
    <p class="text-right show-more">
        <a href="{{ url('/user/groups/view/'. $group->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
    </p>
</div>
