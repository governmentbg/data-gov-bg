@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-xs-12 p-lg">
        <div>
            <p class="request-data">
            {!! __('custom.data_req_info') !!}
            </p>
        </div>
        <form>
            <div class="m-t-lg">
                <span class="required"> {{ __('custom.required') }} *</span>
                <h4>{{ __('custom.short_data_descr') }}&nbsp;<span class="required">*</span></h4>
                <span class="info">{{ __('custom.user_data_descr') }}</span>
                <textarea class="input-border-r-12 input-long" name="description"></textarea>
            </div>
            <div class="m-t-lg">
                <h4>{{ __('custom.data_url') }}<span class="info">({{ __('custom.optional') }})</span></h4>
                <span class="info">{{ __('custom.where_url') }}</span>
                <input type="text"  class="input-border-r-12 input-long" name="url">
            </div>
            <div class="m-t-lg">
                <h4>{{ __('custom.your_name') }}<span class="info">({{ __('custom.optional') }})</span></h4>
                <span class="info">
                {{ __('custom.anon_survey') }}
                </span>
                <input type="text"  class="input-border-r-12 input-long" name="name">
            </div>
            <div class="m-t-lg">
                <h4>{{ __('custom.email') }}<span class="info">({{ __('custom.optional') }})</span></h4>
                <span class="info">
                {{ __('custom.anon_survey') }}
                </span>
                <input type="text"  class="input-border-r-12 input-long" name="user-email">
            </div>
            <div class="m-t-lg">
                <h4>{{ __('custom.notes') }}<span class="info">({{ __('custom.optional') }})</span></h4>
                <span class="info">
                {{ __('custom.additional_notes') }}
                </span>
                <input class="input-border-r-12 input-long" name="notes">
            </div>
            <div class="m-t-lg">
                <h4>{{ __('custom.organisation_email') }}</h4>
                <input type="text" class="input-border-r-12 input-long" name="org-email">
            </div>
            <div class="m-t-lg text-right">
                <button type="submit" class="btn badge badge-pill">{{ __('custom.send') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
