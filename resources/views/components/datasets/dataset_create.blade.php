<div class="col-xs-12 sidenav m-t-lg m-b-lg">
    <span class="my-profile m-l-sm">{{uctrans('custom.dataset_add')}}</span>
</div>
<div class="col-xs-12 m-t-lg">
    <p class='req-fields'>{{ __('custom.all_fields_required') }}</p>
    <form method="POST">
        {{ csrf_field() }}
        <div class="form-group row">
            <label
                for="identifier"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ __('custom.unique_identificator') }}:</label>
            <div class="col-sm-9">
                <input
                    id="identifier"
                    name="uri"
                    type="text"
                    class="input-border-r-12 form-control"
                    value="{{ old('uri') }}"
                    placeholder="{{ __('custom.unique_identificator') }}"
                >
                <span class="error">{{ $errors->first('uri') }}</span>
            </div>
        </div>

        <div class="form-group row required">
            <label
                for="theme"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ __('custom.main_topic') }}:</label>
            <div class="col-sm-9">
                <select
                    id="theme"
                    name="category_id"
                    class="js-select form-control"
                    data-placeholder="{{ __('custom.select_main_topic') }}"
                >
                    <option></option>
                    @foreach ($categories as $id => $category)
                        <option
                            value="{{ $id }}"
                            {{ $id == old('category_id') ? 'selected' : '' }}
                        >{{ $category }}</option>
                    @endforeach
                </select>
                <span class="error">{{ $errors->first('category_id') }}</span>
            </div>
        </div>

        @foreach ($fields as $field)
            @if ($field['view'] == 'translation')
                @include(
                    'components.form_groups.translation_input',
                    ['field' => $field]
                )
            @elseif ($field['view'] == 'translation_txt')
                @include(
                    'components.form_groups.translation_textarea',
                    ['field' => $field]
                )
            @endif
        @endforeach

        @include('components.form_groups.tags')

        <div class="form-group row">
            <label
                for="terms-of-use"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ __('custom.terms_and_conditions') }}:</label>
            <div class="{{ empty($admin) ? 'col-sm-6' : 'col-sm-9' }}">
                <select
                    id="terms-of-use"
                    name="terms_of_use_id"
                    class="js-select form-control"
                >
                    <option value="">{{ utrans('custom.select_terms_of_use') }}</option>
                    @foreach ($termsOfUse as $id => $term)
                        <option
                            value="{{ $id }}"
                            {{ $id == old('terms_of_use_id') ? 'selected' : '' }}
                        >{{ $term }}</option>
                    @endforeach
                </select>
                <span class="error">{{ $errors->first('terms_of_use_id') }}</span>
            </div>

            @if (empty($admin))
                <div class="col-sm-3 text-right add-terms">
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-toggle="modal"
                        data-target="#add-license"
                    >{{ __('custom.new_terms_and_conditions') }}</button>
                </div>
            @endif
        </div>

        <div class="form-group row">
            <label
                for="organisation"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ utrans('custom.organisations', 1) }}:</label>
            <div class="col-sm-9">
                <select
                    id="organisation"
                    name="org_id"
                    class="js-autocomplete form-control"
                >
                    <option value="">{{ utrans('custom.select_org') }}</option>
                    @foreach ($organisations as $id => $org)
                        <option
                            value="{{ $id }}"
                            {{ $id == old('org_id') ? 'selected' : '' }}
                        >{{ $org }}</option>
                    @endforeach
                </select>
                <span class="error">{{ $errors->first('org_id') }}</span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="group"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ utrans('custom.groups', 1) }}:</label>
            <div class="col-sm-9">
                <select
                    id="group"
                    name="group_id[]"
                    class="js-autocomplete form-control"
                    data-placeholder="{{ utrans('custom.groups', 1) }}"
                    multiple="multiple"
                >
                    <option></option>
                    @foreach ($groups as $id => $group)
                        <option
                            value="{{ $id }}"
                            {{ !empty(old('group_id')) && in_array($id, old('group_id')) ? 'selected' : '' }}
                        >{{ $group }}</option>
                    @endforeach
                </select>
                <span class="error">{{ $errors->first('group_id') }}</span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="visibility"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ utrans('custom.visibility') }}:</label>
            <div class="col-sm-9">
                <select
                    id="visibility"
                    name="visibility"
                    class="js-select form-control"
                    data-placeholder="{{ utrans('custom.select_visibility') }}"
                >
                    <option></option>
                    @foreach ($visibilityOpt as $id => $visOpt)
                        <option
                            value="{{ $id }}"
                            {{ $id == old('visibility') ? 'selected' : '' }}
                        >{{ $visOpt }}</option>
                    @endforeach
                </select>
                <span class="error">{{ $errors->first('visibility') }}</span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="source"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ utrans('custom.source') }}:</label>
            <div class="col-sm-9">
                <input
                    id="source"
                    name="source"
                    class="input-border-r-12 form-control"
                    value="{{ old('source') }}"
                    type="text"
                    placeholder="{{ __('custom.source') }}"
                >
                <span class="error">{{ $errors->first('source') }}</span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="version"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ utrans('custom.version') }}:</label>
            <div class="col-sm-9">
                <input
                    id="version"
                    name="version"
                    class="input-border-r-12 form-control"
                    value="{{ old('version') }}"
                    type="text"
                    placeholder="{{ __('custom.version') }}"
                >
                <span class="error">{{ $errors->first('version') }}</span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="author"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ utrans('custom.author') }}:</label>
            <div class="col-sm-9">
                <input
                    id="author"
                    name="author_name"
                    class="input-border-r-12 form-control"
                    value="{{ old('author_name') }}"
                    type="text"
                    placeholder="{{ __('custom.author') }}">
                <span class="error">{{ $errors->first('author_name') }}</span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="author-email"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ __('custom.author_email') }}:</label>
            <div class="col-sm-9">
                <input
                    id="author-email"
                    name="author_email"
                    class="input-border-r-12 form-control"
                    value="{{ old('author_email') }}"
                    type="email"
                    placeholder="{{ __('custom.author_email') }}"
                >
                <span class="error">{{ $errors->first('author_email') }}</span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="support"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ utrans('custom.contacts') }}:</label>
            <div class="col-sm-9">
                <input
                    id="support"
                    name="support_name"
                    class="input-border-r-12 form-control"
                    value="{{ old('support_name') }}"
                    type="text"
                    placeholder="{{ trans_choice(__('custom.contacts'), 1) }}"
                >
                <span class="error">{{ $errors->first('support_name') }}</span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="support-email"
                class="col-sm-3 col-xs-12 col-form-label"
            >{{ __('custom.contact_email') }}:</label>
            <div class="col-sm-9">
                <input
                    id="support-email"
                    name="support_email"
                    class="input-border-r-12 form-control"
                    value="{{ old('support_email') }}"
                    type="email"
                    placeholder="{{ __('custom.contact_email') }}"
                >
                <span class="error">{{ $errors->first('support_email') }}</span>
            </div>
        </div>

        @foreach ($fields as $field)
            @if ($field['view'] == 'translation_custom')
                @include(
                    'components.form_groups.translation_custom_fields',
                    ['field' => $field]
                )
            @endif
        @endforeach

        <div class="form-group row">
            <div class="col-xs-12 text-right mng-btns">
                <button
                    name="back"
                    class="btn btn-primary"
                >{{ uctrans('custom.close') }}</button>
                @if (!empty($admin) || !empty($buttons['add']))
                    <button
                        type="submit"
                        name="add_resource"
                        class="btn btn-primary"
                    >{{ uctrans('custom.add_resource') }}</button>
                @endif
                <button
                    type="submit"
                    name="create"
                    class="btn btn-primary"
                >{{ uctrans('custom.save') }}</button>
            </div>
        </div>
    </form>

    @if (empty($admin))
        @include('partials.add-license')
    @endif
</div>
