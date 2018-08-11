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
                            <a
                                class="{{ $view == 'view' ? 'active' : null }}"
                                href="{{ route('userOrgView', ['uri' => $organisation->uri]) }}"
                            >{{ ultrans('custom.organisation') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'members' ? 'active' : null }}"
                                href="{{ url('/user/organisations/members/'. $organisation->uri) }}"
                            >{{ ultrans('custom.members') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
