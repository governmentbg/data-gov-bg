@extends('layouts.app')

@section('content')
<div class="container home-stats">
    <div class="flash-message">
        @foreach (['danger', 'warning', 'success', 'info'] as $msg)
            @if(Session::has('alert-' . $msg))
                <p class="alert alert-{{ $msg }}">
                    {{ Session::get('alert-' . $msg) }}
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </p>
            @endif
        @endforeach
    </div>
    <div class="col-md-8 basic-stats">
        <div class="row">
            <div class="col-md-6">
                <a href="{{ url('/users/list') }}" class="reg-users">
                    <p>{{ $users }}</p>
                    <hr>
                    <p>{{ __('custom.registered_users') }}</p>
                    <img src="{{ asset('/img/reg-users.svg') }}">
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ url('organisation') }}" class="reg-orgs">
                    <p>{{ $organisations }}</p>
                    <hr>
                    <p>{{ utrans('custom.organisations', 2) }}</p>
                    <img src="{{ asset('/img/reg-orgs.svg') }}">
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <a href="{{ url('data') }}" class="data-sets">
                    <p>{{ $datasets }}</p>
                    <hr>
                    <p>{{ __('custom.data_sets') }}</p>
                    <img src="{{ asset('/img/data-sets.svg') }}">
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ url('data') }}" class="updates">
                    <p>{{ $updates }}</p>
                    <hr>
                    <p>{{ __('custom.updates') }} </p>
                    <img src="{{ asset('/img/updates.svg') }}">
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4 most-active">
        <a href="{{ isset($mostActiveOrg->uri) ? url('organisation/profile/'. $mostActiveOrg->uri) : '#' }}">
            <img src="{{ asset('/img/medal.svg') }}">
            <p>{{ __('custom.most_active_agency') }} {{ $lastMonth }}</p>
            <hr>
            <span>{{ isset($mostActiveOrg->name) ? $mostActiveOrg->name : null }}</span>
            <img src="{{ asset('img/open-data.png') }}">
        </a>
    </div>
</div>

