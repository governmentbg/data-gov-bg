@if (isset($resPagination))
    <div class="row">
        <div class="col-xs-12 col-sm-4" style="padding-top: 30px;">
            @if (
                $resPagination instanceof Illuminate\Pagination\LengthAwarePaginator
                && !empty($resPagination->items())
            )
                {{ sprintf(
                    __('custom.resource_pagination_info'),
                    $resPagination->items()['from'],
                    $resPagination->items()['to'],
                    $resPagination->total()
                ) }}
            @endif
        </div>
        <div class="col-xs-12 col-sm-8 text-right">
            {{ $resPagination->render() }}
        </div>
    </div>
@endif
