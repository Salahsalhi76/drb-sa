@extends('admin.layouts.app')
@section('title', 'Main page')

@section('content')
    {{-- {{session()->get('errors')}} --}}

    <!-- bootstrap datepicker -->
    <link rel="stylesheet" href="{!! asset('assets/vendor_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') !!}">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>


    <!-- Start Page content -->
    <div class="content" x-data="sequentialPromoCode()">
        <div class="container-fluid">

            <div class="row">
                <div class="col-sm-12">
                    <div class="box">

                        <div class="box-header with-border">
                            <a href="{{ url('offers') }}">
                                <button class="btn btn-danger btn-sm pull-right" type="submit">
                                    <i class="mdi mdi-keyboard-backspace mr-2"></i>
                                    @lang('view_pages.back')
                                </button>
                            </a>
                        </div>

                        <div class="col-sm-12">

                            <form method="post" class="form-horizontal" action="{{ url('offers/store') }}">
                                @csrf

                                <div class="row">

                                    {{-- Area Service Location ID --}}
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="admin_id">@lang('view_pages.select_area')
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="service_location_id" id="service_location_id"
                                                class="form-control select2"
                                                data-placeholder="@lang('view_pages.select')">
                                                <option value="">@lang('view_pages.select_area')</option>
                                                @foreach ($cities as $city)
                                                    @php
                                                        $oldServiceLocationIds = is_array(old('service_location_id'))
                                                            ? old('service_location_id')
                                                            : [];
                                                    @endphp
                                                    <option value="{{ $city->id }}"
                                                        {{ in_array($city->id, $oldServiceLocationIds) ? 'selected' : '' }}>
                                                        {{ $city->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('service_location_id') }}</span>
                                        </div>
                                    </div>
                                    {{-- Area Service Location ID --}}


                                    {{-- Request Number  --}}
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="request_number">Request Number <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="number" id="request_number" name="request_number"
                                                value="{{ old('request_number') }}" required
                                                placeholder="@lang('view_pages.enter') Request Number">
                                            <span class="text-danger">{{ $errors->first('request_number') }}</span>
                                        </div>
                                    </div>
                                    {{-- Request Number  --}}
                                </div>


                                <div class="row">
                                    {{-- From Date --}}
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="from_date">@lang('view_pages.from') <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control " type="datetime-local" id="from_date"
                                                name="from_date" value="{{ old('from_date') }}" required
                                                placeholder="@lang('view_pages.enter') @lang('view_pages.from')" autocomplete="off">
                                            <span class="text-danger">{{ $errors->first('from_date') }}</span>
                                        </div>
                                    </div>
                                    {{-- From Date --}}

                                    {{-- To Date --}}
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="to_date">@lang('view_pages.to') <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control " type="datetime-local" id="to"
                                                name="to_date" value="{{ old('to_date') }}" required=""
                                                placeholder="@lang('view_pages.enter') @lang('view_pages.to')" autocomplete="off">
                                            <span class="text-danger">{{ $errors->first('to_date') }}</span>
                                        </div>
                                    </div>
                                    {{-- To Date --}}


                                    {{-- Earning Price --}}
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="earning_price">Earning Price <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="number" id="earning_price" name="earning_price"
                                                value="{{ old('earning_price') }}" required
                                                placeholder="@lang('view_pages.enter') Earning Number">
                                            <span class="text-danger">{{ $errors->first('earning_price') }}</span>
                                        </div>
                                    </div>
                                    {{-- Earning Price --}}


                                </div>



                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="subject">Subject <span
                                                    class="text-danger">*</span></label>
                                            <textarea class="form-control" type="number" id="subject" name="subject"
                                                value="{{ old('subject') }}" required
                                                placeholder="Subject Of Offer"></textarea>
                                            <span class="text-danger">{{ $errors->first('subject') }}</span>
                                        </div>
                                    </div>
                                </div>




                                <!-- Submit Button -->
                                <div class="form-group">
                                    <div class="col-12">
                                        <button class="btn btn-primary" type="submit">Submit</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- container -->
    </div>
    <!-- content -->






@endsection
