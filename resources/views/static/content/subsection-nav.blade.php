@if (isset($subsections))
    <div class="row static-pages">
        <div class="col-xs-12 p-h-sm p-l-r-none">
            <div class="filter-content">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    @foreach ($subsections as $subsec)
                                        <li>
                                            <a
                                                href="{{ isset($subsec->base_url) ? $subsec->base_url : '' }}"
                                                class="{{
                                                    isset(app('request')->input()['subsection'])
                                                    && app('request')->input()['subsection'] == $subsec->id
                                                        ? 'active'
                                                        : ''
                                                }}"
                                            >{{ $subsec->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
