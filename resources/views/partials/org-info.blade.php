<div class="col-sm-3 col-xs-12 m-t-lg">
    <div><img class="full-size" src="{{ $organisation->logo }}"></div>
    <h2 class="elipsis-1">{{ $organisation->name }}</h2>
    <h4>{{ isset($organisation->descript) ? truncate($organisation->descript, 150) : truncate($organisation->description, 150) }}</h4>
    <p class="text-right show-more">
        <a href="{{ url('/admin/organisations/view/'. $organisation->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
    </p>
</div>
