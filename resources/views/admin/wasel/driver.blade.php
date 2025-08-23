@extends('admin.layouts.app')

@section('title', 'تسجيل سائق واصل')

@section('content')

    <div class="content">
        <div class="card">
            <div class="container">

                @if (session()->has('message'))
                    <div class="alert alert-success my-3" role="alert">
                        {{ session()->get('message') }}
                    </div>
                @endif

                @error('error')
                    <div class="alert alert-danger my-3" role="alert">
                        {{ $message }}.
                    </div>
                @enderror

                <form action="{{ route('wasel.driver.store', ['id' => $driver->id]) }}" method="POST">
                    @csrf
                    <div>
                        <h3>معلومات السائق</h3>
                        <hr />
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group m-b-25">
                                    <label for="identityNumber">رقم الهوية <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="identityNumber" name="identityNumber"
                                        required placeholder="" value="{{ old('identityNumber',  $driver->identityNumber) }}">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group m-b-25">
                                    <label for="dateOfBirthGregorian">تاريخ الميلاد <span class="text-danger">*</span></label>
                                    <input class="form-control" type="date" id="dateOfBirthGregorian"
                                        name="dateOfBirthGregorian" required value="{{ old('dateOfBirthGregorian', $driver->dateOfBirthGregorian) }}"
                                        placeholder="dd/mm/yyyy">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group m-b-25">
                                    <label for="dateOfBirthHijri">تاريخ الميلاد الهجري <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control hijri-date-input" type="text" id="dateOfBirthHijri"
                                        name="dateOfBirthHijri" required value="{{ old('dateOfBirthHijri', $driver->dateOfBirthHijri) }}"
                                        placeholder="dd/mm/yyyy">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group m-b-25">
                                    <label for="mobileNumber">رقم جوال السائق <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="mobileNumber" name="mobileNumber"
                                        required value="{{ old('mobileNumber', "+966".$driver->mobile) }}" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3>معلومات المركبة</h3>
                        <hr />
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group m-b-25">
                                    <label for="sequenceNumber">رقم التسلسل <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="sequenceNumber" name="sequenceNumber"
                                        required value="{{ old('sequenceNumber', $driver->sequenceNumber) }}" placeholder="">
                                </div>
                            </div>



                            <div class="col-3">
                                <div class="form-group m-b-25">
                                    <label for="plateLetterRight">الحرف الأيمن للوحة <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="plateLetterRight" name="plateLetterRight"
                                        required value="{{ old('plateLetterRight', mb_substr($driver->carNumber, 0, 1)) }}" placeholder="">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group m-b-25">
                                    <label for="plateLetterMiddle">الحرف الأوسط للوحة <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="plateLetterMiddle"
                                        name="plateLetterMiddle" value="{{ old('plateLetterMiddle', mb_substr($driver->carNumber, 1, 1)) }}" required
                                        placeholder="">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group m-b-25">
                                    <label for="plateLetterLeft">الحرف الأيسر للوحة <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="plateLetterLeft" name="plateLetterLeft"
                                        required value="{{ old('plateLetterLeft', mb_substr($driver->carNumber, 2, 1)) }}" placeholder="">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group m-b-25">
                                    <label for="plateNumber">رقم اللوحة <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="plateNumber" name="plateNumber" required
                                        value="{{ old('plateNumber', mb_substr($driver->carNumber, 3)) }}" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-12">
                            <button class="btn btn-primary btn-sm m-5 pull-right" type="submit">
                                @lang('view_pages.save')
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
