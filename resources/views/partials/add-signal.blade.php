<div class="modal inmodal fade" id="addSignal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">{{ __('custom.close') }}</span>
                    </button>
                    <h2>{{ __('custom.send_signal') }}</h2>
                </div>
                <div class="modal-body">
                    <div id="js-alert-success" class="alert alert-success" role="alert" hidden>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <p>{{ __('custom.send_signal_success') }}</p>
                    </div>
                    <div id="js-alert-danger" class="alert alert-danger" role="alert" hidden>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <p>{{ __('custom.send_signal_error') }}</p>
                    </div>
                    <form id="sendSignal" method="POST" action="{{ url($postUrl) }}" class="m-t-lg">
                        {{ csrf_field() }}
                        <input type="hidden" name="resource_id" value="{{ $resource->id }}">
                        <div class="form-group row required">
                            <label for="fname" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.name') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    id="fname"
                                    class="input-border-r-12 form-control"
                                    name="firstname"
                                    type="text"
                                    value="{{ !empty($userData) ? $userData['firstname'] : '' }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="lname" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.family_name') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    id="lname"
                                    class="input-border-r-12 form-control"
                                    name="lastname"
                                    type="text"
                                    value="{{ !empty($userData) ? $userData['lastname'] : '' }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="email" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.e_mail') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    id="email"
                                    class="input-border-r-12 form-control"
                                    name="email"
                                    type="email"
                                    value="{{ !empty($userData) ? $userData['email'] : '' }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.description') }}:</label>
                            <div class="col-sm-9">
                                <textarea
                                    id="description"
                                    class="input-border-r-12 form-control"
                                    name="description"
                                ></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="button" class="m-l-md btn btn-danger" data-dismiss="modal">{{ uctrans('custom.close') }}</button>
                                <button type="submit" class="m-l-md btn btn-custom">{{ uctrans('custom.send') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>