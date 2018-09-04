<div class="row m-t-md">
    <div class="col-xs-12 sidenav m-b-lg">
        <span class="my-profile m-b-lg m-l-sm">{{ __('custom.admin_profile') }}</span>
    </div>
    <div class="col-xs-12">
        <div class="filter-content">
            <div class="col-md-12">
                <div class="row">
                    <ul class="nav filter-type right-border js-nav">
                        <li>
                            <a
                                class="{{ $view == 'newsfeed' ? 'active' : '' }}"
                                href="{{ url('/user') }}"
                            >{{ __('custom.notifications') }}</a>
                        </li>
                        <li>
                            <!-- if there is resource with signal -->
                            @if (isset($hasReported))
                                <div class="col-xs-12 text-center exclamation-sign">
                                    <img src="{{ asset('img/reported.svg') }}">
                                </div>
                            @endif
                            <a
                                class="{{ $view == 'dataset' ? 'active' : '' }}"
                                href="{{ url('/admin/datasets') }}"
                            >{{ __('custom.my_data') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'group' ? 'active' : '' }}"
                                href="{{ url('/admin/groups') }}"
                            >{{ trans_choice(__('custom.groups'), 2) }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'organisation' ? 'active' : '' }}"
                                href="{{ url('/admin/organisations') }}"
                            >{{ trans_choice(__('custom.organisations'), 2) }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'users' ? 'active' : '' }}"
                                href="{{ url('/admin/users') }}"
                            >{{ trans_choice(__('custom.users'), 2) }}</a>
                        <li>
                            <a
                                class="{{ $view == 'createProfile' ? 'active' : '' }}"
                                href="{{ url('/admin/users/create') }}"
                            >{{ __('custom.create_profile') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'statsAnalytics' ? 'active' : '' }}"
                                href="#"
                            >{{ __('custom.stats_analytics') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'setting' ? 'active' : '' }}"
                                href="{{ url('/user/settings') }}"
                            >{{ __('custom.settings') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'topicsCategories' ? 'active' : '' }}"
                                href="#"
                            >{{ __('custom.topics_categories') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'topicsSubtopics' ? 'active' : '' }}"
                                href="#"
                            >{{ __('custom.topics_subtopics') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'languages' ? 'active' : '' }}"
                                href="{{ url('/admin/languages') }}"
                            >{{ __('custom.languages') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'termsConditions' ? 'active' : '' }}"
                                href="{{ url('/admin/terms-of-use/list') }}"
                            >{{ ultrans('custom.terms_and_conditions') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'termsConditionsReq' ? 'active' : '' }}"
                                href="{{ url('/admin/terms-of-use-request/list') }}"
                            >{{ ultrans('custom.terms_and_conditions_req') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'actionsHistory' ? 'active' : '' }}"
                                href="#"
                            >{{ __('custom.actions_history') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'loginsHistory' ? 'active' : '' }}"
                                href="#"
                            >{{ __('custom.logins_history') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'signals' ? 'active' : '' }}"
                                href="#"
                            >{{ __('custom.signals') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'manageRoles' ? 'active' : '' }}"
                                href="{{ url('/admin/roles') }}"
                            >{{ __('custom.manage_roles') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'documents' ? 'active' : '' }}"
                                href="{{ url('/admin/documents/list') }}"
                            >{{ ultrans('custom.documents') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@if (isset($pagination) && $view !== 'group' && $view !== 'organisation')
    <div class="row m-t-md">
        <div class="col-xs-12 text-center">
            {{ $pagination->render() }}
        </div>
    </div>
@endif
