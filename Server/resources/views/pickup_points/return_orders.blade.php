@extends('pickup_points.layouts.app')

@section('panel_content')
<div class="card shadow-none rounded-0 border">
    <div class="card-header border-bottom-0">
        <h5 class="mb-0 fs-20 fw-700 text-dark">{{ translate('Return Orders') }}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead class="text-gray fs-12">
                <tr>
                    <th class="pl-0">{{ translate('Code') }}</th>
                    <th>{{ translate('Date') }}</th>
                    <th>{{ translate('Amount') }}</th>
                    <th>{{ translate('Payment Status') }}</th>
                    <th class="text-right pr-0">{{ translate('Options') }}</th>
                </tr>
            </thead>
            <tbody class="fs-14">
                @foreach ($return_orders as $order)
                    <tr>
                        <td class="pl-0"><a href="{{ route('pickup-point.order-detail', encrypt($order->id)) }}">{{ $order->code }}</a></td>
                        <td>{{ date('d-m-Y h:i A', strtotime($order->updated_at)) }}</td>
                        <td class="fw-700">{{ single_price($order->grand_total) }}</td>
                        <td>
                            @if ($order->payment_status == 'paid')
                                <span class="badge badge-inline badge-success p-3 fs-12">{{ translate('Paid') }}</span>
                            @else
                                <span class="badge badge-inline badge-danger p-3 fs-12">{{ translate('Unpaid') }}</span>
                            @endif
                        </td>
                        <td class="text-right pr-0">
                            <a href="{{ route('pickup-point.order-detail', encrypt($order->id)) }}" class="btn btn-soft-info btn-icon btn-circle btn-sm" title="{{ translate('Order Details') }}">
                                <i class="las la-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination mt-2">{{ $return_orders->appends(request()->input())->links() }}</div>
    </div>
</div>
@endsection
