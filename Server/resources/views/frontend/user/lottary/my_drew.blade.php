@extends('frontend.layouts.user_panel')

@section('panel_content')
<div class="page-content" style="min-height: calc(100vh - 60px); display: flex; flex-direction: column;">
    <!-- Header Section - Centered -->
    <div style="padding: 1.5rem 1rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); text-align: center;">
        <div class="header-section">
            <h1 style="font-size: 1.75rem; font-weight: 600; color: #212529; margin-bottom: 0.5rem; line-height: 1.2;">
                {{ translate('My Lottery Wins') }}
            </h1>
            <p style="color: #6c757d; margin-bottom: 1.5rem; font-size: 1rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                {{ translate('View and manage your lottery winnings') }}
            </p>
            
            <div class="wins-counter" style="display: inline-flex; align-items: center; background: white; padding: 0.75rem 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);">
                <span style="
                    display: inline-block;
                    background-color: #e62e04;
                    color: #ffffff;
                    font-size: 1rem;
                    padding: 0.5rem 1rem;
                    border-radius: 6px;
                    margin-right: 12px;
                    font-weight: 500;
                ">
                    {{ translate('Total Wins') }}:
                </span>
                
                <span id="totalWins" style="
                    font-size: 2rem;
                    font-weight: 700;
                    color: #e62e04;
                    vertical-align: middle;
                ">
                    0
                </span>
            </div>
        </div>
    </div>
    <div style="margin-bottom: 1.5rem;">
        <div>
            <div style="background: white; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 0.5rem;">
                <div style="padding: 0rem;">
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;" id="filterTabs">
                        <button class="btn btn-primary btn-sm active" data-filter="all" style="background-color: #e62e04; border-color: #e62e04; color: white; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem;">{{ translate('All Wins') }}</button>
                        <button class="btn btn-outline-secondary btn-sm" data-filter="new" style="background-color: white; border: 1px solid #dee2e6; color: #6c757d; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem;">{{ translate('New Wins') }}</button>
                        <button class="btn btn-outline-secondary btn-sm" data-filter="old" style="background-color: white; border: 1px solid #dee2e6; color: #6c757d; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem;">{{ translate('Previous Wins') }}</button>
                        <button class="btn btn-outline-secondary btn-sm" data-filter="claimed" style="background-color: white; border: 1px solid #dee2e6; color: #6c757d; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem;">{{ translate('Claimed') }}</button>
                        <button class="btn btn-outline-secondary btn-sm" data-filter="unclaimed" style="background-color: white; border: 1px solid #dee2e6; color: #6c757d; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem;">{{ translate('Unclaimed') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingState">
        <div>
            <div style="background: white; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 0.5rem;">
                <div style="padding: 3rem 0; text-align: center;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem; border-width: 0.25em;">
                        <span class="visually-hidden">{{ translate('Loading...') }}</span>
                    </div>
                    <p style="margin-top: 1rem; margin-bottom: 0; color: #6c757d;">{{ translate('Loading your lottery wins...') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div id="emptyState" style="display: none;">
        <div>
            <div style="background: white; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 0.5rem;">
                <div style="padding: 3rem 0; text-align: center;">
                    <div style="margin-bottom: 1.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#6c757d" class="bi bi-gift" viewBox="0 0 16 16" style="opacity: 0.5;">
                            <path d="M3 2.5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1 5 0v.006c0 .07 0 .27-.038.494H15a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 1 14.5V7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2.038A2.968 2.968 0 0 1 3 2.506V2.5zm1.068.5H7v-.5a1.5 1.5 0 1 0-3 0c0 .085.002.274.045.43a.522.522 0 0 0 .023.07zM9 3h2.932a.56.56 0 0 0 .023-.07c.043-.156.045-.345.045-.43a1.5 1.5 0 0 0-3 0V3zM1 4v2h6V4H1zm8 0v2h6V4H9zm5 3H9v8h4.5a.5.5 0 0 0 .5-.5V7zm-7 8V7H2v7.5a.5.5 0 0 0 .5.5H7z"/>
                        </svg>
                    </div>
                    <h4 style="color: #212529; margin-bottom: 0.75rem;">{{ translate('No Wins Yet') }}</h4>
                    <p style="color: #6c757d; margin-bottom: 1.5rem;">{{ translate('You haven\'t won any lottery yet. Keep participating for a chance to win exciting prizes!') }}</p>
                    <a href="{{ route('home') }}" style="background-color: #e62e04; border-color: #e62e04; color: white; padding: 0.5rem 1.5rem; border-radius: 0.375rem; text-decoration: none; display: inline-flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ticket-detailed" viewBox="0 0 16 16" style="margin-right: 0.5rem;">
                            <path d="M4 5.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5M5 7a1 1 0 0 0 0 2h6a1 1 0 1 0 0-2z"/>
                            <path d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 0 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 1 0 0-3A.5.5 0 0 1 0 6zM1.5 4a.5.5 0 0 0-.5.5v1.05a2.5 2.5 0 0 1 0 4.9v1.05a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-1.05a2.5 2.5 0 0 1 0-4.9V4.5a.5.5 0 0 0-.5-.5z"/>
                        </svg>
                        {{ translate('Browse Lotteries') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="winsContainer" style="display: none; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
        <!-- Wins will be dynamically loaded here -->
    </div>
</div>

<!-- Claim Modal -->
<div class="modal fade" id="claimModal" tabindex="-1" aria-labelledby="claimModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <h5 style="color: #e62e04; font-weight: 600; margin: 0;">{{ translate('Claim Your Prize') }}</h5>
                <button type="button" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; padding: 0.25rem; cursor: pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#6c757d" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                    </svg>
                </button>
            </div>
            <form id="claimForm">
                <div style="padding: 1.5rem;">
                    <input type="hidden" id="winnerId" name="winner_id">
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="mobile" style="font-weight: 500; color: #495057; display: block; margin-bottom: 0.5rem;">
                            {{ translate('Mobile Number') }} <span style="color: #e62e04;">*</span>
                        </label>
                        <input type="tel" id="mobile" name="mobile" required 
                               placeholder="{{ translate('Enter your mobile number') }}"
                               style="width: 100%; border: 1px solid #ced4da; border-radius: 0.375rem; padding: 0.5rem 0.75rem; font-size: 1rem;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="address" style="font-weight: 500; color: #495057; display: block; margin-bottom: 0.5rem;">
                            {{ translate('Delivery Address') }} <span style="color: #e62e04;">*</span>
                        </label>
                        <textarea id="address" name="address" rows="4" required 
                                  placeholder="{{ translate('Enter your complete delivery address') }}"
                                  style="width: 100%; border: 1px solid #ced4da; border-radius: 0.375rem; padding: 0.5rem 0.75rem; font-size: 1rem; resize: vertical;"></textarea>
                    </div>
                </div>
                <div style="background-color: #f8f9fa; border-top: 1px solid #dee2e6; padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" data-bs-dismiss="modal" 
                            style="background-color: #6c757d; border: 1px solid #6c757d; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; cursor: pointer;">
                        {{ translate('Cancel') }}
                    </button>
                    <button type="submit" id="claimSubmitBtn"
                            style="background-color: #e62e04; border: 1px solid #e62e04; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center;">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true" style="width: 1rem; height: 1rem; border-width: 0.2em;"></span>
                        {{ translate('Submit Claim') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border: none; border-radius: 0.5rem; overflow: hidden;">
            <div style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 1rem 1.5rem; position: relative;">
                <h5 style="color: #e62e04; font-weight: 600; margin: 0; text-align: center;">{{ translate('Claim QR Code') }}</h5>
                <button type="button" id="closeQrModal" style="position: absolute; top: 50%; right: 1rem; transform: translateY(-50%); background: none; border: none; padding: 0.25rem; cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#6c757d" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                    </svg>
                </button>
            </div>
            <div style="padding: 1.5rem; text-align: center;">
                <div style="margin-bottom: 1.5rem;">
                    <div id="qrCodeDisplay" style="width: 200px; height: 200px; background-color: white; padding: 10px; border-radius: 8px; border: 1px solid #dee2e6; margin: 0 auto;"></div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <small style="color: #6c757d; display: block; margin-bottom: 0.25rem; font-size: 0.875rem;">{{ translate('Claim Code') }}</small>
                    <code id="qrClaimCode" style="background-color: #f8f9fa; color: #e62e04; font-weight: bold; padding: 0.5rem; border-radius: 0.375rem; border: 1px dashed #dee2e6; display: block; font-family: monospace; font-size: 1rem;"></code>
                </div>
                <p style="color: #6c757d; font-size: 0.875rem; margin-bottom: 0;">
                    {{ translate('Show this QR code to claim your prize at the collection point') }}
                </p>
            </div>
            <div style="background-color: #f8f9fa; border-top: 1px solid #dee2e6; padding: 1rem; text-align: center;">
                <button type="button" id="closeQrModalFooter" style="background-color: #e62e04; border: 1px solid #e62e04; color: white; padding: 0.5rem 1.5rem; border-radius: 0.375rem; cursor: pointer;">
                    {{ translate('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadLotteryWins();
    setupEventListeners();
    
    // Load QR code library dynamically
    if (typeof QRCode === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
        script.async = true;
        document.head.appendChild(script);
    }
});

let allWins = [];
let claimModalInstance = null;
let qrModalInstance = null;

function setupEventListeners() {
    // Filter tabs
    document.getElementById('filterTabs').addEventListener('click', function(e) {
        if (e.target.matches('button[data-filter]')) {
            const filter = e.target.dataset.filter;
            filterWins(filter);
            
            // Update active button
            this.querySelectorAll('button').forEach(btn => {
                btn.style.backgroundColor = 'white';
                btn.style.border = '1px solid #dee2e6';
                btn.style.color = '#6c757d';
            });
            
            e.target.style.backgroundColor = '#e62e04';
            e.target.style.borderColor = '#e62e04';
            e.target.style.color = 'white';
        }
    });
    
    // Claim form submission
    document.getElementById('claimForm').addEventListener('submit', submitClaim);
    
    // Initialize Bootstrap modals
    const claimModalElement = document.getElementById('claimModal');
    const qrModalElement = document.getElementById('qrModal');
    
    claimModalInstance = new bootstrap.Modal(claimModalElement);
    qrModalInstance = new bootstrap.Modal(qrModalElement);
    
    // QR Modal close buttons
    document.getElementById('closeQrModal').addEventListener('click', function() {
        qrModalInstance.hide();
    });
    
    document.getElementById('closeQrModalFooter').addEventListener('click', function() {
        qrModalInstance.hide();
    });
    
    // Clean up QR modal when hidden
    qrModalElement.addEventListener('hidden.bs.modal', function () {
        document.getElementById('qrClaimCode').textContent = '';
        const qrContainer = document.getElementById('qrCodeDisplay');
        qrContainer.innerHTML = '';
    });
    
    // Clean up claim modal when hidden
    claimModalElement.addEventListener('hidden.bs.modal', function () {
        document.getElementById('claimForm').reset();
    });
}

function loadLotteryWins() {
    fetch('{{ route("user.lottary.my.wins") }}', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('totalWins').textContent = data.total_wins;
            allWins = data.data || [];
            displayWins(allWins);
            document.getElementById('loadingState').style.display = 'none';
            
            if (allWins.length === 0) {
                document.getElementById('emptyState').style.display = 'block';
                document.getElementById('winsContainer').style.display = 'none';
            } else {
                document.getElementById('emptyState').style.display = 'none';
                document.getElementById('winsContainer').style.display = 'grid';
            }
        } else {
            showAlert(data.message || '{{ translate("No wins found.") }}', 'info');
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('{{ translate("Failed to load lottery wins. Please try again.") }}', 'error');
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
    });
}

function displayWins(wins) {
    const container = document.getElementById('winsContainer');
    container.innerHTML = '';
    
    if (wins.length === 0) {
        container.innerHTML = `
            <div>
                <div style="background-color: #e7f3ff; border: 1px solid #b3d7ff; color: #004085; padding: 1rem; border-radius: 0.375rem; margin-bottom: 0;">
                    {{ translate("No wins found for the selected filter.") }}
                </div>
            </div>
        `;
        return;
    }
    
    wins.forEach(win => {
        const status = win.status || 'old';
        const isClaimed = win.claim && (win.claim.claim_request === true || win.claim.claim_request === 1 || win.claim.claim_request === "1");
        const hasClaimCode = win.claim && win.claim.claim_code;
        const claimCode = hasClaimCode ? win.claim.claim_code : null;
        const prizePhoto = win.prize?.photo_url || '/placeholder-image.jpg';
        const prizeValue = win.prize?.prize_value || '{{ translate("Prize") }}';
        const lotteryTitle = win.lottary?.title || '{{ translate("Lottery") }}';
        const drewDate = win.lottary?.drew_date || new Date().toISOString();
        const ticketNumber = win.ticket_number || 'N/A';
        const sendGift = win.claim?.send_gift || 0;
        const winnerId = win.winner_id;
        
        // Determine delivery status with inline styles
        let deliveryStatus = '';
        let deliveryBgColor = '';
        let deliveryTextColor = '';
        
        if (isClaimed) {
            if (sendGift === 1 || sendGift === true) {
                deliveryStatus = '{{ translate("Delivered") }}';
                deliveryBgColor = '#198754';
                deliveryTextColor = '#ffffff';
            } else {
                deliveryStatus = '{{ translate("Processing") }}';
                deliveryBgColor = '#ffc107';
                deliveryTextColor = '#212529';
            }
        } else {
            deliveryStatus = '{{ translate("Not Claimed") }}';
            deliveryBgColor = '#6c757d';
            deliveryTextColor = '#ffffff';
        }
        
        let claimSectionHtml = '';
        
        if (isClaimed) {
            if (hasClaimCode) {
                claimSectionHtml = `
                    <div style="display: grid; gap: 0.5rem;">
                        <button onclick="showQrCode('${claimCode}')" 
                                style="background-color: white; border: 1px solid #e62e04; color: #e62e04; padding: 0.5rem; border-radius: 0.375rem; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 0.5rem;">
                                <path d="M2 2h2v2H2V2Z"/>
                                <path d="M6 0v6H0V0h6ZM5 1H1v4h4V1ZM4 12H2v2h2v-2Z"/>
                                <path d="M6 10v6H0v-6h6Zm-5 1v4h4v-4H1Zm11-9h2v2h-2V2Z"/>
                                <path d="M10 0v6h6V0h-6Zm5 1v4h-4V1h4ZM8 1V0h1v2H8v2H7V1h1Zm0 5V4h1v2H8ZM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8H6Zm0 0v1H2V8H1v1H0V7h3v1h3Zm10 1h-1V7h1v2Zm-1 0h-1v2h2v-1h-1V9Zm-4 0h2v1h-1v1h-1V9Zm2 3v-1h-1v1h-1v1H9v1h3v-2h1Zm0 0h3v1h-2v1h-1v-2Zm-4-1v1h1v-2H7v1h1Z"/>
                                <path d="M7 12h1v3h4v1H7v-4Zm9 2v2h-3v-1h2v-1h1Z"/>
                            </svg>
                            {{ translate("Show Claim Code") }}
                        </button>
                    </div>
                    <div style="margin-top: 0.75rem; text-align: center;">
                        <span style="background-color: ${deliveryBgColor}; color: ${deliveryTextColor}; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500;">
                            ${deliveryStatus}
                        </span>
                    </div>
                `;
            } else {
                claimSectionHtml = `
                    <div style="text-align: center;">
                        <span style="background-color: ${deliveryBgColor}; color: ${deliveryTextColor}; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500;">
                            ${deliveryStatus}
                        </span>
                    </div>
                `;
            }
        } else {
            claimSectionHtml = `
                <div style="display: grid;">
                    <button onclick="openClaimModal(${winnerId})" 
                            style="background-color: #e62e04; border: 1px solid #e62e04; color: white; padding: 0.5rem; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 0.5rem;">
                            <path d="M3 2.5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1 5 0v.006c0 .07 0 .27-.038.494H15a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 1 14.5V7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2.038A2.968 2.968 0 0 1 3 2.506V2.5zm1.068.5H7v-.5a1.5 1.5 0 1 0-3 0c0 .085.002.274.045.43a.522.522 0 0 0 .023.07zM9 3h2.932a.56.56 0 0 0 .023-.07c.043-.156.045-.345.045-.43a1.5 1.5 0 0 0-3 0V3zM1 4v2h6V4H1zm8 0v2h6V4H9zm5 3H9v8h4.5a.5.5 0 0 0 .5-.5V7zm-7 8V7H2v7.5a.5.5 0 0 0 .5.5H7z"/>
                        </svg>
                        {{ translate("Claim Prize") }}
                    </button>
                </div>
                <p style="font-size: 0.875rem; color: #6c757d; text-align: center; margin-top: 0.5rem; margin-bottom: 0;">
                    {{ translate("Claim your prize within 30 days of draw date") }}
                </p>
            `;
        }
        
        const winCard = document.createElement('div');
        winCard.style.cssText = `
            background: white;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            overflow: hidden;
            height: 100%;
        `;
        
        winCard.innerHTML = `
            <div style="position: relative;">
                <div style="height: 180px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); overflow: hidden;">
                    <img src="${prizePhoto}" alt="${prizeValue}" 
                         style="width: 100%; height: 100%; object-fit: cover;"
                         onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjE4MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjE4MCIgZmlsbD0iI2Y1ZjdmYSIvPjx0ZXh0IHg9IjIwMCIgeT0iOTAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzkxODA5NiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+UHJpemU6ICR7cHJpemVWYWx1ZX08L3RleHQ+PC9zdmc+'">
                </div>
                <span style="position: absolute; top: 0.5rem; right: 0.5rem; margin: 0.5rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600; background-color: ${status === 'new' ? '#dc3545' : '#343a40'}; color: white;">
                    ${status === 'new' ? '{{ translate("NEW") }}' : '{{ translate("PREVIOUS") }}'}
                </span>
            </div>
            <div style="padding: 1.5rem;">
                <h5 style="color: #212529; font-size: 1.125rem; font-weight: 600; margin-bottom: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${lotteryTitle}</h5>
                
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <span style="font-size: 1.25rem; font-weight: 700; color: #e62e04; margin-bottom: 0;">${prizeValue}</span>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;">
                    <div>
                        <small style="display: block; color: #6c757d; font-size: 0.875rem;">{{ translate("Ticket Number") }}</small>
                        <strong style="color: #212529;">${ticketNumber}</strong>
                    </div>
                    <div>
                        <small style="display: block; color: #6c757d; font-size: 0.875rem;">{{ translate("Draw Date") }}</small>
                        <strong style="color: #212529;">${formatDate(drewDate)}</strong>
                    </div>
                    <div>
                        <small style="display: block; color: #6c757d; font-size: 0.875rem;">{{ translate("Claim Status") }}</small>
                        <strong style="color: ${isClaimed ? '#198754' : '#fd7e14'};">${isClaimed ? '{{ translate("Claimed") }}' : '{{ translate("Unclaimed") }}'}</strong>
                    </div>
                    <div>
                        <small style="display: block; color: #6c757d; font-size: 0.875rem;">{{ translate("Delivery Status") }}</small>
                        <strong style="color: ${deliveryStatus === '{{ translate("Delivered") }}' ? '#198754' : deliveryStatus === '{{ translate("Processing") }}' ? '#0d6efd' : '#fd7e14'};">${deliveryStatus}</strong>
                    </div>
                </div>
                
                <div style="border-top: 1px solid #dee2e6; padding-top: 1rem;">
                    ${claimSectionHtml}
                </div>
            </div>
        `;
        
        container.appendChild(winCard);
    });
}

function filterWins(filter) {
    let filteredWins = [];
    
    switch(filter) {
        case 'new':
            filteredWins = allWins.filter(win => win.status === 'new');
            break;
        case 'old':
            filteredWins = allWins.filter(win => win.status === 'old');
            break;
        case 'claimed':
            filteredWins = allWins.filter(win => {
                const isClaimed = win.claim && (
                    win.claim.claim_request === true || 
                    win.claim.claim_request === 1 || 
                    win.claim.claim_request === "1"
                );
                return isClaimed;
            });
            break;
        case 'unclaimed':
            filteredWins = allWins.filter(win => {
                const isClaimed = win.claim && (
                    win.claim.claim_request === true || 
                    win.claim.claim_request === 1 || 
                    win.claim.claim_request === "1"
                );
                return !isClaimed;
            });
            break;
        default:
            filteredWins = allWins;
    }
    
    displayWins(filteredWins);
}

function openClaimModal(winnerId) {
    document.getElementById('winnerId').value = winnerId;
    claimModalInstance.show();
}

function submitClaim(e) {
    e.preventDefault();
    
    const winnerId = document.getElementById('winnerId').value;
    const mobile = document.getElementById('mobile').value.trim();
    const address = document.getElementById('address').value.trim();
    const submitBtn = document.getElementById('claimSubmitBtn');
    const spinner = submitBtn.querySelector('.spinner-border');
    
    if (!mobile || !address) {
        showAlert('{{ translate("Please fill in all required fields.") }}', 'error');
        return;
    }
    
    // Disable button and show spinner
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    fetch('{{ url("lottery/winner") }}/' + winnerId + '/claim', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            mobile: mobile,
            address: address
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('{{ translate("Claim submitted successfully!") }}' + (data.claim_code ? ` {{ translate("Your claim code:") }} ${data.claim_code}` : ''), 'success');
            claimModalInstance.hide();
            document.getElementById('claimForm').reset();
            
            setTimeout(() => {
                loadLotteryWins();
            }, 1000);
        } else {
            showAlert(data.message || '{{ translate("Failed to submit claim.") }}', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('{{ translate("Network error. Please check your connection and try again.") }}', 'error');
    })
    .finally(() => {
        // Re-enable button and hide spinner
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
}

function showQrCode(claimCode) {
    document.getElementById('qrClaimCode').textContent = claimCode;
    
    const qrContainer = document.getElementById('qrCodeDisplay');
    qrContainer.innerHTML = '';
    
    if (typeof QRCode !== 'undefined') {
        try {
            // Clear any previous QR code
            qrContainer.innerHTML = '';
            
            // Generate new QR code
            new QRCode(qrContainer, {
                text: claimCode,
                width: 180,
                height: 180,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (error) {
            console.error('QR Code generation error:', error);
            fallbackQrDisplay(claimCode, qrContainer);
        }
    } else {
        fallbackQrDisplay(claimCode, qrContainer);
    }
    
    // Show modal
    qrModalInstance.show();
}

function fallbackQrDisplay(claimCode, container) {
    container.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
            <div style="text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#6c757d" viewBox="0 0 16 16" style="margin-bottom: 1rem;">
                    <path d="M2 2h2v2H2V2Z"/>
                    <path d="M6 0v6H0V0h6ZM5 1H1v4h4V1ZM4 12H2v2h2v-2Z"/>
                    <path d="M6 10v6H0v-6h6Zm-5 1v4h4v-4H1Zm11-9h2v2h-2V2Z"/>
                    <path d="M10 0v6h6V0h-6Zm5 1v4h-4V1h4ZM8 1V0h1v2H8v2H7V1h1Zm0 5V4h1v2H8ZM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8H6Zm0 0v1H2V8H1v1H0V7h3v1h3Zm10 1h-1V7h1v2Zm-1 0h-1v2h2v-1h-1V9Zm-4 0h2v1h-1v1h-1V9Zm2 3v-1h-1v1h-1v1H9v1h3v-2h1Zm0 0h3v1h-2v1h-1v-2Zm-4-1v1h1v-2H7v1h1Z"/>
                    <path d="M7 12h1v3h4v1H7v-4Zm9 2v2h-3v-1h2v-1h1Z"/>
                </svg>
                <p style="margin-bottom: 0; font-family: monospace; font-size: 1rem; color: #e62e04; font-weight: bold;">${claimCode}</p>
            </div>
        </div>
    `;
}

function showAlert(message, type) {
    // Remove any existing alerts
    const existingAlert = document.querySelector('.alert-toast');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create alert element
    const alert = document.createElement('div');
    alert.className = 'alert-toast';
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1060;
        min-width: 300px;
        max-width: 400px;
        background-color: ${type === 'error' ? '#f8d7da' : type === 'success' ? '#d1e7dd' : '#cfe2ff'};
        border: 1px solid ${type === 'error' ? '#f5c2c7' : type === 'success' ? '#badbcc' : '#b6d4fe'};
        color: ${type === 'error' ? '#842029' : type === 'success' ? '#0f5132' : '#084298'};
        padding: 1rem;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
    `;
    
    alert.innerHTML = `
        <div style="display: flex; align-items: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 0.5rem;">
                ${type === 'error' ? '<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>' : 
                type === 'success' ? '<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>' :
                '<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>'}
            </svg>
            ${message}
        </div>
        <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.25rem; color: inherit; cursor: pointer; padding: 0; margin-left: 0.5rem;">
            &times;
        </button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return '{{ translate("Invalid Date") }}';
        }
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return dateString;
    }
}
</script>

<style>
    /* Mobile Responsive Styles */
    @media (max-width: 767px) {
        .wins-grid {
            grid-template-columns: 1fr !important;
            gap: 0.75rem !important;
            padding: 0 !important;
        }
        
        .wins-grid > div {
            margin: 0 0.5rem !important;
            border-radius: 0.5rem !important;
        }
        
        .header-section {
            text-align: center !important;
            padding: 0 1rem !important;
        }
        
        .header-section h1 {
            font-size: 1.25rem !important;
            margin-bottom: 0.5rem !important;
        }
        
        .header-section p {
            font-size: 0.875rem !important;
            margin-bottom: 1rem !important;
        }
        
        .wins-counter {
            justify-content: center !important;
            width: 100% !important;
        }
        
        .wins-counter span:first-child {
            font-size: 0.875rem !important;
            padding: 0.5rem 0.75rem !important;
        }
        
        #totalWins {
            font-size: 1.5rem !important;
        }
        
        /* Filter tabs mobile */
        #filterTabs {
            padding: 0.5rem !important;
            gap: 0.25rem !important;
            justify-content: center !important;
            flex-wrap: wrap !important;
        }
        
        #filterTabs button {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.75rem !important;
            min-height: 36px !important;
            flex: 1 !important;
            min-width: 100px !important;
        }
        
        /* Modal fixes for mobile */
        .modal-dialog {
            margin: 0 !important;
            max-width: 100% !important;
            height: 100% !important;
        }
        
        .modal-content {
            border-radius: 0 !important;
            min-height: 60vh !important;
            border: none !important;
        }
        
        .modal-header, .modal-footer {
            padding: 1rem !important;
        }
        
        .modal-body {
            padding: 1rem !important;
        }
        
        /* Card mobile optimization */
        .win-card img {
            height: 150px !important;
        }
        
        .win-card h5 {
            font-size: 1rem !important;
            white-space: normal !important;
        }
        
        /* Center alignment for mobile */
        body {
            display: flex !important;
            flex-direction: column !important;
            min-height: 100vh !important;
        }
        
        .container-fluid {
            padding: 0 !important;
        }
        
        .row {
            margin: 0 !important;
        }
        
        .col-xxl-9 {
            padding: 0 !important;
        }
        
        /* Remove top gap */
        .page-content {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }
    }
    
    /* Extra small devices */
    @media (max-width: 375px) {
        #filterTabs button {
            min-width: 85px !important;
            font-size: 0.7rem !important;
            padding: 0.25rem 0.5rem !important;
        }
        
        .wins-counter span:first-child {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.5rem !important;
        }
        
        #totalWins {
            font-size: 1.25rem !important;
        }
    }
    
    /* Tablet */
    @media (min-width: 768px) and (max-width: 1023px) {
        .wins-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 1rem !important;
        }
    }
    
    /* Desktop */
    @media (min-width: 1024px) {
        .wins-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }
    
    /* Touch-friendly improvements */
    button, input, textarea {
        font-size: 16px !important; /* Prevents iOS zoom */
        min-height: 44px !important; /* Minimum touch target size */
    }
    
    /* Modal backdrop for mobile */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.9) !important;
    }
    
    /* QR Code mobile optimization */
    @media (max-width: 767px) {
        #qrModal .modal-dialog {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 100vh !important;
            padding: 0.5rem !important;
        }
        
        #qrModal .modal-content {
            max-width: 320px !important;
            margin: 0 auto !important;
        }
        
        #qrCodeDisplay {
            width: 180px !important;
            height: 180px !important;
            margin: 0 auto !important;
        }
        
        #qrClaimCode {
            font-size: 0.875rem !important;
            word-break: break-all !important;
        }
    }
</style>

@endsection