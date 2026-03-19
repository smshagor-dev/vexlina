@php
    $system_currency = get_system_currency();
@endphp

@extends('frontend.layouts.user_panel')

@section('panel_content')
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">

    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('user.lottary.index') }}" 
           class="back-btn"
           style="display: inline-flex; align-items: center; gap: 8px; color: #4f46e5; text-decoration: none; font-weight: 500; padding: 10px 20px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; transition: all 0.2s;">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            {{ translate('Back to My Tickets') }}
        </a>
    </div>

    <!-- Ticket Card -->
    <div class="ticket-row">
        <div class="ticket-perforation"></div>

        <div class="ticket-content">
            <!-- Left Column -->
            <div class="ticket-main-info">
                <div class="ticket-header">
                    <span class="ticket-number-large">#{{ $ticket->ticket_number }}</span>
                    <span class="ticket-status {{ $ticket->is_drew == 1 ? 'ticket-status-completed' : 'ticket-status-pending' }}">
                        {{ $ticket->is_drew == 1 ? translate('Completed') : translate('Pending') }}
                    </span>
                    @if($ticket->is_drew == 1)
                        <span class="ticket-win-status {{ $ticket->win_status }}">
                            {{ $ticket->win_status == 'win' ? translate('Win') : translate('Lose') }}
                        </span>
                    @endif
                </div>

                <h2 class="ticket-title">{{ $ticket->title }}</h2>
                @if($ticket->description)
                <p class="ticket-description">{{ $ticket->description }}</p>
                @endif

                <div class="ticket-details-grid">
                    @php
                        $details = [
                            ['label' => translate('Ticket Holder'), 'value' => $ticket->name],
                            ['label' => translate('Contact Email'), 'value' => $ticket->email],
                            ['label' => translate('Draw Date'), 'value' => \Carbon\Carbon::parse($ticket->drew_date)->format('M d, Y')],
                            ['label' => translate('Ticket Price'), 'value' => translate('Free on Purchases')],
                            ['label' => translate('Got Ticket On'), 'value' => \Carbon\Carbon::parse($ticket->ticket_buy_date)->format('M d, Y')],
                            ['label' => translate('Contact Phone'), 'value' => $ticket->phone ?: translate('N/A')],
                        ];
                    @endphp
                    @foreach($details as $detail)
                        <div class="ticket-detail-item">
                            <span class="ticket-detail-label">{{ $detail['label'] }}</span>
                            <span class="ticket-detail-value">{{ $detail['value'] }}</span>
                        </div>
                    @endforeach
                </div>

                @if($ticket->full_address && $ticket->full_address !== 'Address not provided')
                <div class="ticket-address">
                    <span class="ticket-detail-label">{{ translate('Address') }}</span>
                    <span class="ticket-detail-value">{{ $ticket->full_address }}</span>
                </div>
                @endif

                <div class="ticket-full-id">
                    <span class="ticket-detail-label">{{ translate('Full Ticket ID') }}</span>
                    <span class="ticket-detail-value">{{ $ticket->ticket_number }}</span>
                </div>
            </div>

            <!-- Middle Column -->
            <div class="ticket-qr-section">
                <div class="ticket-qr-container">
                    <img id="qrCodeImage" alt="QR Code">
                </div>
                <div class="ticket-qr-text">
                    <div>{{ translate('Scan QR to view ticket') }}</div>
                    <div>{{ translate('Valid Ticket') }}</div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="ticket-actions" style="display: flex; gap: 0.5rem;">
                <button onclick="downloadTicketScreenshot()" class="action-btn">
                    {{ translate('Download Ticket') }}
                </button>
            
                @if($ticket->win_status === 'win')
                    <button onclick="openClaimModal('{{ $ticket->ticket_number }}')" 
                            class="action-btn" 
                            style="background-color: #e62e04; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; cursor: pointer;">
                        {{ translate('Claim Prize') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="ticket-perforation"></div>
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
                    <!-- Ticket Number -->
                     <input type="hidden" id="ticket_number" name="ticket_number">
            
                    <!-- Mobile Number -->
                    <div style="margin-bottom: 1rem;">
                        <label for="mobile" style="font-weight: 500; color: #495057; display: block; margin-bottom: 0.5rem;">
                            {{ translate('Mobile Number') }} <span style="color: #e62e04;">*</span>
                        </label>
                        <input type="tel" id="mobile" name="mobile" required 
                               placeholder="{{ translate('Enter your mobile number') }}"
                               style="width: 100%; border: 1px solid #ced4da; border-radius: 0.375rem; padding: 0.5rem 0.75rem; font-size: 1rem;">
                    </div>
            
                    <!-- Delivery Address -->
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

<style>
.ticket-row { background:white; border-radius:16px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 4px 6px rgba(0,0,0,0.05); margin-bottom:20px; width:100%; position:relative; animation:fadeIn 0.6s ease-out; }
.ticket-perforation { width:100%; height:1px; background: repeating-linear-gradient(to right, transparent, transparent 10px, #e2e8f0 10px, #e2e8f0 20px); }
.ticket-content { display:grid; grid-template-columns:2fr 1fr auto; gap:24px; align-items:start; padding:24px; width:100%; }

.ticket-header { display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:8px; }
.ticket-number-large { font-family:'Courier New', monospace; font-size:22px; font-weight:bold; color:#4f46e5; letter-spacing:2px; background:#f8fafc; padding:10px 16px; border-radius:10px; border:2px solid #e2e8f0; }
.ticket-status-pending { background: linear-gradient(135deg,#f59e0b,#fbbf24); color:white; padding:6px 16px; border-radius:20px; font-weight:500; }
.ticket-status-completed { background: linear-gradient(135deg,#10b981,#34d399); color:white; padding:6px 16px; border-radius:20px; font-weight:500; }
.ticket-win-status.win { background:#10b981; color:white; padding:6px 16px; border-radius:20px; font-weight:500; }
.ticket-win-status.lose { background:#ef4444; color:white; padding:6px 16px; border-radius:20px; font-weight:500; }

.ticket-title { font-size:20px; font-weight:600; color:#2d3748; margin:0; }
.ticket-description { font-size:14px; color:#718096; margin:0; line-height:1.5; }
.ticket-details-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-top:8px; }
.ticket-detail-label { font-size:12px; color:#718096; font-weight:500; }
.ticket-detail-value { font-size:14px; color:#2d3748; font-weight:500; }
.ticket-address { margin-top:8px; }
.ticket-full-id { margin-top:12px; padding:12px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; font-family:'Courier New', monospace; }

.ticket-qr-section { display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; }
.ticket-qr-container { width:160px; height:160px; background:white; border-radius:8px; display:flex; align-items:center; justify-content:center; border:1px solid #e2e8f0; }
.ticket-qr-text { text-align:center; font-size:12px; color:#718096; }
.ticket-qr-text div:last-child { color:#4f46e5; font-weight:500; font-size:11px; }

.ticket-actions { display:flex; flex-direction:column; gap:12px; min-width:200px; }
.action-btn { padding:12px 20px; border:none; border-radius:10px; font-weight:600; cursor:pointer; font-size:14px; background:linear-gradient(135deg,#f093fb,#f5576c); color:white; transition:all 0.3s; }
.action-btn:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.15); }

@keyframes fadeIn { from{opacity:0;transform:translateY(10px);} to{opacity:1;transform:translateY(0);} }

/* Responsive */
@media(max-width:1024px){
    .ticket-content { grid-template-columns:1fr !important; gap:20px !important; }
    .ticket-qr-section { order:2; width:100%; }
    .ticket-actions { order:3; flex-direction:row !important; min-width:100%; }
    .action-btn { flex:1; }
}
@media(max-width:768px){
    .ticket-number-large { font-size:18px; }
    .ticket-details-grid { grid-template-columns:1fr !important; }
    .ticket-qr-container { width:140px; height:140px; }
}
@media(max-width:480px){
    .ticket-number-large { font-size:16px; }
    .ticket-content { padding:16px; }
    .ticket-actions { flex-direction:column !important; }
    .ticket-qr-container { width:120px; height:120px; }
    .action-btn { padding:10px 16px; font-size:13px; }
}
</style>

<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const claimModalEl = document.getElementById('claimModal');
    const claimModalInstance = new bootstrap.Modal(claimModalEl);
    let currentTicketNumber = null;

    function openClaimModal(ticketNumber) {
        currentTicketNumber = ticketNumber;
        document.getElementById('ticket_number').value = ticketNumber;
        claimModalInstance.show();
    }

    async function submitClaim(e) {
        e.preventDefault();

        const mobile = document.getElementById('mobile').value.trim();
        const address = document.getElementById('address').value.trim();
        const submitBtn = document.getElementById('claimSubmitBtn');
        const spinner = submitBtn.querySelector('.spinner-border');

        if (!mobile || !address) {
            showAlert('Please fill in all required fields.', 'error');
            return;
        }

        if (!currentTicketNumber) {
            showAlert('Ticket number is missing. Please try again.', 'error');
            return;
        }

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            const res = await fetch('{{ route("user.lottary.single.claim") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    mobile: mobile,
                    address: address,
                    ticket_number: currentTicketNumber
                })
            });

            const text = await res.text();

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${text}`);
            }

            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                throw new Error(`Invalid JSON response: ${text}`);
            }

            if (data.success) {
                showAlert(
                    'Claim submitted successfully! ' + (data.claim_code ? `Your claim code: ${data.claim_code}` : ''),
                    'success'
                );

                claimModalInstance.hide();
                document.getElementById('claimForm').reset();
                currentTicketNumber = null;

                submitBtn.disabled = true;
                submitBtn.innerText = 'Claim Submitted';

                setTimeout(() => {
                    window.location.href = '{{ route("user.lottary.wins.ticket") }}';
                }, 1000);

            } else {
                showAlert(data.message || 'Failed to submit claim.', 'error');
                submitBtn.disabled = false;
            }

        } catch (err) {
            console.error('Fetch/JSON error:', err);
            showAlert('Network error. Please check your connection and try again.', 'error');
            submitBtn.disabled = false;
        } finally {
            spinner.classList.add('d-none');
        }
    }

    document.getElementById('claimForm').addEventListener('submit', submitClaim);

    // Toast function
    function showAlert(message, type = 'success') {
        const bg = type === 'success' ? 'bg-success' : 'bg-danger';

        const alertDiv = document.createElement('div');
        alertDiv.className = `toast align-items-center text-white ${bg} border-0`;
        alertDiv.role = 'alert';
        alertDiv.ariaLive = 'assertive';
        alertDiv.ariaAtomic = 'true';
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '1rem';
        alertDiv.style.right = '1rem';
        alertDiv.style.zIndex = 9999;

        alertDiv.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        document.body.appendChild(alertDiv);

        const toast = new bootstrap.Toast(alertDiv, { delay: 5000 });
        toast.show();

        alertDiv.addEventListener('hidden.bs.toast', () => alertDiv.remove());
    }
</script>


<script>
// Generate QR Code
function generateQRCode() {
    const publicUrl = window.location.href;
    const encodedUrl = encodeURIComponent(publicUrl);
    const qrCodeSize = 200;
    const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${qrCodeSize}x${qrCodeSize}&data=${encodedUrl}&format=png&margin=10&color=2d3748&bgcolor=f8fafc`;
    
    const qrImage = document.getElementById('qrCodeImage');
    qrImage.src = qrCodeUrl;
    qrImage.onerror = function() {
        // Fallback SVG if QR code fails to load
        this.src = 'data:image/svg+xml;base64,' + btoa(`
            <svg xmlns="http://www.w3.org/2000/svg" width="${qrCodeSize}" height="${qrCodeSize}" viewBox="0 0 ${qrCodeSize} ${qrCodeSize}">
                <rect width="${qrCodeSize}" height="${qrCodeSize}" fill="#f8fafc"/>
                <text x="50%" y="50%" font-family="Arial" font-size="16" text-anchor="middle" dy=".3em" fill="#718096">{{ translate("QR Code") }}</text>
            </svg>
        `);
    };
}

// Share ticket function - matching your list design
function shareTicket() {
    const ticketData = @json($ticket);
    const publicUrl = window.location.href;
    const text = `{{ translate("Check out my lottery ticket for") }} "${ticketData.title}"! 🎫\n{{ translate("Ticket Number") }}: ${formatTicketNumber(ticketData.ticket_number)}\n{{ translate("Draw Date") }}: ${new Date(ticketData.drew_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}\n\n{{ translate("View ticket") }}: ${publicUrl}`;
    
    if (isMobileDevice()) {
        showMobileShareOptions(ticketData, publicUrl, text);
    } else {
        copyToClipboard(publicUrl);
    }
}

// Check if device is mobile - matching your list design
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Mobile share options - matching your list design
function showMobileShareOptions(ticket, publicUrl, text) {
    const shareSheet = document.createElement('div');
    shareSheet.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        z-index: 1000;
        animation: fadeIn 0.3s;
    `;
    
    shareSheet.innerHTML = `
        <div style="background: white; width: 100%; max-width: 500px; border-radius: 20px 20px 0 0; padding: 20px; animation: slideUp 0.3s;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; color: #2d3748; font-size: 18px; font-weight: 600;">{{ translate("Share Ticket") }}</h3>
                <button onclick="this.closest('div[style*=\"position: fixed\"]').remove()" 
                        style="background: none; border: none; color: #718096; cursor: pointer; padding: 8px;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px; padding: 16px; background: #f8fafc; border-radius: 12px;">
                <div style="font-family: 'Courier New', monospace; font-size: 16px; font-weight: bold; color: #4f46e5; margin-bottom: 8px;">
                    ${formatTicketNumber(ticket.ticket_number)}
                </div>
                <div style="font-size: 14px; color: #4a5568; font-weight: 500;">${ticket.title}</div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px;">
                <button onclick="shareViaWhatsApp('${encodeURIComponent(text)}')" 
                        style="background: none; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 50px; height: 50px; background: #25D366; border-radius: 25px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.76.982.998-3.675-.236-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.9 6.994c-.004 5.45-4.438 9.88-9.888 9.88m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.333.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.333 11.893-11.893 0-3.18-1.24-6.162-3.495-8.411"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; color: #4a5568; font-weight: 500;">{{ translate("WhatsApp") }}</span>
                </button>
                
                <button onclick="shareViaTelegram('${encodeURIComponent(text)}')" 
                        style="background: none; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 50px; height: 50px; background: #0088cc; border-radius: 25px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.158c.202.043.348.202.391.407l.522 2.628c.043.202.043.435 0 .652-.043.217-.174.391-.391.521l-1.761.913a.478.478 0 00-.217.239c-.087.173-.087.369 0 .543l.369 1.324c.087.282.022.586-.174.804-.195.217-.478.304-.782.239l-2.109-.391a3.772 3.772 0 00-.652 0l-2.109.391c-.304.065-.587-.022-.782-.239-.196-.217-.261-.522-.174-.804l.369-1.324a.78.78 0 010-.543.478.478 0 00-.217-.239l-1.761-.913c-.217-.13-.348-.304-.391-.521-.043-.217-.043-.435 0-.652l.522-2.628c.043-.205.189-.365.391-.407.652-.13 2.935-.587 4.37-.869.174-.043.348-.043.522 0 1.435.282 3.718.739 4.37.869z"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; color: #4a5568; font-weight: 500;">{{ translate("Telegram") }}</span>
                </button>
                
                <button onclick="shareViaFacebook('${encodeURIComponent(publicUrl)}', '${encodeURIComponent(ticket.title)}')" 
                        style="background: none; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 50px; height: 50px; background: #1877F2; border-radius: 25px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; color: #4a5568; font-weight: 500;">{{ translate("Facebook") }}</span>
                </button>
                
                <button onclick="copyToClipboard('${publicUrl}')" 
                        style="background: none; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 50px; height: 50px; background: #4f46e5; border-radius: 25px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; color: #4a5568; font-weight: 500;">{{ translate("Copy Link") }}</span>
                </button>
            </div>
            
            <div style="margin-bottom: 20px;">
                <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                    <input type="text" id="shareLinkInput" value="${publicUrl}" readonly 
                           style="flex: 1; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; background: #f8fafc;">
                </div>
            </div>
            
            <button onclick="this.closest('div[style*=\"position: fixed\"]').remove()" 
                    style="width: 100%; padding: 14px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 500; cursor: pointer;">
                {{ translate("Cancel") }}
            </button>
        </div>
    `;
    
    // Add animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(shareSheet);
}

// Mobile share functions - matching your list design
function shareViaWhatsApp(text) {
    window.open(`whatsapp://send?text=${text}`, '_blank');
}

function shareViaTelegram(text) {
    window.open(`tg://msg?text=${text}`, '_blank');
}

function shareViaFacebook(url, quote = '') {
    window.open(`fb://share?u=${url}&quote=${quote}`, '_blank');
}

// Copy to clipboard - matching your list design
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('{{ translate("Link copied to clipboard!") }}', 'success');
    }).catch(err => {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('{{ translate("Link copied to clipboard!") }}', 'success');
    });
}

// Show toast - matching your list design
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 500;
        z-index: 1000;
        animation: slideIn 0.3s, slideOut 0.3s 2.7s;
    `;
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    if (!document.querySelector('#toast-animations')) {
        const style = document.createElement('style');
        style.id = 'toast-animations';
        style.textContent = `
            @keyframes slideIn {
                from { bottom: -50px; opacity: 0; }
                to { bottom: 20px; opacity: 1; }
            }
            @keyframes slideOut {
                from { bottom: 20px; opacity: 1; }
                to { bottom: -50px; opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Download ticket as screenshot - EXACTLY matching your list design
function downloadTicketScreenshot() {
    const ticketElement = document.querySelector('.ticket-row');
    
    if (!ticketElement || !window.html2canvas) {
        downloadFallbackTicket();
        return;
    }
    
    // Temporarily hide the back button if present
    const backButton = document.querySelector('a[href*="my-lottaries"]');
    const originalBackButtonDisplay = backButton ? backButton.style.display : '';
    if (backButton) {
        backButton.style.display = 'none';
    }
    
    // Add temporary styling for screenshot
    const originalBorder = ticketElement.style.border;
    const originalBoxShadow = ticketElement.style.boxShadow;
    const originalMargin = ticketElement.style.margin;
    const originalWidth = ticketElement.style.width;
    
    ticketElement.style.border = '2px solid #4f46e5';
    ticketElement.style.boxShadow = '0 8px 32px rgba(79, 70, 229, 0.2)';
    ticketElement.style.margin = '0 auto';
    ticketElement.style.width = 'fit-content';
    
    // Temporarily hide action buttons for cleaner screenshot
    const actionButtons = ticketElement.querySelector('.ticket-actions');
    const originalActionsDisplay = actionButtons ? actionButtons.style.display : '';
    if (actionButtons) {
        actionButtons.style.display = 'none';
    }
    
    // Capture screenshot
    html2canvas(ticketElement, {
        backgroundColor: '#ffffff',
        scale: 2,
        useCORS: true,
        logging: false,
        allowTaint: true,
        windowWidth: ticketElement.scrollWidth,
        windowHeight: ticketElement.scrollHeight,
    }).then(function(canvas) {
        // Restore original styles
        ticketElement.style.border = originalBorder;
        ticketElement.style.boxShadow = originalBoxShadow;
        ticketElement.style.margin = originalMargin;
        ticketElement.style.width = originalWidth;
        
        if (backButton) {
            backButton.style.display = originalBackButtonDisplay;
        }
        
        if (actionButtons) {
            actionButtons.style.display = originalActionsDisplay;
        }
        
        // Convert canvas to data URL
        const dataURL = canvas.toDataURL('image/png');
        
        // Create download link
        const link = document.createElement('a');
        link.download = `ticket-{{ $ticket->ticket_number }}.png`;
        link.href = dataURL;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('{{ translate("Ticket downloaded successfully!") }}', 'success');
    }).catch(function(error) {
        console.error('Error taking screenshot:', error);
        downloadFallbackTicket();
        
        // Restore styles even on error
        ticketElement.style.border = originalBorder;
        ticketElement.style.boxShadow = originalBoxShadow;
        ticketElement.style.margin = originalMargin;
        ticketElement.style.width = originalWidth;
        
        if (backButton) {
            backButton.style.display = originalBackButtonDisplay;
        }
        
        if (actionButtons) {
            actionButtons.style.display = originalActionsDisplay;
        }
    });
}

// Fallback download function
function downloadFallbackTicket() {
    const ticketData = @json($ticket);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = 800;
    canvas.height = 400;
    
    // Background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Border
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 2;
    ctx.strokeRect(20, 20, canvas.width - 40, canvas.height - 40);
    
    // Ticket number
    ctx.fillStyle = '#4f46e5';
    ctx.font = 'bold 24px "Courier New", monospace';
    ctx.textAlign = 'left';
    ctx.fillText(`#${formatTicketNumber(ticketData.ticket_number)}`, 40, 70);
    
    // Title
    ctx.fillStyle = '#2d3748';
    ctx.font = 'bold 20px Arial';
    ctx.fillText(ticketData.title, 40, 110);
    
    // Details
    ctx.fillStyle = '#718096';
    ctx.font = '14px Arial';
    ctx.fillText(`{{ translate("Ticket Holder") }}: ${ticketData.name}`, 40, 150);
    ctx.fillText(`{{ translate("Draw Date") }}: ${new Date(ticketData.drew_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}`, 40, 180);
    ctx.fillText(`{{ translate("Status") }}: ${ticketData.is_drew == 1 ? '{{ translate("Completed") }}' : '{{ translate("Pending") }}'}`, 40, 210);
    
    // Status badge
    ctx.fillStyle = ticketData.is_drew == 1 ? '#10b981' : '#f59e0b';
    ctx.fillRect(40, 240, 120, 30);
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 14px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(ticketData.is_drew == 1 ? '{{ translate("COMPLETED") }}' : '{{ translate("PENDING") }}', 100, 260);
    
    // Convert to data URL and download
    const dataURL = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.download = `ticket-{{ $ticket->ticket_number }}.png`;
    link.href = dataURL;
    link.click();
    
    showToast('{{ translate("Ticket downloaded successfully!") }}', 'success');
}

// Helper function to format ticket number
function formatTicketNumber(number) {
    if (!number) return 'N/A';
    const cleaned = number.replace(/[^a-zA-Z0-9]/g, '');
    
    if (cleaned.length <= 4) {
        return cleaned;
    } else if (cleaned.length <= 8) {
        return `${cleaned.substring(0, 4)}-${cleaned.substring(4)}`;
    } else {
        return `${cleaned.substring(0, 4)}-${cleaned.substring(4, 8)}-${cleaned.substring(8, 12)}`;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Generate QR code
    generateQRCode();
});
</script>

<!-- Include html2canvas library for screenshot functionality -->
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
@endsection

<?php
// Helper function to format ticket number
if (!function_exists('formatTicketNumber')) {
    function formatTicketNumber($number) {
        if (!$number) return 'N/A';
        $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $number);
        
        if (strlen($cleaned) <= 4) {
            return $cleaned;
        } elseif (strlen($cleaned) <= 8) {
            return substr($cleaned, 0, 4) . '-' . substr($cleaned, 4);
        } else {
            return substr($cleaned, 0, 4) . '-' . substr($cleaned, 4, 4) . '-' . substr($cleaned, 8, 4);
        }
    }
}
?>