@extends('layouts.app', [
    'link' => isset($organisation->uri) ? true : false
])

@section('content')
<div class="container">
    <div class="col-xs-12 m-l-sm">
        <div class="row">
            <div class="col-sm-9 col-xs-12 p-sm col-sm-offset-3">
                <div class="filter-content">
                    <div class="row">
                        <div class="col-xs-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="active p-l-none" href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ __('custom.profile') }}</a></li>
                                    <li><a href="{{ url('/organisation/'. $organisation->uri .'/datasets') }}">{{ __('custom.data') }}</a></li>
                                    <li><a href="{{ url('/organisation/chronology/'. $organisation->uri ) }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row m-t-lg">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        @if (isset($parentOrg))
                            <a href="{{ url('/organisation/profile/'. $parentOrg->uri) }}">
                                <img class="img-responsive" src="{{ $parentOrg->logo }}"/>
                            </a>
                        @else
                            <img class="img-responsive" src="{{ $organisation->logo }}"/>
                        @endif
                    </div>
                    <div class="col-md-9 col-sm-6 col-xs-12 info-box">
                        <div class="row">
                            <div class="col-lg-4 col-md-5 col-xs-12">
                                <a href="#" class="followers">
                                    <p>{{ $organisation->followers_count }}</p>
                                    <hr>
                                    <p>{{ __('custom.followers') }} </p>
                                    <img src="{{ asset('/img/followers.svg') }}">
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-5 col-xs-12">
                                <a href="#" class="data-sets">
                                    <p>{{ $organisation->datasets_count }}</p>
                                    <hr>
                                    <p>{{ __('custom.data_sets') }}</p>
                                    <img src="{{ asset('/img/data-sets.svg') }}">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @if (isset($parentOrg))
                    <div class="row">
                        <div class="col-xs-12">
                            <br/>
                            <h3>
                                {{ __('custom.main_org') }}:
                                <a href="{{ url('/organisation/profile/'. $parentOrg->uri) }}">{{ $parentOrg->name }}</a>
                            </h3>
                        </div>
                    </div>
                @endif

                <div class="m-t-lg">
                    <div class="m-b-md">
                        @if (isset($parentOrg))
                            <div class="col-md-2 col-sm-6 col-xs-12">
                                <img class="img-responsive" src="{{ $organisation->logo }}"/>
                            </div>
                        @endif
                        <div>
                            <div class="col-xs-12 p-l-none">
                                <div>
                                    <h3>{{ $organisation->name }} </h3><br/>
                                    <p>{!! nl2br(e($organisation->description)) !!}</p><br/>
                                </div>
                            </div>
                            @if (!empty($organisation->activity_info))
                                <div class="col-xs-12 p-l-none">
                                    <span><b>{{ __('custom.activity') }}:</b></span><br/><br/>
                                    <p>{!! nl2br(e($organisation->activity_info)) !!}</p><br/>
                                </div>
                            @endif
                            <div class="col-xs-12 p-l-r-none articles">
                                @if (!empty($organisation->contacts))
                                    <div class="col-sm-8 col-xs-12 p-l-none article pull-left">
                                        <span><b>{{ __('custom.contact_person') }}:</b></span><br/><br/>
                                        <p>{!! nl2br(e($organisation->contacts)) !!}</p><br/>
                                    </div>
                                @endif
                                @if (isset($organisation->custom_fields[0]) && !empty($organisation->custom_fields[0]->key))
                                    <div class="col-sm-8 col-xs-12 p-l-none article pull-left">
                                        <p><b>{{ __('custom.additional_fields') }}:</b></p>
                                        @foreach ($organisation->custom_fields as $field)
                                            <div class="row">
                                                <div class="col-xs-6">{{ $field->key }}</div>
                                                <div class="col-xs-6 text-left">{{ $field->value }}</div>
                                            </div>
                                        @endforeach
                                        <br/>
                                    </div>
                                @endif
                                <div class="col-sm-4 col-xs-12 pull-right text-right">
                                    <form method="post">
                                        {{ csrf_field() }}
                                        @if (isset($buttons['follow']) && $buttons['follow'])
                                            <div class="row">
                                                <button
                                                    class="btn btn-primary pull-right m-r-xs"
                                                    type="submit"
                                                    name="follow"
                                                    value="{{ $organisation->id }}"
                                                >{{ utrans('custom.follow') }}</button>
                                            </div>
                                        @elseif (isset($buttons['unfollow']) && $buttons['unfollow'])
                                            <div class="row">
                                                <button
                                                    class="btn btn-primary pull-right"
                                                    type="submit"
                                                    name="unfollow"
                                                    value="{{ $organisation->id }}"
                                                >{{ uctrans('custom.stop_follow') }}</button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                                <div class="col-xs-12 view-btns p-h-md">
                                    <div class="row">
                                        @if (isset($buttons['edit']) && $buttons['edit'])
                                            <form
                                                method="POST"
                                                class="inline-block"
                                                action="{{ url('/'. $buttons['rootUrl'] .'/organisations/edit/'. $organisation->uri) }}"
                                            >
                                                {{ csrf_field() }}
                                                <button class="btn btn-primary" type="submit">{{ uctrans('custom.edit') }}</button>
                                                <input type="hidden" name="view" value="1">
                                            </form>
                                        @endif
                                        @if (isset($buttons['delete']) && $buttons['delete'])
                                            <form
                                                method="POST"
                                                class="inline-block"
                                                action="{{ route('orgDelete', array_except(app('request')->input(), ['page'])) }}"
                                            >
                                                {{ csrf_field() }}
                                                <button
                                                    class="btn del-btn btn-primary del-btn"
                                                    type="submit"
                                                    name="delete"
                                                    data-confirm="{{ __('custom.delete_organisation_confirm') }}"
                                                >{{ uctrans('custom.remove') }}</button>
                                                <input class="user-org-del" type="hidden" name="org_uri" value="{{ $organisation->uri }}">
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (count($childOrgs) > 0)
            <div class="row">
                <div class="col-sm-12">
                    <hr>
                </div>
            </div>
            <h3>{{ __('custom.child_orgs') }}</h3><br/>
            @foreach ($childOrgs as $childOrg)
                <div class="row">
                    <div class="col-sm-12">
                        <a href="{{ url('/organisation/profile/'. $childOrg->uri) }}">{{ $childOrg->name }}</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
