@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Claim Request Details</h1>
                    @if($claim->lottary || $claim->prize)
                    <p class="mb-0 text-muted">
                        {{ $claim->lottary->title ?? 'Lottery' }} - {{ $claim->prize->name ?? 'Prize' }}
                    </p>
                    @endif
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lottary.claim.request') }}">Claim Requests</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-info" onclick="downloadPDF()">
                                    <i class="las la-file-pdf mr-2"></i> Download PDF
                                </button>
                                
                                @if($claim->send_gift == 0)
                                <form id="sendGiftForm" action="{{ route('admin.lottary.sendgift') }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" value="{{ $claim->id }}">
                                    <button type="button" class="btn btn-success" onclick="confirmSendGift()">
                                        <i class="las la-gift mr-2"></i> Send Gift
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-success" disabled>
                                    <i class="las la-check-circle mr-2"></i> Gift Sent
                                </button>
                                @endif
                                
                                <a href="{{ route('admin.lottary.claim.request') }}" class="btn btn-secondary">
                                    <i class="las la-arrow-left mr-2"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- A4 Paper Layout -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card a4-paper">
                        <div class="card-body p-5">
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6 border-end">
                                    <!-- QR Code -->
                                    <div class="text-center mb-4">
                                        <div id="qrCodeContainer" class="mb-3"></div>
                                        <div class="alert alert-light border">
                                            <i class="las la-info-circle text-info mr-1"></i>
                                            Scan to verify claim authenticity
                                        </div>
                                    </div>
                                    
                                    <!-- User Information -->
                                    <div class="user-info-section">
                                        <h4 class="section-title">
                                            <i class="las la-user-circle mr-2"></i>User Information
                                        </h4>
                                        
                                        <div class="info-item mb-3">
                                            <div class="info-label">Full Name</div>
                                            <div class="info-value">{{ $claim->user->name ?? 'N/A' }}</div>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <div class="info-label">Email Address</div>
                                            <div class="info-value">{{ $claim->user->email ?? 'N/A' }}</div>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <div class="info-label">Mobile Number</div>
                                            <div class="info-value">{{ $claim->Mobile ?? 'N/A' }}</div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Delivery Address</div>
                                            <div class="info-value address-box">
                                                {{ $claim->claim_request_address ?? 'Not provided' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <!-- Ticket Information -->
                                    <div class="ticket-info-section">
                                        <h4 class="section-title">
                                            <i class="las la-ticket-alt mr-2"></i>Ticket & Claim Details
                                        </h4>
                                        
                                        <div class="info-item mb-3">
                                            <div class="info-label">Ticket Number</div>
                                            <div class="info-value ticket-number">{{ $claim->ticket_number }}</div>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <div class="info-label">Claim Code</div>
                                            <div class="info-value claim-code">{{ $claim->claim_code }}</div>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <div class="info-label">Request Date</div>
                                            <div class="info-value">
                                                {{ $claim->created_at->format('F d, Y - h:i A') }}
                                            </div>
                                        </div>
                                        
                                        @if($claim->lottary && $claim->lottary->created_at)
                                        <div class="info-item mb-3">
                                            <div class="info-label">Draw Date</div>
                                            <div class="info-value">
                                                {{ $claim->lottary->created_at->format('F d, Y') }}
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <div class="info-item mb-3">
                                            <div class="info-label">Lottery Name</div>
                                            <div class="info-value">{{ $claim->lottary->title ?? 'N/A' }}</div>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <div class="info-label">Prize Won</div>
                                            <div class="info-value prize-name">{{ $claim->prize->name ?? 'N/A' }}</div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Status</div>
                                            <div class="info-value">
                                                <span style="
                                                    display: inline-block;
                                                    padding: 0.25em 0.6em;
                                                    font-size: 75%;
                                                    font-weight: 700;
                                                    line-height: 1;
                                                    color: {{ $claim->send_gift == 0 ? '#856404' : '#155724' }};
                                                    background-color: {{ $claim->send_gift == 0 ? '#fff3cd' : '#d4edda' }};
                                                    border-radius: 0.25rem;
                                                ">
                                                    {{ $claim->send_gift == 0 ? 'Pending' : 'Sent' }}
                                                </span>
                                                @if($claim->send_gift == 1)
                                                <small style="color: #6c757d; margin-left: 0.5rem;">
                                                    Sent on {{ $claim->updated_at->format('M d, Y') }}
                                                </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Verification Details -->
                                    <div class="verification-section mt-4 pt-4 border-top">
                                        <h6 class="text-uppercase text-muted mb-3">Verification Details</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted d-block">Document ID</small>
                                                <strong>CLM-{{ $claim->id }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Valid Until</small>
                                                <strong>{{ \Carbon\Carbon::parse($claim->created_at)->addMonths(3)->format('M d, Y') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Footer -->
                            <div class="row mt-5 pt-4 border-top">
                                <div class="col-12">
                                    <div class="text-center">
                                        <p class="mb-0 text-muted small">
                                            <i class="las la-shield-alt mr-1"></i>
                                            This is an official document. Verification required for prize collection.
                                        </p>
                                        <p class="mb-0 text-muted small">
                                            Generated on {{ now()->format('F d, Y \a\t h:i A') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    /* A4 Paper Style */
    .a4-paper {
        background: white;
        border: 1px solid #dee2e6;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        min-height: 29.7cm;
        margin-bottom: 2rem;
    }
    
    @media print {
        .a4-paper {
            box-shadow: none;
            border: none;
            margin: 0;
            padding: 0;
        }
        
        .content-header, .breadcrumb, .action-buttons {
            display: none !important;
        }
    }
    
    /* Section Titles */
    .section-title {
        color: #2c3e50;
        padding-bottom: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #3498db;
        font-weight: 600;
    }
    
    /* Info Items */
    .info-item {
        margin-bottom: 15px;
    }
    
    .info-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .info-value {
        font-size: 1.1rem;
        color: #2c3e50;
        font-weight: 500;
        word-break: break-word;
    }
    
    .address-box {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 12px;
        margin-top: 8px;
        line-height: 1.5;
    }
    
    /* Special Values */
    .ticket-number,
    .claim-code {
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        display: inline-block;
    }
    
    .prize-name {
        color: #e74c3c;
        font-weight: 600;
    }
    
    /* QR Code Container */
    #qrCodeContainer {
        width: 200px;
        height: 200px;
        margin: 0 auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        background: white;
    }
    
    /* Action Buttons */
    .btn {
        padding: 8px 20px;
        font-weight: 500;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .a4-paper {
            padding: 15px !important;
        }
        
        .border-end {
            border-right: none !important;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        
        #qrCodeContainer {
            width: 180px;
            height: 180px;
        }
    }
    
    @media (max-width: 576px) {
        .d-flex {
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .btn {
            flex: 1;
            min-width: 150px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate QR Code
    generateQRCode();
    
    // Initialize toast notifications
    initToast();
});

function generateQRCode() {
    const claimCode = "{{ $claim->claim_code }}";
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    
    const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(claimCode)}`;
    
    qrCodeContainer.innerHTML = `
        <img src="${qrCodeUrl}" 
             alt="QR Code for {{ $claim->claim_code }}" 
             class="img-fluid rounded"
             style="width: 100%; height: 100%; object-fit: contain;">
    `;
}

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    
    // Add background color
    doc.setFillColor(248, 249, 250);
    doc.rect(0, 0, 210, 297, 'F');
    
    // Header
    doc.setFontSize(20);
    doc.setTextColor(33, 37, 41);
    doc.setFont('helvetica', 'bold');
    doc.text('Claim Request Details', 105, 20, null, null, 'center');
    
    doc.setFontSize(12);
    doc.setTextColor(108, 117, 125);
    doc.setFont('helvetica', 'normal');
    doc.text('{{ $claim->lottary->title ?? "Lottery" }} - {{ $claim->prize->name ?? "Prize" }}', 105, 28, null, null, 'center');
    
    // Draw line
    doc.setDrawColor(52, 152, 219);
    doc.setLineWidth(0.5);
    doc.line(20, 35, 190, 35);
    
    // Left Column - User Information
    doc.setFontSize(14);
    doc.setTextColor(44, 62, 80);
    doc.setFont('helvetica', 'bold');
    doc.text('User Information', 60, 50);
    
    doc.setFontSize(10);
    doc.setTextColor(108, 117, 125);
    doc.text('Full Name:', 20, 65);
    doc.setTextColor(33, 37, 41);
    doc.text('{{ $claim->user->name ?? "N/A" }}', 60, 65);
    
    doc.setTextColor(108, 117, 125);
    doc.text('Email Address:', 20, 75);
    doc.setTextColor(33, 37, 41);
    doc.text('{{ $claim->user->email ?? "N/A" }}', 60, 75);
    
    doc.setTextColor(108, 117, 125);
    doc.text('Mobile Number:', 20, 85);
    doc.setTextColor(33, 37, 41);
    doc.text('{{ $claim->Mobile ?? "N/A" }}', 60, 85);
    
    doc.setTextColor(108, 117, 125);
    doc.text('Delivery Address:', 20, 95);
    doc.setTextColor(33, 37, 41);
    const address = '{{ addslashes($claim->claim_request_address ?? "Not provided") }}';
    const splitAddress = doc.splitTextToSize(address, 70);
    doc.text(splitAddress, 60, 95);
    
    // Right Column - Ticket Information
    doc.setFontSize(14);
    doc.setTextColor(44, 62, 80);
    doc.setFont('helvetica', 'bold');
    doc.text('Ticket & Claim Details', 140, 50);
    
    let yPos = 65;
    const details = [
        ['Ticket Number:', '{{ $claim->ticket_number }}'],
        ['Claim Code:', '{{ $claim->claim_code }}'],
        ['Request Date:', '{{ $claim->created_at->format("F d, Y - h:i A") }}'],
        @if($claim->lottary && $claim->lottary->created_at)
        ['Draw Date:', '{{ $claim->lottary->created_at->format("F d, Y") }}'],
        @endif
        ['Lottery Name:', '{{ $claim->lottary->title ?? "N/A" }}'],
        ['Prize Won:', '{{ $claim->prize->name ?? "N/A" }}'],
    ];
    
    details.forEach(([label, value]) => {
        doc.setFontSize(10);
        doc.setTextColor(108, 117, 125);
        doc.text(label, 110, yPos);
        doc.setTextColor(33, 37, 41);
        doc.text(value, 140, yPos);
        yPos += 10;
    });
    
    // QR Code
    const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent('{{ $claim->claim_code }}')}`;
    
    // Add QR code to PDF
    doc.addImage({
        imageData: qrCodeUrl,
        x: 20,
        y: 130,
        width: 50,
        height: 50
    });
    
    doc.setFontSize(9);
    doc.setTextColor(108, 117, 125);
    doc.text('Scan to verify claim', 20, 185);
    doc.text('Claim Code: {{ $claim->claim_code }}', 20, 190);
    
    // Footer
    doc.setDrawColor(222, 226, 230);
    doc.line(20, 250, 190, 250);
    
    doc.setFontSize(8);
    doc.setTextColor(108, 117, 125);
    doc.text('This is an official document. Verification required for prize collection.', 105, 260, null, null, 'center');
    doc.text('Document ID: CLM-{{ $claim->id }} | Generated: {{ now()->format("Y-m-d H:i:s") }}', 105, 265, null, null, 'center');
    doc.text('Valid until: {{ \Carbon\Carbon::parse($claim->created_at)->addMonths(3)->format("M d, Y") }}', 105, 270, null, null, 'center');
    
    // Save PDF
    doc.save('claim-{{ $claim->ticket_number }}-{{ now()->format("Ymd") }}.pdf');
    
    showToast('success', 'PDF downloaded successfully');
}

function confirmSendGift() {
    const confirmMessage = `Are you sure you want to mark this gift as sent?\n\nTicket: {{ $claim->ticket_number }}\nUser: {{ $claim->user->name ?? "N/A" }}\nPrize: {{ $claim->prize->name ?? "N/A" }}`;
    
    if (confirm(confirmMessage)) {
        const form = document.getElementById('sendGiftForm');
        const submitBtn = form.querySelector('button');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="las la-spinner la-spin mr-2"></i> Processing...';
        submitBtn.disabled = true;
        
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: "{{ $claim->id }}",
                _method: 'PUT'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                showToast('success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast('error', data.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            showToast('error', 'An error occurred. Please try again.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }
}

function initToast() {
    window.showToast = function(type, message) {
        if (!document.getElementById('toastContainer')) {
            const toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-white ${bgColor} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="las la-${icon} mr-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        document.getElementById('toastContainer').appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    };
}
</script>

<!-- Include jsPDF for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@endsection