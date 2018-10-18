
<div class="col-sm-3 sidenav p-l-r-none hidden-xs">
    @include('partials.admin-sidebar-body')
</div>

<div class="navbar-header hidden-lg hidden-md hidden-sm p-l-r-none sidebar-open">
    <button type="button" class="navbar-toggle btn-sidebar pull-left" data-toggle="collapse" data-target="#sidebar-wrapper">
        <span><i class="fa fa-angle-right"></i></span>
    </button>
</div>

<div class="sidenav js-sidenav p-l-r-none hidden-lg hidden-md hidden-sm" id="sidebar-wrapper">
    <ul class="nav">
        <li class="js-show-submenu">
            <button
                type="button"
                class="navbar-toggle btn-sidebar pull-right hidden-lg hidden-md hidden-sm"
                data-toggle="collapse"
                data-target="#sidebar-wrapper"
            ><span><i class="fa fa-angle-left"></i></span></button>
        </li>
    </ul>

    @include('partials.admin-sidebar-body')
</div>