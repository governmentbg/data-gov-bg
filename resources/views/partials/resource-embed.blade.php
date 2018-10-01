<div class="modal inmodal fade" id="embed-resource" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">{{ __('custom.close') }}</span>
                    </button>
                    <h2>{{ uctrans('custom.embed_resource') }}</h2>
                </div>
                <div class="modal-body">
                    <p class="embed-content">{{ __('custom.embed_info') }}</p>
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="width">{{ uctrans('custom.width') }}</label>
                            <div>
                                <input id="js-width" type="text" name="width" value="700" placeholder="">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="height">{{ uctrans('custom.height') }}</label>
                            <div>
                                <input id="js-height" type="text" name="height" value="400" placeholder="">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <label for="code">{{ uctrans('custom.code') }}</label>
                            <div>
                                <textarea id="js-code" name="code"rows="3" class="col-sm-12"></textarea>
                            </div>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary m-t js-copy"
                    >{{ uctrans('custom.copy') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>