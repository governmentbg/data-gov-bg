<div class="js-help-bar help-container hidden">
    <img class="help-icon-open" src="{{ asset('/img/help-icon.svg') }}">
    <img class="close-help close-btn close" src="{{ asset('/img/X.svg') }}">
    <div class="help-content m-t-sm">
        @if (!empty($help) && $help->active)
            <h3>{{ $help->title }}</h3>
            <div class="nano help-nano">
                <div class="nano-content">
                    {!! $help->body !!}
                    @if (App\Role::isAdmin())
                        <span class="help-edit pull-right">
                            <a
                                href="{{ url('/admin/help/page/edit/'. $help->id) }}"
                            >{{ __('custom.edit') }}</a>
                        </span>
                    @endif
                </div>
            </div>
        @else
            <h3>{{ utrans('custom.no_help') }}</h3>
            @if (App\Role::isAdmin())
                <span class="help-edit pull-right">
                    <a
                        href="{{ url('/admin/help/page/edit/'. $help->id) }}"
                    >{{ __('custom.edit') }}</a>
                </span>
            @endif
        @endif
    </div>
</div>
