<div class="row">
    <div class="col-sm-3 col-xs-12 sidenav">
        <span class="my-organisation m-b-lg m-l-sm"></span>
    </div>
    <div class="col-sm-9 col-xs-12">
        <div class="filter-content org-nav-bar">
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav filter-type right-border">
                        <li>
                            @if (\App\Role::isAdmin())
                                <a
                                    class="{{ $view == 'view' ? 'active' : null }}"
                                    href="{{ route('adminOrgView', ['uri' => $organisation->uri]) }}"
                                >{{ ultrans('custom.organisations') }}</a>
                            @else
                                <a
                                    class="{{ $view == 'view' ? 'active' : null }}"
                                    href="{{ route('userOrgView', ['uri' => $organisation->uri]) }}"
                                >{{ ultrans('custom.organisations') }}</a>
                            @endif
                        </li>
                        <li>
                            @if (\App\Role::isAdmin())
                                <a
                                    class="{{ $view == 'members' ? 'active' : null }}"
                                    href="{{ url('/admin/organisations/members/'. $organisation->uri) }}"
                                >{{ ultrans('custom.members') }}</a>
                            @else
                                <a
                                    class="{{ $view == 'members' ? 'active' : null }}"
                                    href="{{ url('/user/organisations/members/'. $organisation->uri) }}"
                                >{{ ultrans('custom.members') }}</a>
                            @endif
                        </li>
                        <li>
                            @if (\App\Role::isAdmin())
                                <a
                                    class="{{ $view == 'members' ? 'active' : null }}"
                                    href="{{ url('/user/organisations/datasets/'. $organisation->uri) }}"
                                >{{ ultrans('custom.datasets') }}</a>
                            @else
                                <a
                                    class="{{ $view == 'members' ? 'active' : null }}"
                                    href="{{ url('/user/organisations/datasets/'. $organisation->uri) }}"
                                >{{ ultrans('custom.datasets') }}</a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
