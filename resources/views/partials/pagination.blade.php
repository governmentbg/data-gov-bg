@if (isset($pagination))
    <div class="row">
        <div class="col-xs-12 text-center">
            {{ $pagination->render() }}
        </div>
    </div>
@endif
