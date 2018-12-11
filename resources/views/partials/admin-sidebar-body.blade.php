@if (isset($options))
    @if (in_array('range', $options))
        <form
            method="GET"
        >
            <div class="row m-b-sm">
                <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.from') }}:</div>
                <div class="col-lg-7 col-xs-9 text-left search-field admin">
                    <input class="js-from-filter datepicker input-border-r-12 form-control" name="from" value="{{ $range['from'] }}">
                </div>
            </div>
            <div class="row m-b-sm">
                <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.to') }}:</div>
                <div class="col-lg-7 col-xs-9 text-left search-field admin">
                    <input class="js-to-filter datepicker input-border-r-12 form-control" name="to" value="{{ $range['to'] }}">
                </div>
            </div>
            @if (isset(app('request')->input()['order_field']))
                <input type="hidden" name="order_field" value="{{ app('request')->input()['order_field'] }}">
            @endif
            @if (isset(app('request')->input()['order_type']))
                <input type="hidden" name="order_type" value="{{ app('request')->input()['order_type'] }}">
            @endif
            @if (isset(app('request')->input()['q']))
                <input type="hidden" name="q" value="{{ app('request')->input()['q'] }}">
            @endif
            @if (isset(app('request')->input()['status']))
                <input type="hidden" name="status" value="{{ app('request')->input()['status'] }}">
            @endif
            @if (isset(app('request')->input()['order']))
                <input type="hidden" name="order" value="{{ app('request')->input()['order'] }}">
            @endif
            @if (isset(app('request')->input()['dtype']))
                <input type="hidden" name="status" value="{{ app('request')->input()['dtype'] }}">
            @endif
        </form>
    @endif

    @if (in_array('organisation', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.organisations') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['orgs_count'])
                                    ? action(
                                        $action, array_merge(
                                            ['orgs_count' => $orgDropCount],
                                            array_except(app('request')->input(), ['orgs_count', 'page'])
                                        )
                                    )
                                    : action(
                                        $action, array_merge(
                                            [
                                                'orgs_count'    => null,
                                                'org'           => [],
                                            ],
                                            array_except(app('request')->input(), ['org', 'orgs_count', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['orgs_count']) && app('request')->input()['orgs_count'] == $orgDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['orgs_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($organisations as $id => $org)
                            <li>
                                <a
                                    href="{{
                                        !in_array($id, $selectedOrgs)
                                            ? action(
                                                $action, array_merge(
                                                    ['org' => array_merge([$id], $selectedOrgs)],
                                                    array_except(app('request')->input(), ['org', 'page'])
                                                )
                                            )
                                            : action(
                                                $action, array_merge(
                                                    ['org' => array_diff($selectedOrgs, [$id])],
                                                    array_except(app('request')->input(), ['org', 'page'])
                                                )
                                            )
                                    }}"
                                    class="{{
                                        isset($selectedOrgs) && in_array($id, $selectedOrgs)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $org }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('group', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.groups') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['groups_count'])
                                    ? action(
                                        $action, array_merge(
                                            ['groups_count' => $groupDropCount],
                                            array_except(app('request')->input(), ['groups_count', 'page'])
                                        )
                                    )
                                    : action(
                                        $action, array_merge(
                                            [
                                                'groups_count'  => null,
                                                'group'         => [],
                                            ],
                                            array_except(app('request')->input(), ['group', 'groups_count', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['groups_count']) && app('request')->input()['groups_count'] == $groupDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['groups_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($groups as $id => $group)
                            <li>
                                <a
                                    href="{{
                                        !in_array($id, $selectedGroups)
                                            ? action(
                                                $action, array_merge(
                                                    ['group' => array_merge([$id], $selectedGroups)],
                                                    array_except(app('request')->input(), ['group', 'page'])
                                                )
                                            )
                                            : action(
                                                $action, array_merge(
                                                    ['group' => array_diff($selectedGroups, [$id])],
                                                    array_except(app('request')->input(), ['group', 'page'])
                                                )
                                            )
                                    }}"
                                    class="{{
                                        isset($selectedGroups) && in_array($id, $selectedGroups)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $group }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('user', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.users') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['users_count'])
                                    ? action(
                                        $action, array_merge(
                                            ['users_count' => $userDropCount],
                                            array_except(app('request')->input(), ['users_count', 'page'])
                                        )
                                    )
                                    : action(
                                        $action, array_merge(
                                            [
                                                'users_count'  => null,
                                                'user'         => [],
                                            ],
                                            array_except(app('request')->input(), ['user', 'users_count', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['users_count']) && app('request')->input()['users_count'] == $userDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['users_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($users as $id => $user)
                            <li>
                                <a
                                    href="{{
                                        action(
                                            $action, array_merge(
                                                ['user' => $id],
                                                array_except(app('request')->input(), ['user', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset(app('request')->input()['user']) && $id == $selectedUser
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $user }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('category', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.main_topic') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['categories_count'])
                                    ? action(
                                        $action, array_merge(
                                            ['categories_count' => $catDropCount],
                                            array_except(app('request')->input(), ['categories_count', 'page'])
                                        )
                                    )
                                    : action(
                                        $action, array_merge(
                                            [
                                                'categories_count'  => null,
                                                'category'         => [],
                                            ],
                                            array_except(app('request')->input(), ['category', 'categories_count', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['categories_count']) && app('request')->input()['categories_count'] == $catDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['categories_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($categories as $id => $category)
                            <li>
                                <a
                                    href="{{
                                        !in_array($id, $selectedCategories)
                                            ? action(
                                                $action, array_merge(
                                                    ['category' => array_merge([$id], $selectedCategories)],
                                                    array_except(app('request')->input(), ['category', 'page'])
                                                )
                                            )
                                            : action(
                                                $action, array_merge(
                                                    ['category' => array_diff($selectedCategories, [$id])],
                                                    array_except(app('request')->input(), ['category', 'page'])
                                                )
                                            )
                                    }}"
                                    class="{{
                                        isset($selectedCategories) && in_array($id, $selectedCategories)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $category }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('tag', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.tags') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['tags_count'])
                                    ? action(
                                        $action, array_merge(
                                            ['tags_count' => $tagsDropCount],
                                            array_except(app('request')->input(), ['tags_count', 'page'])
                                        )
                                    )
                                    : action(
                                        $action, array_merge(
                                            [
                                                'tags_count'  => null,
                                                'tag'         => [],
                                            ],
                                            array_except(app('request')->input(), ['tag', 'tags_count', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['tags_count']) && app('request')->input()['tags_count'] == $tagsDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['tags_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($tags as $id => $tag)
                            <li>
                                <a
                                    href="{{
                                        !in_array($id, $selectedTags)
                                            ? action(
                                                $action, array_merge(
                                                    ['tag' => array_merge([$id], $selectedTags)],
                                                    array_except(app('request')->input(), ['tag', 'page'])
                                                )
                                            )
                                            : action(
                                                $action, array_merge(
                                                    ['tag' => array_diff($selectedTags, [$id])],
                                                    array_except(app('request')->input(), ['tag', 'page'])
                                                )
                                            )
                                    }}"
                                    class="{{
                                        isset($selectedTags) && in_array($id, $selectedTags)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $tag }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('format', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.format') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        <li>
                            <a
                                href="{{
                                    action(
                                        $action, array_merge(
                                            [
                                                'formatsCount'  => null,
                                                'format'         => [],
                                            ],
                                            array_except(app('request')->input(), ['format', 'formatsCount', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['formatsCount']) && app('request')->input()['formatsCount'] == $formatsCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($formats as $id => $format)
                            <li>
                                <a
                                    href="{{
                                        !in_array($format, $selectedFormats)
                                            ? action(
                                                $action, array_merge(
                                                    ['format' => array_merge([$format], $selectedFormats)],
                                                    array_except(app('request')->input(), ['format', 'page'])
                                                )
                                            )
                                            : action(
                                                $action, array_merge(
                                                    ['format' => array_diff($selectedFormats, [$format])],
                                                    array_except(app('request')->input(), ['format', 'page'])
                                                )
                                            )
                                    }}"
                                    class="{{
                                        isset($selectedFormats) && in_array($format, $selectedFormats)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $format }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('terms', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.terms_and_conditions') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['terms_count'])
                                    ? action(
                                        $action, array_merge(
                                            ['terms_count' => $termsDropCount],
                                            array_except(app('request')->input(), ['terms_count', 'page'])
                                        )
                                    )
                                    : action(
                                        $action, array_merge(
                                            [
                                                'terms_count'  => null,
                                                'term'         => [],
                                            ],
                                            array_except(app('request')->input(), ['term', 'terms_count', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['terms_count']) && app('request')->input()['terms_count'] == $termsDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['terms_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($terms as $id => $term)
                            <li>
                                <a
                                    href="{{
                                        !in_array($id, $selectedTerms)
                                            ? action(
                                                $action, array_merge(
                                                    ['term' => array_merge([$id], $selectedTerms)],
                                                    array_except(app('request')->input(), ['term', 'page'])
                                                )
                                            )
                                            : action(
                                                $action, array_merge(
                                                    ['term' => array_diff($selectedTerms, [$id])],
                                                    array_except(app('request')->input(), ['term', 'page'])
                                                )
                                            )
                                    }}"
                                    class="{{
                                        isset($selectedTerms) && in_array($id, $selectedTerms)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $term }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('role', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.roles') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        @foreach ($roles as $role)
                            <li>
                                <a
                                    href="{{
                                        !in_array($role->id, $selectedRoles)
                                        ? action(
                                            $action, array_merge(
                                                ['role' => array_merge([$role->id], $selectedRoles), 'page' => 1],
                                                array_except(app('request')->input(), ['role', 'page'])
                                            )
                                        )
                                        : action(
                                            $action, array_merge(
                                                ['role' => array_diff($selectedRoles, [$role->id]), 'page' => 1],
                                                array_except(app('request')->input(), ['role', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset($selectedRoles) && in_array($role->id, $selectedRoles)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $role->name }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('signal', $options))
        <form method="GET" class="inline-block">
            <div class="form-group adm-filter">
                <label for="signaled" class="col-lg-8 col-sm-8 col-xs-9">{{ uctrans('custom.signaled') }}:</label>
                <div class="col-lg-4 col-sm-4 col-xs-3">
                    <input
                        type="checkbox"
                        class="js-check js-submit form-control"
                        id="signaled"
                        name="signaled"
                        value="1"
                        {{ isset($signaledFilter) ? 'checked' : '' }}
                    >
                    @foreach (app('request')->except(['signaled', 'page']) as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $innerValue)
                                <input name="{{ $key }}[]" type="hidden" value="{{ $innerValue }}">
                            @endforeach
                        @else
                            <input name="{{ $key }}" type="hidden" value="{{ $value }}">
                        @endif
                    @endforeach
                </div>
            </div>
        </form>
    @endif

    @if (in_array('approved', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.approved_side') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        <li>
                            <a
                                href="{{
                                    action(
                                        $action,
                                        array_merge(
                                            ['approved' => 1],
                                            array_except(app('request')->input(), ['approved', 'q', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['approved']) && app('request')->input()['approved']
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ __('custom.show_approved') }}</a>
                        </li>
                        <li>
                            <a
                                href="{{
                                    action(
                                        $action,
                                        array_merge(
                                            ['approved' => 0],
                                            array_except(app('request')->input(), ['approved', 'q', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['approved']) && !app('request')->input()['approved']
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ __('custom.hide_approved') }}</a>
                        </li>
                        <li>
                            <a
                                href="{{
                                    action(
                                        $action,
                                        array_except(
                                            app('request')->input(),
                                            ['approved', 'q', 'page']
                                        )
                                    )
                                }}"
                            >{{ __('custom.show_all') }}</a>
                        </li>
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('active', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ __('custom.active_side') }}</a>
                <ul class="sidebar-submenu nano m-b-md">
                    <div class="nano-content">
                        <li>
                            <a
                                href="{{
                                    action(
                                        $action,
                                        array_merge(
                                            ['active' => 1],
                                            array_except(app('request')->input(), ['active', 'q', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['active']) && app('request')->input()['active']
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ __('custom.show_active') }}</a>
                        </li>
                        <li>
                            <a
                                href="{{
                                    action(
                                        $action,
                                        array_merge(
                                            ['active' => 0],
                                            array_except(app('request')->input(), ['active', 'q', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['active']) && !app('request')->input()['active']
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ __('custom.hide_active') }}</a>
                        </li>
                        <li>
                            <a
                                href="{{
                                    action(
                                        $action,
                                        array_except(app('request')->input(), ['active', 'q', 'page'])
                                    )
                                }}"
                            >{{ __('custom.show_all') }}</a>
                        </li>
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('section', $options))
    <ul class="nav">
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.section') }}</a>
            <ul class="sidebar-submenu m-b-md nano">
                <div class="nano-content">
                    <li>
                        <a
                            href="{{
                                action(
                                    $action,
                                    array_except(app('request')->input(), ['section', 'page'])
                                )
                            }}"
                        >{{ __('custom.show_all') }}</a>
                    </li>
                    @if (count($sections))
                        @foreach ($sections as $id => $sec)
                            <li>
                                <a
                                    href="{{
                                        action(
                                            $action,
                                            array_merge(
                                                ['section' => $id],
                                                array_except(app('request')->input(), ['section', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset(app('request')->input()['section']) && app('request')->input()['section'] == $id
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $sec }}</a>
                            </li>
                        @endforeach
                    @endif
                </div>
            </ul>
        </li>
    </ul>
    @endif

    @if (in_array('dateOf', $options))
    <ul class="nav">
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ __('custom.date_of') }}</a>
            <ul class="sidebar-submenu nano m-b-md">
                <div class="nano-content">
                    <li>
                        <a
                            href="{{
                                action(
                                    $action,
                                    array_merge(
                                        ['dtype' => \App\Document::DATE_TYPE_CREATED],
                                        array_except(app('request')->input(), ['dtype', 'q', 'page'])
                                    )
                                )
                            }}"
                            class="{{
                                isset(app('request')->input()['dtype'])
                                && app('request')->input()['dtype'] == \App\Document::DATE_TYPE_CREATED
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ utrans('custom.creation') }}</a>
                    </li>
                    <li>
                        <a
                            href="{{
                                action(
                                    $action,
                                    array_merge(
                                        ['dtype' => \App\Document::DATE_TYPE_UPDATED],
                                        array_except(app('request')->input(), ['dtype', 'q', 'page'])
                                    )
                                )
                            }}"
                            class="{{
                                isset(app('request')->input()['dtype'])
                                && app('request')->input()['dtype'] == \App\Document::DATE_TYPE_UPDATED
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ utrans('custom.edit_date') }}</a>
                    </li>
                    <li>
                        <a
                            href="{{
                                action(
                                    $action,
                                    array_except(app('request')->input(), ['dtype', 'q'])
                                )
                            }}"
                        >{{ __('custom.by_default') }}</a>
                    </li>
                </div>
            </ul>
        </li>
    </ul>
    @endif

    @if (in_array('parentOrg', $options))
        <div class="form-group">
            <div class="col-xs-12 search-field admin">
                <form
                    method="GET"
                    class="form-horisontal"
                >
                    <div class="form-group row m-b-lg m-t-md">
                        <div class="col-lg-10 col-xs-12">
                            <select
                                class="js-ajax-autocomplete-org form-control js-parent-org-filter"
                                data-url="{{ url('/api/listOrganisations') }}"
                                data-post="{{ json_encode(['api_key' => \Auth::user()->api_key]) }}"
                                data-placeholder="{{__('custom.main_org')}}"
                                name="parent"
                            >
                                @if (isset($selectedOrg) && !is_null($selectedOrg))
                                    <option value="{{ $selectedOrg->uri }}" selected>{{ $selectedOrg->name }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    @if (isset(app('request')->input()['active']))
                        <input type="hidden" name="active" value="{{ app('request')->input()['active'] }}">
                    @endif
                    @if (isset(app('request')->input()['approved']))
                        <input type="hidden" name="approved" value="{{ app('request')->input()['approved'] }}">
                    @endif
                </form>
            </div>
        </div>
    @endif

    @if (in_array('admin', $options))
        <form method="GET" class="inline-block">
            <div class="form-group adm-filter">
                <label for="is_admin" class="col-md-9 col-xs-10">{{ __('custom.admin') }}:</label>
                <div class="col-md-3 col-xs-1 text-center p-l-none">
                    <input
                        type="checkbox"
                        class="js-check js-submit form-control"
                        id="is_admin"
                        name="is_admin"
                        value="1"
                        {{ $adminFilter ? 'checked' : '' }}
                    >
                    @foreach (app('request')->except(['is_admin', 'page']) as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $innerValue)
                                <input name="{{ $key }}[]" type="hidden" value="{{ $innerValue }}">
                            @endforeach
                        @else
                            <input name="{{ $key }}" type="hidden" value="{{ $value }}">
                        @endif
                    @endforeach
                </div>
            </div>
        </form>
    @endif

    @if (in_array('state', $options))
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.status') }}</a>
                <ul class="sidebar-submenu m-b-md nano">
                    <div class="nano-content">
                        @foreach ($statuses as $key => $status)
                            <li>
                                <a
                                    href="{{
                                        action(
                                            $action,
                                            array_merge(
                                                ['status' => $key],
                                                array_except(app('request')->input(), ['status', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset(app('request')->input()['status'])
                                        && app('request')->input()['status'] == $key
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ uctrans('custom.'. $status) }}</a>
                            </li>
                        @endforeach
                        <li>
                            <a
                                href="{{
                                    action(
                                        $action,
                                        array_except(app('request')->input(), ['status', 'page'])
                                    )
                                }}"
                            >{{ uctrans('custom.all') }}</a>
                        </li>
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    @if (in_array('search', $options))
        <div class="form-group">
            <div class="col-lg-10 col-md-12 search-field admin">
                <form
                    method="GET"
                >
                    <input
                        type="text"
                        class="m-t-md input-border-r-12 form-control js-ga-event"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($search) ? $search : '' }}"
                        name="q"
                        data-ga-action="search"
                        data-ga-label="data search"
                        data-ga-category="data"
                    >
                    @foreach (app('request')->except(['q', 'page']) as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $innerValue)
                                <input name="{{ $key }}[]" type="hidden" value="{{ $innerValue }}">
                            @endforeach
                        @else
                            <input name="{{ $key }}" type="hidden" value="{{ $value }}">
                        @endif
                    @endforeach
                </form>
            </div>
        </div>
    @endif
@endif

@if (isset($history))
    <form
        method="GET"
        action="{{ url('/admin/history/'. $view) }}"
    >
        <div class="row m-b-sm">
            <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.from') }}:</div>
            <div class="col-lg-7 col-xs-9 text-left search-field admin">
                <input class="js-from-filter datepicker input-border-r-12 form-control" name="period_from" value="{{ $range['from'] }}">
            </div>
        </div>
        <div class="row m-b-sm">
            <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.to') }}:</div>
            <div class="col-lg-7 col-xs-9 text-left search-field admin">
                <input class="js-to-filter datepicker input-border-r-12 form-control" name="period_to" value="{{ $range['to'] }}">
            </div>
        </div>
        @if (isset(app('request')->input()['order_field']))
            <input type="hidden" name="order_field" value="{{ app('request')->input()['order_field'] }}">
        @endif
        @if (isset(app('request')->input()['order_type']))
            <input type="hidden" name="order_type" value="{{ app('request')->input()['order_type'] }}">
        @endif
        @if (isset(app('request')->input()['q']))
            <input type="hidden" name="q" value="{{ app('request')->input()['q'] }}">
        @endif
    </form>

    <ul class="nav">
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.organisations') }}</a>
            <ul class="sidebar-submenu nano">
                <div class="nano-content">
                    <li>
                        <a
                            href="{{
                                !isset(app('request')->input()['orgs_count'])
                                ? action(
                                    $action, array_merge(
                                        [
                                            'orgs_count'    => $orgDropCount,
                                            'type'          => $view,
                                            'page'          => 1,
                                        ],
                                        array_except(app('request')->input(), ['orgs_count', 'page'])
                                    )
                                )
                                : action(
                                    $action, array_merge(
                                        [
                                            'orgs_count'    => null,
                                            'org'           => [],
                                            'type'          => $view,
                                            'page'          => 1,
                                        ],
                                        array_except(app('request')->input(), ['org', 'orgs_count', 'page'])
                                    )
                                )
                            }}"
                            class="{{
                                isset(app('request')->input()['orgs_count']) && app('request')->input()['orgs_count'] == $orgDropCount
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ !isset(app('request')->input()['orgs_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                    </li>
                    @foreach ($organisations as $id => $org)
                        <li>
                            <a
                                href="{{
                                    !in_array($id, $selectedOrgs)
                                        ? action(
                                            $action, array_merge(
                                                [
                                                    'org'   => array_merge([$id], $selectedOrgs),
                                                    'type'  => $view,
                                                    'page'  => 1,
                                                ],
                                                array_except(app('request')->input(), ['org', 'page'])
                                            )
                                        )
                                        : action(
                                            $action, array_merge(
                                                [
                                                    'org'   => array_diff($selectedOrgs, [$id]),
                                                    'type'  => $view,
                                                    'page'  => 1,
                                                ],
                                                array_except(app('request')->input(), ['org', 'page'])
                                            )
                                        )
                                }}"
                                class="{{
                                    isset($selectedOrgs) && in_array($id, $selectedOrgs)
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ $org }}</a>
                        </li>
                    @endforeach
                </div>
            </ul>
        </li>
    </ul>

    @if ($view == 'action')
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.module') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        @foreach ($modules as $id => $module)
                            <li>
                                <a
                                    href="{{
                                        !in_array($module, $selectedModules)
                                        ? action(
                                            $action, array_merge(
                                                [
                                                    'module'    => array_merge([$module],$selectedModules),
                                                    'type'      => $view,
                                                    'page'      => 1,
                                                ],
                                                array_except(app('request')->input(), ['module', 'page'])
                                            )
                                        )
                                        : action(
                                            $action, array_merge(
                                                [
                                                    'module'    => array_diff($selectedModules, [$module]),
                                                    'type'      => $view,
                                                    'page'      => 1,
                                                ],
                                                array_except(app('request')->input(), ['module', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset($selectedModules) && in_array($module, $selectedModules)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $module }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
        <ul class="nav">
            <li class="js-show-submenu">
                <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.action') }}</a>
                <ul class="sidebar-submenu nano">
                    <div class="nano-content">
                        @foreach ($actionTypes as $id => $act)
                            <li>
                                <a
                                    href="{{
                                        !in_array($id, $selectedActions)
                                        ? action(
                                            $action, array_merge(
                                                [
                                                    'action'    => array_merge([$id], $selectedActions),
                                                    'type'      => $view,
                                                    'page'      => 1,
                                                ],
                                                array_except(app('request')->input(), ['action', 'page'])
                                            )
                                        )
                                        : action(
                                            $action, array_merge(
                                                [
                                                    'action'    => array_diff($selectedActions, [$id]),
                                                    'type'      => $view,
                                                    'page'      => 1,
                                                ],
                                                array_except(app('request')->input(), ['action', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset($selectedActions) && in_array($id, $selectedActions)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{  $act }}</a>
                            </li>
                        @endforeach
                    </div>
                </ul>
            </li>
        </ul>
    @endif

    <ul class="nav">
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.users') }}</a>
            <ul class="sidebar-submenu nano">
                <div class="nano-content">
                    <li>
                        <a
                            href="{{
                                !isset(app('request')->input()['users_count'])
                                ? action(
                                    $action, array_merge(
                                        [
                                            'users_count'   => $userDropCount,
                                            'type'          => $view,
                                            'page'          => 1,
                                        ],
                                        array_except(app('request')->input(), ['users_count', 'page'])
                                    )
                                )
                                : action(
                                    $action, array_merge(
                                        [
                                            'users_count'  => null,
                                            'user'         => [],
                                            'type'         => $view,
                                            'page'         => 1,
                                        ],
                                        array_except(app('request')->input(), ['user', 'users_count', 'page'])
                                    )
                                )
                            }}"
                            class="{{
                                isset(app('request')->input()['users_count']) && app('request')->input()['users_count'] == $userDropCount
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ !isset(app('request')->input()['users_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                    </li>
                    @foreach ($users as $id => $user)
                        <li>
                            <a
                                href="{{
                                    empty($selectedUser)
                                    ? action(
                                        $action, array_merge(
                                            [
                                                'user'  => $id,
                                                'type'  => $view,
                                                'page'  => 1,
                                            ],
                                            array_except(app('request')->input(), ['user', 'page'])
                                        )
                                    )
                                    : action(
                                        $action, array_merge(
                                            [
                                                'user'  => [],
                                                'type'  => $view,
                                                'page'  => 1,
                                            ],
                                            array_except(app('request')->input(), ['user', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['user']) && $id == $selectedUser
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ $user }}</a>
                        </li>
                    @endforeach
                </div>
            </ul>
        </li>
    </ul>

    <ul class="nav">
        <li class="js-show-submenu">
            <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.ip_address') }}</a>
            <ul class="sidebar-submenu nano">
                <div class="nano-content">
                    <li>
                        <a
                            href="{{
                                !isset(app('request')->input()['ips_count'])
                                ? action(
                                    $action, array_merge(
                                        [
                                            'ips_count' => $ipDropCount,
                                            'type'      => $view,
                                            'page'      => 1,
                                        ],
                                        array_except(app('request')->input(), ['ips_count', 'page'])
                                    )
                                )
                                : action(
                                    $action, array_merge(
                                        [
                                            'ips_count'  => null,
                                            'ip'         => [],
                                            'type'       => $view,
                                            'page'       => 1,
                                        ],
                                        array_except(app('request')->input(), ['ip', 'ips_count', 'page'])
                                    )
                                )
                            }}"
                            class="{{
                                isset(app('request')->input()['ips_count']) && app('request')->input()['ips_count'] == $userDropCount
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ !isset(app('request')->input()['ips_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                    </li>
                    @foreach ($ips as $ip)
                        <li>
                            <a
                                href="{{
                                    action(
                                        $action, array_merge(
                                            [
                                                'ip'    => $ip,
                                                'type'  => $view,
                                                'page'  => 1,
                                            ],
                                            array_except(app('request')->input(), ['ip', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['ip']) && $ip == $selectedIp
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ $ip }}</a>
                        </li>
                    @endforeach
                </div>
            </ul>
        </li>
    </ul>
@endif

