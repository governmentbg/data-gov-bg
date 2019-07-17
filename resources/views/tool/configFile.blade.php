@extends('layouts.app')

@section('content')
<form method="post" class="form-horisontal config-form" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="container p-l-r-none">
        @include('partials.alerts-bar')

        <input
            type="hidden"
            name="conn_id"
            value="{{ empty($post['conn_id']) ? '' : $post['conn_id'] }}"
        >

        @if (!empty($files))
            @foreach ($files as $file)
                @foreach ($file->dataQueries as $query)
                    <div class="data-query">
                        <span
                        >{{ $file->connection_name .'('. $query->name .')' }}</span>
                        <input
                            type="submit"
                            class="btn btn-primary pull-right"
                            name="delete_file[{{ $file->id }}]"
                            value="{{ uctrans('custom.delete') }}"
                            data-confirm="{{ __('custom.remove_data') }}"
                        >
                        <input
                            type="submit"
                            class="btn btn-primary pull-right save-btn"
                            name="file_conn_id[{{ $file->id }}]"
                            value="{{ uctrans('custom.edit') }}"
                        >
                        <input
                            type="submit"
                            class="btn btn-primary save-btn pull-right"
                            name="send_file_query[{{ $query->id }}]"
                            value="{{ uctrans('custom.send') }}"
                        >
                    </div>
                @endforeach
            @endforeach
        @endif

        <div class="js-file-form file-form">
            <div class="form-group required">
                <label class="col-md-3">{{ __('custom.title') }}:</label>
                <div class="col-md-9">
                    <input
                        type="text"
                        class="form-control"
                        name="file_conn_name"
                        value="{{ request('file_conn_name', empty($post['file_conn_name']) ? '' : $post['file_conn_name']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('file_conn_name') }}</span>
                </div>
            </div>
            <div class="form-group required">
                <label class="col-sm-3">{{ utrans('custom.file') }}</label>
                <div class="col-md-9">
                    <input
                        type="text"
                        name="file"
                        class="input-border-r-12 form-control"
                        placeholder="{{ __('custom.file_desc') }}"
                        value="{{ request('file', empty($post['file']) || empty($post['conn_id']) ? '' : $post['file']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('file') }}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3">{{ __('custom.notification_email') }}:</label>
                <div class="col-md-9">
                    <input
                        class="form-control"
                        name="file_nt_email"
                        value="{{ request('file_nt_email', empty($post['file_nt_email']) || empty($post['conn_id']) ? '' : $post['file_nt_email']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('file_nt_email') }}</span>
                </div>
            </div>
            <div class="form-group col-md-9 col-md-offset-3">
                <input
                    type="submit"
                    class="btn btn-primary test-btn pull-right save-btn"
                    name="test_file"
                    value="{{ __('custom.check') }}"
                >
            </div>
            @if (!empty($data))
                @include('partials.resource-visualisation')
            @endif
            <h2>{{ __('custom.target') }}:</h2><br>
            <div class="form-group">
                <label class="col-md-3">{{ __('custom.api_key') }}:</label>
                <div class="col-md-9">
                    <input
                        class="form-control"
                        name="file_api_key"
                        value="{{ request('file_api_key', empty($post['file_api_key']) || empty($post['conn_id']) ? '' : $post['file_api_key']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('file_api_key') }}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3">{{ __('custom.resource_key') }}:</label>
                <div class="col-md-9">
                    <input
                        class="form-control"
                        name="file_rs_key"
                        value="{{ request('file_rs_key', empty($post['file_rs_key']) || empty($post['conn_id']) ? '' : $post['file_rs_key']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('file_rs_key') }}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3">{{ __('custom.refresh_freq') }}:</label>
                <div class="col-md-9">
                    <input
                        class="form-control freq-number"
                        name="file_upl_freq"
                        value="{{ request('file_upl_freq', empty($post['file_upl_freq']) || empty($post['conn_id']) ? '' : $post['file_upl_freq']) }}"
                    >
                    <select
                        name="file_upl_freq_type"
                        class="js-select form-control"
                    >
                        @foreach ($freqTypes as $freqTypeId => $freqType)
                            <option
                                value="{{ $freqTypeId }}"
                                {{
                                    $freqTypeId == request('file_upl_freq_type', empty($post['file_upl_freq_type']) || empty($post['conn_id'])
                                        ? ''
                                        : $post['file_upl_freq_type']) ? 'selected' : ''
                                }}
                            >{{ $freqType }}</option>
                        @endforeach
                    </select>
                    <div><span class="error">{{ empty($errors) ? null : $errors->first('file_upl_freq') }}</span></div>
                </div>
            </div>
            <div class="form-group col-md-9 col-md-offset-3">
                @if (!empty($post['conn_id']))
                    <input
                        type="submit"
                        class="btn btn-primary test-btn save-btn"
                        name="send_file"
                        value="{{ uctrans('custom.send') }}"
                    >
                @endif
                <input
                    type="submit"
                    class="btn btn-primary save-btn pull-right"
                    name="save_file"
                    value="{{ uctrans('custom.save') }}"
                >
                @if (!empty($post['conn_id']))
                    <input
                        type="submit"
                        class="btn btn-primary save-btn m-r-sm pull-right"
                        name="new"
                        value="{{ uctrans('custom.new') }}"
                    >
                @endif
            </div>
        </div>
    </div>
</form>
@endsection
