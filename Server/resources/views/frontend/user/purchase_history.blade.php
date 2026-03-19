@extends('frontend.layouts.user_panel')

@section('panel_content')
<div class="card shadow-none rounded-0 border p-4">
    <h5 class="mb-2 fs-20 fw-700 text-dark">{{ translate('Purchase History') }}</h5>

    <!-- Tabs & Filters -->
    <div class="border-bottom pb-3">
        <!-- Desktop tabs (hidden on mobile) -->
        <div class="d-flex justify-content-between align-items-center d-none d-md-flex">
            <ul class="nav nav-tabs purchase-history-tab border-0 fs-12 ml-n3" id="orderTabs">
                @foreach (['All', 'Unpaid', 'Confirmed', 'Picked_Up', 'Delivered', 'To Review'] as $status)
                <li class="nav-item">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                        onclick="changeTab(this, '{{ Str::slug($status) }}')">
                        {{ translate($status) }}
                    </button>
                </li>
                @endforeach
            </ul>
    
            <div class="form-group mb-0 w-25">
                <select class="form-control aiz-selectpicker purchase-history" name="delivery_status" id="delivery_status"
                    data-style="btn-light" data-width="100%">
                    <option value="">{{ translate('All') }}</option>
                    <option value="pending" {{ request('delivery_status') == 'pending' ? 'selected' : '' }}>{{ translate('Pending') }}</option>
                    <option value="on_the_way" {{ request('delivery_status') == 'on_the_way' ? 'selected' : '' }}>{{ translate('On The Way') }}</option>
                    <option value="delivered" {{ request('delivery_status') == 'delivered' ? 'selected' : '' }}>{{ translate('Delivered') }}</option>
                    <option value="cancelled" {{ request('delivery_status') == 'cancelled' ? 'selected' : '' }}>{{ translate('Cancelled') }}</option>
                </select>
            </div>
        </div>
    
        <!-- Mobile view (shown only on mobile) -->
        <div class="d-md-none">
    
            <!-- Box container for status options -->
            <div class="d-flex flex-wrap gap-2 mb-3" id="mobileStatusBoxes">
                @foreach (['All', 'Unpaid', 'Confirmed', 'Picked_Up', 'Delivered', 'To Review'] as $status)
                <div class="status-box-wrapper" style="flex: 1 0 calc(33.333% - 0.5rem);">
                    <button class="btn btn-outline-secondary w-100 py-2 px-1 text-truncate {{ $loop->first ? 'active' : '' }}"
                        onclick="selectStatusBox(this, '{{ Str::slug($status) }}')"
                        data-status="{{ Str::slug($status) }}">
                        {{ translate($status) }}
                    </button>
                </div>
                @endforeach
            </div>
    
            <!-- Mobile filter dropdown -->
            <div class="form-group mb-0">
                <select class="form-control aiz-selectpicker purchase-history fs-14" name="mobile_delivery_status" id="mobile_delivery_status"
                    data-style="btn-light" data-width="100%">
                    <option value="">{{ translate('All Delivery Status') }}</option>
                    <option value="pending" {{ request('delivery_status') == 'pending' ? 'selected' : '' }}>{{ translate('Pending') }}</option>
                    <option value="on_the_way" {{ request('delivery_status') == 'on_the_way' ? 'selected' : '' }}>{{ translate('On The Way') }}</option>
                    <option value="delivered" {{ request('delivery_status') == 'delivered' ? 'selected' : '' }}>{{ translate('Delivered') }}</option>
                    <option value="cancelled" {{ request('delivery_status') == 'cancelled' ? 'selected' : '' }}>{{ translate('Cancelled') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Dynamic Tab Content -->
    <div class="tab-content mt-4" id="orderTabContent">
        <div class="tab-pane fade show active" id="tab-content">
            <!-- AJAX content will load here -->
        </div>
    </div>
</div>

<style>
/* Mobile status boxes styling */
#mobileStatusBoxes button {
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    min-height: 44px;
    border-width: 1.5px;
}

#mobileStatusBoxes button.active {
    background-color: #fa3e00;
    border-color: #fa3e00;
    color: white;
}

/* Hover effect */
#mobileStatusBoxes button:hover:not(.active) {
    background-color: rgba(250, 62, 0, 0.1);
    border-color: #fa3e00;
    color: #fa3e00;
}

#mobileStatusBoxes button.active:hover {
    background-color: #e03600;
    border-color: #e03600;
}

/* Make boxes 3 per row on all mobile screens */
@media (max-width: 767px) {
    .status-box-wrapper {
        flex: 1 0 calc(33.333% - 0.5rem) !important;
    }
}

/* For very small screens where 3 boxes might be too tight */
@media (max-width: 320px) {
    .status-box-wrapper {
        flex: 1 0 calc(50% - 0.5rem) !important;
    }
}

