@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'dataset'])
    <div class="row">
        <div class="col-sm-3 col-xs-12 text-left">
            <span class="badge badge-pill m-t-lg new-data user-add-btn"><a href="{{ url('/admin/dataset/add') }}">{{ __('custom.add_new_dataset') }}</a></span>
        </div>
        <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field">
            <form method="GET">
                <input
                    type="text"
                    class="m-t-md input-border-r-12 form-control"
                    placeholder="{{ __('custom.search') }}.."
                    value="{{ isset($search) ? $search : '' }}"
                    name="q"
                >
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 sidenav col-xs-12 m-t-md">
            <form
                method="GET"
                action="{{ action('Admin\DataSetController@listDatasets', []) }}"
            >
                <div class="row m-b-sm">
                    <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.from') }}:</div>
                    <div class="col-md-7 col-sm-8 text-left search-field admin">
                        <input class="js-from-filter datepicker input-border-r-12 form-control" name="from" value="{{ $range['from'] }}">
                    </div>
                </div>
                <div class="row m-b-sm">
                    <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.to') }}:</div>
                    <div class="col-md-7 col-sm-8 text-left search-field admin">
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
            </form>
            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.organisations') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['orgs_count'])
                                    ? action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            ['orgs_count' => $orgDropCount],
                                            array_except(app('request')->input(), ['orgs_count'])
                                        )
                                    )
                                    : action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            [
                                                'orgs_count'    => null,
                                                'org'           => [],
                                            ],
                                            array_except(app('request')->input(), ['org', 'orgs_count'])
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
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['org' => array_merge([$id], $selectedOrgs)],
                                                    array_except(app('request')->input(), ['org'])
                                                )
                                            )
                                            : action(
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['org' => array_diff($selectedOrgs, [$id])],
                                                    array_except(app('request')->input(), ['org'])
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
                    </ul>
                </li>
            </ul>
            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.groups') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['groups_count'])
                                    ? action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            ['groups_count' => $groupDropCount],
                                            array_except(app('request')->input(), ['groups_count'])
                                        )
                                    )
                                    : action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            [
                                                'groups_count'  => null,
                                                'group'         => [],
                                            ],
                                            array_except(app('request')->input(), ['group', 'groups_count'])
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
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['group' => array_merge([$id], $selectedGroups)],
                                                    array_except(app('request')->input(), ['group'])
                                                )
                                            )
                                            : action(
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['group' => array_diff($selectedGroups, [$id])],
                                                    array_except(app('request')->input(), ['group'])
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
                    </ul>
                </li>
            </ul>

            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.users') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['users_count'])
                                    ? action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            ['users_count' => $userDropCount],
                                            array_except(app('request')->input(), ['users_count'])
                                        )
                                    )
                                    : action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            [
                                                'users_count'  => null,
                                                'user'         => [],
                                            ],
                                            array_except(app('request')->input(), ['user', 'users_count'])
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
                                            'Admin\DataSetController@listDatasets', array_merge(
                                                ['user' => $id],
                                                array_except(app('request')->input(), ['user'])
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
                    </ul>
                </li>
            </ul>

            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.main_topic') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['categories_count'])
                                    ? action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            ['categories_count' => $catDropCount],
                                            array_except(app('request')->input(), ['categories_count'])
                                        )
                                    )
                                    : action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            [
                                                'categories_count'  => null,
                                                'category'         => [],
                                            ],
                                            array_except(app('request')->input(), ['category', 'categories_count'])
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
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['category' => array_merge([$id], $selectedCategories)],
                                                    array_except(app('request')->input(), ['category'])
                                                )
                                            )
                                            : action(
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['category' => array_diff($selectedCategories, [$id])],
                                                    array_except(app('request')->input(), ['category'])
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
                    </ul>
                </li>
            </ul>

            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.tags') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['tags_count'])
                                    ? action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            ['tags_count' => $tagsDropCount],
                                            array_except(app('request')->input(), ['tags_count'])
                                        )
                                    )
                                    : action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            [
                                                'tags_count'  => null,
                                                'tag'         => [],
                                            ],
                                            array_except(app('request')->input(), ['tag', 'tags_count'])
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
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['tag' => array_merge([$id], $selectedTags)],
                                                    array_except(app('request')->input(), ['tag'])
                                                )
                                            )
                                            : action(
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['tag' => array_diff($selectedTags, [$id])],
                                                    array_except(app('request')->input(), ['tag'])
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
                    </ul>
                </li>
            </ul>

            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.format') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            [
                                                'formatsCount'  => null,
                                                'format'         => [],
                                            ],
                                            array_except(app('request')->input(), ['format', 'formatsCount'])
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
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['format' => array_merge([$format], $selectedFormats)],
                                                    array_except(app('request')->input(), ['format'])
                                                )
                                            )
                                            : action(
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['format' => array_diff($selectedFormats, [$format])],
                                                    array_except(app('request')->input(), ['format'])
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
                    </ul>
                </li>
            </ul>

            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.terms_and_conditions') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['terms_count'])
                                    ? action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            ['terms_count' => $termsDropCount],
                                            array_except(app('request')->input(), ['terms_count'])
                                        )
                                    )
                                    : action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            [
                                                'terms_count'  => null,
                                                'term'         => [],
                                            ],
                                            array_except(app('request')->input(), ['term', 'terms_count'])
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
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['term' => array_merge([$id], $selectedTerms)],
                                                    array_except(app('request')->input(), ['term'])
                                                )
                                            )
                                            : action(
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['term' => array_diff($selectedTerms, [$id])],
                                                    array_except(app('request')->input(), ['term'])
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
                    </ul>
                </li>
            </ul>

            <form method="GET" class="inline-block">
                <div class="form-group adm-filter">
                    <label for="signaled" class="col-lg-8 col-sm-8 col-xs-12">{{ __('custom.signaled') }}:</label>
                    <div class="col-lg-4 col-sm-4 col-xs-12">
                        <input
                            type="checkbox"
                            class="js-check js-submit form-control"
                            id="signaled"
                            name="signaled"
                            value="1"
                            {{ $signaledFilter ? 'checked' : '' }}
                        >
                        @foreach (app('request')->except(['signaled']) as $key => $value)
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

        </div>
        <div class="col-xs-9">
            <div class="row m-b-lg">
                <div class="col-sm-9 col-xs-12 m-t-lg m-b-md p-l-lg">{{ __('custom.order_by') }}</div>
                <div class="col-sm-9 col-xs-12 p-l-lg order-datasets">
                    <a
                        href="{{
                            action(
                                'Admin\DataSetController@listDatasets',
                                array_merge(
                                    ['order_field' => 'name'],
                                    array_except(app('request')->input(), ['order_field'])
                                )
                            )
                        }}"

                        class="{{
                            isset(app('request')->input()['order_field'])
                            && app('request')->input()['order_field'] == 'name'
                                ? 'active'
                                : ''
                        }}"
                    >{{ __('custom.relevance') }}</a>
                    <a
                        href="{{
                            action(
                                'Admin\DataSetController@listDatasets',
                                array_merge(
                                    ['order_type' => 'asc'],
                                    array_except(app('request')->input(), ['order_type'])
                                )
                            )
                        }}"

                        class="{{
                            isset(app('request')->input()['order_type'])
                            && app('request')->input()['order_type'] == 'asc'
                                ? 'active'
                                : ''
                        }}"
                    >{{ uctrans('custom.order_asc') }}</a>
                    <a
                        href="{{
                            action(
                                'Admin\DataSetController@listDatasets',
                                array_merge(
                                    ['order_type' => 'desc'],
                                    array_except(app('request')->input(), ['order_type'])
                                )
                            )
                        }}"

                        class="{{
                            isset(app('request')->input()['order_type'])
                            && app('request')->input()['order_type'] == 'desc'
                                ? 'active'
                                : ''
                        }}"
                    >{{ uctrans('custom.order_desc') }}</a>
                    <a
                        href="{{
                            action(
                                'Admin\DataSetController@listDatasets',
                                array_merge(
                                    ['order_field' => 'updated_at'],
                                    array_except(app('request')->input(), ['order_field'])
                                )
                            )
                        }}"

                        class="{{
                            isset(app('request')->input()['order_field'])
                            && app('request')->input()['order_field'] == 'updated_at'
                                ? 'active'
                                : ''
                        }}"
                    >{{ __('custom.last_update') }}</a>
                </div>
            </div>
            <div class="articles m-t-lg">
                @if (count($datasets))
                    @foreach ($datasets as $set)
                        <div class="article m-b-lg col-xs-12 user-dataset">
                            <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                            <div class="col-sm-12 p-l-none">
                                <a href="{{ url('/admin/dataset/view/'. $set->uri) }}">
                                    <h2 class="m-t-xs">{{ $set->name }}</h2>
                                </a>
                                @if ($set->status == App\DataSet::STATUS_DRAFT)
                                    <span>({{ __('custom.draft') }})</span>
                                @else
                                    <span>({{ __('custom.published') }})</span>
                                @endif
                                <div class="desc">
                                    {{ $set->descript }}
                                </div>
                                <div class="col-sm-12 p-l-none btns">
                                    <div class="pull-left row">
                                        <div class="col-xs-6">
                                            <span class="badge badge-pill m-r-md m-b-sm">
                                                <a href="{{ url('/admin/dataset/edit/'. $set->uri) }}">{{ uctrans('custom.edit') }}</a>
                                            </span>
                                        </div>
                                        <div class="col-xs-6">
                                            <form method="POST" action="{{ url('/admin/dataset/delete') }}">
                                                {{ csrf_field() }}
                                                <div class="col-xs-6 text-right">
                                                    <button
                                                        class="badge badge-pill m-b-sm del-btn"
                                                        type="submit"
                                                        name="delete"
                                                        data-confirm="{{ __('custom.remove_data') }}"
                                                    >{{ uctrans('custom.remove') }}</button>
                                                </div>
                                                <input type="hidden" name="dataset_uri" value="{{ $set->uri }}">
                                            </form>
                                        </div>
                                    </div>
                                    <div class="pull-right">
                                        <span><a href="{{ url('/admin/dataset/view/'. $set->uri) }}">{{ __('custom.see_more') }}</a></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-sm-12 m-t-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    @if (isset($pagination))
        <div class="row">
            <div class="col-xs-12 text-center pagination">
                {{ $pagination->render() }}
            </div>
        </div>
    @endif
</div>
@endsection
