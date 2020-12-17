@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'news'])
        <div class="col-xs-12 m-t-lg m-b-md p-l-r-none">
            <span class="my-profile m-l-sm">{{ uctrans('custom.news_list') }}</span>
        </div>
        @if($pagination)
            @include('partials.pagination')
        @endif
        <div class="row">
            @include('partials.admin-sidebar', [
                'action' => 'Admin\NewsController@list',
                'options' => ['active', 'range']
            ])
            <div class="col-sm-9 col-xs-12">
                <div class="row">
                    @if($pagination)
                        @include('partials.pagination')
                    @endif
                    <div class="col-xs-12 text-right">
                        <a
                            class="btn btn-primary add pull-right"
                            href="{{ url('admin/news/add') }}"
                        >{{ __('custom.add') }}</a>
                    </div>
                    <div class="col-xs-12 m-b-lg p-l-r-none">
                    @if (count($news))
                        <form method="POST" class="form-horizontal">
                            {{ csrf_field() }}
                            <div class="col-xs-12">
                                <div class="m-t-md">
                                    @if($pagination)
                                        <div class="table-responsive opn-tbl text-center">
                                            <table class="table">
                                                <thead>
                                                    <th>{{ utrans('custom.title') }}</th>
                                                    <th>{{ utrans('custom.forum') }}</th>
                                                    <th>{{ utrans('custom.active') }}</th>
                                                    <th>{{ __('custom.valid_from') }}</th>
                                                    <th>{{ __('custom.valid_to') }}</th>
                                                    <th>{{ __('custom.action') }}</th>
                                                </thead>
                                                <tbody>
                                                    @foreach ($news as $signleNews)
                                                        <tr>
                                                            <td class="name">{{ $signleNews->title }}</td>
                                                            <td>
                                                                {{
                                                                    !empty($signleNews->forum_link)
                                                                        ? __('custom.yes')
                                                                        :  __('custom.no')
                                                                }}
                                                            </td>
                                                            <td>
                                                                {{
                                                                    !empty($signleNews->active)
                                                                        ? __('custom.yes')
                                                                        :  __('custom.no')
                                                                }}
                                                            </td>
                                                            <td>{{ $signleNews->valid_from }}</td>
                                                            <td>{{ $signleNews->valid_to }}</td>
                                                            <td class="buttons">
                                                                <a
                                                                    class="link-action"
                                                                    href="{{ url('admin/news/edit/'. $signleNews->id) }}"
                                                                >{{ utrans('custom.edit') }}</a>
                                                                <a
                                                                    class="link-action"
                                                                    href="{{ url('admin/news/view/'. $signleNews->id) }}"
                                                                >{{ utrans('custom.preview') }}</a>
                                                                <a
                                                                    class="link-action red"
                                                                    href="{{ url('admin/news/delete/'. $signleNews->id) }}"
                                                                    data-confirm="{{ __('custom.remove_data') }}"
                                                                >{{ __('custom.delete') }}</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="col-sm-12 m-t-md text-center no-info">
                                            {{ __('custom.no_info') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="col-sm-12 m-t-md text-center no-info">
                            {{ __('custom.no_info') }}
                        </div>
                    @endif
                </div>
                    @if (isset($pagination))
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                @if ($pagination)
                                    {{ $pagination->render() }}
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