/* For tablet screens (768px to 991px) - show 4 per row */
@media (min-width: 768px) and (max-width: 991px) {
    .status-box-wrapper {
        flex: 1 0 calc(25% - 0.5rem) !important;
    }
}

/* Hide desktop on mobile, hide mobile on desktop */
.d-md-none {
    display: none;
}

@media (max-width: 767.98px) {
    .d-md-none {
        display: block;
    }
    
    .d-none.d-md-flex {
        display: none !important;
    }
}
</style>
@endsection

@section('modal')
<!-- Product Review Modal -->
<div class="modal fade" id="product-review-modal">
    <div class="modal-dialog">
        <div class="modal-content" id="product-review-modal-content"></div>
    </div>
</div>

<!-- Delete modal -->
<div id="delete-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{translate('Cancel Confirmation')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mt-1 fs-14">{{translate('Are you sure to Cancel this Order?')}}</p>
                <button type="button" class="btn btn-secondary rounded-5 mt-2 btn-sm" data-dismiss="modal">{{translate('No')}}</button>
                <a href="" id="delete-link" class="btn btn-primary rounded-5 mt-2 btn-sm">{{translate('Yes')}}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    let currentTab = 'all';

    function getOrderData(slug, page = 1) {
        currentTab = slug;
        $('#tab-content').html('<div class="footable-loader mt-5"><span class="fooicon fooicon-loader"></span></div>');
        $.ajax({
            url: `{{ route('purchase_history.filter') }}?page=${page}`,
            method: 'GET',
            data: {
                tab: slug.replace(/-/g, '_'),
            },
            success: function(response) {
                $('#tab-content').html(response.html);
            },
            error: function() {
                $('#tab-content').html('<div class="text-danger p-4">{{ translate("Failed to load data.") }}</div>');
            }
        });
    }

    function changeTab(button, statusSlug) {
        document.querySelectorAll('#orderTabs .nav-link').forEach(el => el.classList.remove('active'));
        button.classList.add('active');
        getOrderData(statusSlug);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const deliverySelect = document.getElementById('delivery_status');

        function loadOrdersByStatus(status) {
            getOrderData(status);
        }

        deliverySelect.addEventListener('change', function() {
            loadOrdersByStatus(this.value || 'all');
            document.querySelectorAll('#orderTabs .nav-link').forEach(el => el.classList.remove('active'));
        });
        const urlParams = new URLSearchParams(window.location.search);
        const toReviewParam = urlParams.get('to_review');
        if (toReviewParam && (toReviewParam === '1')) {
            const toReviewBtn = document.querySelector(`#orderTabs button[onclick*="to-review"]`);
            if (toReviewBtn) {
                document.querySelectorAll('#orderTabs .nav-link').forEach(el => el.classList.remove('active'));
                toReviewBtn.classList.add('active');
                getOrderData('to-review');
            }

        } else {
            loadOrdersByStatus('all');
        }
    });


    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        getOrderData(currentTab, page);
    });

    function product_review(product_id,order_id) {
        $.post(`{{ route('product_review_modal') }}`, {
            _token: '{{ @csrf_token() }}',
            product_id: product_id,
            order_id: order_id
        }, function(data) {
            $('#product-review-modal-content').html(data);
            $('#product-review-modal').modal('show', {
                backdrop: 'static'
            });
            AIZ.extra.inputRating();
        });
    }

    $(document).on('click', '.confirm-delete', function (e) {
        e.preventDefault();
        let url = $(this).data('href');
        $('#delete-link').attr('href', url);
        $('#delete-modal').modal('show');
    });
</script>


<script>
function selectStatusBox(element, statusSlug) {
    // Remove active class from all boxes
    document.querySelectorAll('#mobileStatusBoxes button').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.add('btn-outline-secondary');
    });
    
    // Add active class to clicked box
    element.classList.remove('btn-outline-secondary');
    element.classList.add('active');
    
    // Update dropdown
    const dropdown = document.getElementById('mobileStatusDropdown');
    if (dropdown) {
        dropdown.value = statusSlug;
    }
    
    // Trigger tab change
    changeTab(element, statusSlug);
}

function changeMobileTab(statusSlug) {
    // Find and click the corresponding box
    const box = document.querySelector(`#mobileStatusBoxes button[data-status="${statusSlug}"]`);
    if (box) {
        selectStatusBox(box, statusSlug);
    }
}

// Your existing changeTab function
function changeTab(element, statusSlug) {
    // Your existing tab change logic here
    console.log('Tab changed to:', statusSlug);
    
    // If on desktop, also update the desktop tabs
    const desktopTabs = document.querySelectorAll('#orderTabs .nav-link');
    desktopTabs.forEach(tab => {
        if (tab.getAttribute('onclick').includes(statusSlug)) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });
}
</script>

@endsection