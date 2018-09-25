@if (!empty($dataset))
    <div class="row">
        <div class="col-sm-9 col-xs-11 page-content col-sm-offset-3">
            <div class="articles">
                <div class="article col-xs-12 p-l-none">
                    <div>
                    @if (!empty($organisation))
                        <div class="col-sm-7 col-xs-12 p-l-r-none m-t-lg m-b-md">
                            <div class="col-xs-6 logo-img">
                                <a href="{{ url('/organisation/profile/'. $organisation->uri) }}" title="{{ $organisation->name }}">
                                    <img class="img-responsive" src="{{ $organisation->logo }}" alt="{{ $organisation->name }}">
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-12 col-xs-12 p-l-r-none">
                            <h3>
                                <a href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ $organisation->name }}</a>
                            </h3>
                        </div>
                    @else
                        <div class="col-sm-12 col-xs-12 p-l-r-none">
                            <div class="pull-left">
                                <h2>
                                    {{ utrans('custom.author') }}:
                                    @if (!empty($user))
                                        <a href="{{ url('/user/profile/'. $user->id) }}">
                                            {{ ($user->firstname || $user->lastname) ? trim($user->firstname .' '. $user->lastname) : $user->username }}
                                        </a>
                                    @else
                                        <span>{{ $dataset->created_by }}</span>
                                    @endif
                                </h2>
                            </div>
                        </div>
                    @endif
                    </div>
                    <div class="col-xs-12 p-l-none m-t-md m-b-md">
                        <div class="art-heading-bar row">
                            <div class="col-sm-7 col-xs-12 p-l-r-none">
                                <div class="socialPadding p-w-sm">
                                    <div class="social fb"><a href="#"><i class="fa fa-facebook"></i></a></div>
                                    <div class="social tw"><a href="#"><i class="fa fa-twitter"></i></a></div>
                                    <div class="social gp"><a href="#"><i class="fa fa-google-plus"></i></a></div>
                                </div>
                                @if ($approved)
                                    <div class="status p-w-sm">
                                        <span>{{ __('custom.approved') }} </span>
                                    </div>
                                @else
                                    <div class="status notApproved p-w-sm p-l-r-none">
                                        <span>{{ __('custom.unapproved') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="follow pull-right">
                                <form method="post">
                                    {{ csrf_field() }}
                                    @if (isset($buttons['follow']) && $buttons['follow'])
                                        <div>
                                            <button class="badge badge-pill" type="submit" name="follow" value="{{ $dataset->id }}">{{ utrans('custom.follow') }}</button>
                                        </div>
                                    @elseif (isset($buttons['unfollow']) && $buttons['unfollow'])
                                        <div>
                                            <button class="badge badge-pill" type="submit" name="unfollow" value="{{ $dataset->id }}">{{ uctrans('custom.stop_follow') }}</button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                        <h2>{{ $dataset->name }}</h2>
                        @if (!empty($dataset->description))
                            <p><strong>{{ __('custom.description') }}:</strong></p>
                            <p>{!! nl2br(e($dataset->description)) !!}</p>
                        @endif
                        @if (!empty($dataset->terms_of_use_id))
                            <p>
                                <strong>{{ utrans('custom.license', 1) }}:</strong>
                                &nbsp;{{ $dataset->terms_of_use_name }}
                            </p>
                        @endif
                        @if (!empty($dataset->category_id))
                            <p>
                                <strong>{{ __('custom.main_topic') }}:</strong>
                                &nbsp;{{ $dataset->category_name }}
                            </p>
                        @endif
                        <div class="col-xs-12 p-l-none">
                            <div class="pull-left">
                                @if (isset($dataset->tags) && count($dataset->tags) > 0)
                                    @foreach ($dataset->tags as $tag)
                                        <span class="badge badge-pill m-b-sm">{{ $tag->name }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @if (count($resources) > 0)
                        <div class="col-sm-12 pull-left p-h-sm p-l-none">
                            <div class="pull-left history">
                                @foreach ($resources as $resource)
                                    <div class="{{ $resource->reported ? 'signaled' : '' }}">
                                        <a href="{{ url($rootUrl .'/resourceView/'. $resource->uri) }}">
                                            <span>
                                                <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path d="M26.72,29.9H3.33V0H26.72ZM4.62,28.61H25.43V1.29H4.62Z"/><path d="M11.09,6.18V9.12H8.14V6.18h2.95m1.29-1.3H6.85v5.53h5.53V4.88Z"/><path d="M11.09,13.48v2.94H8.14V13.48h2.95m1.29-1.29H6.85v5.52h5.53V12.19Z"/><path d="M11.09,20.78v2.94H8.14V20.78h2.95m1.29-1.29H6.85V25h5.53V19.49Z"/><rect x="14.34" y="21.38" width="7.57" height="1.74"/><rect x="14.34" y="14.08" width="7.57" height="1.74"/><rect x="14.34" y="6.78" width="7.57" height="1.74"/></svg>
                                            </span>
                                            <span class="version-heading">{{ utrans('custom.resource') }}</span>
                                            <span class="version">&nbsp;&#8211;&nbsp;{{ $resource->name }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <p>
                            <strong>{{ utrans('custom.version') }}:</strong>&nbsp;{{ $dataset->version }}
                        </p>
                        @if (!empty($dataset->source))
                            <p>
                                <strong>{{ __('custom.source') }}:</strong>&nbsp;{{ $dataset->source }}
                            </p>
                        @endif
                        @if (!empty($dataset->author_name))
                            <p>
                                <strong>{{ __('custom.author') }}:</strong>&nbsp;{{ $dataset->author_name }}
                            </p>
                        @endif
                        @if (!empty($dataset->author_email))
                            <p>
                                <strong>{{ __('custom.contact_author') }}:</strong>&nbsp;{{ $dataset->author_email }}
                            </p>
                        @endif
                        @if (!empty($dataset->support_email))
                            <p>
                                <strong>{{ __('custom.contact_support_name') }}:</strong>&nbsp;{{ $dataset->support_email }}
                            </p>
                        @endif
                        @if (!empty($dataset->support_email))
                            <p>
                                <strong>{{ __('custom.contact_support') }}:</strong>&nbsp;{{ $dataset->support_email }}
                            </p>
                        @endif
                        @if (!empty($dataset->sla))
                            <p>
                                <strong>{{ __('custom.sla_agreement') }}:&nbsp;</strong>
                            </p>
                            <div class="m-b-sm"><p>{!! nl2br(e($dataset->sla)) !!}</p></div>
                        @endif
                        <div class="info-bar-sm col-sm-12 col-xs-12 p-l-none">
                            <ul class="p-l-none p-h-sm">
                                <li>{{ __('custom.created_at') }}: {{ $dataset->created_at }}</li>
                                <li>{{ __('custom.created_by') }}: {{ $dataset->created_by }}</li>
                                @if (!empty($dataset->updated_by))
                                    <li>{{ __('custom.updated_at') }}: {{ $dataset->updated_at }}</li>
                                    <li>{{ __('custom.updated_by') }}: {{ $dataset->updated_by }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-9 col-xs-11 page-content p-sm col-sm-offset-3 mng-btns">
            @if (isset($buttons['edit']) && $buttons['edit'])
                <a
                    class="btn btn-primary badge badge-pill"
                    href="{{ url('/data/edit/'. $dataset->uri) }}"
                >{{ uctrans('custom.edit') }}</a>
            @endif
            @if (isset($buttons['delete']) && $buttons['delete'])
                <form method="POST" class="inline-block" action="{{ url('/dataset/delete') }}">
                    {{ csrf_field() }}
                    <button
                        class="btn del-btn btn-primary badge badge-pill"
                        type="submit"
                        name="delete"
                        data-confirm="{{ __('custom.remove_data') }}"
                    >{{ uctrans('custom.remove') }}</button>
                    <input type="hidden" name="dataset_uri" value="{{ $dataset->uri }}">
                </form>
            @endif
        </div>
    </div>
@endif
