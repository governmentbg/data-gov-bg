@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'signals'])
        <div class="row">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.signal_edit') }}</h2>
                    </div>
                    <div class="body">
                        <form method="POST" class="form-horisontal">
                            {{ csrf_field() }}
                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="firstname" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.name') }}</label>
                                <div class="col-sm-9">
                                    <input
                                        name="firstname"
                                        class="input-border-r-12 form-control"
                                        value="{{ !empty($signal->firstname)
                                            ? $signal->firstname
                                            : old('firstname')
                                        }}"
                                    >
                                    @if (isset($errors) && $errors->has('firstname'))
                                        <span class="error">{{ $errors->first('firstname') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="lastname" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.lastname') }}</label>
                                <div class="col-sm-9">
                                    <input
                                        name="lastname"
                                        class="input-border-r-12 form-control"
                                        value="{{ !empty($signal->lastname)
                                            ? $signal->lastname
                                            : old('lastname')
                                        }}"
                                    >
                                    @if (isset($errors) && $errors->has('lastname'))
                                        <span class="error">{{ $errors->first('lastname') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="email" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.email') }}</label>
                                <div class="col-sm-9">
                                    <input
                                        name="email"
                                        class="input-border-r-12 form-control"
                                        value="{{ !empty($signal->email)
                                            ? $signal->email
                                            : old('email')
                                        }}"
                                    >
                                    @if (isset($errors) && $errors->has('email'))
                                        <span class="error">{{ $errors->first('email') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="description" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.description') }}</label>
                                <div class="col-sm-9">
                                    <textarea
                                        name="description"
                                        class="input-border-r-12 form-control"
                                    >{{ !empty($signal->description) ? $signal->description : old('description') }}</textarea>
                                    @if (isset($errors) && $errors->has('description'))
                                        <span class="error">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if (is_array($statuses))
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-3 col-xs-12 col-form-label"></label>
                                    <div class="col-sm-9">
                                        <select
                                            class="input-border-r-12 form-control js-autocomplete"
                                            name="status"
                                            data-live-search="false"
                                        >
                                            <option value="">{{ utrans('custom.status') }}</option>
                                            @foreach ($statuses as $key => $status)
                                                <option
                                                    value="{{ $key }}"
                                                    {{ $key == $signal->status ? 'selected' : '' }}
                                                >{{ utrans('custom.'. $status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group row m-b-lg m-t-md"><div class="col-xs-12 terms-hr"><hr></div></div>
                            <div class="form-group row m-b-lg m-t-md">
                                <div class="col-xs-12">{{ __('custom.created_at') }}: &nbsp; {{ $signal->created_at }}</div>
                                <div class="col-xs-12">{{ __('custom.created_by') }}: &nbsp; {{ $signal->created_by }}</div>
                                <div class="col-xs-12">{{ __('custom.updated_at') }}: &nbsp; {{ $signal->updated_at }}</div>
                                <div class="col-xs-12">{{ __('custom.updated_by') }}: &nbsp; {{ $signal->updated_by }}</div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <button type="submit" name="edit" value="1" class="m-l-md btn btn-custom">{{ utrans('custom.edit') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-1"></div>
        </div>
    </div>
@endsection
