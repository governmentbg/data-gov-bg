@extends('layouts.app')

@section('content')
<form method="post" class="form-horisontal config-form">
    {{ csrf_field() }}
    <div class="container p-l-r-none">
        @include('partials.alerts-bar')

        @if (!empty($post['edit_dbms']))
            <input
                type="hidden"
                name="edit_dbms"
                value="{{ $post['edit_dbms'] }}"
            >
        @endif

        @foreach ($dbs as $db)
            <div class="data-query">
                <span>{{ $db->connection_name }}</span>
                <input
                    type="submit"
                    class="btn btn-primary pull-right"
                    name="delete_dbms[{{ $db->id }}]"
                    value="{{ uctrans('custom.delete') }}"
                    data-confirm="{{ __('custom.remove_data') }}"
                >
                <input
                    type="submit"
                    class="btn btn-primary pull-right save-btn"
                    name="edit_dbms[{{ $db->id }}]"
                    value="{{ uctrans('custom.edit') }}"
                >
            </div>
        @endforeach

        <div class="js-dbms-form dbms-form">
            <div class="form-group required">
                <label class="col-md-3">{{ __('custom.title') }}:</label>
                <div class="col-md-9">
                    <input
                        type="text"
                        class="form-control"
                        name="connection_name"
                        value="{{ request('connection_name', empty($post['connection_name']) ? '' : $post['connection_name']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('connection_name') }}</span>
                </div>
            </div>
            <div class="form-group required">
                <label class="col-md-3">{{ __('custom.host') }}:</label>
                <div class="col-md-9">
                    <input
                        class="form-control"
                        name="source_db_host"
                        value="{{ request('source_db_host', empty($post['source_db_host']) ? '' : $post['source_db_host']) }}"
                        placeholder="127.0.0.1:3306"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('source_db_host') }}</span>
                </div>
            </div>
            <div class="form-group required">
                <label class="col-md-3">{{ __('custom.user_name') }}:</label>
                <div class="col-md-9">
                    <input
                        class="form-control"
                        name="source_db_user"
                        value="{{ request('source_db_user', empty($post['source_db_user']) ? '' : $post['source_db_user']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('source_db_user') }}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3">{{ __('custom.password') }}:</label>
                <div class="col-md-9">
                    <input
                        class="form-control"
                        name="source_db_pass"
                        value="{{ request('source_db_pass', empty($post['source_db_pass']) ? '' : $post['source_db_pass']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('source_db_pass') }}</span>
                </div>
            </div>
            <div class="form-group required">
                <label class="col-md-3">{{ __('custom.db_name') }}:</label>
                <div class="col-md-9">
                    <input
                        class="form-control"
                        name="source_db_name"
                        value="{{ request('source_db_name', empty($post['source_db_name']) ? '' : $post['source_db_name']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('source_db_name') }}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3">{{ __('custom.notification_email') }}:</label>
                <div class="col-md-9">
                    <input
                        class="form-control"
                        name="notification_email"
                        value="{{ request('notification_email', empty($post['notification_email']) ? '' : $post['notification_email']) }}"
                    >
                    <span class="error">{{ empty($errors) ? null : $errors->first('notification_email') }}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3"></label>
                <div class="col-md-9">
                    <textarea
                        type="text"
                        class="input-border-r-12 form-control"
                        name="test_query"
                    >{{ request('test_query', empty($post['test_query']) ? 'SELECT * FROM ___' : $post['test_query']) }}</textarea>
                    <span class="error">{{ empty($errors) ? null : $errors->first('test_query') }}</span>
                </div>
            </div>
            <div class="form-group col-md-9 col-md-offset-3">
                <input
                    type="submit"
                    class="btn btn-primary test-btn save-btn"
                    name="test_conn"
                    value="{{ __('custom.test_connection') }}"
                >
                <input
                    type="submit"
                    class="btn btn-primary save-btn pull-right"
                    name="save_conn"
                    value="{{ uctrans('custom.save') }}"
                >
                @if (!empty($post['edit_dbms']))
                    <input
                        type="submit"
                        class="btn btn-primary save-btn m-r-sm pull-right"
                        name="new_conn"
                        value="{{ uctrans('custom.new') }}"
                    >
                @endif
            </div>
        </div>

        @if ($foundData === [])
            <p class="alert alert-danger">
                {{ __('custom.query_fail') }}
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </p>
        @elseif (!empty($foundData))
            <div class="m-t-md m-b-md js-show-on-load js-data-table overflow-hidden">
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
                <div class="align-right">
                    <input
                        type="button"
                        class="btn btn-primary pull-right js-hide-button save-btn"
                        data-target=".js-data-table"
                        value="{{ uctrans('custom.close') }}"
                    >
                </div>
            </div>
        @endif

        @if (!empty($post['edit_dbms']))
            <div class="js-query-form query-form">
                <h2>{{ __('custom.source') }}:</h2><br>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.title') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="name"
                            value="{{ request('name', empty($post['name']) ? '' : $post['name']) }}"
                        >
                        <span class="error">{{ empty($errors) ? null : $errors->first('name') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.query') }}:</label>
                    <div class="col-md-9">
                        <textarea
                            type="text"
                            class="input-border-r-12 form-control"
                            name="query"
                        >{{ request('query', empty($post['query']) ? 'SELECT * FROM ___' : $post['query']) }}</textarea>
                        <span class="error">{{ empty($errors) ? null : $errors->first('query') }}</span>
                    </div>
                </div>
                <h2>{{ __('custom.target') }}:</h2><br>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.api_key') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="api_key"
                            value="{{ request('api_key', empty($post['api_key']) ? '' : $post['api_key']) }}"
                        >
                        <span class="error">{{ empty($errors) ? null : $errors->first('api_key') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.resource_key') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control"
                            name="resource_key"
                            value="{{ request('resource_key', empty($post['resource_key']) ? '' : $post['resource_key']) }}"
                        >
                        <span class="error">{{ empty($errors) ? null : $errors->first('resource_key') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3">{{ __('custom.refresh_freq') }}:</label>
                    <div class="col-md-9">
                        <input
                            class="form-control freq-number"
                            name="upl_freq"
                            value="{{ request('upl_freq', empty($post['upl_freq']) ? '' : $post['upl_freq']) }}"
                        >
                        <select
                            name="upl_freq_type"
                            class="js-select form-control"
                        >
                            @foreach ($freqTypes as $freqTypeId => $freqType)
                                <option
                                    value="{{ $freqTypeId }}"
                                    {{
                                        $freqTypeId == request('upl_freq_type', empty($post['upl_freq_type'])
                                        ? ''
                                        : $post['upl_freq_type']) ? 'selected' : ''
                                    }}
                                >{{ $freqType }}</option>
                            @endforeach
                        </select>
                        <div><span class="error">{{ empty($errors) ? null : $errors->first('upl_freq') }}</span></div>
                    </div>
                </div>
                <div class="form-group col-md-9 col-md-offset-3">
                    <input
                        type="submit"
                        class="btn btn-primary save-btn pull-right"
                        name="save_query"
                        value="{{ uctrans('custom.save') }}"
                    >
                    @if (!empty($post['query_id']))
                        <input
                            type="submit"
                            class="btn btn-primary save-btn m-r-sm pull-right"
                            name="new_query"
                            value="{{ uctrans('custom.new') }}"
                        >
                    @endif
                </div>
            </div>

            <input name="query_id" type="hidden" value="{{ empty($post['query_id']) ? '' : $post['query_id'] }}">

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
                        class="btn btn-primary pull-right save-btn"
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
    </div>
</form>
@endsection
