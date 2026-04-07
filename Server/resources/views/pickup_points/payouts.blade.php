@extends('pickup_points.layouts.app')

@section('panel_content')
    <div class="row gutters-5 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted fs-12">{{ translate('Current Balance') }}</div>
                    <div class="fs-22 fw-700 text-dark">{{ single_price($payoutSummary['current_balance']) }}</div>
                    <div class="text-muted fs-12 mt-2">{{ translate('Approved payouts are already deducted here') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted fs-12">{{ translate('Requestable Balance') }}</div>
                    <div class="fs-22 fw-700 text-primary">{{ single_price($payoutSummary['requestable_balance']) }}</div>
                    <div class="text-muted fs-12 mt-2">{{ translate('Pending requests are reserved from this amount') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted fs-12">{{ translate('Pending Requests') }}</div>
                    <div class="fs-22 fw-700 text-warning">{{ single_price($payoutSummary['pending_payout_total']) }}</div>
                    <div class="text-muted fs-12 mt-2">{{ translate('Waiting for admin processing') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted fs-12">{{ translate('Payout Cycle') }}</div>
                    <div class="fs-22 fw-700 text-success">{{ $payoutSummary['payout_frequency_days'] }} {{ translate('Days') }}</div>
                    <div class="text-muted fs-12 mt-2">
                        {{ $payoutSummary['next_eligible_at'] ? translate('Next eligible') . ': ' . \Carbon\Carbon::parse($payoutSummary['next_eligible_at'])->format('d M, Y') : translate('Request available now if balance exists') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gutters-5">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Payout Information') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pickup-point.payouts.update-info') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>{{ translate('Payout Method') }}</label>
                            <select name="payout_method" class="form-control aiz-selectpicker" required>
                                <option value="bank" @if(($pickupPoint->payout_method ?? 'bank') === 'bank') selected @endif>{{ translate('Bank') }}</option>
                                <option value="mobile_wallet" @if(($pickupPoint->payout_method ?? null) === 'mobile_wallet') selected @endif>{{ translate('Mobile Wallet') }}</option>
                                <option value="manual" @if(($pickupPoint->payout_method ?? null) === 'manual') selected @endif>{{ translate('Manual / Other') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Account Holder Name') }}</label>
                            <input type="text" name="payout_account_name" value="{{ $pickupPoint->payout_account_name }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Account / Wallet Number') }}</label>
                            <input type="text" name="payout_account_number" value="{{ $pickupPoint->payout_account_number }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Bank Name') }}</label>
                            <input type="text" name="payout_bank_name" value="{{ $pickupPoint->payout_bank_name }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Branch Name') }}</label>
                            <input type="text" name="payout_branch_name" value="{{ $pickupPoint->payout_branch_name }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Routing Number') }}</label>
                            <input type="text" name="payout_routing_number" value="{{ $pickupPoint->payout_routing_number }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Mobile Wallet Type') }}</label>
                            <input type="text" name="payout_mobile_wallet_type" value="{{ $pickupPoint->payout_mobile_wallet_type }}" class="form-control" placeholder="{{ translate('Example: bKash, Nagad') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Mobile Wallet Number') }}</label>
                            <input type="text" name="payout_mobile_wallet_number" value="{{ $pickupPoint->payout_mobile_wallet_number }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Additional Notes') }}</label>
                            <textarea name="payout_notes" rows="4" class="form-control">{{ $pickupPoint->payout_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ translate('Save Payout Info') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Request Payout') }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <div><strong>{{ translate('Current Balance') }}:</strong> {{ single_price($payoutSummary['current_balance']) }}</div>
                        <div><strong>{{ translate('Requestable Balance') }}:</strong> {{ single_price($payoutSummary['requestable_balance']) }}</div>
                        <div><strong>{{ translate('Schedule') }}:</strong> {{ $payoutSummary['payout_frequency_days'] }} {{ translate('days') }}</div>
                        @if (!empty($payoutSummary['eligibility_message']))
                            <div class="mt-2">{{ $payoutSummary['eligibility_message'] }}</div>
                        @endif
                    </div>
                    <form action="{{ route('pickup-point.payouts.request-store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>{{ translate('Request Amount') }}</label>
                            <input type="number" min="0.01" step="0.01" max="{{ $payoutSummary['requestable_balance'] }}" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Message to Admin') }}</label>
                            <textarea name="message" rows="5" class="form-control" placeholder="{{ translate('Add settlement note or reference information') }}"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" @if(!$payoutSummary['can_request']) disabled @endif>
                            {{ translate('Submit Payout Request') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Payout Request History') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Requested At') }}</th>
                            <th>{{ translate('Processed At') }}</th>
                            <th>{{ translate('Message') }}</th>
                            <th>{{ translate('Admin Note') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payoutRequests as $key => $requestItem)
                            <tr>
                                <td>{{ ($key + 1) + (($payoutRequests->currentPage() - 1) * $payoutRequests->perPage()) }}</td>
                                <td>{{ single_price($requestItem->amount) }}</td>
                                <td>
                                    <span class="badge badge-inline {{ (int) $requestItem->status === 1 ? 'badge-success' : ((int) $requestItem->status === 2 ? 'badge-danger' : 'badge-warning') }}">
                                        {{ $requestItem->statusLabel() }}
                                    </span>
                                </td>
                                <td>{{ optional($requestItem->requested_at ?: $requestItem->created_at)->format('d M, Y h:i A') }}</td>
                                <td>{{ optional($requestItem->processed_at)->format('d M, Y h:i A') ?: '-' }}</td>
                                <td>{{ $requestItem->message ?: '-' }}</td>
                                <td>{{ $requestItem->admin_note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ translate('No payout requests found yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="aiz-pagination mt-3">
                {{ $payoutRequests->links() }}
            </div>
        </div>
    </div>
@endsection
