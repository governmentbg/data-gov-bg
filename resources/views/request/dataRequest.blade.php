@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    <div class="col-xs-12 m-b-md">
        <div>
            <p class="request-data">
            {!! __('custom.data_req_info') !!}
            </p>
        </div>
        <form method="POST">
            {{ csrf_field() }}
            <div class="form-group">
                <span class="required"> {{ __('custom.required') }} *</span>
                <h4>{{ __('custom.short_data_descr') }} <span class="required">*</span></h4>
                <span class="info">{{ __('custom.user_data_descr') }}</span>
                <textarea class="form-control" name="description" required maxlength="191"></textarea>
            </div>
            <div class="form-group">
                <h4>{{ __('custom.data_url') }}<span class="info">({{ __('custom.optional') }})</span></h4>
                <span class="info">{{ __('custom.where_url') }}</span>
                <input type="text" class="form-control" name="published_url">
            </div>
            <div class="form-group">
                <h4>{{ __('custom.your_name') }}<span class="info">({{ __('custom.optional') }})</span></h4>
                <span class="info">
                {{ __('custom.anon_survey') }}
                </span>
                <input type="text" class="form-control" name="contact_name">
            </div>
            <div class="form-group">
                <h4>{{ __('custom.email') }}<span class="info">({{ __('custom.optional') }})</span></h4>
                <span class="info">
                {{ __('custom.anon_survey') }}
                </span>
                <input type="email" class="form-control" name="email">
            </div>
            <div class="form-group">
                <h4>{{ __('custom.notes') }}<span class="info">({{ __('custom.optional') }})</span></h4>
                <span class="info">
                {{ __('custom.additional_notes') }}
                </span>
                <textarea class="form-control" name="notes"></textarea>
            </div>
            <div class="form-group">
                <h4>{{ utrans('custom.organisations', 1) }} <span class="required">*</span></h4>
                <select
                    class="js-ajax-autocomplete js-ajax-autocomplete-org form-control"
                    data-url="{{ url('/api/listOrganisations') }}"
                    name="org_id"
                    id="org"
                    data-id="true"
                    data-do-not-apply-clear="true"
                    data-order-field="name"
                    data-order-type="asc"
                    required
                >
                </select>
            </div>
            <div class="m-t-lg text-right">
                <button type="submit" name="save" class="btn badge badge-pill">{{ __('custom.send') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
