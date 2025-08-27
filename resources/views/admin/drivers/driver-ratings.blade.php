@extends('admin.layouts.app')

@section('title', 'Driver Ratings')

@section('content')
    <style>
        .rating-filter .btn {
            padding: 6px 10px;
            font-size: 14px;
            border-radius: 4px;
            margin: 0 4px;
        }

        .rating-filter .btn input {
            display: none;
        }

        .search-section {
            margin-bottom: 15px;
        }
    </style>

    <!-- Start Page content -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="box">

                    <div class="box-header with-border">
                        <!-- Search Section -->
                        <div class="row search-section">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <input type="text" id="search_keyword" class="form-control"
                                           placeholder="@lang('view_pages.enter_keyword')">
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <button id="search" class="btn btn-success btn-sm py-2">
                                    @lang('view_pages.search')
                                </button>
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div class="row">
                            <div class="col-12">
                                <label><strong>@lang('view_pages.filter_by_rating')</strong></label>
                                <div class="btn-group rating-filter" role="group">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <label class="btn btn-outline-primary btn-sm">
                                            <input type="radio" name="rating" class="filter" value="{{ $i }}" autocomplete="off">
                                            {{ $i }} Star
                                        </label>
                                    @endfor
                                    <label class="btn btn-secondary btn-sm">
                                        <input type="radio" name="rating" class="resetfilter" value="" autocomplete="off">
                                        All
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Container -->
                    <div id="drivers-ratings">
                        <include-fragment src="{{ url('drivers/fetch/driver-ratings') }}">
                            <div style="text-align: center; font-weight: bold; padding: 20px;">
                                @lang('view_pages.loading')
                            </div>
                        </include-fragment>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('assets/js/fetchdata.min.js') }}"></script>
    <script>
        var search_keyword = '';
        var query = '';

        $(function () {
            // Pagination click
            $('body').on('click', '.pagination a', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');

                let rating = $('input[name="rating"]:checked').val();
                let extraParams = '?';
                if (search_keyword) extraParams += 'search=' + encodeURIComponent(search_keyword);
                if (rating) extraParams += (extraParams === '?' ? '' : '&') + 'rating=' + rating;

                $.get(url + extraParams, function (data) {
                    $('#drivers-ratings').html(data);
                });
            });

            // Search button
            $('#search').on('click', function (e) {
                e.preventDefault();
                search_keyword = $('#search_keyword').val();

                let rating = $('input[name="rating"]:checked').val();
                let ratingQuery = rating ? '&rating=' + rating : '';

                fetch('drivers/fetch/driver-ratings?search=' + encodeURIComponent(search_keyword) + ratingQuery)
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('#drivers-ratings').innerHTML = html;
                    });
            });

            // Filter/Reset buttons (status, area, etc.)
            $('.filter, .resetfilter').on('click', function () {
                let filterColumn = ['active', 'approve', 'available', 'area'];
                let className = $(this);
                query = '';

                // Add rating filter
                let rating = $('input[name="rating"]:checked').val();
                if (rating) {
                    query += 'rating=' + rating + '&';
                }

                // Add other filters
                $.each(filterColumn, function (index, value) {
                    if (className.hasClass('resetfilter')) {
                        $('input[name="' + value + '"]').prop('checked', false);
                        if (value === 'area') {
                            $('#service_location_id').val('all');
                        }
                    } else {
                        let $input = $('input[name="' + value + '"]:checked');
                        if ($input.length && $input.attr('id') !== undefined) {
                            let val = $input.attr('data-val');
                            query += value + '=' + val + '&';
                        } else if (value === 'area') {
                            let area = $('#service_location_id').val();
                            if (area && area !== 'all') {
                                query += 'area=' + area + '&';
                            }
                        }
                    }
                });

                // Remove trailing &
                if (query.endsWith('&')) {
                    query = query.slice(0, -1);
                }

                fetch('drivers/fetch/driver-ratings?' + query)
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('#drivers-ratings').innerHTML = html;
                    });
            });

            // Sweet delete confirmation
            $(document).on('click', '.sweet-delete', function (e) {
                e.preventDefault();
                let url = $(this).attr('data-url');
                swal({
                    title: "Are you sure to delete?",
                    type: "error",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Delete",
                    cancelButtonText: "No! Keep it",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function (isConfirm) {
                    if (isConfirm) {
                        swal.close();
                        $.ajax({
                            url: url,
                            success: function (res) {
                                let rating = $('input[name="rating"]:checked').val();
                                let ratingQuery = rating ? '&rating=' + rating : '';
                                fetch('drivers/fetch/driver-ratings?search=' + encodeURIComponent(search_keyword) + ratingQuery)
                                    .then(response => response.text())
                                    .then(html => {
                                        document.querySelector('#drivers-ratings').innerHTML = html;
                                    });

                                $.toast({
                                    heading: '',
                                    text: res,
                                    position: 'top-right',
                                    loaderBg: '#ff6849',
                                    icon: 'success',
                                    hideAfter: 5000,
                                    stack: 1
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection