@extends('pickup_points.layouts.app')

@section('panel_content')
<div class="card shadow-none rounded-0 border">
    <div class="card-header border-bottom-0">
        <h5 class="mb-0 fs-20 fw-700 text-dark">{{ translate('Upcoming Orders') }}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead class="text-gray fs-12">
                <tr>
                    <th class="pl-0">{{ translate('Code') }}</th>
                    <th>{{ translate('Date') }}</th>
                    <th>{{ translate('Amount') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Mark As Pickup') }}</th>
                    <th class="text-right pr-0">{{ translate('Options') }}</th>
                </tr>
            </thead>
            <tbody class="fs-14">
                @foreach ($upcoming_orders as $order)
                    <tr>
                        <td class="pl-0"><a href="{{ route('pickup-point.order-detail', encrypt($order->id)) }}">{{ $order->code }}</a></td>
                        <td>{{ date('d-m-Y h:i A', $order->date) }}</td>
                        <td class="fw-700">{{ single_price($order->grand_total) }}</td>
                        <td>{{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="updatePickupStatus(this, 'picked_up')" value="{{ $order->id }}" type="checkbox">
                                <span class="slider round"></span>
                            </label>
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
        <div class="aiz-pagination mt-2">{{ $upcoming_orders->appends(request()->input())->links() }}</div>
    </div>
</div>
@endsection

@section('script')
<script>
    function updatePickupStatus(el, status) {
        $.post('{{ route('pickup-point.orders.update-delivery-status') }}', {
            _token: '{{ csrf_token() }}',
            order_id: el.value,
            status: status
        }, function (data) {
            AIZ.plugins.notify('success', data.message || '{{ translate('Delivery status has been updated') }}');
            location.reload();
        }).fail(function (xhr) {
            el.checked = false;
            AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Something went wrong') }}');
        });
    }
</script>
@endsection
