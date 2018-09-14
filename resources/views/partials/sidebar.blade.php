<div class="col-sm-3 sidenav p-l-r-none hidden-xs">
    <ul class="nav">
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ untrans('custom.organisations', 2) }}</a>
            <ul class="sidebar-submenu">
            @if (count($organisations) > 0)
                @foreach ($organisations as $organisation)
                    <li>
                        <a
                            href="{{
                                    !in_array($organisation->id, $getParams['org'])
                                        ? action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['org', 'page']),
                                                ['org' => array_merge([$organisation->id], $getParams['org'])]
                                            )
                                        )
                                        : action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['org', 'page']),
                                                ['org' => array_diff($getParams['org'], [$organisation->id])]
                                            )
                                        )
                            }}"
                            class="{{
                                    isset($getParams['org']) && in_array($organisation->id, $getParams['org'])
                                        ? 'active'
                                        : ''
                            }}"
                        >{{ $organisation->name }} ({{ $organisation->datasets_count }})</a>
                    </li>
                @endforeach
            @else
                <li><span>{{ __('custom.no_matches') }}</span></li>
            @endif
            @php
                $showAll = isset($display['show_all']) && isset($display['show_all']['org']) && $display['show_all']['org'];
                $onlyPopular = isset($display['only_popular']) && isset($display['only_popular']['org']) && $display['only_popular']['org'];
            @endphp
            @if ($showAll || $onlyPopular)
                <li>
                    <a
                        href="{{
                                !isset(app('request')->input()['org_limit'])
                                ? action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['org_limit']),
                                        ['org_limit' => 0]
                                    )
                                )
                                : action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['org_limit']),
                                        []
                                    )
                                )
                        }}"
                    >{{ $showAll ? __('custom.show_all') : __('custom.only_popular') }}</a>
                </li>
            @endif
            </ul>
        </li>
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ untrans('custom.users', 2) }}</a>
            <ul class="sidebar-submenu">
            @if (count($users) > 0)
                @foreach ($users as $user)
                    <li>
                        <a
                            href="{{
                                    !in_array($user->id, $getParams['user'])
                                        ? action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['user', 'page']),
                                                ['user' => array_merge([$user->id], $getParams['user'])]
                                            )
                                        )
                                        : action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['user', 'page']),
                                                ['user' => array_diff($getParams['user'], [$user->id])]
                                            )
                                        )
                            }}"
                            class="{{
                                    isset($getParams['user']) && in_array($user->id, $getParams['user'])
                                        ? 'active'
                                        : ''
                            }}"
                        >{{ ($user->first_name || $user->last_name) ? trim($user->first_name .' '. $user->last_name) : $user->username }}
                         ({{ $user->datasets_count }})</a>
                    </li>
                @endforeach
            @else
                <li><span>{{ __('custom.no_matches') }}</span></li>
            @endif
            @php
                $showAll = isset($display['show_all']) && isset($display['show_all']['user']) && $display['show_all']['user'];
                $onlyPopular = isset($display['only_popular']) && isset($display['only_popular']['user']) && $display['only_popular']['user'];
            @endphp
            @if ($showAll || $onlyPopular)
                <li>
                    <a
                        href="{{
                                !isset(app('request')->input()['user_limit'])
                                ? action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['user_limit']),
                                        ['user_limit' => 0]
                                    )
                                )
                                : action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['user_limit']),
                                        []
                                    )
                                )
                        }}"
                    >{{ $showAll ? __('custom.show_all') : __('custom.only_popular') }}</a>
                </li>
            @endif
            </ul>
        </li>
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ untrans('custom.groups', 2) }}</a>
            <ul class="sidebar-submenu">
            @if (count($groups) > 0)
                @foreach ($groups as $group)
                    <li>
                        <a
                            href="{{
                                    !in_array($group->id, $getParams['group'])
                                        ? action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['group', 'page']),
                                                ['group' => array_merge([$group->id], $getParams['group'])]
                                            )
                                        )
                                        : action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['group', 'page']),
                                                ['group' => array_diff($getParams['group'], [$group->id])]
                                            )
                                        )
                            }}"
                            class="{{
                                    isset($getParams['group']) && in_array($group->id, $getParams['group'])
                                        ? 'active'
                                        : ''
                            }}"
                        >{{ $group->name }} ({{ $group->datasets_count }})</a>
                    </li>
                @endforeach
            @else
                <li><span>{{ __('custom.no_matches') }}</span></li>
            @endif
            @php
                $showAll = isset($display['show_all']) && isset($display['show_all']['group']) && $display['show_all']['group'];
                $onlyPopular = isset($display['only_popular']) && isset($display['only_popular']['group']) && $display['only_popular']['group'];
            @endphp
            @if ($showAll || $onlyPopular)
                <li>
                    <a
                        href="{{
                                !isset(app('request')->input()['group_limit'])
                                ? action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['group_limit']),
                                        ['group_limit' => 0]
                                    )
                                )
                                : action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['group_limit']),
                                        []
                                    )
                                )
                        }}"
                    >{{ $showAll ? __('custom.show_all') : __('custom.only_popular') }}</a>
                </li>
            @endif
            </ul>
        </li>
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ __('custom.topics') }}</a>
            <ul class="sidebar-submenu">
            @if (count($categories) > 0)
                @foreach ($categories as $category)
                    <li>
                        <a
                            href="{{
                                    !in_array($category->id, $getParams['category'])
                                        ? action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['category', 'page']),
                                                ['category' => array_merge([$category->id], $getParams['category'])]
                                            )
                                        )
                                        : action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['category', 'page']),
                                                ['category' => array_diff($getParams['category'], [$category->id])]
                                            )
                                        )
                            }}"
                            class="{{
                                    isset($getParams['category']) && in_array($category->id, $getParams['category'])
                                        ? 'active'
                                        : ''
                            }}"
                        >{{ $category->name }} ({{ $category->datasets_count }})</a>
                    </li>
                @endforeach
            @else
                <li><span>{{ __('custom.no_matches') }}</span></li>
            @endif
            @php
                $showAll = isset($display['show_all']) && isset($display['show_all']['category']) && $display['show_all']['category'];
                $onlyPopular = isset($display['only_popular']) && isset($display['only_popular']['category']) && $display['only_popular']['category'];
            @endphp
            @if ($showAll || $onlyPopular)
                <li>
                    <a
                        href="{{
                                !isset(app('request')->input()['category_limit'])
                                ? action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['category_limit']),
                                        ['category_limit' => 0]
                                    )
                                )
                                : action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['category_limit']),
                                        []
                                    )
                                )
                        }}"
                    >{{ $showAll ? __('custom.show_all') : __('custom.only_popular') }}</a>
                </li>
            @endif
            </ul>
        </li>
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ ultrans('custom.tags', 2) }}</a>
            <ul class="sidebar-submenu">
            @if (count($tags) > 0)
                @foreach ($tags as $tag)
                    <li>
                        <a
                            href="{{
                                    !in_array($tag->id, $getParams['tag'])
                                        ? action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['tag', 'page']),
                                                ['tag' => array_merge([$tag->id], $getParams['tag'])]
                                            )
                                        )
                                        : action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['tag', 'page']),
                                                ['tag' => array_diff($getParams['tag'], [$tag->id])]
                                            )
                                        )
                            }}"
                            class="{{
                                    isset($getParams['tag']) && in_array($tag->id, $getParams['tag'])
                                        ? 'active'
                                        : ''
                            }}"
                        >{{ $tag->name }} ({{ $tag->datasets_count }})</a>
                    </li>
                @endforeach
            @else
                <li><span>{{ __('custom.no_matches') }}</span></li>
            @endif
            @php
                $showAll = isset($display['show_all']) && isset($display['show_all']['tag']) && $display['show_all']['tag'];
                $onlyPopular = isset($display['only_popular']) && isset($display['only_popular']['tag']) && $display['only_popular']['tag'];
            @endphp
            @if ($showAll || $onlyPopular)
                <li>
                    <a
                        href="{{
                                !isset(app('request')->input()['tag_limit'])
                                ? action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['tag_limit']),
                                        ['tag_limit' => 0]
                                    )
                                )
                                : action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['tag_limit']),
                                        []
                                    )
                                )
                        }}"
                    >{{ $showAll ? __('custom.show_all') : __('custom.only_popular') }}</a>
                </li>
            @endif
            </ul>
        </li>
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ __('custom.format') }}</a>
            <ul class="sidebar-submenu">
            @if (count($formats) > 0)
                @foreach ($formats as $format)
                    <li>
                        <a
                            href="{{
                                    !in_array(strtolower($format->format), $getParams['format'])
                                        ? action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['format', 'page']),
                                                ['format' => array_merge([strtolower($format->format)], $getParams['format'])]
                                            )
                                        )
                                        : action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['format', 'page']),
                                                ['format' => array_diff($getParams['format'], [strtolower($format->format)])]
                                            )
                                        )
                            }}"
                            class="{{
                                    isset($getParams['format']) && in_array(strtolower($format->format), $getParams['format'])
                                        ? 'active'
                                        : ''
                            }}"
                        >{{ strtolower($format->format) }} ({{ $format->datasets_count }})</a>
                    </li>
                @endforeach
            @else
                <li><span>{{ __('custom.no_matches') }}</span></li>
            @endif
            @php
                $showAll = isset($display['show_all']) && isset($display['show_all']['format']) && $display['show_all']['format'];
                $onlyPopular = isset($display['only_popular']) && isset($display['only_popular']['format']) && $display['only_popular']['format'];
            @endphp
            @if ($showAll || $onlyPopular)
                <li>
                    <a
                        href="{{
                                !isset(app('request')->input()['format_limit'])
                                ? action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['format_limit']),
                                        ['format_limit' => 0]
                                    )
                                )
                                : action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['format_limit']),
                                        []
                                    )
                                )
                        }}"
                    >{{ $showAll ? __('custom.show_all') : __('custom.only_popular') }}</a>
                </li>
            @endif
            </ul>
        </li>
        <li class="js-show-submenu">
            @php
                $params = array_except($getParams, ['license']);
                $url = url()->current() . (!empty($params) ? '?'. http_build_query($params) : '') .'#';
            @endphp
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ ultrans('custom.license', 2) }}</a>
            <ul class="sidebar-submenu">
            @if (count($termsOfUse) > 0)
                @foreach ($termsOfUse as $termOfUse)
                    <li>
                        <a
                            href="{{
                                    !in_array($termOfUse->id, $getParams['license'])
                                        ? action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['license', 'page']),
                                                ['license' => array_merge([$termOfUse->id], $getParams['license'])]
                                            )
                                        )
                                        : action(
                                            'DataController@list', array_merge(
                                                array_except(app('request')->input(), ['license', 'page']),
                                                ['license' => array_diff($getParams['license'], [$termOfUse->id])]
                                            )
                                        )
                            }}"
                            class="{{
                                    isset($getParams['license']) && in_array($termOfUse->id, $getParams['license'])
                                        ? 'active'
                                        : ''
                            }}"
                        >{{ $termOfUse->name }} ({{ $termOfUse->datasets_count }})</a>
                    </li>
                @endforeach
            @else
                <li><span>{{ __('custom.no_matches') }}</span></li>
            @endif
            @php
                $showAll = isset($display['show_all']) && isset($display['show_all']['license']) && $display['show_all']['license'];
                $onlyPopular = isset($display['only_popular']) && isset($display['only_popular']['license']) && $display['only_popular']['license'];
            @endphp
            @if ($showAll || $onlyPopular)
                <li>
                    <a
                        href="{{
                                !isset(app('request')->input()['license_limit'])
                                ? action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['license_limit']),
                                        ['license_limit' => 0]
                                    )
                                )
                                : action(
                                    'DataController@list', array_merge(
                                        array_except(app('request')->input(), ['license_limit']),
                                        []
                                    )
                                )
                        }}"
                    >{{ $showAll ? __('custom.show_all') : __('custom.only_popular') }}</a>
                </li>
            @endif
            </ul>
        </li>
    </ul>
</div>
