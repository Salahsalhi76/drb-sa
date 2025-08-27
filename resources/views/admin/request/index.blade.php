@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    <style>
        /* Layout helpers for the filter row */
        .filters-row {
            align-items: center; /* vertical centering */
        }

        /* Make each filter cell tidy */
        .filter-cell {
            margin-bottom: 10px;
        }

        .filter-cell label {
            margin-bottom: .25rem;
            font-weight: 500;
            white-space: nowrap;
        }

        /* Make inputs a bit compact and consistent */
        .filter-cell .form-control {
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
        }

        /* Button column: stretch to same height as the tallest control in the row */
        .actions-col {
            display: flex;
            height: 100%;
            align-items: stretch; /* make inner wrapper fill height */
        }

        /* Inner wrapper to stack buttons vertically while filling available height */
        .actions-wrap {
            display: flex;
            gap: .5rem;
            width: 100%;
            align-items: center;      /* center vertically when height is larger */
        }

        /* On md+ we’ll stack them to naturally fill height if needed */
        @media (min-width: 768px) {
            .actions-wrap {
                flex-direction: column;
                justify-content: stretch;
            }
        }

        /* Make buttons occupy necessary height & look balanced */
        .actions-wrap .btn {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem; /* a touch taller */
            width: 100%;
            display: flex;
            align-items: center;      /* center the label vertically inside the button */
            justify-content: center;
        }

        /* Mobile tweaks */
        @media (max-width: 767.98px) {
            .filters-row {
                align-items: stretch;  /* make cells full width on mobile */
            }
            .actions-wrap {
                flex-direction: row;   /* side-by-side on small screens */
                justify-content: center;
            }
        }
    </style>

    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="box">
                    <div class="box-header with-border">
                        <!-- Filters & Search -->
                        <div class="row filters-row g-2">

                            <!-- Search -->
                            <div class="col-12 col-md-3 filter-cell">
                                <label for="search_keyword">@lang('view_pages.search')</label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        id="search_keyword"
                                        class="form-control"
                                        placeholder="@lang('view_pages.enter_keyword')">
                                    <button id="search-btn" class="btn btn-primary">
                                        @lang('view_pages.search')
                                    </button>
                                </div>
                            </div>

                            <!-- Trip Status -->
                            <div class="col-6 col-md-2 filter-cell">
                                <label for="trip_status">@lang('view_pages.trip_status')</label>
                                <select id="trip_status" class="form-control">
                                    <option value="">@lang('view_pages.all')</option>
                                    <option value="is_completed">@lang('view_pages.completed')</option>
                                    <option value="is_cancelled">@lang('view_pages.cancelled')</option>
                                    <option value="is_trip_start">@lang('view_pages.not_yet_started')</option>
                                </select>
                            </div>

                            <!-- Paid Status -->
                            <div class="col-6 col-md-2 filter-cell">
                                <label for="is_paid">@lang('view_pages.paid_status')</label>
                                <select id="is_paid" class="form-control">
                                    <option value="">@lang('view_pages.all')</option>
                                    <option value="1">@lang('view_pages.paid')</option>
                                    <option value="0">@lang('view_pages.unpaid')</option>
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="col-6 col-md-2 filter-cell">
                                <label for="payment_opt">@lang('view_pages.payment_option')</label>
                                <select id="payment_opt" class="form-control">
                                    <option value="">@lang('view_pages.all')</option>
                                    <option value="0">@lang('view_pages.card')</option>
                                    <option value="1">@lang('view_pages.cash')</option>
                                    <option value="2">@lang('view_pages.wallet')</option>
                                    <option value="3">@lang('view_pages.cash_wallet')</option>
                                </select>
                            </div>

                            <!-- Apply / Reset (auto-stretch height, centered vertically) -->
                            <div class="col-12 col-md-3 filter-cell actions-col">
                                <div class="actions-wrap w-100">
                                    <button id="apply-filters" class="btn btn-success">
                                        @lang('view_pages.apply_filters')
                                    </button>
                                    <button id="reset-filters" class="btn btn-danger">
                                        @lang('view_pages.reset_filters')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results -->
                    <div id="js-request-partial-target" class="table-responsive">
                        <include-fragment src="requests/fetch">
                            <div style="text-align: center; font-weight: bold; padding: 20px;">
                                @lang('view_pages.loading')
                            </div>
                        </include-fragment>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('assets/js/fetchdata.min.js') }}"></script>
        <script>
            (function () {
                let search_keyword = '';

                // Pagination (preserve filters)
                document.body.addEventListener('click', function (e) {
                    const target = e.target.closest('.pagination a');
                    if (!target) return;

                    e.preventDefault();
                    const url = target.getAttribute('href');
                    const fullUrl = url + getQueryParams();
                    fetch(fullUrl)
                        .then(r => r.text())
                        .then(html => document.querySelector('#js-request-partial-target').innerHTML = html);
                });

                // Search button
                document.getElementById('search-btn').addEventListener('click', function (e) {
                    e.preventDefault();
                    search_keyword = document.getElementById('search_keyword').value.trim();
                    fetchData();
                });

                // Search on Enter
                document.getElementById('search_keyword').addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        search_keyword = this.value.trim();
                        fetchData();
                    }
                });

                // Apply Filters
                document.getElementById('apply-filters').addEventListener('click', function (e) {
                    e.preventDefault();
                    // keep current search_keyword (don’t reset)
                    fetchData();
                });

                // Reset Filters
                document.getElementById('reset-filters').addEventListener('click', function (e) {
                    e.preventDefault();
                    document.getElementById('search_keyword').value = '';
                    document.getElementById('trip_status').value = '';
                    document.getElementById('is_paid').value = '';
                    document.getElementById('payment_opt').value = '';
                    search_keyword = '';
                    fetchData();
                });

                function fetchData(pageUrl = 'requests/fetch') {
                    const params = new URLSearchParams();
                    if (search_keyword) params.set('search', search_keyword);

                    const tripStatus = document.getElementById('trip_status').value;
                    const isPaid     = document.getElementById('is_paid').value;
                    const payOpt     = document.getElementById('payment_opt').value;

                    if (tripStatus) params.set('trip_status', tripStatus);
                    if (isPaid !== '') params.set('is_paid', isPaid);
                    if (payOpt !== '') params.set('payment_opt', payOpt);

                    const url = pageUrl + (params.toString() ? '?' + params.toString() : '');

                    fetch(url)
                        .then(response => response.text())
                        .then(html => {
                            document.querySelector('#js-request-partial-target').innerHTML = html;
                        })
                        .catch(err => {
                            console.error('Fetch error:', err);
                            document.querySelector('#js-request-partial-target').innerHTML =
                                '<div class="text-danger text-center py-3">Failed to load data.</div>';
                        });
                }

                function getQueryParams() {
                    const tripStatus = document.getElementById('trip_status').value;
                    const isPaid     = document.getElementById('is_paid').value;
                    const payOpt     = document.getElementById('payment_opt').value;

                    let params = '';
                    if (search_keyword) params += '&search=' + encodeURIComponent(search_keyword);
                    if (tripStatus) params += '&trip_status=' + encodeURIComponent(tripStatus);
                    if (isPaid !== '') params += '&is_paid=' + encodeURIComponent(isPaid);
                    if (payOpt !== '') params += '&payment_opt=' + encodeURIComponent(payOpt);
                    return params;
                }
            })();
        </script>
    @endsection
