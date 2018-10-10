<div class="js-help-bar help-container hidden">
    @if(!empty($help))
        <div class="nano">
            <div class="nano-content">
                <div class="help-content">
                    <img class="help-icon-open" src="{{ asset('/img/help-icon.svg') }}">
                    <div class="close"><span class="close-btn">X</span></div>
                    <h3>{{ $help->title }}</h3>
                    <div class="p-b-md">
                        {!! $help->body !!}
                    </div>
                    @if (App\Role::isAdmin())
                        <span class="help-edit pull-right">
                            <a
                                href="{{ url('/admin/help/page/edit/'. $help->id) }}"
                            >{{ __('custom.edit') }}</a>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
