@if (isset($resPagination))
    <div class="row">
        <div class="col-xs-12 text-right">
            {{ $resPagination->render() }}
        </div>
    </div>
@endif
