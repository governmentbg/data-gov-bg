@extends('layouts.app')

@section('content')
<form method="post" class="form-horisontal config-form" enctype="multipart/form-data">
    <div class="container p-l-r-none">
        @include('partials.alerts-bar')

        <div class="js-config-control-form config-control-form">
            {{ csrf_field() }}
            <div class="form-group required">
                <label class="col-sm-4 col-xs-12 col-form-label">{{ __('custom.title') }}:</label>
                <div class="col-sm-8">
                    @if (
                        empty($post['source_type']) && empty(old('source_type'))
                        || !empty($post['source_type']) && $post['source_type'] == 'dbms'
                        || !empty(old('source_type')) && old('source_type') == 'dbms'
                    )
                        <input
                            type="text"
                            class="input-border-r-12 form-control"
                            name="connection_name"
                            value="{{ old('connection_name', empty($post['connection_name']) ? '' : $post['connection_name']) }}"
                        >
                        <span class="error">{{ $errors->first('connection_name') }}</span>
                    @else
                        <input
                            type="text"
                            class="input-border-r-12 form-control"
                            name="file_conn_name"
                            value="{{ old('file_conn_name', empty($post['file_conn_name']) || !$edit ? '' : $post['file_conn_name']) }}"
                        >
                        <span class="error">{{ $errors->first('file_conn_name') }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group required">
                <label class="col-sm-4 col-xs-12 col-form-label m-b-sm">{{ __('custom.connection_type') }}:</label>
                @foreach ($sourceTypes as $i => $name)
                    <div class="col-sm-4 col-xs-6 m-b-md">
                        <label class="radio-label {{ $i == 2 ? 'pull-right' : null }}">
                            {{ uctrans('custom.'. $name) }}
                            <div class="js-check js-submit">
                                <input
                                    type="radio"
                                    name="source_type"
                                    value="{{ $name }}"
                                    @if (!empty(old('source_type')) && old('source_type') == $name)
                                        {{ 'checked' }}
                                    @elseif (!empty($post['source_type']) && $post['source_type'] == $name)
                                        {{ 'checked' }}
                                    @elseif (empty(old('source_type')) && empty($post['source_type']) && $name == 'dbms')
                                        {{ 'checked' }}
                                    @endif
                                >
                            </div>
                        </label>
                    </div>
                @endforeach
                @if (isset($errors) && $errors->has('type'))
                    <div class="row">
                        <div class="col-xs-12 m-l-md">
                            <span class="error">{{ $errors->first('type') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <hr class="section-line"></hr>

    <div class="container p-l-r-none m-b-lg">
        @if (
            empty($post['source_type']) && empty(old('source_type'))
            || !empty($post['source_type']) && $post['source_type'] == 'dbms'
            || !empty(old('source_type')) && old('source_type') == 'dbms'
        )
            <div class="js-dbms-form dbms-form">
                <div class="form-group required">
                    <label class="col-md-3">{{ __('custom.host') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="source_db_host"
                            value="{{ old('source_db_host', empty($post['source_db_host']) ? '' : $post['source_db_host']) }}"
                            placeholder="127.0.0.1:3306"
                        >
                        <span class="error">{{ $errors->first('source_db_host') }}</span>
                    </div>
                </div>
                <div class="form-group required">
                    <label class="col-md-3">{{ __('custom.user_name') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="source_db_user"
                            value="{{ old('source_db_user', empty($post['source_db_user']) ? '' : $post['source_db_user']) }}"
                        >
                        <span class="error">{{ $errors->first('source_db_user') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.password') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="source_db_pass"
                            value="{{ old('source_db_pass', empty($post['source_db_pass']) ? '' : $post['source_db_pass']) }}"
                        >
                        <span class="error">{{ $errors->first('source_db_pass') }}</span>
                    </div>
                </div>
                <div class="form-group required">
                    <label class="col-md-3">{{ __('custom.db_name') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="source_db_name"
                            value="{{ old('source_db_name', empty($post['source_db_name']) ? '' : $post['source_db_name']) }}"
                        >
                        <span class="error">{{ $errors->first('source_db_name') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.notification_email') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="notification_email"
                            value="{{ old('notification_email', empty($post['notification_email']) ? '' : $post['notification_email']) }}"
                        >
                        <span class="error">{{ $errors->first('notification_email') }}</span>
                    </div>
                </div>
                <!-- <div class="form-group">
                    <label class="col-md-3"></label>
                    <div class="col-md-9">
                        <input
                            class="btn btn-primary generate-btn form-control"
                            type="submit"
                            name="generate"
                            value="{{ __('custom.generate') }} {{ __('custom.command') }}"
                        >
                    </div>
                </div> -->
                <div class="form-group">
                    <label class="col-md-3"></label>
                    <div class="col-md-9">
                        <textarea
                            type="text"
                            class="input-border-r-12 form-control"
                            name="test_query"
                        >{{ old('test_query', empty($post['test_query']) ? 'SELECT * FROM ___' : $post['test_query']) }}</textarea>
                        <span class="error">{{ $errors->first('test_query') }}</span>
                    </div>
                </div>
                <div class="form-group col-md-9 col-md-offset-3">
                    <input
                        type="submit"
                        class="btn btn-primary test-btn"
                        name="test_conn"
                        value="{{ __('custom.test_connection') }}"
                    >
                    <input
                        type="submit"
                        class="btn btn-primary save-btn pull-right"
                        name="save_conn"
                        value="{{ uctrans('custom.save') }}"
                    >
                </div>
            </div>

            @if ($foundData === [])
                <p class="alert alert-danger">
                    {{ __('custom.query_fail') }}
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </p>
            @elseif (!empty($foundData))
                <div class="m-t-md m-b-md js-show-on-load js-data-table">
                    <table class="data-table" data-page-length="10">
                        <thead>
                            @foreach ($foundData as $index => $row)
                                @if ($index == 0)
                                    @foreach ($row as $key => $value)
                                        <th><p>{{ $value }}</p></th>
                                    @endforeach
                                    </thead>
                                    <tbody>
                                @else
                                    <tr>
                                        @foreach ($row as $key => $value)
                                            <td>{{ $value }}</td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="align-right">
                    <input
                        type="button"
                        class="btn btn-primary pull-right js-hide-button"
                        data-target=".js-data-table"
                        value="{{ uctrans('custom.close') }}"
                    >
                </div>
            @endif

            @if ($hasDb)
                <div class="js-query-form query-form">
                    <h2>{{ __('custom.source') }}:</h2><br>
                    <div class="form-group">
                        <label class="col-md-3">{{ __('custom.title') }}:</label>
                        <div class="col-md-9">
                            <input
                                class="form-control"
                                name="name"
                                value="{{ old('name', empty($post['name']) ? '' : $post['name']) }}"
                            >
                            <span class="error">{{ $errors->first('name') }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3">{{ __('custom.query') }}:</label>
                        <div class="col-md-9">
                            <textarea
                                type="text"
                                class="input-border-r-12 form-control"
                                name="query"
                            >{{ old('query', empty($post['query']) ? 'SELECT * FROM ___' : $post['query']) }}</textarea>
                            <span class="error">{{ $errors->first('query') }}</span>
                        </div>
                    </div>
                    <h2>{{ __('custom.target') }}:</h2><br>
                    <div class="form-group">
                        <label class="col-md-3">{{ __('custom.api_key') }}:</label>
                        <div class="col-md-9">
                            <input
                                class="form-control"
                                name="api_key"
                                value="{{ old('api_key', empty($post['api_key']) ? '' : $post['api_key']) }}"
                            >
                            <span class="error">{{ $errors->first('api_key') }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3">{{ __('custom.resource_key') }}:</label>
                        <div class="col-md-9">
                            <input
                                class="form-control"
                                name="resource_key"
                                value="{{ old('resource_key', empty($post['resource_key']) ? '' : $post['resource_key']) }}"
                            >
                            <span class="error">{{ $errors->first('resource_key') }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3">{{ __('custom.refresh_freq') }}:</label>
                        <div class="col-md-9">
                            <input
                                class="form-control freq-number"
                                name="upl_freq"
                                value="{{ old('upl_freq', empty($post['upl_freq']) ? '' : $post['upl_freq']) }}"
                            >
                            <select
                                name="upl_freq_type"
                                class="js-select form-control"
                            >
                                @foreach ($freqTypes as $freqTypeId => $freqType)
                                    <option
                                        value="{{ $freqTypeId }}"
                                        {{ $freqTypeId == old('upl_freq_type', empty($post['upl_freq_type']) ? '' : $post['upl_freq_type']) ? 'selected' : '' }}
                                    >{{ $freqType }}</option>
                                @endforeach
                            </select>
                            <div><span class="error">{{ $errors->first('upl_freq') }}</span></div>
                        </div>
                    </div>
                    <div class="form-group col-md-9 col-md-offset-3">
                        @if (!empty($post['id']))
                            <input
                                type="submit"
                                class="btn btn-primary save-btn"
                                name="new_query"
                                value="{{ uctrans('custom.new') }}"
                            >
                        @endif
                        <input
                            type="submit"
                            class="btn btn-primary save-btn pull-right"
                            name="save_query"
                            value="{{ uctrans('custom.save') }}"
                        >
                    </div>
                </div>
                <input name="id" value="{{ empty($post['id']) ? '' : $post['id'] }}" hidden>

                @foreach ($dataQueries as $dataQuery)
                    <div class="data-query">
                        <span>{{ $dataQuery->name }}</span>
                        <input
                            type="submit"
                            class="btn btn-primary pull-right"
                            name="delete_query[{{ $dataQuery->id }}]"
                            value="{{ uctrans('custom.delete') }}"
                            data-confirm="{{ __('custom.remove_data') }}"
                        >
                        <input
                            type="submit"
                            class="btn btn-primary pull-right"
                            name="edit_query[{{ $dataQuery->id }}]"
                            value="{{ uctrans('custom.edit') }}"
                        >
                        <input
                            type="submit"
                            class="btn btn-primary save-btn pull-right"
                            name="send_query[{{ $dataQuery->id }}]"
                            value="{{ uctrans('custom.send') }}"
                        >
                    </div>
                @endforeach
            @endif
        @else
            <div class="data-query">
                @if (old('edit', $edit))
                    <input
                        type="submit"
                        class="btn btn-primary save-btn new-file pull-right"
                        name="new"
                        value="{{ uctrans('custom.new') }}"
                    >
                @endif
                @if (!empty($post['file_conn_name']))
                    <input
                        type="hidden"
                        name="conn_id"
                        value="{{ old('conn_id', empty($post['conn_id']) ? '' : $post['conn_id']) }}"
                    >
                @endif
            </div>
            <div class="js-file-form file-form">
                <div class="form-group required">
                    <label for="file" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.file') }}</label>
                    <div class="col-sm-6">
                        <input
                            type="file"
                            name="file"
                            class="input-border-r-12 form-control doc-upload-input js-doc-input"
                            value="{{ old('file', empty($post['file']) || !$edit ? '' : $post['file']) }}"
                        >
                        @if (isset($errors) && $errors->has('file'))
                            <span class="error">{{ $errors->first('file') }}</span>
                        @endif
                    </div>
                    <div class="col-sm-3 text-right">
                        <button type="submit" class="btn btn-custom js-doc-btn">{{ __('custom.select_file') }}</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.notification_email') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="file_nt_email"
                            value="{{ old('file_nt_email', empty($post['file_nt_email']) || !$edit ? '' : $post['file_nt_email']) }}"
                        >
                        <span class="error">{{ $errors->first('file_nt_email') }}</span>
                    </div>
                </div>
                <div class="form-group col-md-9 col-md-offset-3">
                    <input
                        type="submit"
                        class="btn btn-primary test-btn pull-right"
                        name="test_file"
                        value="{{ __('custom.check') }}"
                    >
                </div>
                <h2>{{ __('custom.target') }}:</h2><br>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.api_key') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="file_api_key"
                            value="{{ old('file_api_key', empty($post['file_api_key']) || !$edit ? '' : $post['file_api_key']) }}"
                        >
                        <span class="error">{{ $errors->first('file_api_key') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.resource_key') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="file_rs_key"
                            value="{{ old('file_rs_key', empty($post['file_rs_key']) || !$edit ? '' : $post['file_rs_key']) }}"
                        >
                        <span class="error">{{ $errors->first('file_rs_key') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.refresh_freq') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control freq-number"
                            name="file_upl_freq"
                            value="{{ old('file_upl_freq', empty($post['file_upl_freq']) || !$edit ? '' : $post['file_upl_freq']) }}"
                        >
                        <select
                            name="file_upl_freq_type"
                            class="js-select form-control"
                        >
                            @foreach ($freqTypes as $freqTypeId => $freqType)
                                <option
                                    value="{{ $freqTypeId }}"
                                    {{
                                        $freqTypeId == old('file_upl_freq_type', empty($post['file_upl_freq_type']) || !$edit
                                            ? ''
                                            : $post['file_upl_freq_type']) ? 'selected' : ''
                                    }}
                                >{{ $freqType }}</option>
                            @endforeach
                        </select>
                        <div><span class="error">{{ $errors->first('file_upl_freq') }}</span></div>
                    </div>
                </div>
                <div class="form-group col-md-9 col-md-offset-3">
                    @if (old('edit', $edit))
                        <input
                            type="submit"
                            class="btn btn-primary save-btn"
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
                </div>
            </div>
            @if (!empty($files))
                @foreach ($files as $file)
                    @foreach ($file->dataQueries as $query)
                        <div class="data-query">
                            <span>{{ $file->connection_name .'('. $query->name .')' }}</span>
                            <input
                                type="submit"
                                class="btn btn-primary pull-right"
                                name="delete_file[{{ $file->id }}]"
                                value="{{ uctrans('custom.delete') }}"
                                data-confirm="{{ __('custom.remove_data') }}"
                            >
                            <input
                                type="submit"
                                class="btn btn-primary pull-right"
                                name="file_conn_id[{{ $file->id }}]"
                                value="{{ uctrans('custom.edit') }}"
                            >
                            <input
                                type="submit"
                                class="btn btn-primary save-btn pull-right"
                                name="send_query[{{ $query->id }}]"
                                value="{{ uctrans('custom.send') }}"
                            >
                        </div>
                    @endforeach
                @endforeach
            @endif
        @endif
    </div>
</form>
@endsection
