@php $root = empty($admin) ? 'user' : 'admin'; @endphp
<div class="col-xs-12 m-t-md">
    <div class="articles">
        <div class="article m-b-md">
            <div class="m-t-lg">
                <div class="col-sm-12 col-xs-12 p-l-none">
                    <ul class="p-l-none">
                        <li>{{ __('custom.contact_support_name') }}:</li>
                        <li>{{ __('custom.version') }}: {{ $resource->version }}</li>
                        <li>{{ __('custom.last_update') }}: {{ $resource->updated_by }}</li>
                        <li>{{ __('custom.created') }}: {{ $resource->created_by }}</li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-12 p-l-none art-heading-bar">
                <div class="socialPadding">
                    <div class="social fb"><a href="#"><i class="fa fa-facebook"></i></a></div>
                    <div class="social tw"><a href="#"><i class="fa fa-twitter"></i></a></div>
                    <div class="social gp"><a href="#"><i class="fa fa-google-plus"></i></a></div>
                </div>
                <div class="sendMail p-w-sm">
                    <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                </div>
            </div>
            <h2>{{ $resource->name }}</h2>
            <p>{{ $resource->description }}</p>
        </div>

        <div class="col-sm-12 p-l-none">
            <div class="col-sm-12 m-t-lg p-l-r-none">
                @include('partials.resource-visualisation')
            </div>

            @if (!empty($admin) || !empty($buttons[$resource->uri]['delete']))
                <div class="col-xs-12 m-t-md p-l-r-none text-right">
                    <form method="POST">
                        {{ csrf_field() }}
                        <button
                            name="delete"
                            class="badge badge-pill m-b-sm del-btn"
                            data-confirm="{{ __('custom.remove_data') }}"
                        >{{ uctrans('custom.remove') }}</button>
                    </form>
                </div>
            @endif

            <!-- IF there are old versions of this article -->
            <div class="col-sm-12 pull-left m-t-md p-l-none">
                <div class="pull-left history">
                    <div>
                        <a href="#">
                            <span class="version-heading">{{ __('custom.title') }}</span>
                            <span class="version">&nbsp;&#8211;&nbsp;версия 1</span>
                        </a>
                    </div>
                    <div>
                        <a href="#">
                            <span class="version-heading">{{ __('custom.title') }}</span>
                            <span class="version">&nbsp;&#8211;&nbsp;версия 2</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
