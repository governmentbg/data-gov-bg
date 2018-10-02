@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'forum'])
        @include('partials.pagination')
        <div class="col-xs-2 m-t-lg m-b-lg">
            <span class="my-profile head">{{ utrans('custom.discussion_posts') }}</span>
        </div>
        <div class="col-xs-10 m-t-lg text-right section">
            <div class="filter-content section-nav-bar">
                <ul class="nav filter-type right-border">
                    <li>
                        <a
                            class="active"
                            href="{{ url('/admin/forum/discussions/list') }}"
                        >{{ __('custom.discussions') }}</a>
                    </li>
                    <li>
                        <a
                        href="{{ url('/admin/forum/categories/list') }}"
                        >{{ __('custom.categories') }}</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row m-b-sm">
            <div class="col-xs-12 text-right">
                <span class="badge badge-pill long-badge">
                    <a href="{{ url('/admin/forum/discussions/add') }}">{{ __('custom.add') }}</a>
                </span>
            </div>
        </div>
        <div class="row m-b-lg">
            @if (count($posts))
                <form method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="col-xs-12 m-l-sm">
                        <div class="m-t-md">
                            <div class="table-responsive opn-tbl text-center">
                                <table class="table">
                                    <thead>
                                        <th>{{ utrans('custom.user') }}</th>
                                        <th>{{ utrans('custom.discussion') }}</th>
                                        <th>{{ __('custom.created_at') }}</th>
                                        <th>{{ __('custom.updated_at') }}</th>
                                        <th>{{ __('custom.action') }}</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($posts as $post)
                                            <tr>
                                                <td class="name">{{ isset($post->user) ? $post->user : $post->user_id }}</td>
                                                <td class="name">{{ isset($discussion) ? $discussion : '' }}</td>
                                                <td class="name">{{ $post->created_at }}</td>
                                                <td class="name">{{ $post->updated_at }}</td>
                                                <td class="buttons">
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('admin/forum/posts/view/'. $post->id) }}"
                                                    >{{ utrans('custom.preview') }}</a>
                                                    <a
                                                        class="link-action red"
                                                        data-confirm="{{ __('custom.remove_data') }}"
                                                        href="{{ url('admin/forum/posts/delete/'. $post->id) }}"
                                                    >{{ __('custom.delete') }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                <div class="col-sm-12 m-t-xl text-center no-info">
                    {{ __('custom.no_info') }}
                </div>
            @endif
        </div>
        @if (isset($pagination))
            <div class="row">
                <div class="col-xs-12 text-center">
                    {{ $pagination->render() }}
                </div>
            </div>
        @endif
    </div>
@endsection
