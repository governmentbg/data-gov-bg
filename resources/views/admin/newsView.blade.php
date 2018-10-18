@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'news'])
        <div class="row m-t-lg">
            @if (!is_null($news))
                <div class="col-md-2 col-sm-1"></div>
                <div class="col-md-8 col-sm-10">
                    <div class="frame add-terms">
                        <div class="p-w-md text-center m-t-lg">
                            <h2>{{ __('custom.news_preview') }}</h2>
                        </div>
                        <div class="body">
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.title') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $news->title }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="active" class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.active') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ !empty($news->active) ? utrans('custom.yes') : utrans('custom.no') }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.browser_head') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $news->head_title }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.browser_keywords') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $news->meta_keywords }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.browser_desc') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $news->meta_description }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.valid') }}:</label>
                                <div class="col-sm-3 col-xs-12">
                                    <div>{{ __('custom.from') .': '. $news->valid_from }}</div>
                                </div>
                                <div class="col-sm-3 col-xs-12">
                                    <div>{{ __('custom.to') .': '. $news->valid_to }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.short_txt') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{!! $news->abstract !!}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.content') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{!! $news->body !!}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.forum_link') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $news->forum_link }}</div>
                                </div>
                            </div>

                            <div class="text-center m-b-lg terms-hr">
                                <hr>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $news->created_by }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $news->created_at }}</div>
                                </div>
                            </div>
                            @if ($news->created_at != $news->updated_at)
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $news->updated_by }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $news->updated_at }}</div>
                                    </div>
                                </div>
                            @endif
                            @if (\App\Role::isAdmin())
                                <div class="text-right">
                                    <div class="row">
                                        <form
                                            method="POST"
                                            class="inline-block"
                                            action="{{ url('admin/news/edit/'. $news->id) }}"
                                        >
                                            {{ csrf_field() }}
                                            <button class="btn btn-primary m-b-sm" type="submit">{{ uctrans('custom.edit') }}</button>
                                            <input type="hidden" name="view" value="1">
                                        </form>
                                        <form
                                            method="POST"
                                            class="inline-block"
                                        >
                                            {{ csrf_field() }}
                                        <button
                                            name="back"
                                            class="btn btn-primary m-b-sm"
                                        >{{ uctrans('custom.close') }}</button>
                                        </form>
                                        <form
                                            method="POST"
                                            class="inline-block"
                                            action="{{ url('admin/news/delete/'. $news->id) }}"
                                        >
                                            {{ csrf_field() }}
                                                <button
                                                    class="btn btn-primary del-btn m-b-sm"
                                                    type="submit"
                                                    name="delete"
                                                    data-confirm="{{ __('custom.remove_data') }}"
                                                >{{ uctrans('custom.remove') }}</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-1"></div>
            @else
                <div class="col-sm-12 m-t-md text-center no-info">
                    {{ __('custom.no_info') }}
                </div>
            @endif
        </div>
    </div>
@endsection
