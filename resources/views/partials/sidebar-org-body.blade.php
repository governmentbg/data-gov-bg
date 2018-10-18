<ul class="nav">
    <li class="js-show-submenu">
        <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ __('custom.topics') }}</a>
        <button type="button" class="navbar-toggle btn-sidebar pull-right" data-toggle="collapse" data-target="#sidebar-wrapper">
            <span><i class="fa fa-angle-left"></i></span>
        </button>
        <ul class="sidebar-submenu">
        @if (count($categories) > 0)
            @foreach ($categories as $category)
                <li>
                    <a
                        href="{{
                                !in_array($category->id, $getParams['category'])
                                    ? action(
                                        'OrganisationController@datasets', array_merge(
                                            array_except(app('request')->input(), ['category', 'page']),
                                            ['uri' => $organisation->uri, 'category' => array_merge([$category->id], $getParams['category'])]
                                        )
                                    )
                                    : action(
                                        'OrganisationController@datasets', array_merge(
                                            array_except(app('request')->input(), ['category', 'page']),
                                            (array_diff($getParams['category'], [$category->id])
                                                ? ['uri' => $organisation->uri, 'category' => array_diff($getParams['category'], [$category->id])]
                                                : ['uri' => $organisation->uri])
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
                                'OrganisationController@datasets', array_merge(
                                    array_except(app('request')->input(), ['category_limit']),
                                    ['uri' => $organisation->uri, 'category_limit' => 0]
                                )
                            )
                            : action(
                                'OrganisationController@datasets', array_merge(
                                    array_except(app('request')->input(), ['category_limit']),
                                    ['uri' => $organisation->uri]
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
                                        'OrganisationController@datasets', array_merge(
                                            array_except(app('request')->input(), ['tag', 'page']),
                                            ['uri' => $organisation->uri, 'tag' => array_merge([$tag->id], $getParams['tag'])]
                                        )
                                    )
                                    : action(
                                        'OrganisationController@datasets', array_merge(
                                            array_except(app('request')->input(), ['tag', 'page']),
                                            (array_diff($getParams['tag'], [$tag->id])
                                                ? ['uri' => $organisation->uri, 'tag' => array_diff($getParams['tag'], [$tag->id])]
                                                : ['uri' => $organisation->uri])
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
                                'OrganisationController@datasets', array_merge(
                                    array_except(app('request')->input(), ['tag_limit']),
                                    ['uri' => $organisation->uri, 'tag_limit' => 0]
                                )
                            )
                            : action(
                                'OrganisationController@datasets', array_merge(
                                    array_except(app('request')->input(), ['tag_limit']),
                                    ['uri' => $organisation->uri]
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
                                        'OrganisationController@datasets', array_merge(
                                            array_except(app('request')->input(), ['format', 'page']),
                                            ['uri' => $organisation->uri, 'format' => array_merge([strtolower($format->format)], $getParams['format'])]
                                        )
                                    )
                                    : action(
                                        'OrganisationController@datasets', array_merge(
                                            array_except(app('request')->input(), ['format', 'page']),
                                            (array_diff($getParams['format'], [strtolower($format->format)])
                                                ? ['uri' => $organisation->uri, 'format' => array_diff($getParams['format'], [strtolower($format->format)])]
                                                : ['uri' => $organisation->uri])
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
                                'OrganisationController@datasets', array_merge(
                                    array_except(app('request')->input(), ['format_limit']),
                                    ['uri' => $organisation->uri, 'format_limit' => 0]
                                )
                            )
                            : action(
                                'OrganisationController@datasets', array_merge(
                                    array_except(app('request')->input(), ['format_limit']),
                                    ['uri' => $organisation->uri]
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
                                        'OrganisationController@datasets', array_merge(
                                            array_except(app('request')->input(), ['license', 'page']),
                                            ['uri' => $organisation->uri, 'license' => array_merge([$termOfUse->id], $getParams['license'])]
                                        )
                                    )
                                    : action(
                                        'OrganisationController@datasets', array_merge(
                                            array_except(app('request')->input(), ['license', 'page']),
                                            (array_diff($getParams['license'], [$termOfUse->id])
                                                ? ['uri' => $organisation->uri, 'license' => array_diff($getParams['license'], [$termOfUse->id])]
                                                : ['uri' => $organisation->uri])
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
                                'OrganisationController@datasets', array_merge(
                                    array_except(app('request')->input(), ['license_limit']),
                                    ['uri' => $organisation->uri, 'license_limit' => 0]
                                )
                            )
                            : action(
                                'OrganisationController@datasets', array_merge(
                                    array_except(app('request')->input(), ['license_limit']),
                                    ['uri' => $organisation->uri]
                                )
                            )
                    }}"
                >{{ $showAll ? __('custom.show_all') : __('custom.only_popular') }}</a>
            </li>
        @endif
        </ul>
    </li>
</ul>