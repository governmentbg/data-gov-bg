@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'manageRoles'])

    <h3 class="m-b-xl">{{ __('custom.edit_rights') }}</h3>
    @if ($roleName)
        <h3 class="m-b-xl">{{ $roleName[0]->name }}</h3>
    @endif
    <form method="POST" class="form-horizontal m-b-xl">
        {{ csrf_field() }}
        <div class="table-responsive opn-tbl text-center">
            <table class="table">
                <thead>
                    <th colspan="2">{{ __('custom.rights') }}</th>
                    <th>{{ __('custom.about') }}</th>
                </thead>
                <tbody>
                    @foreach ($modules as $i => $module)
                        <tr>
                            <td>{{ __('custom.' . $module) }}</td>
                            <td>
                                @foreach ($rightTypes as $key => $rightType)
                                    <label class="m-l-r-xs">
                                        {{ $rightType }}
                                        <div class="js-check js-uncheck inline-block m-l-r-xs">
                                            <input
                                                type="radio"
                                                name="rights[{{ $i }}][right]"
                                                value="{{ $key }}"
                                                {{
                                                    isset($rights[$i]['right'])
                                                    && $rights[$i]['right'] == $key
                                                    ? 'checked'
                                                    : null
                                                }}
                                            >
                                        </div>
                                    </label>
                                @endforeach
                            </td>
                            <td class="shrink">
                                <label class="m-l-r-xs">
                                    {{ __('custom.for_own_records') }}
                                    <div class="js-check inline-block m-l-r-xs">
                                        <input
                                            type="checkbox"
                                            name="rights[{{ $i }}][limit_to_own_data]"
                                            value="1"
                                            {{ empty($rights[$i]['limit_to_own_data']) ? null : 'checked' }}
                                        >
                                    </div>
                                </label>
                                <label class="m-l-r-xs">
                                    {{ __('custom.for_api') }}
                                    <div class="js-check inline-block m-l-r-xs">
                                        <input
                                            type="checkbox"
                                            name="rights[{{ $i }}][api]"
                                            value="1"
                                            {{ empty($rights[$i]['api']) ? null : 'checked' }}
                                        >
                                    </div>
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="form-group row m-t-md">
            <div class="col-lg-12 text-right">
                <button type="submit" name="edit" class="btn btn-primary">{{ uctrans('custom.save') }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
