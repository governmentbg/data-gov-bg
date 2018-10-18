<div class="modal inmodal fade" id="embed-resource" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-sm">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">{{ __('custom.close') }}</span>
                    </button>
                    <h2>{{ uctrans('custom.embed_resource') }}</h2>
                </div>
                <div class="modal-body">
                    <p class="embed-content">{{ __('custom.embed_info') }}</p>
                    <div class="row">
                        <div class="col-sm-6 col-xs-12 p-l-r-none m-t-sm m-t-sm">
                            <label for="width" class="col-sm-4 col-xs-12 col-form-label">{{ uctrans('custom.width') }}</label>
                            <div class="col-xs-12">
                                <input
                                    id="js-width"
                                    class="input-border-r-12 form-control"
                                    type="text"
                                    name="width"
                                    value="700"
                                    placeholder=""
                                >
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12 p-l-r-none m-t-sm">
                            <label for="height" class="col-sm-4 col-xs-12 col-form-label">{{ uctrans('custom.height') }}</label>
                            <div class="col-xs-12">
                                <input
                                    id="js-height"
                                    class="input-border-r-12 form-control"
                                    type="text"
                                    name="height"
                                    value="400"
                                    placeholder=""
                                >
                            </div>
                        </div>
                        <div class="col-xs-12 p-l-r-none m-t-sm">
                            <label for="code" class="col-sm-4 col-xs-12 col-form-label">{{ uctrans('custom.code') }}</label>
                            <div class="col-xs-12">
                                <textarea
                                    id="js-code"
                                    class="input-border-r-12 form-control"
                                    name="code"
                                    rows="4"
                                    class="col-sm-12"
                                ></textarea>
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