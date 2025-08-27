<div class="box-body">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>@lang('view_pages.s_no') <span style="float:right;"></span></th>
                <th>@lang('view_pages.name') <span style="float:right;"></span></th>
                <th>@lang('view_pages.area') <span style="float:right;"></span></th>
                <th>@lang('view_pages.email') <span style="float:right;"></span></th>
                <th>@lang('view_pages.mobile') <span style="float:right;"></span></th>

                @if($app_for == "super" || $app_for == "bidding")
                    <th>@lang('view_pages.transport_type') <span style="float:right;"></span></th>
                @endif

                <th>@lang('view_pages.document_view') <span style="float:right;"></span></th>
                <th>@lang('view_pages.approve_status') <span style="float:right;"></span></th>
                <th>@lang('view_pages.declined_reason') <span style="float:right;"></span></th>
                <th>@lang('view_pages.rating') <span style="float:right;"></span></th>

                {{-- NEW: Cancelled rides --}}
                <th>@lang('view_pages.cancelled_rides') <span style="float:right;"></span></th>

                <th>@lang('view_pages.action') <span style="float:right;"></span></th>
            </tr>
            </thead>

            <tbody>
            @if (count($results) < 1)
                <tr>
                    <td colspan="12">
                        <p id="no_data" class="lead no-data text-center">
                            <img src="{{ asset('assets/img/dark-data.svg') }}" style="width:150px;margin-top:25px;margin-bottom:25px;" alt="">
                            <h4 class="text-center" style="color:#333;font-size:25px;">@lang('view_pages.no_data_found')</h4>
                        </p>
                    </td>
                </tr>
            @else
                @php $i = $results->firstItem(); @endphp
                @foreach ($results as $key => $result)
                    <tr>
                        <td>{{ $results->firstItem() + $key }}</td>
                        <td>{{ $result->name }}</td>

                        @if ($result->serviceLocation)
                            <td>{{ $result->serviceLocation->name }}</td>
                        @else
                            <td>--</td>
                        @endif

                        @if (env('APP_FOR') == 'demo')
                            <td>**********</td>
                        @else
                            <td>{{ $result->email }}</td>
                        @endif

                        @if (env('APP_FOR') == 'demo')
                            <td>**********</td>
                        @else
                            <td>{{ $result->mobile }}</td>
                        @endif

                        @if ($app_for == "super" || $app_for == "bidding")
                            <td>{{ $result->transport_type }}</td>
                        @endif

                        <td>
                            @if(auth()->user()->can('driver-document'))
                                <a href="{{ url('drivers/document/view', $result->id) }}" class="btn btn-social-icon btn-bitbucket">
                                    <i class="fa fa-file-text"></i>
                                </a>
                            @endif
                        </td>

                        @if ($result->approve)
                            <td><button class="btn btn-success btn-sm">{{ trans('view_pages.approved') }}</button></td>
                        @else
                            <td><button class="btn btn-danger btn-sm">{{ trans('view_pages.disapproved') }}</button></td>
                        @endif

                        @if ($result->reason)
                            <td>{{ $result->reason }}</td>
                        @else
                            <td>--</td>
                        @endif

                        <td>
                            @php $rating = $result->rating($result->user_id); @endphp
                            @foreach (range(1,5) as $i)
                                <span class="fa-stack" style="width:1em">
                                    @if ($rating > 0)
                                        @if ($rating > 0.5)
                                            <i class="fa fa-star checked"></i>
                                        @else
                                            <i class="fa fa-star-half-o"></i>
                                        @endif
                                    @else
                                        <i class="fa fa-star-o"></i>
                                    @endif
                                    @php $rating--; @endphp
                                </span>
                            @endforeach
                        </td>

                        {{-- NEW: Cancelled rides count (from withCount alias "canceled_rides_count") --}}
                        <td>
                            <span class="badge badge-danger">{{ $result->canceled_rides_count ?? 0 }}</span>
                        </td>

                        <td>
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                @lang('view_pages.action')
                            </button>

                            @if ($result->approve == 1)
                                <div class="dropdown-menu w-48">
                                    @if(auth()->user()->can('edit-drivers'))
                                        <a class="dropdown-item" href="{{ url('drivers', $result->id) }}">
                                            <i class="fa fa-pencil"></i>@lang('view_pages.edit')
                                        </a>
                                    @endif

                                    @if(auth()->user()->can('toggle-drivers'))
                                        <a class="dropdown-item decline" data-reason="{{ $result->reason }}" data-id="{{ $result->id }}" href="{{ url('drivers/toggle_approve', ['driver' => $result->id, 'approval_status' => 0]) }}">
                                            <i class="fa fa-dot-circle-o"></i>@lang('view_pages.disapproved')
                                        </a>
                                        <a class="dropdown-item" href="{{ url('drivers/toggle_approve', ['driver' => $result->id, 'approval_status' => 1]) }}">
                                            <i class="fa fa-dot-circle-o"></i>@lang('view_pages.approved')
                                        </a>
                                    @endif

                                    @if(auth()->user()->can('delete-drivers'))
                                        <a class="dropdown-item sweet-delete" href="#" data-url="{{ url('drivers/delete', $result->id) }}">
                                            <i class="fa fa-trash-o"></i>@lang('view_pages.delete')
                                        </a>
                                    @endif

                                    @if(auth()->user()->can('view-request-list'))
                                        <a class="dropdown-item" href="{{ url('drivers/request-list', $result->id) }}">
                                            <i class="fa fa-dot-circle-o"></i>@lang('view_pages.request_list')
                                        </a>
                                    @endif

                                    @if(auth()->user()->can('driver-payment-history'))
                                        <a class="dropdown-item" href="{{ url('drivers/payment-history', $result->id) }}">
                                            <i class="fa fa-dot-circle-o"></i>@lang('view_pages.driver_payment_history')
                                        </a>
                                    @endif

                                    @if(auth()->user()->can('view-driver-profile'))
                                        <a class="dropdown-item" href="{{ url('driver_profile_dashboard_view', $result->id) }}">
                                            <i class="fa fa-dot-circle-o"></i>@lang('view_pages.view_profile')
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="dropdown-menu">
                                    @if(auth()->user()->can('edit-drivers'))
                                        <a class="dropdown-item" href="{{ url('drivers', $result->id) }}">
                                            <i class="fa fa-pencil"></i>@lang('view_pages.edit')
                                        </a>
                                    @endif

                                    <a class="dropdown-item" href="{{ route('wasel.driver', $result->id) }}">
                                        <i class="fa fa-plus"></i>Registration Wasel
                                    </a>

                                    @if(auth()->user()->can('toggle-drivers'))
                                        <a class="dropdown-item decline" data-reason="{{ $result->reason }}" data-id="{{ $result->id }}" href="{{ url('drivers/toggle_approve', ['driver' => $result->id, 'approval_status' => 0]) }}">
                                            <i class="fa fa-dot-circle-o"></i>@lang('view_pages.disapproved')
                                        </a>
                                        <a class="dropdown-item" href="{{ url('drivers/toggle_approve', ['driver' => $result->id, 'approval_status' => 1]) }}">
                                            <i class="fa fa-dot-circle-o"></i>@lang('view_pages.approved')
                                        </a>
                                    @endif

                                    @if(auth()->user()->can('delete-drivers'))
                                        <a class="dropdown-item sweet-delete" href="#" data-url="{{ url('drivers/delete', $result->id) }}">
                                            <i class="fa fa-trash-o"></i>@lang('view_pages.delete')
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>

        <div class="text-right">
            <span style="float:right">
                {{ $results->links() }}
            </span>
        </div>
    </div>
</div>
