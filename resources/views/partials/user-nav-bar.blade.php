<div class="row">
    <div class="col-sm-3 col-xs-12 sidenav">
        <span class="my-profile m-b-lg m-l-sm">Моят профил</span>
    </div>
    <div class="col-sm-9 col-xs-12">
        <div class="filter-content">
            <div class="col-md-12">
                <div class="row">
                    <ul class="nav filter-type right-border js-nav">
                        <li><a class="p-l-none" href="{{ url('/user') }}">известия</a></li>
                        <li>
                            <!-- if there is resource with signal -->
                            @if (isset($hasReported))
                                <div class="col-xs-12 text-center exclamation-sign">
                                    <img src="{{ asset('img/reported.svg') }}">
                                </div>
                            @endif
                            <a
                                class="{{ $view == 'dataset' ? 'active' : '' }}"
                                href="{{ url('/user/datasets') }}"
                            >моите данни</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'group' ? 'active' : '' }}"
                                href="{{ url('/user/userGroups') }}"
                            >групи</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'organisation' ? 'active' : '' }}"
                                href="{{ url('/user/organisations') }}"
                            >организации</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'setting' ? 'active' : '' }}"
                                href="{{ url('/user/settings') }}"
                            >настройки</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'invite' ? 'active' : '' }}"
                                href="{{ url('/user/invite') }}"
                            >покана</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
