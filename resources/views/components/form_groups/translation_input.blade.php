@foreach (Lang::getInstance()->getActive() as $key => $active)
    <div
            class="
            form-group
            row
            {{ !empty($field['required']) && !$key ? 'required' : '' }}
            {{ isset($result->errors->{ $field['name'] }) ? 'has-error' : '' }}
                    "
    >
        <label for="name" class="col-sm-3 col-xs-12 col-form-label">{{ !$key ? uctrans($field['label']) . ':' : '' }}</label>
        <div class="col-sm-9">
            <div class="input-group">
                @if (isset($model) && empty(old($field['name'])))
                    <input
                            value="{{ $model->translate($active['locale'], $active['locale'])->{ $field['name']} }}"
                            name="{{ $field['name'] }}[{{ $active['locale'] }}]"
                            class="input-border-r-12 form-control"
                            @if($model instanceof \App\Organisation && !\Auth::user()->is_admin) disabled="disabled" @endif
                    >
                @elseif (is_array(old($field['name'])) && !empty(old($field['name'])[$active['locale']]))
                    <input
                            value="{{ old($field['name'])[$active['locale']] }}"
                            name="{{ $field['name'] }}[{{ $active['locale'] }}]"
                            class="input-border-r-12 form-control"
                    >
                @else
                    <input
                            name="{{ $field['name'] }}[{{ $active['locale'] }}]"
                            class="input-border-r-12 form-control"
                            value=""
                    >
                @endif
                <span class="input-group-addon">
                    <span class="flag-icon flag-icon-{{ locale_to_flag($active['locale']) }}"></span>
                </span>
            </div>
            @if (isset($result->errors->{ $field['name'] }))
                <span class="error">{{ $result->errors->{ $field['name'] }[0] }}</span>
            @elseif (isset($errors) && $errors->has($field['name'] .'.'. $active['locale']))
                <span class="error">{{ $errors->first($field['name'] .'.'. $active['locale']) }}</span>
            @elseif (isset($errors) && $errors->has('data.'. $field['name'] .'.'. $active['locale']))
                <span class="error">{{ $errors->first('data.'. $field['name'] .'.'. $active['locale']) }}</span>
            @elseif (isset($errors) && $errors->has($field['name']))
                <span class="error">{{ $errors->first($field['name']) }}</span>
            @endif
        </div>
    </div>
@endforeach