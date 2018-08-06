@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-lg">
        <p class='req-fields'>{{ __('custom.all_fields_required') }}</p>
        <p class='req-fields has-error'>{{ __('custom.all_fields_required') }}</p>
        <form>
            <div class="form-group row required">
                <label for="title" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.title') }}:</label>
                <div class="col-sm-9">
                    <input type="email" class="input-border-r-12 form-control" id="title" value="Lorem ipsum BG">
                </div>
            </div>
            <div class="form-group row required has-error">
                <label for="titleTR" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.title') }}:</label>
                <div class="col-sm-9">
                    <input type="email" class="input-border-r-12 form-control" id="titleTR" value="Lorem ipsum EN">
                </div>
            </div>
            <div class="form-group row">
                <label for="identifier" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.unique_identificator') }}:</label>
                <div class="col-sm-9">
                    <input type="text" class="input-border-r-12 form-control" id="identifier" value="256266233">
                </div>
            </div>
            <div class="form-group row">
                <label for="description" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.description') }}:</label>
                <div class="col-sm-9">
                    <textarea type="text" class="input-border-r-12 form-control" id="description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut.</textarea>
                </div>
            </div>
            <div class="form-group row">
                <label for="descriptionTR" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.description') }}:</label>
                <div class="col-sm-9">
                    <textarea type="text" class="input-border-r-12 form-control" id="descriptionTR">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut.</textarea>
                </div>
            </div>
            <div class="form-group row required">
                <label for="theme" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.main_topic') }}:</label>
                <div class="col-sm-9">
                    <input type="text" class="input-border-r-12 form-control" id="theme" value="Основна тема">
                </div>
            </div>
            <div class="form-group row tagsBG">
                <label for="tagsBG" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.label_BG') }}:</label>
                <div class="col-sm-9 example ">
                    <input type="text" class="input-border-r-12 form-control"  id="tagsBG" value="образование,наредба" data-role="tagsinput">
                </div>
            </div>
            <div class="form-group row tagsEN">
                <label for="tagsEN" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.label_EN') }}:</label>
                <div class="col-sm-9">
                    <input type="text" class="input-border-r-12 form-control"  id="tagsEN" value="education,regulation" data-role="tagsinput">
                </div>
            </div>
            <div class="form-group row">
                <label for="termsOfuse" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.terms_and_conditions') }}:</label>
                <div class="col-sm-6">
                    <select class="input-border-r-12 form-control term-use"  id="termsOfuse" size="5">
                        @for ($i = 0; $i < 5; $i++)
                        <option>Lorem ipsum</option>
                        @endfor
                    </select>
                </div>
                <div class="col-sm-3 text-right add-terms">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLicense">{{ __('custom.new_terms_and_conditions') }}</button>
                </div>
            </div>
            <div class="form-group row">
                <label for="organisation" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.organisations') }}:</label>
                <div class="col-sm-9">
                    <select class="input-border-r-12 form-control" id="organisation">
                        <option value="">{{ __('custom.select_org') }}</option>
                        <option value="idOrg">име организация</option>
                        <option value="idOrg">име организация</option>
                        <option value="idOrg">име организация</option>
                        <option value="idOrg">име организация</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="group" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.groups') }}:</label>
                <div class="col-sm-9">
                    <select class="input-border-r-12 form-control" id="group">
                        <option value="">{{ __('custom.select_group') }}</option>
                        <option value="idOrg">име група</option>
                        <option value="idOrg">име група</option>
                        <option value="idOrg">име група</option>
                        <option value="idOrg">име група</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="visibility" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.visibility') }}:</label>
                <div class="col-sm-9">
                    <select class="input-border-r-12 form-control" id="visibility">
                        <option value="">{{ __('custom.select_visibility') }}</option>
                        <option value="idVisibility">видим за всички</option>
                        <option value="idVisibility">видим за всички</option>
                        <option value="idVisibility">видим за всички</option>
                        <option value="idVisibility">видим за всички</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="source" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.source') }}:</label>
                <div class="col-sm-9">
                    <input type="text" class="input-border-r-12 form-control" id="source" value="Източник">
                </div>
            </div>
            <div class="form-group row">
                <label for="version" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.version') }}:</label>
                <div class="col-sm-9">
                    <input type="text" class="input-border-r-12 form-control" id="version" value="1.0">
                </div>
            </div>
            <div class="form-group row">
                <label for="author" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.author') }}:</label>
                <div class="col-sm-9">
                    <input type="text" class="input-border-r-12 form-control" id="author" value="Иван Иванов">
                </div>
            </div>
            <div class="form-group row">
                <label for="author-email" class="col-sm-3 col-xs-12 col-form-label">
                {{ __('custom.author_email') }}:
                    <span class="info-icon"><i class="fa fa-info"></i></span>
                </label>
                <div class="col-sm-9">
                    <input type="email" class="input-border-r-12 form-control" id="author-email" value="ivanov@abv.bg">
                </div>
            </div>
            <div class="form-group row">
                <!--Service Level Agreement (SLA) -->
                <label for="sla" class="col-sm-3 col-xs-12 col-form-label">
                {{ __('custom.sla_agreement') }}:
                    <span class="info-icon"><i class="fa fa-info"></i></span>
                </label>
                <div class="col-sm-9">
                    <textarea type="text" class="input-border-r-12 form-control" id="sla" placeholder="Service Level Agreement"></textarea>
                </div>
            </div>
            <div class="form-group row">
                @for($i = 1; $i <= 3; $i++)
                    <div class="m-t-sm col-sm-12 p-l-r-none">
                        <div class="col-xs-12 m-t-sm"> {{ __('custom.additional_field') }}:</div>
                        <div class="col-sm-12 col-xs-12 p-r-none">
                            <div class="row">
                                <div class="col-sm-6 col-xs-12">
                                    <label for="sla" class="col-sm-2 col-xs-12 col-form-label p-h-xs"> {{ __('custom.title') }}:</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="input-border-r-12 form-control" id="author-email" placeholder="Lorem ipsum">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xs-12">
                                    <label for="sla" class="col-sm-2 col-xs-12 col-form-label p-h-xs"> {{ __('custom.value') }}:</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="input-border-r-12 form-control" id="author-email" placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            <div class="form-group row">
                <div class="col-sm-12 pull right text-right">
                    <div class="row">
                        <button type="button" class="btn btn-primary">{{ __('custom.add_resource') }}</button>
                        <button type="button" class="btn btn-primary">{{ __('custom.preview') }}</button>
                        <button type="button" class="btn btn-primary">{{ __('custom.publish') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
