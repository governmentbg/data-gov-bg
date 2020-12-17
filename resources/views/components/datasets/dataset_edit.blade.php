<div class="{{ isset($fromOrg) || isset($group) ? 'col-sm-9' : 'col-sm-12' }} sidenav m-t-lg">
    <span class="my-profile m-l-sm">{{uctrans('custom.dataset_edit')}}</span>
</div>
@php
    $root = empty($admin) ? 'user' : 'admin';
    $statusError = $errors->first('status');
@endphp
<div class="{{ isset($fromOrg) || isset($group) ? 'col-sm-9' : 'col-sm-12' }} m-t-lg p-l-r-none">
    <p class='req-fields'>{{ __('custom.all_fields_required') }}</p>
    @if (!empty($statusError))
        <div class="m-t-md">
            <div class="flash-message">
                <p class="alert alert-danger">
                    {{ $statusError }}
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </p>
            </div>
        </div>
    @endif
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
                    value="{{ empty(old('uri')) ? $dataSet->uri : old('uri') }}"
                    placeholder="{{ __('custom.unique_identificator') }}"
                    readonly
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
                            {{
                                empty(old('category_id'))
                                && $id == $dataSet->category_id
                                || $id == old('category_id')
                                ? 'selected' : null
                            }}
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
                    ['field' => $field, 'model' => $dataSet]
                )
            @elseif ($field['view'] == 'translation_txt')
                @include(
                    'components.form_groups.translation_textarea',
                    ['field' => $field, 'model' => $dataSet]
                )
            @endif
        @endforeach

        @include('components.form_groups.tags', ['model' => $tagModel])

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
                            {{
                                empty(old('terms_of_use_id'))
                                && $id == $dataSet->terms_of_use_id
                                || $id == old('terms_of_use_id')
                                ? 'selected' : null
                            }}
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

        <div class="form-group row {{ isset($orgRequired) && $orgRequired ? 'required' : '' }}">
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
                            {{
                                empty(old('org_id'))
                                && $id == $dataSet->org_id
                                || $id == old('org_id')
                                ? 'selected' : null
                            }}
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
                    @foreach ($groups as $id => $groupName)
                        <option
                            value="{{ $id }}"
                            {{
                                !empty(old('group_id'))
                                && in_array($id, old('group_id'))
                                || !empty($setGroups)
                                && in_array($id, $setGroups)
                                ? 'selected' : ''
                            }}
                        >{{ $groupName }}</option>
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
                            {{
                                empty(old('visibility'))
                                && $id == $dataSet->visibility
                                || $id == old('visibility')
                                ? 'selected' : null
                            }}
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
                    value="{{ empty(old('source')) ? $dataSet->source : old('source') }}"
                    type="text"
                    placeholder="{{ __('custom.source') }}"
                >
                <span class="error">{{ $errors->first('source') }}</span>
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
                    value="{{ empty(old('author_name')) ? $dataSet->author_name : old('author_name') }}"
                    type="text"
                    placeholder="{{ __('custom.author') }}"
                >
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
                    value="{{ empty(old('author_email')) ? $dataSet->author_email : old('author_email') }}"
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
                    value="{{ empty(old('support_name')) ? $dataSet->support_name : old('support_name') }}"
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
                    value="{{ empty(old('support_email')) ? $dataSet->support_email : old('support_email') }}"
                    type="email"
                    placeholder="{{ __('custom.contact_email') }}"
                >
                <span class="error">{{ $errors->first('support_email') }}</span>
            </div>
        </div>

        @if (!empty($admin))
            <div class="form-group row">
                <label
                    for="forum-link"
                    class="col-sm-3 col-xs-12 col-form-label"
                >{{ __('custom.forum_link') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="forum-link"
                        name="forum_link"
                        class="input-border-r-12 form-control"
                        value="{{ empty(old('forum_link')) ? $dataSet->forum_link : old('forum_link') }}"
                        type="text"
                        placeholder="{{ __('custom.forum_link') }}"
                    >
                    <span class="error">{{ $errors->first('forum_link') }}</span>
                </div>
            </div>
        @endif

        <div class="form-group row">
            <label for="trusted" class="col-lg-3 col-md-5 col-xs-8 col-form-label">{{ uctrans('custom.trusted') }}:</label>
            <div class="col-lg-9 col-md-7 col-xs-4">
                @if (Auth::user()->is_admin)
                    <div class="js-check">
                        <input
                            type="checkbox"
                            name="trusted"
                            value="1"
                            {{ !empty($dataSet->trusted) ? 'checked' : '' }}
                        >
                    </div>
                @else
                    {{ !empty($dataSet->trusted) ? uctrans('custom.yes') : uctrans('custom.no') }}
                @endif
            </div>
        </div>

        @foreach ($fields as $field)
            @if ($field['view'] == 'translation_custom')
                @include(
                    'components.form_groups.translation_custom_fields',
                    ['field' => $field, 'model' => $withModel]
                )
            @endif
        @endforeach

        <div class="form-group row">
            <div class="col-xs-12 text-right mng-btns">
                <a
                    class="btn btn-primary"
                        @if (isset($group))
                            href="{{ url('/'. $root .'/groups/'. $group->uri .'/dataset/resource/create/'. $dataSet->uri) }}"
                        @elseif (isset($fromOrg))
                            href="{{ url('/'. $root .'/organisations/'. $fromOrg->uri .'/dataset/resource/create/'. $dataSet->uri) }}"
                        @else
                            href="{{ url('/'. $root .'/dataset/resource/create/'. $dataSet->uri) }}"
                        @endif
                >{{ uctrans('custom.add_resource') }}</a>
                @if ($hasResources && Auth::user()->is_admin)
                    <button
                        type="submit"
                        name="publish"
                        class="btn btn-primary"
                    >{{ uctrans('custom.publish') }}</button>
                @endif
                @php
                    if (isset($fromOrg)):
                        $close = url($root .'/organisations/datasets/'. $fromOrg->uri);
                        $preview = route($root .'OrgDatasetView', ['uri' => $dataSet->uri]);
                    elseif (isset($group)):
                        $close = url($root .'/groups/datasets/'. $group->uri);
                        $preview = route($root .'GroupDatasetView', ['uri' => $dataSet->uri, 'grpUri' => $group->uri]);
                    else:
                        $close = url($root .'/datasets/');
                        $preview = route($root .'DatasetView', ['uri' => $dataSet->uri]);
                    endif;
                @endphp
                <a
                    type="button"
                    class="btn btn-primary"
                    href="{{ $preview }}"
                >{{ uctrans('custom.preview') }}</a>
                <a
                    type="button"
                    class="btn btn-primary"
                    href="{{ $close }}"
                >
                    {{ uctrans('custom.close') }}
                </a>
                <button type="submit" name="save" class="btn btn-primary">{{ uctrans('custom.save') }}</button>
            </div>
        </div>
    </form>

    @if (empty($admin))
        @include('partials.add-license')
    @endif
</div>
