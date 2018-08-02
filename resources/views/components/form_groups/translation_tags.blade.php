@foreach (Lang::getInstance()->getActive() as $key => $active)
    <div
        class="
            form-group
            row
            {{ isset($result->errors->{ $field['name'] }) ? 'has-error' : '' }}
        "
    >
        <label for="tags" class="col-sm-3 col-xs-12 col-form-label">{{ !$key ? $field['label'] . ':' : '' }}</label>
        <div class="col-sm-9 example ">
            <div class="input-group">
                @if (isset($model) && empty(old($field['name'])))
                    <input
                        name="{{ $field['name'] }}[{{ $active['locale'] }}]"
                        class="input-border-r-12 form-control"
                        value="{{ $model->translate($active['locale'], $active['locale'])->{ $field['name'] } }}"
                        data-role="tagsinput"
                    >
                @elseif (is_array(old($field['name'])) && !empty(old($field['name'])[$active['locale']]))
                    <input
                        name="{{ $field['name'] }}[{{ $active['locale'] }}]"
                        class="input-border-r-12 form-control"
                        value="{{ old($field['name'])[$active['locale']] }}"
                        data-role="tagsinput"
                    >
                @else
                    <input
                        name="{{ $field['name'] }}[{{ $active['locale'] }}]"
                        class="input-border-r-12 form-control"
                        data-role="tagsinput"
                    >
                @endif
                <span class="input-group-addon">
                    <span class="flag-icon flag-icon-{{ locale_to_flag($active['locale']) }}"></span>
                </span>
            </div>
            @if (isset($result->errors->{ $field['name'] }))
                <span class="error">{{ $result->errors->{ $field['name'] }[0] }}</span>
            @endif
        </div>
    </div>
@endforeach