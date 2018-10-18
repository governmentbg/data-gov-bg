<div class="org m-t-lg side-info col-xs-12">
    <div class="org-logo"><img src="{{ $organisation->logo }}"></div>
    <h4>{{ $organisation->name }}</h4>
    @if (isset($organisation->descript))
        <h5>{!! nl2br(truncate(e($organisation->descript), 150)) !!}</h5>
    @elseif (isset($organisation->description))
        <h5>{!! nl2br(truncate(e($organisation->description), 150)) !!}</h5>
    @endif
    <p class="text-right show-more">
        @if (\Auth::user()->is_admin)
            <a href="{{ url('/admin/organisations/view/'. $organisation->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
        @else
            <a href="{{ url('/user/organisations/view/'. $organisation->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
        @endif
    </p>
</div>
