@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    <section class="content">

        <div class="container mt-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>User Details</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Name:</strong></div>
                        <div class="col-md-8">{{ $user->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Email:</strong></div>
                        <div class="col-md-8">{{ $user->email ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Mobile:</strong></div>
                        <div class="col-md-8">{{ $user->mobile ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Amount Balance:</strong></div>
                        <div class="col-md-8">{{ $user->userWallet->amount_balance . ' SAR' ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Joined on:</strong></div>
                        <div class="col-md-8">{{ $user->created_at->format('d-m-Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

    </section>




@endsection
