
<div class="filter-content">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6 p-l-none">
                <div>
                    <ul class="nav filter-type right-border">
                        @foreach ($orgTypes as $orgType)
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'OrganisationController@list',
                                            array_merge(
                                                ['type' => $orgType->id],
                                                array_except(app('request')->input(), ['type', 'page', 'q'])
                                            )
                                        )
                                    }}"
                                    class="{{ (isset($getParams['type']) && $getParams['type'] == $orgType->id) ? 'active' : '' }}"
                                >{{ $orgType->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>