<div class="container-fluid gray-background">
    <div class="container activity-chart">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading"> {{ __('custom.activity_dynamics') }} </h4>
                <img src="{{ asset('img/test-img/bar-chart.jpg') }}">
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h4 class="heading">{{ utrans('custom.topics') }}</h4>
            <div class="picks-box">
                <a
                    href="{{ route('dataView', [
                        'filter'    => 'healthcare',
                    ]) }}"
                >
                    <svg data-name="Layer 4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 77 77"><defs></defs><title> {{ utrans('custom.healthcare') }}</title><path class="cls-1" d="M53.55,23.72V9.45A8.94,8.94,0,0,0,44.64.53H32.36a8.94,8.94,0,0,0-8.91,8.92V23.72H9.17A8.94,8.94,0,0,0,.26,32.64V44.91a8.94,8.94,0,0,0,8.91,8.92H23.45V68.1A8.94,8.94,0,0,0,32.36,77H44.64a8.94,8.94,0,0,0,8.91-8.92V53.83H67.83a8.94,8.94,0,0,0,8.91-8.92V32.64a8.94,8.94,0,0,0-8.91-8.92Z"/></svg>
                    <p>{{ utrans('custom.healthcare') }}</p>
                </a>
                <a
                    href="{{ route('dataView', [
                        'filter'    => 'innovation',
                    ]) }}"
                >
                    <svg data-name="Layer 2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 77 77"><defs></defs><title>{{ utrans('custom.innovation') }}</title><path class="cls-1" d="M26.81,44.05A12.7,12.7,0,1,1,38.37,57.78,12.69,12.69,0,0,1,26.81,44.05ZM66.93,22.83l-3.75-4.08a.16.16,0,0,0-.23,0l-2.82,2.59A31.74,31.74,0,0,0,45.36,14V8.51A.38.38,0,0,0,45,8.12H34a.39.39,0,0,0-.39.39V13.9a31.69,31.69,0,0,0-15.17,7.2l-2.36-2.16a.16.16,0,0,0-.23,0L12.07,23a.16.16,0,0,0,0,.23l2.25,2.07a31.79,31.79,0,1,0,49.84.27l2.75-2.54a.16.16,0,0,0,0-.23ZM39.14,16.3A28.91,28.91,0,1,0,68.05,45.21,28.91,28.91,0,0,0,39.14,16.3ZM72.4,19.39a.91.91,0,0,1-.05,1.27l-2.84,2.62a.89.89,0,0,1-1.27-.05l-5.32-5.75a.89.89,0,0,1,0-1.27l2.83-2.63a.91.91,0,0,1,1.27.05l5.33,5.76Zm-65.8.2,5.33-5.76a.91.91,0,0,1,1.27-.05L16,16.4a.9.9,0,0,1,0,1.28l-5.32,5.75a.9.9,0,0,1-1.27.05L6.65,20.86a.91.91,0,0,1-.05-1.27ZM29.46,0H49.41a1,1,0,0,1,1,1V6.36a1,1,0,0,1-1,1H29.46a1,1,0,0,1-1-1V1a1,1,0,0,1,1-.95Zm5.79,44.67a4,4,0,1,0,4.31-3.62,4,4,0,0,0-4.31,3.62Zm-16-4.33L16,40.81a.36.36,0,0,0-.31.33l-.31,3.62a.35.35,0,0,0,.25.38l3.07,1a20.7,20.7,0,0,0,.86,4.92L17.1,53a.35.35,0,0,0-.1.44l1.54,3.29a.36.36,0,0,0,.4.21L22,56.32a21.3,21.3,0,0,0,3.26,3.89l-1.14,2.86a.36.36,0,0,0,.13.44l3,2.08a.36.36,0,0,0,.45,0L30,63.51a20.39,20.39,0,0,0,4.84,1.75l.43,3a.36.36,0,0,0,.33.31l3.63.32a.37.37,0,0,0,.38-.26l.94-2.87a20.45,20.45,0,0,0,5.08-.93l1.86,2.36a.35.35,0,0,0,.44.1l3.3-1.54a.37.37,0,0,0,.2-.4l-.63-3A20.51,20.51,0,0,0,54.7,59l2.81,1.12A.36.36,0,0,0,58,60L60,57a.35.35,0,0,0,0-.45l-2.06-2.3a20.47,20.47,0,0,0,1.69-4.81L62.69,49a.38.38,0,0,0,.32-.34l.31-3.62a.38.38,0,0,0-.25-.38l-3-1a20.29,20.29,0,0,0-.94-4.93l2.5-2a.36.36,0,0,0,.11-.44l-1.54-3.3a.38.38,0,0,0-.41-.2l-3.19.68a20.53,20.53,0,0,0-3.22-3.74l1.21-3a.35.35,0,0,0-.13-.44l-3-2.08a.35.35,0,0,0-.45,0L48.54,26.5A20.5,20.5,0,0,0,44,24.88l-.48-3.29a.38.38,0,0,0-.33-.32L39.52,21a.37.37,0,0,0-.38.25l-1,3.21a20.49,20.49,0,0,0-4.77.87l-2.07-2.63a.36.36,0,0,0-.44-.11l-3.29,1.54a.38.38,0,0,0-.21.41l.7,3.3a20.52,20.52,0,0,0-3.71,3.13l-3.09-1.24a.37.37,0,0,0-.44.14l-2.08,3a.36.36,0,0,0,0,.45l2.21,2.47a20.62,20.62,0,0,0-1.68,4.61Zm5.19,3.45A15,15,0,1,0,40.69,30.11,15,15,0,0,0,24.44,43.79Z"/></svg>
                    <p>{{ utrans('custom.innovation') }}</p>
                </a>
                <a
                    href="{{ route('dataView', [
                        'filter'    => 'education',
                    ]) }}"
                >
                    <svg data-name="65" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 77 77"><defs></defs><title>{{ utrans('custom.education') }}</title><polygon class="cls-1" points="71.99 56.8 65.33 56.8 66.76 51.13 70.56 51.13 71.99 56.8"/><rect class="cls-2" x="68.03" y="36.32" width="1.32" height="16.67"/><path class="cls-1" d="M71.33,51.13a2.65,2.65,0,1,0-2.64,2.7,2.64,2.64,0,0,0,2.64-2.7Z"/><path class="cls-1" d="M.33,35.88,38.47,21.3l38.2,14.58L57.24,43.31V37.48c0-4.29-9.74-6.27-18.77-6.27s-18.76,2-18.76,6.27v5.83Z"/><path class="cls-1" d="M34.07,44.63c1.38-.05,2.86-.16,4.4-.16s3.08.11,4.4.16c5.23.33,11.45,1.22,14.86,3V37.43c0-4.41-10-6.44-19.26-6.44s-19.2,2-19.2,6.44V47.66c3.41-1.81,9.57-2.7,14.8-3Z"/></svg>
                    <p>{{ utrans('custom.education') }}</p>
                </a>
                <a
                    href="{{ route('dataView', [
                        'filter'    => 'public_sector',
                    ]) }}"
                >
                    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 77 77"><defs></defs><title>{{ uctrans('custom.management_and_public') }}</title><g id="_Group_" data-name="&lt;Group&gt;"><rect id="_Path_" data-name="&lt;Path&gt;" class="cls-1" x="0.18" y="67.12" width="76.79" height="7.05"/><rect id="_Path_2" data-name="&lt;Path&gt;" class="cls-1" x="3.7" y="60.82" width="69.74" height="3.9"/><rect id="_Path_3" data-name="&lt;Path&gt;" class="cls-1" x="3.7" y="22.3" width="69.74" height="3.9"/><rect id="_Path_4" data-name="&lt;Path&gt;" class="cls-1" x="10" y="28.34" width="9.32" height="29.96"/><rect id="_Path_5" data-name="&lt;Path&gt;" class="cls-1" x="25.86" y="28.34" width="9.32" height="29.96"/><rect id="_Path_6" data-name="&lt;Path&gt;" class="cls-1" x="41.72" y="28.34" width="9.32" height="29.96"/><rect id="_Path_7" data-name="&lt;Path&gt;" class="cls-1" x="57.58" y="28.34" width="9.32" height="29.96"/><polygon id="_Path_8" data-name="&lt;Path&gt;" class="cls-1" points="5.46 18.27 73.45 18.27 38.57 3.67 5.46 18.27"/></g></svg>
                    <p>{{  uctrans('custom.management_and_public') }}</p>
                </a>
                <a
                    href="{{ route('dataView', [
                        'filter'    => 'municipalities',
                    ]) }}"
                >
                    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150"><defs></defs><title>{{ utrans('custom.municipalities') }}</title><g id="_Group_" data-name="&lt;Group&gt;"><path id="_Compound_Path_" data-name="&lt;Compound Path&gt;" class="cls-1" d="M48.63,3V147H67.21V118.15h15.6V147h19.9V3ZM65.88,95.37H56.71V72.89h9.17Zm0-28.75H56.71V44.14h9.17Zm0-27.84H56.71V16.3h9.17ZM80.26,95.37H71.08V72.89h9.18Zm0-28.75H71.08V44.14h9.18Zm0-27.84H71.08V16.3h9.18ZM94.63,95.37H85.46V72.89h9.17Zm0-28.75H85.46V44.14h9.17Zm0-27.84H85.46V16.3h9.17Z"/><path id="_Compound_Path_2" data-name="&lt;Compound Path&gt;" class="cls-1" d="M34.56,123.94V109.87H46.18v-9.79H34.56V86H46.18V75H34.56V60.93H46.18V50.56H34.56V36.49H46.18V27.31H0V139.56H46.18V123.94Zm-11.47,0H9V109.87H23.09Zm0-23.86H9V86H23.09Zm0-25.08H9V60.93H23.09Zm0-24.44H9V36.49H23.09Z"/><path id="_Compound_Path_3" data-name="&lt;Compound Path&gt;" class="cls-1" d="M144.21,134.15V43.83H106.28v8.56H135v4.9H106.28v8.56H135v4.87H106.28v8.56H135v4.16H106.28V92H135v4.59H106.28v8.56H135v7.95H106.28v28.3h44v-7.25Zm-15.91-.4H111.17V121.21H128.3Z"/></g></svg>
                    <p>{{ utrans('custom.municipalities') }}</p>
                </a>
                <a
                    href="{{ route('dataView', [
                        'filter'    => 'agriculture',
                    ]) }}"
                >
                    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 77 77"><defs></defs><title>{{ utrans('custom.agriculture') }}</title><path class="cls-1" d="M10,77c0-20.27,19.43-26.51,26.15-8.28,8-11.27,14.87,0,14.87,0,0-5,20.27-18.47,20.27,8.28Z"/><path class="cls-1" d="M69.49,17.15c-9.57,3-34.3,2.29-34.42,30.75h0V68.72h2.16V48C69.81,46.83,67.38,19.26,69.49,17.15ZM53.15,28.61c-.77.51-1.55,1.1-2.36,1.69s-1.6,1.26-2.44,1.89-1.61,1.36-2.4,2.07-1.57,1.42-2.24,2.21a28,28,0,0,0-2,2.28A24.18,24.18,0,0,0,40,41,24.34,24.34,0,0,0,38.6,43.2c-.38.71-.75,1.35-1.09,1.93s-.5,1.14-.7,1.57c-.26.59-.46,1-.57,1.2h-.08c0-.22,0-.68.1-1.34.05-.49.09-1.1.21-1.78s.36-1.43.62-2.25a13.79,13.79,0,0,1,1-2.57,16,16,0,0,1,1.53-2.7,21.41,21.41,0,0,1,2-2.66A30.58,30.58,0,0,1,44,32.13c.86-.75,1.74-1.48,2.63-2.15s1.82-1.27,2.75-1.79a23.7,23.7,0,0,1,2.7-1.44c.86-.45,1.72-.76,2.5-1.08A23.45,23.45,0,0,1,60,24.23s-1.93,1.14-4.67,2.91C54.66,27.61,53.92,28.07,53.15,28.61Z"/><path class="cls-1" d="M10.64,24.7c4.75.83,18.63.83,22.23,23.32C17.4,49.08,10.64,24.7,10.64,24.7Z"/><path class="cls-1" d="M34,28.3c-.13-.64-.27-1.33-.39-2.06s-.38-1.48-.55-2.27-.42-1.59-.59-2.42a16.28,16.28,0,0,0-.67-2.46c-.26-.81-.52-1.62-.77-2.42-.14-.39-.23-.81-.38-1.19l-.48-1.11c-.32-.72-.59-1.45-.87-2.13s-.68-1.23-1-1.8A10.66,10.66,0,0,0,27.42,9a9,9,0,0,0-.79-1.11c-.24-.28-.42-.54-.57-.69l-.23-.22a5.23,5.23,0,0,1,1.08.59A6.59,6.59,0,0,1,28,8.4a8.48,8.48,0,0,1,1.3,1.31c.43.53.9,1.1,1.37,1.73s.8,1.38,1.2,2.13A12.16,12.16,0,0,1,33,15.94c.29.84.59,1.69.89,2.54a14.78,14.78,0,0,1,.62,2.59c.18.87.3,1.73.44,2.55A14.58,14.58,0,0,1,35.22,26c0,.76,0,1.49,0,2.16,0,.89,0,1.66,0,2.32C44.47,9.05,27.36,2.86,22,0c0,0-6.7,24.36,12.54,30.64C34.38,30,34.2,29.2,34,28.3Z"/></svg>
                    <p>{{ utrans('custom.agriculture') }}</p>
                </a>
                <a
                    href="{{ route('dataView', [
                        'filter'    => 'justice',
                    ]) }}"
                >
                    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 77 77"><defs></style></defs><title>{{ utrans('custom.justice') }}</title><path class="cls-1" d="M15.63,17.77l.84,1.95a44.73,44.73,0,0,1,20.76-3.58l0-2.12A47.08,47.08,0,0,0,15.63,17.77Z"/><polygon class="cls-1" points="31.26 50.71 16.79 18.2 15.63 18.72 14.47 18.2 0 50.71 2.31 51.74 15.63 21.83 28.95 51.74 31.26 50.71"/><path class="cls-1" d="M18.51,18.71a2.88,2.88,0,1,1-2.88-2.88A2.88,2.88,0,0,1,18.51,18.71Z"/><path class="cls-1" d="M15.63,62.58C24.26,62.58,31.26,57,31.26,50H0C0,57,7,62.58,15.63,62.58Z"/><polygon class="cls-1" points="37.41 5.47 37.25 14.02 37.23 16.14 36.75 71.94 38.84 71.95 38.84 5.47 37.41 5.47"/><path class="cls-1" d="M61.37,17.77l-.84,1.95a44.83,44.83,0,0,0-20.76-3.58l0-2.12A47.08,47.08,0,0,1,61.37,17.77Z"/><polygon class="cls-1" points="45.74 50.71 60.21 18.2 61.37 18.72 62.52 18.2 77 50.71 74.69 51.74 61.37 21.83 48.05 51.74 45.74 50.71"/><path class="cls-1" d="M58.49,18.71a2.88,2.88,0,1,0,2.88-2.88A2.88,2.88,0,0,0,58.49,18.71Z"/><path class="cls-1" d="M35.65,4.88A2.89,2.89,0,1,0,38.54,2,2.89,2.89,0,0,0,35.65,4.88Z"/><path class="cls-1" d="M61.37,62.58C52.74,62.58,45.74,57,45.74,50H77C77,57,70,62.58,61.37,62.58Z"/><polygon class="cls-1" points="39.59 5.47 39.75 14.02 39.77 16.14 40.25 71.95 38.16 71.95 38.16 5.47 39.59 5.47"/><path class="cls-1" d="M38.84,65.69C34,65.69,30,68.79,30,72.62h8.81Z"/><polygon class="cls-1" points="30.02 71.94 25.42 71.94 25.42 76 38.84 76 38.84 71.94 30.02 71.94"/><path class="cls-1" d="M47,72.62c0-3.83-3.94-6.93-8.81-6.93v6.93Z"/><polygon class="cls-1" points="51.58 71.94 46.97 71.94 38.16 71.94 38.16 76 51.58 76 51.58 71.94"/></svg>
                    <p>{{ utrans('custom.justice') }}</p>
                </a>
                <a
                    href="{{ route('dataView', [
                        'filter'    => 'economy_business',
                    ]) }}"
                >
                    <svg data-name="Layer 2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 77 77"><defs></style></defs><title>{{ uctrans('custom.economy_business') }}</title><rect class="cls-1" x="3.69" y="61.61" width="70.5" height="3.39"/><polygon class="cls-1" points="68.84 16.02 70.03 17.7 46.3 34.02 32.11 14 0 36.37 0.01 39.99 31.43 18.1 45.61 37.99 71.73 20.11 72.92 21.8 77 14.56 68.84 16.02"/><polygon class="cls-1" points="4.21 58.84 15.96 58.84 15.96 31.34 4.21 39.75 4.21 58.84"/><polygon class="cls-1" points="71.11 24 60.61 31.42 60.61 58.84 72.36 58.84 72.36 25.76 71.11 24"/><polygon class="cls-1" points="46.51 40.56 46.51 58.84 58.26 58.84 58.26 32.26 46.54 40.54 46.51 40.56"/><polygon class="cls-1" points="18.31 30.5 18.31 58.84 30.06 58.84 30.06 22.07 18.31 30.5"/><polygon class="cls-1" points="32.41 23.78 32.41 58.84 44.16 58.84 44.16 40.85 32.41 23.78"/></svg>
                    <p>{{ uctrans('custom.economy_business') }}</p>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
