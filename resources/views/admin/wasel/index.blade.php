@extends('admin.layouts.app')

@section('title', 'تسجيل سائق واصل')

@section('content')

    <div class="content">
        <div class="container">
            @foreach ($data as $data)
                @php
                    $driver = \App\Models\Admin\Driver::where('identityNumber', $data['identityNumber'])->first();
                @endphp

                <div class="card mb-4 p-3">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <h5>اسم السائق</h5>
                                <p>{{ $driver ? $driver->name : 'الاسم غير موجود' }}</p>
                            </div>
                            <div class="col-md-3">
                                <h5>رقم الهوية</h5>
                                <p>{{ $data['identityNumber'] }}</p>
                            </div>
                            <div class="col-md-3">
                                <h5>أهلية السائق</h5>
                                <p class="{{ $data['driverEligibility'] === 'VALID' ? 'text-success' : 'text-danger' }}">
                                    {{ $data['driverEligibility'] }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <h5>{{ $data['driverEligibility'] === 'INVALID' ? 'سبب الرفض' : 'تاريخ انتهاء الأهلية' }}</h5>
                                <p class="{{ $data['driverEligibility'] === 'VALID' ? 'text-primary' : 'text-danger text-bold' }}">
                                    {{ $data['driverEligibility'] === 'INVALID' ? $data['driverRejectionReason'] : ($data['eligibilityExpiryDate'] ?? 'N/A') }}
                                </p>
                            </div>
                        </div>
                        <hr>
                        @foreach ($data['vehicles'] as $vehicle)
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <h6>لوحة المركبة</h6>
                                    <p>{{ $vehicle['vehiclePlate'] }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6>أهلية المركبة</h6>
                                    <p class="{{ $vehicle['vehicleEligibility'] === 'VALID' ? 'text-success' : 'text-danger' }}">
                                        {{ $vehicle['vehicleEligibility'] }}
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <h6>{{ $vehicle['vehicleEligibility'] === 'VALID' ? 'تاريخ انتهاء الأهلية' : 'سبب الرفض' }}</h6>
                                    <p>{{ $vehicle['vehicleEligibility'] === 'VALID' ? ($vehicle['eligibilityExpiryDate'] ?? 'N/A') : $vehicle['vehicleRejectionReason'] }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6>تاريخ انتهاء رخصة المركبة</h6>
                                    <p>{{ $vehicle['vehicleLicenseExpiryDate'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <hr>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@endsection
