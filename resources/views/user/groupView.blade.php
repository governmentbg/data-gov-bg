@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'group'])
    @include('partials.group-nav-bar', ['view' => 'view', 'group' => $group])
    @if (!empty($group))
        <div class="row">
            <div class="col-xs-12 m-t-md">
                <div class="row">
                    <div class="col-xs-12 page-content p-sm">
                        <div class="col-xs-12 list-orgs">
                            <div class="row">
                                <div class="col-xs-12 p-md">
                                    <div class="col-xs-12 org-logo">
                                        <img class="img-responsive" src="{{ $group->logo }}"/>
                                    </div>
                                    <div class="col-xs-12">
                                        <h3>{{ $group->name }}</h3>
                                        <p>{{ $group->description }}</p>
                                    </div>
                                    @if ($buttons[$group->uri]['edit'])
                                        <div class="col-xs-12 view-btns">
                                            <div class="row">
                                                <form
                                                    method="POST"
                                                    class="inline-block"
                                                    action="{{ url('/user/groups/edit/'. $group->uri) }}"
                                                >
                                                    {{ csrf_field() }}
                                                    <button class="btn btn-primary" type="submit">{{ uctrans('custom.edit') }}</button>
                                                    <input type="hidden" name="view" value="1">
                                                </form>
                                    @endif
                                    @if ($buttons[$group->uri]['delete'])
                                                <form
                                                    method="POST"
                                                    class="inline-block"
                                                    action="{{ url('/user/groups/delete/'. $id) }}"
                                                >
                                                    {{ csrf_field() }}
                                                        <button
                                                            class="btn del-btn btn-primary"
                                                            type="submit"
                                                            name="delete"
                                                            data-confirm="{{ __('custom.delete_group_confirm') }}"
                                                        >{{ uctrans('custom.remove') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
