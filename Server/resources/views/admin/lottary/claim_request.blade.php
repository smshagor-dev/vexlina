@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Prize Claim Requests</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Claim Requests</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Manage Claim Requests</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filter Tabs -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <ul class="nav nav-pills mb-3">
                                        <li class="nav-item">
                                            <a class="nav-link {{ !request()->has('send_gift') ? 'active' : '' }}" 
                                               href="{{ route('admin.lottary.claim.request') }}">
                                                All Requests
                                                <span class="badge badge-light ml-1">{{ $totalCount ?? count($data) }}</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->get('send_gift') === '0' ? 'active' : '' }}" 
                                               href="{{ route('admin.lottary.claim.request', ['send_gift' => 0]) }}">
                                                Pending
                                                <span class="badge badge-warning ml-1">{{ $pendingCount ?? 0 }}</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->get('send_gift') === '1' ? 'active' : '' }}" 
                                               href="{{ route('admin.lottary.claim.request', ['send_gift' => 1]) }}">
                                                Sent
                                                <span class="badge badge-success ml-1">{{ $sentCount ?? 0 }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Search Form -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <form method="GET" action="{{ route('admin.lottary.claim.request') }}">
                                        @if(request()->has('send_gift'))
                                            <input type="hidden" name="send_gift" value="{{ request()->get('send_gift') }}">
                                        @endif
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="search" 
                                                   placeholder="Search by ticket number or claim code..."
                                                   value="{{ request()->get('search') }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-primary" type="submit">
                                                    <i class="fas fa-search"></i> Search
                                                </button>
                                                @if(request()->has('search'))
                                                <a href="{{ route('admin.lottary.claim.request', request()->only('send_gift')) }}" 
                                                   class="btn btn-outline-secondary">
                                                    <i class="fas fa-times"></i> Clear
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="{{ route('admin.lottary.claim.request') }}" class="btn btn-success">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </a>
                                </div>
                            </div>

                            <!-- Claims Table -->
                            @if(count($data) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Lottery & Prize</th>
                                            <th>Drew & Request Date</th>
                                            <th>Ticket Details</th>
                                            <th>Contact</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data as $index => $claim)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration + ($claims->currentPage() - 1) * $claims->perPage() }}</td>
                                            <td>
                                                <div class="user-info">{{ $claim['user_name'] }}</div>
                                            </td>
                                            <td>
                                                <div class="lottery-info">
                                                    <div><strong>{{ $claim['lottary_title'] }}</strong></div>
                                                    <div class="prize-name">{{ $claim['prize_name'] }}</div>
                                                </div>
                                            </td>
                                            <td style="min-width: 150px; font-size: 0.875rem; line-height: 1.4;">
                                                @php
                                                    $drawDate = $claim['draw_date'] ? \Carbon\Carbon::parse($claim['draw_date']) : null;
                                                    $requestDate = $claim['request_date'] ? \Carbon\Carbon::parse($claim['request_date']) : null;
                                                @endphp
                                            
                                                @if($drawDate)
                                                    <div>
                                                        <strong>Draw:</strong> {{ $drawDate->format('d M Y h:i A') }}
                                                    </div>
                                                @else
                                                    <div><strong>Draw:</strong> N/A</div>
                                                @endif
                                            
                                                @if($requestDate)
                                                    <div>
                                                        <strong>Request:</strong> {{ $requestDate->format('d M Y h:i A') }}
                                                    </div>
                                                @else
                                                    <div><strong>Request:</strong> N/A</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="mb-1">
                                                    <span class="ticket-number">Ticket Number: {{ $claim['ticket_number'] }}</span>
                                                </div>
                                                <div>
                                                    <small>Claim Code: <code class="claim-code">{{ $claim['claim_code'] }}</code></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div><i class="fas fa-phone mr-1"></i> {{ $claim['mobile'] ?: 'N/A' }}</div>
                                                <div class="text-muted small mt-1">
                                                    <i class="fas fa-map-marker-alt mr-1"></i> 
                                                    {{ $claim['claim_request_address'] ? substr($claim['claim_request_address'], 0, 20) . (strlen($claim['claim_request_address']) > 20 ? '...' : '') : 'Not provided' }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($claim['send_gift'] == 0)
                                                    <span style="
                                                        display: inline-block;
                                                        padding: 0.25em 0.6em;
                                                        font-size: 75%;
                                                        font-weight: 700;
                                                        line-height: 1;
                                                        color: #856404;
                                                        background-color: #fff3cd;
                                                        border-radius: 0.25rem;
                                                    ">Pending</span>
                                                @else
                                                    <span style="
                                                        display: inline-block;
                                                        padding: 0.25em 0.6em;
                                                        font-size: 75%;
                                                        font-weight: 700;
                                                        line-height: 1;
                                                        color: #155724;
                                                        background-color: #d4edda;
                                                        border-radius: 0.25rem;
                                                    ">Sent</span>
                                                @endif
                                            </td>

                                            <td>
                                                <div class="action-buttons d-flex">
                                                    <a href="{{ route('admin.lottary.claim.details', $claim['id']) }}" 
                                                       class="btn btn-sm btn-info mr-1">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                    
                                                    @if($claim['send_gift'] == 0)
                                                    <button class="btn btn-sm btn-success send-gift-btn" 
                                                            data-id="{{ $claim['id'] }}"
                                                            data-ticket="{{ $claim['ticket_number'] }}"
                                                            data-toggle="modal" 
                                                            data-target="#sendGiftModal">
                                                        <i class="fas fa-gift mr-1"></i> Send
                                                    </button>
                                                    @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-check mr-1"></i> Sent
                                                    </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted">
                                        Showing {{ ($claims->currentPage() - 1) * $claims->perPage() + 1 }} 
                                        to {{ min($claims->currentPage() * $claims->perPage(), $claims->total()) }} 
                                        of {{ $claims->total() }} entries
                                    </div>
                                    <div>
                                        {{ $claims->withQueryString()->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5>No claim requests found</h5>
                                <p class="text-muted">
                                    @if(request()->has('search'))
                                    No results found for "{{ request()->get('search') }}"
                                    @else
                                    There are currently no prize claim requests
                                    @endif
                                </p>
                                @if(request()->has('search') || request()->has('send_gift'))
                                <a href="{{ route('admin.lottary.claim.request') }}" class="btn btn-primary">
                                    <i class="fas fa-redo mr-1"></i> View All Requests
                                </a>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Send Gift Confirmation Modal -->
<div class="modal fade" id="sendGiftModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-gift mr-2"></i>Confirm Gift Delivery
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-question-circle fa-3x text-warning mb-3"></i>
                    <h5 id="modalTicketTitle"></h5>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    After confirming, the status will change to "Sent".
                </div>
                <input type="hidden" id="winnerIdToSend">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmSendGift">
                    <i class="fas fa-check mr-1"></i> Yes, Mark as Sent
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-pills .nav-link {
        border-radius: 0.25rem;
        padding: 0.5rem 1rem;
        margin-right: 0.5rem;
        cursor: pointer;
    }
    
    .nav-pills .nav-link.active {
        background-color: #007bff;
        color: white !important;
    }
    
    .table td, .table th {
        vertical-align: middle;
        padding: 0.75rem;
    }
    
    .badge {
        font-size: 0.85em;
        padding: 0.35em 0.65em;
    }
    
    .action-buttons .btn {
        margin: 2px;
    }
    
    .status-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    .ticket-number {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        color: #495057;
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        display: inline-block;
    }
    
    .claim-code {
        font-family: 'Courier New', monospace;
        background-color: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.9rem;
    }
    
    .user-info {
        font-weight: 500;
    }
    
    .lottery-info {
        font-size: 0.9rem;
    }
    
    .prize-name {
        color: #28a745;
        font-weight: 600;
    }
</style>

<script>
$(document).ready(function() {
    // Send Gift Modal Setup
    $(document).on('click', '.send-gift-btn', function() {
        const winnerId = $(this).data('id');
        const ticketNumber = $(this).data('ticket');
        $('#winnerIdToSend').val(winnerId);
        $('#modalTicketTitle').html(`
            Mark gift as sent for ticket<br>
            <strong class="text-primary">${ticketNumber}</strong>?
        `);
    });

    // Confirm Send Gift
    $('#confirmSendGift').click(function() {
        const winnerId = $('#winnerIdToSend').val();
        const btn = $(this);
        
        $.ajax({
            url: "{{ route('admin.lottary.sendgift') }}",
            type: "PUT",
            data: {
                id: winnerId,
                _token: "{{ csrf_token() }}"
            },
            beforeSend: function() {
                btn.prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                    Processing...
                `);
            },
            success: function(response) {
                if (response.status) {
                    $('#sendGiftModal').modal('hide');
                    showToast('success', response.message);
                    // Reload page after 1.5 seconds
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast('error', response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to update gift status';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 409) {
                    errorMessage = 'Gift already sent';
                } else if (xhr.status === 404) {
                    errorMessage = 'Invalid claim request';
                }
                showToast('error', errorMessage);
            },
            complete: function() {
                btn.prop('disabled', false).html(`
                    <i class="fas fa-check mr-1"></i> Yes, Mark as Sent
                `);
            }
        });
    });

    // Toast notification function
    function showToast(type, message) {
        // Create toast container if not exists
        if ($('#toastContainer').length === 0) {
            $('body').append(`
                <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
            `);
        }

        const bgColor = type === 'success' ? 'bg-success' : 
                       type === 'error' ? 'bg-danger' : 'bg-info';
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'error' ? 'exclamation-circle' : 'info-circle';
        
        const toastId = 'toast-' + Date.now();
        const toast = $(`
            <div id="${toastId}" class="toast align-items-center text-white ${bgColor} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${icon} mr-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        $('#toastContainer').append(toast);
        
        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        
        // Remove toast from DOM after hide
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});
</script>
@endsection