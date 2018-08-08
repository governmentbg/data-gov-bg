
@for ($i = 1; $i <= 3; $i++)
    @foreach (Lang::getInstance()->getActive() as $key => $active)
        <div
            class="
                form-group
                row
                {{ !empty($field['required']) && !$key ? 'required' : '' }}
                {{ isset($result->errors->{ $field['name'] }) ? 'has-error' : '' }}
            "
        >
            <div class="col-xs-12">{{ !$key ?  __('custom.additional_field')  : '' }}</div>
            <div class="col-lg-12">
                <div class="col-sm-12 col-xs-12 p-r-none">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <label class="col-lg-4 col-md-6 col-xs-12 col-form-label">
                                {{ !$key ? utrans($field['label'][0]) : '' }}
                            </label>
                            <div class="col-lg-8 col-md-6 col-sm-6 col-sm-12 custom-trans-filed">
                                <div class="input-group">
                                    @if (isset($model) && empty(old($field['name'])))
                                        <input
                                            value="{{ isset($model[$i - 1]) ? $model[$i - 1]->translate($active['locale'], $active['locale'])->{ $field['val'][0] } : null }}"
                                            name="{{ $field['name'] }}[{{ $i }}][label][{{ $active['locale'] }}]"
                                            class="input-border-r-12 form-control"
                                        >
                                    @elseif (
                                        is_array(old($field['name'])[$i]['label'])
                                        && !empty(old($field['name'])[$i]['label'][$active['locale']])
                                    )
                                        <input
                                            value="{{ old($field['name'])[$i]['label'][$active['locale']] }}"
                                            name="{{ $field['name'] }}[{{ $i }}][label][{{ $active['locale'] }}]"
                                            class="input-border-r-12 form-control"
                                        >
                                    @else
                                        <input
                                            name="{{ $field['name'] }}[{{ $i }}][label][{{ $active['locale'] }}]"
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
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <label class="col-lg-4 col-md-6 col-xs-12 col-form-label">{{ !$key ? utrans($field['label'][1]) : '' }}</label>
                            <div class="col-lg-8 col-md-6 col-sm-6 col-sm-12 custom-trans-filed">
                                <div class="input-group">
                                    @if (isset($model) && empty(old($field['name'])))
                                        <input
                                            value="{{ isset($model[$i - 1]) ? $model[$i - 1]->translate($active['locale'], $active['locale'])->{ $field['val'][1]} : null }}"
                                            name="{{ $field['name'] }}[{{ $i }}][{{ $field['val'][1] }}][{{ $active['locale'] }}]"
                                            class="input-border-r-12 form-control"
                                        >
                                    @elseif (
                                        is_array(old($field['name'])[$i][$field['val'][1]])
                                        && !empty(old($field['name'])[$i][$field['val'][1]][$active['locale']])
                                    )
                                        <input
                                            value="{{ old($field['name'])[$i][$field['val'][1]][$active['locale']] }}"
                                            name="{{ $field['name'] }}[{{ $i }}][{{ $field['val'][1] }}][{{ $active['locale'] }}]"
                                            class="input-border-r-12 form-control"
                                        >
                                    @else
                                        <input
                                            name="{{ $field['name'] }}[{{ $i }}][{{ $field['val'][1] }}][{{ $active['locale'] }}]"
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
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endfor
