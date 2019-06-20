<div class="row">
    <div class="col-sm-3 col-xs-12 text-left">
    </div>
</div>
<div class="row">
    <div class="col-sm-3 col-xs-12 text-left p-l-none">
    @if ($view == 'organisation')
        @include('partials.org-info', ['organisation' => $organisation])
        @php
            $viewPath = 'organisations';
        @endphp
    @else
        @include('partials.group-info', ['group' => $organisation])
        @php
            $viewPath = 'groups';
        @endphp
    @endif
    </div>
    <div class="col-sm-9 col-xs-12 m-t-md">
        <div class="row">
            <div class="articles m-t-lg">
                @if (count($datasets))
                    @foreach ($datasets as $set)
                        <div class="article m-b-lg col-xs-12 user-dataset">
                            <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                            <div class="col-sm-13 p-l-none">
                                <a href="{{ url('/admin/'. $viewPath .'/'. $organisation->uri .'/viewDeletedDataset/'. $set->uri) }}">
                                    <h2 class="m-t-xs">{{ $set->name }}</h2>
                                </a>
                                <div class="desc">
                                    {!! nl2br(truncate(e($set->descript), 150)) !!}
                                </div>
                                <div class="col-sm-12 p-l-none btns">
                                    <div class="pull-left row">
                                        <div class="col-xs-6">
                                            <span class="badge badge-pill m-r-md m-b-sm">
                                                <a
                                                    href="{{ url('/admin/'. $viewPath .'/'. $organisation->uri .'/viewDeletedDataset/'. $set->uri) }}"
                                                >{{ uctrans('custom.preview') }}</a>
                                            </span>
                                        </div>
                                        <div class="col-xs-6">
                                            @if (in_array($set->id, $allowActionsForDataset))
                                                <form method="POST" action="{{ url('admin/organisations/hardDeleteDataset') }}">
                                                    {{ csrf_field() }}
                                                    <div class="col-xs-6 text-right">
                                                        <button
                                                            class="badge badge-pill m-b-sm del-btn"
                                                            type="submit"
                                                            name="delete"
                                                            data-confirm="{{ __('custom.remove_data') }}"
                                                        >{{ uctrans('custom.remove') }}</button>
                                                    </div>
                                                    <input type="hidden" name="dataset_id" value="{{ $set->id }}">
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-sm-12 m-t-md text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
