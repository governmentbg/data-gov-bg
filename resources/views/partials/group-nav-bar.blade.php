@php $isAdmin = \App\Role::isAdmin() @endphp
<div class="row">
    @if (!$isAdmin)
        <div class="col-sm-3 col-xs-12 sidenav">
            <span class="my-organisation m-b-lg m-l-sm"></span>
        </div>
    @endif
    <div class="{{ !$isAdmin ? 'col-sm-9' : 'p-l-lg' }} col-xs-12">
        <div class="filter-content org-nav-bar">
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav filter-type right-border">
                        <li>
                            @if ($isAdmin)
                                <a
                                    class="{{ $view == 'view' ? 'active' : null }}"
                                    href="{{ url('/admin/groups/view/'. $group->uri) }}"
                                >{{ ultrans('custom.groups') }}</a>
                            @else
                                <a
                                    class="{{ $view == 'view' ? 'active' : null }}"
                                    href="{{ url('/user/groups/view/'. $group->uri) }}"
                                >{{ ultrans('custom.groups') }}</a>
                            @endif
                        </li>
                        <li>
                            @if ($isAdmin)
                                <a
                                    class="{{ $view == 'members' ? 'active' : null }}"
                                    href="{{ url('/admin/groups/members/'. $group->uri) }}"
                                >{{ ultrans('custom.members') }}</a>
                            @else
                                <a
                                    class="{{ $view == 'members' ? 'active' : null }}"
                                    href="{{ url('/user/groups/members/'. $group->uri) }}"
                                >{{ ultrans('custom.members') }}</a>
                            @endif
                        </li>
                        <li>
                            @if ($isAdmin)
                                <a
                                    class="{{ $view == 'dataset' ? 'active' : null }}"
                                    href="{{ url('/admin/groups/datasets/'. $group->uri) }}"
                                >{{ ultrans('custom.datasets') }}</a>
                            @else
                                <a
                                    class="{{ $view == 'dataset' ? 'active' : null }}"
                                    href="{{ url('/user/groups/datasets/'. $group->uri) }}"
                                >{{ ultrans('custom.datasets') }}</a>
                            @endif
                        </li>
                        @if ($isAdmin)
                            <li>
                                <a
                                    class="{{ $view == 'deletedDatasets' ? 'active' : null }}"
                                    href="{{ url('admin/groups/deletedDatasets/'. $group->uri) }}"
                                >{{ ultrans('custom.deleted_datasets') }}</a>
                            </li>
                        @endif
                        <li>
                            @if ($isAdmin)
                                <a
                                    class="{{ $view == 'chronology' ? 'active' : null }}"
                                    href="{{ url('/admin/groups/chronology/'. $group->uri) }}"
                                >{{ ultrans('custom.chronology') }}</a>
                            @else
                                <a
                                    class="{{ $view == 'chronology' ? 'active' : null }}"
                                    href="{{ url('/user/groups/chronology/'. $group->uri) }}"
                                >{{ ultrans('custom.chronology') }}</a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
