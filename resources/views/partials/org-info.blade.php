<div class="col-sm-3 col-xs-12 m-t-lg">
    <div><img class="full-size" src="{{ $organisation->logo }}"></div>
    <h2 class="elipsis-1">{{ $organisation->name }}</h2>
    @if (isset($organisation->descript))
        <h4>{!! nl2br(truncate(e($organisation->descript), 150)) !!}</h4>
    @elseif (isset($organisation->description))
        <h4>{!! nl2br(truncate(e($organisation->description), 150)) !!}</h4>
    @endif
    <p class="text-right show-more">
        <a href="{{ url('/admin/organisations/view/'. $organisation->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
    </p>
</div>
