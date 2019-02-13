@if (isset($sorting))
    <div class="row m-b-md">
        <div class="col-xs-12 m-b-sm">{{ __('custom.order_by_c') }}:</div>
        <div class="col-xs-12 order-documents">
            @if ($sorting == 'userGroupDatasetView' || $sorting == 'adminGroupDatasetView')
                <a
                    href="{{
                        route(
                            $sorting,
                            array_merge(
                                [$grpUri, $uri, 'order' => 'name'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'name'
                            ? 'active'
                            : ''
                    }}"
                >{{ utrans('custom.name') }}</a><a
                    href="{{
                        route(
                            $sorting,
                            array_merge(
                                [$grpUri, $uri, 'order' => 'created_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'created_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_created') }}</a><a
                    href="{{
                        route(
                            $sorting,
                            array_merge(
                                [$grpUri, $uri, 'order' => 'updated_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'updated_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_updated') }}</a>
                <a
                    href="{{
                        route(
                            $sorting,
                            array_merge(
                                [$grpUri, $uri, 'order_type' => 'asc'],
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
                        route(
                            $sorting,
                            array_merge(
                                [$grpUri, $uri, 'order_type' => 'desc'],
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
            @elseif ($sorting == 'adminOrgDatasetView' || $sorting == 'userOrgDatasetView')
                <a
                    href="{{
                        route(
                            $sorting,
                            array_merge(
                                [$uri, 'order' => 'name'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'name'
                            ? 'active'
                            : ''
                    }}"
                >{{ utrans('custom.name') }}</a><a
                    href="{{
                        route(
                            $sorting,
                            array_merge(
                                [$uri, 'order' => 'created_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'created_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_created') }}</a><a
                    href="{{
                        route(
                            $sorting,
                            array_merge(
                                [$uri, 'order' => 'updated_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'updated_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_updated') }}</a>
                <a
                    href="{{
                        route(
                            $sorting,
                            array_merge(
                                [$uri, 'order_type' => 'asc'],
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
                        route(
                            $sorting,
                            array_merge(
                                [$uri, 'order_type' => 'desc'],
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
            @elseif ($sorting == 'adminMyData')
                <a
                    href="{{
                        route(
                            'adminDatasetView',
                            array_merge(
                                [$uri, 'order' => 'name'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'name'
                            ? 'active'
                            : ''
                    }}"
                >{{ utrans('custom.name') }}</a><a
                    href="{{
                        route(
                            'adminDatasetView',
                            array_merge(
                                [$uri, 'order' => 'created_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'created_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_created') }}</a><a
                    href="{{
                        route(
                            'adminDatasetView',
                            array_merge(
                                [$uri, 'order' => 'updated_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'updated_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_updated') }}</a>
                <a
                    href="{{
                        route(
                            'adminDatasetView',
                            array_merge(
                                [$uri, 'order_type' => 'asc'],
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
                        route(
                            'adminDatasetView',
                            array_merge(
                                [$uri, 'order_type' => 'desc'],
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
            @elseif ($sorting == 'dataView')
                <a
                    href="{{
                        route(
                            'dataView',
                            array_merge(
                                [$uri, 'order' => 'name'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'name'
                            ? 'active'
                            : ''
                    }}"
                >{{ utrans('custom.name') }}</a><a
                    href="{{
                        route(
                            'dataView',
                            array_merge(
                                [$uri, 'order' => 'created_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'created_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_created') }}</a><a
                    href="{{
                        route(
                            'dataView',
                            array_merge(
                                [$uri, 'order' => 'updated_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'updated_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_updated') }}</a>
                <a
                    href="{{
                        route(
                            'dataView',
                            array_merge(
                                [$uri, 'order_type' => 'asc'],
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
                        route(
                            'dataView',
                            array_merge(
                                [$uri, 'order_type' => 'desc'],
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
            @elseif ($sorting == 'orgViewDataset')
                <a
                    href="{{
                        route(
                            'orgViewDataset',
                            array_merge(
                                [$uri, 'order' => 'name'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'name'
                            ? 'active'
                            : ''
                    }}"
                >{{ utrans('custom.name') }}</a><a
                    href="{{
                        route(
                            'orgViewDataset',
                            array_merge(
                                [$uri, 'order' => 'created_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'created_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_created') }}</a><a
                    href="{{
                        route(
                            'orgViewDataset',
                            array_merge(
                                [$uri, 'order' => 'updated_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'updated_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_updated') }}</a>
                <a
                    href="{{
                        route(
                            'orgViewDataset',
                            array_merge(
                                [$uri, 'order_type' => 'asc'],
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
                        route(
                            'orgViewDataset',
                            array_merge(
                                [$uri, 'order_type' => 'desc'],
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
            @elseif ($sorting == 'userDatasetView')
                <a
                    href="{{
                        route(
                            'userDatasetView',
                            array_merge(
                                [$uri, 'order' => 'name'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'name'
                            ? 'active'
                            : ''
                    }}"
                >{{ utrans('custom.name') }}</a><a
                    href="{{
                        route(
                            'userDatasetView',
                            array_merge(
                                [$uri, 'order' => 'created_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'created_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_created') }}</a><a
                    href="{{
                        route(
                            'userDatasetView',
                            array_merge(
                                [$uri, 'order' => 'updated_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'updated_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_updated') }}</a>
                <a
                    href="{{
                        route(
                            'userDatasetView',
                            array_merge(
                                [$uri, 'order_type' => 'asc'],
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
                        route(
                            'userDatasetView',
                            array_merge(
                                [$uri, 'order_type' => 'desc'],
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
            @endif
        </div>
    </div>
@endif
