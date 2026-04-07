@extends('backend.layouts.app')

@section('content')
    @php
        $workflowBase = max($totalOrders, 1);
        $completionRate = $totalOrders > 0 ? round(($statusCounts['delivered'] / $totalOrders) * 100) : 0;
        $returnRate = $totalOrders > 0 ? round(($statusCounts['returned'] / $totalOrders) * 100) : 0;
    @endphp

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h3 mb-1">{{ $pickup_point->getTranslation('name') }}</h1>
                <p class="text-muted mb-0">
                    {{ translate('Pickup point activity overview, workflow tracking, and recent operations') }}
                </p>
            </div>
            <div class="col-md-4 text-md-right">
                <a href="{{ route('pick_up_points.index') }}" class="btn btn-soft-secondary">
                    {{ translate('Back to Pickup Points') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row gutters-5 mb-4">
        <div class="col-md-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="text-muted fs-12">{{ translate('Total Orders') }}</div>
                    <div class="fs-24 fw-700 text-dark">{{ $totalOrders }}</div>
                    <div class="text-muted fs-12 mt-2">{{ translate('Active workflow orders') }}: {{ $activeOrders }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="text-muted fs-12">{{ translate('Completion Rate') }}</div>
                    <div class="fs-24 fw-700 text-success">{{ $completionRate }}%</div>
                    <div class="text-muted fs-12 mt-2">{{ translate('Delivered orders') }}: {{ $statusCounts['delivered'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="text-muted fs-12">{{ translate('Return Due') }}</div>
                    <div class="fs-24 fw-700 text-warning">{{ $returnDueOrders }}</div>
                    <div class="text-muted fs-12 mt-2">{{ translate('Reached orders past hold window') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="text-muted fs-12">{{ translate('Return Rate') }}</div>
                    <div class="fs-24 fw-700 text-danger">{{ $returnRate }}%</div>
                    <div class="text-muted fs-12 mt-2">{{ translate('Returned orders') }}: {{ $statusCounts['returned'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gutters-5 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Branch Details') }}</h5>
                </div>
                <div class="card-body fs-13">
                    <div class="mb-2"><strong>{{ translate('Manager') }}:</strong> {{ optional(optional($pickup_point->staff)->user)->name ?: translate('No Manager Assigned') }}</div>
                    <div class="mb-2"><strong>{{ translate('Internal Code') }}:</strong> {{ $pickup_point->internal_code ?: '-' }}</div>
                    <div class="mb-2"><strong>{{ translate('Phone') }}:</strong> {{ $pickup_point->phone ?: '-' }}</div>
                    <div class="mb-2"><strong>{{ translate('Working Hours') }}:</strong> {{ $pickup_point->workingHoursLabel() }}</div>
                    <div class="mb-2"><strong>{{ translate('Pickup Hold Days') }}:</strong> {{ $pickup_point->holdDays() }}</div>
                    <div class="mb-2"><strong>{{ translate('Supports COD') }}:</strong> {{ $pickup_point->supportsCod() ? translate('Yes') : translate('No') }}</div>
                    <div class="mb-2"><strong>{{ translate('Supports Return') }}:</strong> {{ $pickup_point->supportsReturn() ? translate('Yes') : translate('No') }}</div>
                    <div class="mb-2"><strong>{{ translate('Status') }}:</strong>
                        <span class="badge badge-inline {{ $pickup_point->pick_up_status ? 'badge-success' : 'badge-danger' }}">
                            {{ $pickup_point->pick_up_status ? translate('Open') : translate('Close') }}
                        </span>
                    </div>
                    <div class="mb-0"><strong>{{ translate('Address') }}:</strong> {{ $pickup_point->getTranslation('address') }}</div>
                    @if (!empty($pickup_point->instructions))
                        <div class="mt-2"><strong>{{ translate('Instructions') }}:</strong> {{ $pickup_point->instructions }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Workflow Tracking') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row gutters-5">
                        @foreach ($workflowSteps as $step)
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="fw-700">{{ $step['label'] }}</div>
                                        <span class="badge badge-inline badge-info">{{ $step['count'] }}</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ round(($step['count'] / $workflowBase) * 100) }}%;"></div>
                                    </div>
                                    <div class="text-muted fs-12">{{ $step['description'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gutters-5 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Operational Snapshot') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row gutters-5">
                        <div class="col-sm-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted fs-12">{{ translate('Upcoming') }}</div>
                                <div class="fs-20 fw-700">{{ $statusCounts['pending'] + $statusCounts['confirmed'] }}</div>
                                <div class="text-muted fs-12">{{ translate('Pending') }}: {{ $statusCounts['pending'] }}, {{ translate('Confirmed') }}: {{ $statusCounts['confirmed'] }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted fs-12">{{ translate('Ready For Pickup') }}</div>
                                <div class="fs-20 fw-700">{{ $statusCounts['reached'] }}</div>
                                <div class="text-muted fs-12">{{ translate('Waiting at branch for customer handover') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted fs-12">{{ translate('Last Activity') }}</div>
                                <div class="fs-15 fw-700">{{ $latestActivityAt ? \Carbon\Carbon::parse($latestActivityAt)->format('d M, Y h:i A') : '-' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted fs-12">{{ translate('Latest Delivered') }}</div>
                                <div class="fs-15 fw-700">{{ $latestDeliveredAt ? \Carbon\Carbon::parse($latestDeliveredAt)->format('d M, Y h:i A') : '-' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted fs-12">{{ translate('Latest Reached') }}</div>
                                <div class="fs-15 fw-700">{{ $latestReachedAt ? \Carbon\Carbon::parse($latestReachedAt)->format('d M, Y h:i A') : '-' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted fs-12">{{ translate('Closed Workflow') }}</div>
                                <div class="fs-20 fw-700">{{ $deliveredOrReturned }}</div>
                                <div class="text-muted fs-12">{{ translate('Delivered + Returned orders') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Status Breakdown') }}</h5>
                </div>
                <div class="card-body">
                    @foreach ([
                        'pending' => 'secondary',
                        'confirmed' => 'info',
                        'picked_up' => 'primary',
                        'on_the_way' => 'warning',
                        'reached' => 'success',
                        'delivered' => 'success',
                        'returned' => 'danger',
                        'cancelled' => 'dark',
                    ] as $status => $tone)
                        <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                            <div>
                                <div class="fw-700">{{ translate(ucfirst(str_replace('_', ' ', $status))) }}</div>
                                <div class="text-muted fs-12">{{ round(($statusCounts[$status] / $workflowBase) * 100) }}% {{ translate('of total orders') }}</div>
                            </div>
                            <span class="badge badge-inline badge-{{ $tone }}">{{ $statusCounts[$status] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Payout Control') }}</h5>
        </div>
        <div class="card-body">
            <div class="row gutters-5 mb-4">
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted fs-12">{{ translate('Current Balance') }}</div>
                        <div class="fs-20 fw-700 text-dark">{{ single_price($payoutSummary['current_balance']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted fs-12">{{ translate('Requestable Balance') }}</div>
                        <div class="fs-20 fw-700 text-primary">{{ single_price($payoutSummary['requestable_balance']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted fs-12">{{ translate('Pending Payout') }}</div>
                        <div class="fs-20 fw-700 text-warning">{{ single_price($payoutSummary['pending_payout_total']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted fs-12">{{ translate('Payout Schedule') }}</div>
                        <div class="fs-20 fw-700 text-success">{{ $payoutSummary['payout_frequency_days'] }} {{ translate('Days') }}</div>
                    </div>
                </div>
            </div>

            <div class="row gutters-5 mb-4">
                <div class="col-lg-5">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-700 mb-3">{{ translate('Pickup Point Submitted Payout Info') }}</div>
                        <div class="mb-2"><strong>{{ translate('Method') }}:</strong> {{ ucfirst(str_replace('_', ' ', $pickup_point->payout_method ?: '-')) }}</div>
                        <div class="mb-2"><strong>{{ translate('Account Holder') }}:</strong> {{ $pickup_point->payout_account_name ?: '-' }}</div>
                        <div class="mb-2"><strong>{{ translate('Account / Wallet Number') }}:</strong> {{ $pickup_point->payout_account_number ?: '-' }}</div>
                        <div class="mb-2"><strong>{{ translate('Bank') }}:</strong> {{ $pickup_point->payout_bank_name ?: '-' }}</div>
                        <div class="mb-2"><strong>{{ translate('Branch') }}:</strong> {{ $pickup_point->payout_branch_name ?: '-' }}</div>
                        <div class="mb-2"><strong>{{ translate('Routing') }}:</strong> {{ $pickup_point->payout_routing_number ?: '-' }}</div>
                        <div class="mb-2"><strong>{{ translate('Wallet Type') }}:</strong> {{ $pickup_point->payout_mobile_wallet_type ?: '-' }}</div>
                        <div class="mb-0"><strong>{{ translate('Notes') }}:</strong> {{ $pickup_point->payout_notes ?: '-' }}</div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-700 mb-3">{{ translate('Payout Request Processing') }}</div>
                        <div class="table-responsive">
                            <table class="table aiz-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ translate('Amount') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                        <th>{{ translate('Requested') }}</th>
                                        <th>{{ translate('Admin Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payoutRequests as $requestItem)
                                        @php
                                            $snapshot = json_decode($requestItem->account_snapshot, true) ?: [];
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-700">{{ single_price($requestItem->amount) }}</div>
                                                @if (!empty($requestItem->message))
                                                    <div class="fs-12 text-muted">{{ $requestItem->message }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-inline {{ (int) $requestItem->status === 1 ? 'badge-success' : ((int) $requestItem->status === 2 ? 'badge-danger' : 'badge-warning') }}">
                                                    {{ $requestItem->statusLabel() }}
                                                </span>
                                                @if ($requestItem->admin_note)
                                                    <div class="fs-12 text-muted mt-1">{{ $requestItem->admin_note }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ optional($requestItem->requested_at ?: $requestItem->created_at)->format('d M, Y h:i A') }}</div>
                                                @if (!empty($snapshot['payout_method']) || !empty($snapshot['payout_account_name']))
                                                    <div class="fs-12 text-muted mt-1">
                                                        {{ ucfirst(str_replace('_', ' ', $snapshot['payout_method'] ?? '-')) }} /
                                                        {{ $snapshot['payout_account_name'] ?? '-' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if ((int) $requestItem->status === 0)
                                                    <form action="{{ route('pick_up_points.payout_requests.process', $requestItem->id) }}" method="POST" class="mb-2">
                                                        @csrf
                                                        <input type="hidden" name="status" value="approved">
                                                        <input type="text" name="payment_method" class="form-control form-control-sm mb-2" placeholder="{{ translate('Payment Method') }}">
                                                        <input type="text" name="payment_reference" class="form-control form-control-sm mb-2" placeholder="{{ translate('Reference') }}">
                                                        <textarea name="admin_note" rows="2" class="form-control form-control-sm mb-2" placeholder="{{ translate('Admin Note') }}"></textarea>
                                                        <button type="submit" class="btn btn-success btn-sm">{{ translate('Approve') }}</button>
                                                    </form>
                                                    <form action="{{ route('pick_up_points.payout_requests.process', $requestItem->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="rejected">
                                                        <textarea name="admin_note" rows="2" class="form-control form-control-sm mb-2" placeholder="{{ translate('Reject Reason') }}"></textarea>
                                                        <button type="submit" class="btn btn-danger btn-sm">{{ translate('Reject') }}</button>
                                                    </form>
                                                @else
                                                    <div class="fs-12 text-muted">
                                                        {{ optional($requestItem->processor)->name ?: translate('Processed by admin') }}<br>
                                                        {{ optional($requestItem->processed_at)->format('d M, Y h:i A') ?: '-' }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">{{ translate('No payout request found yet.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Recent Activities') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Order') }}</th>
                            <th>{{ translate('Customer') }}</th>
                            <th>{{ translate('Payment') }}</th>
                            <th>{{ translate('Workflow Status') }}</th>
                            <th>{{ translate('Last Activity') }}</th>
                            <th>{{ translate('Progress') }}</th>
                            <th class="text-right">{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            @php
                                $progressMap = [
                                    'pending' => 10,
                                    'confirmed' => 20,
                                    'picked_up' => 40,
                                    'on_the_way' => 60,
                                    'reached' => 80,
                                    'delivered' => 100,
                                    'returned' => 100,
                                    'cancelled' => 100,
                                ];
                                $lastActivityAt = $order->delivered_date ?: $order->delivery_history_date ?: $order->updated_at;
                                $isReturnDue = $order->delivery_status === 'reached' && $order->delivery_history_date
                                    ? \Carbon\Carbon::parse($order->delivery_history_date)->startOfDay()->addDays($pickup_point->holdDays())->startOfDay()->lte(\Carbon\Carbon::today())
                                    : false;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-700">{{ $order->code }}</div>
                                    <div class="fs-12 text-muted">{{ translate('Placed') }}: {{ date('d M, Y h:i A', $order->date) }}</div>
                                </td>
                                <td>
                                    <div>{{ optional($order->user)->name ?: translate('Guest') }}</div>
                                    <div class="fs-12 text-muted">{{ optional($order->user)->phone ?: optional(json_decode($order->shipping_address))->phone }}</div>
                                </td>
                                <td>
                                    <div>{{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</div>
                                    <div class="fs-12 text-muted">{{ ucfirst($order->payment_status) }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-inline {{ in_array($order->delivery_status, ['returned', 'cancelled']) ? 'badge-danger' : ($order->delivery_status === 'delivered' ? 'badge-success' : 'badge-info') }}">
                                        {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                    </span>
                                    @if ($isReturnDue)
                                        <div class="fs-12 text-warning mt-1">{{ translate('Return due now') }}</div>
                                    @endif
                                </td>
                                <td>{{ $lastActivityAt ? \Carbon\Carbon::parse($lastActivityAt)->format('d M, Y h:i A') : '-' }}</td>
                                <td style="min-width: 150px;">
                                    <div class="progress mb-1" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $progressMap[$order->delivery_status] ?? 0 }}%;"></div>
                                    </div>
                                    <div class="fs-12 text-muted">{{ $progressMap[$order->delivery_status] ?? 0 }}% {{ translate('completed') }}</div>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('pick_up_point.order_show', encrypt($order->id)) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('View Order') }}">
                                        <i class="las la-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ translate('No pickup point activity found yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
