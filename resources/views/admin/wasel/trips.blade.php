@extends('admin.layouts.app')

@section('title', 'تسجيل سائق واصل')

@section('content')

    <div class="content">
        <div class="card">
            <div class="container">
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>الحالة</th>
                                    <th>رسالة الخطأ</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($trips as $trip)
                                    <tr>
                                        <td>
                                            <a href="/requests/{{ $trip->request_id }}" class="text-primary" target='_blank' >

                                                {{ $trip->request_id }}
                                            </a>
                                        </td>
                                        <td class="{{ $trip->status === 'INVALID' ? 'text-danger' : 'text-success' }}">
                                            {{ $trip->status }}
                                        </td>
                                        <td class="text-danger">
                                            {{ $trip->error_message }}
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
