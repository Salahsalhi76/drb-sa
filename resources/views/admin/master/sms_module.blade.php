@extends('admin.layouts.app')

@section('title', 'Main page')
<!-- Include flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .demo-radio-button label {
        min-width: 100px;
        margin: 0 0 5px 50px;
    }

    input[type=file]::file-selector-button {
        margin-right: 10px;
        border: none;
        background: #084cdf;
        padding: 10px 10px;
        border-radius: 5px;
        color: #fff;
        cursor: pointer;
        transition: background .2s ease-in-out;
        font-size: 10px;
    }

    input[type=file]::file-selector-button:hover {
        background: #0d45a5;
    }

    /* CSS for toggle switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #2196F3;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    a:hover {
        text-decoration: none;
    }


    .demo,
    .demo p {
        margin: 4em 0;
        text-align: center;
    }

    /**
 * Tooltip Styles
 */

    /* Add this attribute to the element that needs a tooltip */
    [data-tooltip] {
        position: relative;
        z-index: 2;
        cursor: pointer;
    }

    /* Hide the tooltip content by default */
    [data-tooltip]:before,
    [data-tooltip]:after {
        visibility: hidden;
        -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
        filter: progid: DXImageTransform.Microsoft.Alpha(Opacity=0);
        opacity: 0;
        pointer-events: none;
    }

    /* Position tooltip above the element */
    [data-tooltip]:before {
        position: absolute;
        bottom: 150%;
        left: 50%;
        margin-bottom: 5px;
        margin-left: -80px;
        padding: 7px;
        width: 160px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        background-color: #000;
        background-color: hsla(0, 0%, 20%, 0.9);
        color: #fff;
        content: attr(data-tooltip);
        text-align: center;
        font-size: 14px;
        line-height: 1.2;
    }

    /* Triangle hack to make tooltip look like a speech bubble */
    [data-tooltip]:after {
        position: absolute;
        bottom: 150%;
        left: 50%;
        margin-left: -5px;
        width: 0;
        border-top: 5px solid #000;
        border-top: 5px solid hsla(0, 0%, 20%, 0.9);
        border-right: 5px solid transparent;
        border-left: 5px solid transparent;
        content: " ";
        font-size: 0;
        line-height: 0;
    }

    /* Show tooltip content on hover */
    [data-tooltip]:hover:before,
    [data-tooltip]:hover:after {
        visibility: visible;
        -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
        filter: progid: DXImageTransform.Microsoft.Alpha(Opacity=100);
        opacity: 1;
    }
</style>
@section('content')


    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="box p-5">


                        <div class="g-col-12 g-col-lg-4">
                            <div class="tab-content mt-5">
                                <form method="post" action="{{ url('system/settings/sms_store') }}"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="row p-5">
                                        <!-- madar -->
                                        <div class="col-lg-6">
                                            <div class="box p-5 mt-5">
                                                <div
                                                    class="d-flex align-items-center justify-content-between p-5 border-bottom border-gray-200 dark-border-dark-5">
                                                    <div class="d-flex align-items-center">
                                                        <h2 class="fw-medium fs-base me-auto">Madar</h2>
                                                        <p><a href="#" data-tooltip="I’m the Madar"><i
                                                                    class="fa fa-info-circle"
                                                                    style="font-size:24px; margin-top:10px;margin-left:10px;"></i></a>
                                                        </p>
                                                    </div>
                                                    <div
                                                        class="form-check form-switch w-sm-auto ms-sm-auto mt-3 mt-sm-0 ps-0">
                                                        @php
                                                            $enabletwilioValue = $sms_settings
                                                                ->where('name', 'enable_twilio')
                                                                ->first();
                                                            // Check if $enabletwilioValue is null or 0
                                                            $twilioisChecked =
                                                                $enabletwilioValue && $enabletwilioValue->value == 1
                                                                    ? 'checked'
                                                                    : '';
                                                        @endphp

                                                        <span class="online-status"></span>
                                                        <label class="switch">
                                                            <input type="checkbox" class="online-toggle"
                                                                name="enable_twilio" value="1" {{ $twilioisChecked }}>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="p-5 text-center">
                                                    <img style="margin:auto;" src="{{ asset('assets/img/madar.png') }}"
                                                        width="200px" alt="">
                                                </div>
                                                <div class="text-end mt-5">
                                                    <button type="submit" class="btn btn-primary w-32">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- madar --}}
                                    </div>
                            </div>
                        </div>
                    </div>
                    <!-- tab end -->
                    </form>

                </div>
            </div>
        </div>
    </div>
    </div>
    <script>
        $(document).ready(function() {
            $(document).on('change', '.online-toggle', function() {
                $('.online-toggle').not(this).prop('checked', false);
                var isChecked = $(this).is(':checked');

                if (!isChecked) {
                    // Prevent unchecking if no other checkbox is checked
                    var anyChecked = $('.online-toggle:checked').length > 0;
                    if (!anyChecked) {
                        $(this).prop('checked', true);
                        alert("Please enable at least one event.");
                    }
                }

                // console.log("Status:", isChecked ? "ON" : "OFF");
            });
        });
    </script>

@endsection
