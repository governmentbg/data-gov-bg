@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'dataRequests'])
        @if($dataRequest)
            <div class="row m-t-lg">
                <div class="col-md-2 col-sm-1"></div>
                <div class="col-md-10 col-sm-10">
                    <div class="frame add-terms">
                        <div class="p-w-md text-center m-b-lg m-t-lg">
                            <h2>{{ __('custom.data_request_edit') }}</h2>
                        </div>
                        <div class="body">
                            <form method="POST" enctype="multipart/form-data" class="form-horisontal">
                                {{ csrf_field() }}
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="description" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.short_data_descr') }}:</label>
                                    <div class="col-sm-9">
                                        <textarea
                                            name="description"
                                            class="input-border-r-12 form-control"
                                            required
                                        >{{ !empty($dataRequest->descript) ? $dataRequest->descript : '' }} </textarea>
                                        @if (isset($errors) && $errors->has('descript'))
                                            <span class="error">{{ $errors->first('descript') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="published_url" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.published_url') }}:</label>
                                    <div class="col-sm-9">
                                        <input
                                            type="text"
                                            name="published_url"
                                            class="input-border-r-12 form-control"
                                            value="{{ !empty($dataRequest->published_url) ? $dataRequest->published_url : '' }}"
                                        >
                                        @if (isset($errors) && $errors->has('published_url'))
                                            <span class="error">{{ $errors->first('published_url') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="contact_name" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.contact_person_org') }}:</label>
                                    <div class="col-sm-9">
                                        <input
                                            type="text"
                                            name="contact_name"
                                            class="input-border-r-12 form-control"
                                            value="{{ !empty($dataRequest->contact_name) ? $dataRequest->contact_name : '' }}"
                                        >
                                        @if (isset($errors) && $errors->has('contact_name'))
                                            <span class="error">{{ $errors->first('contact_name') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="email" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.email') }}:</label>
                                    <div class="col-sm-9">
                                        <input
                                            type="email"
                                            name="email"
                                            class="input-border-r-12 form-control"
                                            value="{{ !empty($dataRequest->email) ? $dataRequest->email : '' }}"
                                        >
                                        @if (isset($errors) && $errors->has('email'))
                                            <span class="error">{{ $errors->first('email') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="notes" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.notes') }}:</label>
                                    <div class="col-sm-9">
                                        <textarea
                                            name="notes"
                                            class="input-border-r-12 form-control"
                                        >{{ !empty($dataRequest->contact_name) ? $dataRequest->notes : '' }}</textarea>
                                        @if (isset($errors) && $errors->has('notes'))
                                            <span class="error">{{ $errors->first('notes') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="org_id" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.organisations') }}:</label>
                                    <div class="col-sm-9">
                                        <select
                                            class="js-autocomplete form-control"
                                            name="org_id"
                                            id="org"
                                            data-live-search="true"
                                            required
                                        >
                                            @if (isset($organisations))
                                                @foreach ($organisations as $organisation)

                                                    <option
                                                        value="{{ $organisation->id }}"
                                                        {{ $organisation->id == $dataRequest->org_id
                                                            ? 'selected'
                                                            : ''
                                                        }}
                                                    >{{ $organisation->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @if (isset($errors) && $errors->has('organisation'))
                                            <span class="error">{{ $errors->first('organisation') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="status" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.status') }}:</label>
                                    <div class="col-sm-9">
                                        <select
                                            class="form-control"
                                            name="status"
                                            id="org"
                                            required
                                        >
                                            @if (isset($statuses))
                                                @foreach ($statuses as $key => $status)
                                                    <option
                                                        value="{{ $key }}"
                                                        {{ $key == $dataRequest->status
                                                            ? 'selected'
                                                            : ''
                                                        }}
                                                    >{{ uctrans('custom.'. $status) }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @if (isset($errors) && $errors->has('status'))
                                            <span class="error">{{ $errors->first('status') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12 text-right">
                                    <a
                                        href="{{ url('admin/data-requests/list') }}"
                                        class="btn btn-primary"
                                    >
                                        {{ uctrans('custom.close') }}
                                    </a>
                                        <button type="submit" name="edit" value="1" class="m-l-md btn btn-custom">{{ uctrans('custom.edit') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-1"></div>
            </div>
        @endif
    </div>
@endsection
