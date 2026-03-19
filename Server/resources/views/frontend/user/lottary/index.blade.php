@php
    $system_currency = get_system_currency();
@endphp

@extends('frontend.layouts.user_panel')

@section('panel_content')
<div class="aiz-titlebar mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="fs-20 fw-700 text-dark">{{ translate('My Lottery Tickets') }}</h1>
        </div>
    </div>
</div>
    <!-- Tab Filter System -->
    <div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px; gap: 4px;">
        <button class="filter-tab" data-status="" 
                style="padding: 12px 24px; background: none; border: none; font-size: 16px; font-weight: 500; color: #718096; cursor: pointer; position: relative; transition: all 0.2s;">
            {{ translate('All Tickets') }}
        </button>
        <button class="filter-tab tickets-tab" data-status="0"
            style="padding: 12px 24px; background: none; border: none; font-size: 16px;
                   font-weight: 500; color: #718096; cursor: pointer; position: relative;">
            {{ translate('Tickets') }}
        </button>

        <button class="filter-tab" data-status="1" 
                style="padding: 12px 24px; background: none; border: none; font-size: 16px; font-weight: 500; color: #718096; cursor: pointer; position: relative; transition: all 0.2s;">
            {{ translate('Completed') }}
        </button>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; justify-content: flex-start; gap: 12px; margin-bottom: 20px; flex-wrap: nowrap; flex-direction: row;">
    
        <!-- My Win Button -->
        
        <a href="{{ route('user.lottary.wins.ticket') }}" style="text-decoration: none;">
            <button id="viewDrawnBtn"
                style="padding: 10px 20px; background: #4f46e5; color: white; border: none;
                       border-radius: 8px; font-weight: 500; cursor: pointer;
                       display: flex; align-items: center; gap: 8px; transition: background 0.2s;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 21h8M12 17v4M7 4h10v3a5 5 0 01-10 0V4z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 4v2a3 3 0 003 3M19 4v2a3 3 0 01-3 3"/>
                </svg>
                {{ translate('Win') }}
            </button>
        </a>
    
        <!-- View All Winners Button -->
        <a href="{{ route('user.lottary.drawn') }}" style="text-decoration: none;">
            <button id="viewDrawnBtn"
                style="padding: 10px 20px; background: #4f46e5; color: white; border: none;
                       border-radius: 8px; font-weight: 500; cursor: pointer;
                       display: flex; align-items: center; gap: 8px; transition: background 0.2s;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7a2 2 0 012-2h14a2 2 0 012 2v3a2 2 0 010 4v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-3a2 2 0 010-4V7z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 8h.01M12 8h.01M16 8h.01M8 16h.01M12 16h.01M16 16h.01"/>
                </svg>
                {{ translate('Drawn') }}
            </button>
        </a>
        
        <!-- Refresh Button -->
        <button id="refreshBtn"
            style="padding: 10px 20px; background: #4f46e5; color: white; border: none;
                   border-radius: 8px; font-weight: 500; cursor: pointer;
                   display: flex; align-items: center; gap: 8px; transition: background 0.2s;">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span class="btn-text">{{ translate('Refresh Tickets') }}</span>
        </button>
    
    </div>
    

    <!-- Loading State -->
    <div id="loading" style="display: block; text-align: center; padding: 60px 20px;">
        <div style="width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top: 4px solid #4f46e5; border-radius: 50%; margin: 0 auto; animation: spin 1s linear infinite;"></div>
        <p style="margin-top: 16px; color: #718096; font-size: 16px;">{{ translate('Loading your active tickets...') }}</p>
    </div>

    <!-- Tickets Container - One ticket per row -->
    <div id="ticketContainer" style="display: none; margin-top: 20px; min-height: calc(100vh - 60px);"></div>

    <!-- Empty State -->
    <div id="emptyState" style="display: none; text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 12px; margin-top: 20px;">
        <svg style="width: 80px; height: 80px; margin-bottom: 20px; color: #cbd5e0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 style="color: #4a5568; margin-bottom: 12px; font-size: 20px;">{{ translate('No tickets found') }}</h3>
        <p style="color: #718096; margin-bottom: 24px; max-width: 400px; margin-left: auto; margin-right: auto;">
            {{ translate('You don\'t have any lottery tickets in this category yet.') }}
        </p>
        <button onclick="loadTickets('')" style="background: #4f46e5; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 500; cursor: pointer;">
            {{ translate('View All Tickets') }}
        </button>
    </div>
</div>



<style>
        
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Active tab styling */
    .filter-tab.active {
        color: #4f46e5 !important;
    }
    
    .tickets-tab::before {
        content: '';
        position: absolute;
        top: 8px;
        right: 12px;
        width: 8px;
        height: 8px;
        background: #e53e3e; /* red */
        border-radius: 50%;
        animation: ticket-bip 1.2s infinite;
    }



    .filter-tab.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: #4f46e5;
    }
    
    @keyframes ticket-bip {
        0%   { transform: scale(1); opacity: 1; }
        70%  { transform: scale(1.6); opacity: 0; }
        100% { opacity: 0; }
    }


    /* Single Ticket Row Design */
    .ticket-row {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
        position: relative;
    }

    .ticket-row:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .ticket-content {
        display: grid;
        grid-template-columns: 2fr 1fr auto;
        gap: 24px;
        align-items: center;
        padding: 24px;
        width: 100%;
    }

    .ticket-main-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .ticket-number-large {
        font-family: 'Courier New', monospace;
        font-size: 22px;
        font-weight: bold;
        color: #4f46e5;
        letter-spacing: 2px;
        background: #f8fafc;
        padding: 10px 16px;
        border-radius: 10px;
        display: inline-block;
        border: 2px solid #e2e8f0;
    }

    .ticket-status {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        text-align: center;
        display: inline-block;
    }

    .ticket-status-pending {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        color: white;
    }

    .ticket-status-completed {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
    }

    .ticket-details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .ticket-detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .ticket-detail-label {
        font-size: 12px;
        color: #718096;
        font-weight: 500;
    }

    .ticket-detail-value {
        font-size: 14px;
        color: #2d3748;
        font-weight: 500;
    }

    .ticket-qr-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .ticket-qr-container {
        width: 120px;
        height: 120px;
        background: white;
        border-radius: 8px;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
    }

    .ticket-qr-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .ticket-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-width: 200px;
    }

    .action-btn {
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s;
        font-size: 14px;
    }

    .action-btn-share {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .action-btn-download {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Perforated Edge Effect */
    .ticket-perforation {
        position: relative;
        margin: 0 20px;
    }

    .ticket-perforation::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: repeating-linear-gradient(
            to right,
            transparent,
            transparent 10px,
            #e2e8f0 10px,
            #e2e8f0 20px
        );
    }
    
    /* Hide button text on small screens */
    @media (max-width: 640px) {
        #refreshBtn .btn-text {
            display: none;
        }
        #refreshBtn {
            padding: 10px;
        }
    }

    /* Mobile responsiveness */
    @media (max-width: 1024px) {
        .ticket-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .ticket-qr-section {
            order: 2;
        }
        
        .ticket-actions {
            order: 3;
            flex-direction: row;
        }
        
        .action-btn {
            flex: 1;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 16px !important;
        }
        
        .filter-tab {
            padding: 10px 16px !important;
            font-size: 14px !important;
            flex: 1;
            text-align: center;
        }
        
        .ticket-content {
            padding: 20px !important;
        }
        
        .ticket-number-large {
            font-size: 18px !important;
        }
        
        .ticket-details-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        h2 {
            font-size: 24px !important;
        }
        
        .filter-tab {
            padding: 8px 12px !important;
            font-size: 13px !important;
        }
        
        .ticket-number-large {
            font-size: 16px !important;
        }
        
        .ticket-qr-container {
            width: 100px;
            height: 100px;
        }
        
        .action-btn {
            padding: 10px 16px;
            font-size: 13px;
        }
    }
</style>

<script>
let currentTickets = [];
let currentFilter = "0";
let ticketQRCodes = {}; // Store QR code data URLs

// Online QR Code Generator Function
function generateQRCodeUrl(text, size = 200) {
    // Using a reliable QR code API
    const encodedText = encodeURIComponent(text);
    return `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodedText}&format=png&margin=10&color=2d3748&bgcolor=f8fafc`;
}

// Generate public ticket URL using Laravel route
function generatePublicTicketUrl(ticket) {
    const baseUrl = window.location.origin;
    return `${baseUrl}/lottery/ticket/${ticket.ticket_number}`;
}

// Check if device is mobile
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Share function that opens mobile apps directly or copies link on desktop
function shareTicket(ticket) {
    const publicUrl = generatePublicTicketUrl(ticket);
    const text = `Check out my lottery ticket for "${ticket.title}"! 🎫\nTicket Number: ${formatTicketNumber(ticket.ticket_number)}\nDraw Date: ${formatDate(ticket.drew_date)}\n\nView ticket: ${publicUrl}`;
    
    if (isMobileDevice()) {
        // On mobile, show option to choose app
        showMobileShareOptions(ticket, publicUrl, text);
    } else {
        // On desktop, copy link to clipboard
        copyToClipboard(publicUrl);
    }
}

// Show mobile share options
function showMobileShareOptions(ticket, publicUrl, text) {
    // Create a simple native-like share sheet
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
                <h3 style="margin: 0; color: #2d3748; font-size: 18px; font-weight: 600;">{{ translate('Share Ticket') }}</h3>
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
                    <span style="font-size: 12px; color: #4a5568; font-weight: 500;">{{ translate('WhatsApp') }}</span>
                </button>
                
                <button onclick="shareViaTelegram('${encodeURIComponent(text)}')" 
                        style="background: none; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 50px; height: 50px; background: #0088cc; border-radius: 25px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.158c.202.043.348.202.391.407l.522 2.628c.043.202.043.435 0 .652-.043.217-.174.391-.391.521l-1.761.913a.478.478 0 00-.217.239c-.087.173-.087.369 0 .543l.369 1.324c.087.282.022.586-.174.804-.195.217-.478.304-.782.239l-2.109-.391a3.772 3.772 0 00-.652 0l-2.109.391c-.304.065-.587-.022-.782-.239-.196-.217-.261-.522-.174-.804l.369-1.324a.78.78 0 010-.543.478.478 0 00-.217-.239l-1.761-.913c-.217-.13-.348-.304-.391-.521-.043-.217-.043-.435 0-.652l.522-2.628c.043-.205.189-.365.391-.407.652-.13 2.935-.587 4.37-.869.174-.043.348-.043.522 0 1.435.282 3.718.739 4.37.869z"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; color: #4a5568; font-weight: 500;">{{ translate('Telegram') }}</span>
                </button>
                
                <button onclick="shareViaFacebook('${encodeURIComponent(publicUrl)}', '${encodeURIComponent(ticket.title)}')" 
                        style="background: none; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 50px; height: 50px; background: #1877F2; border-radius: 25px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; color: #4a5568; font-weight: 500;">{{ translate('Facebook') }}</span>
                </button>
                
                <button onclick="copyToClipboard('${publicUrl}')" 
                        style="background: none; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 50px; height: 50px; background: #4f46e5; border-radius: 25px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; color: #4a5568; font-weight: 500;">{{ translate('Copy Link') }}</span>
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
                {{ translate('Cancel') }}
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

// Mobile app share functions
function shareViaWhatsApp(text) {
    window.open(`whatsapp://send?text=${text}`, '_blank');
}

function shareViaTelegram(text) {
    window.open(`tg://msg?text=${text}`, '_blank');
}

function shareViaFacebook(url, quote = '') {
    window.open(`fb://share?u=${url}&quote=${quote}`, '_blank');
}

// Copy to clipboard function with feedback
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('{{ translate('Link copied to clipboard!') }}', 'success');
    }).catch(err => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('{{ translate('Link copied to clipboard!') }}', 'success');
    });
}

// Show toast notification
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
    
    // Add animation styles
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

// Helper Functions
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

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        return dateString;
    }
}

function truncateText(text, maxLength = 30) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Function to download ticket as screenshot
function downloadTicketScreenshot(ticket, index) {
    const ticketElement = document.querySelector(`[data-ticket-id="${ticket.ticket_number}"]`);
    
    if (!ticketElement) {
        // Fallback to canvas generation if html2canvas not available
        downloadTicket(ticket);
        return;
    }
    
    // Temporarily hide action buttons for cleaner screenshot
    const originalActions = ticketElement.querySelector('.ticket-actions');
    const originalDisplay = originalActions.style.display;
    originalActions.style.display = 'none';
    
    // Add temporary border for ticket visual
    const originalBorder = ticketElement.style.border;
    const originalBoxShadow = ticketElement.style.boxShadow;
    ticketElement.style.border = '2px solid #4f46e5';
    ticketElement.style.boxShadow = '0 8px 32px rgba(79, 70, 229, 0.2)';
    
    html2canvas(ticketElement, {
        backgroundColor: '#ffffff',
        scale: 2,
        useCORS: true,
        logging: false,
        allowTaint: true,
    }).then(function(canvas) {
        // Restore original styles
        ticketElement.style.border = originalBorder;
        ticketElement.style.boxShadow = originalBoxShadow;
        originalActions.style.display = originalDisplay;
        
        // Convert canvas to data URL
        const dataURL = canvas.toDataURL('image/png');
        
        // Create download link
        const link = document.createElement('a');
        link.download = `ticket-${formatTicketNumber(ticket.ticket_number)}.png`;
        link.href = dataURL;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('{{ translate('Ticket downloaded successfully!') }}', 'success');
    }).catch(function(error) {
        console.error('Error taking screenshot:', error);
        // Fallback to canvas method
        downloadTicket(ticket);
    });
}

// Enhanced download function with QR code
function downloadTicket(ticket) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = 1200;
    canvas.height = 600;
    
    // Background gradient
    const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
    gradient.addColorStop(0, '#1e3a8a');
    gradient.addColorStop(1, '#3b82f6');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Ticket border
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
    ctx.lineWidth = 2;
    ctx.strokeRect(40, 40, canvas.width - 80, canvas.height - 80);
    
    // Header
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 36px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('{{ translate('OFFICIAL LOTTERY TICKET') }}', canvas.width / 2, 100);
    
    // Ticket number
    ctx.font = 'bold 48px "Courier New", monospace';
    ctx.fillText(formatTicketNumber(ticket.ticket_number), canvas.width / 2, 180);
    
    // Dotted line
    ctx.setLineDash([10, 10]);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(60, 240);
    ctx.lineTo(canvas.width - 60, 240);
    ctx.stroke();
    ctx.setLineDash([]);
    
    // Left column - Ticket info
    ctx.font = 'bold 24px Arial';
    ctx.textAlign = 'left';
    ctx.fillText(ticket.title, 80, 300);
    
    ctx.font = '20px Arial';
    ctx.fillText(`{{ translate('Ticket Holder') }}: ${ticket.name}`, 80, 340);
    ctx.fillText(`{{ translate('Draw Date') }}: ${formatDate(ticket.drew_date)}`, 80, 380);
    ctx.fillText(`{{ translate('Price') }}: {{ $system_currency->symbol }}${ticket.price}`, 80, 420);
    ctx.fillText(`{{ translate('Email') }}: ${ticket.email}`, 80, 460);
    ctx.fillText(`{{ translate('Phone') }}: ${ticket.phone || 'N/A'}`, 80, 500);
    
    // Right column - Address
    if (ticket.full_address) {
        const addressLines = splitText(ticket.full_address, 30);
        addressLines.forEach((line, i) => {
            ctx.fillText(i === 0 ? `{{ translate('Address') }}: ${line}` : line, 600, 340 + (i * 30));
        });
    }
    
    // Generate QR code for the public URL
    const publicUrl = generatePublicTicketUrl(ticket);
    const qrCodeUrl = generateQRCodeUrl(publicUrl, 300);
    
    const qrImage = new Image();
    qrImage.crossOrigin = 'Anonymous';
    qrImage.onload = function() {
        // Draw QR code
        ctx.drawImage(qrImage, canvas.width - 220, 280, 160, 160);
        
        // Add QR code label
        ctx.fillStyle = '#ffffff';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('{{ translate('Scan to verify') }}', canvas.width - 140, 460);
        ctx.font = '12px Arial';
        ctx.fillText('{{ translate('View ticket online') }}', canvas.width - 140, 480);
        
        // Status
        ctx.font = 'bold 24px Arial';
        if (ticket.is_drew == 1) {
            ctx.fillStyle = '#10b981';
            ctx.fillText('✓ {{ translate('DRAW COMPLETED') }}', canvas.width / 2, 300);
        } else {
            ctx.fillStyle = '#f59e0b';
            ctx.fillText('⏳ {{ translate('PENDING DRAW') }}', canvas.width / 2, 300);
        }
        
        // Footer
        ctx.font = '18px Arial';
        ctx.fillText(`{{ translate('Ticket ID') }}: ${ticket.ticket_number.substring(0, 12).toUpperCase()}`, canvas.width / 2, 550);
        
        // Convert to data URL and download
        const dataURL = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = `lottery-ticket-${formatTicketNumber(ticket.ticket_number)}.png`;
        link.href = dataURL;
        link.click();
        
        showToast('{{ translate('Ticket downloaded successfully!') }}', 'success');
    };
    
    qrImage.onerror = function() {
        // Fallback if QR code fails to load
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(canvas.width - 220, 280, 160, 160);
        ctx.fillStyle = '#000000';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('{{ translate('QR CODE') }}', canvas.width - 140, 380);
        ctx.fillText('{{ translate('Scan to verify') }}', canvas.width - 140, 460);
        
        // Continue with download
        const dataURL = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = `lottery-ticket-${formatTicketNumber(ticket.ticket_number)}.png`;
        link.href = dataURL;
        link.click();
        
        showToast('{{ translate('Ticket downloaded successfully!') }}', 'success');
    };
    
    qrImage.src = qrCodeUrl;
}

// Helper function to split text into lines
function splitText(text, maxLength) {
    const words = text.split(' ');
    const lines = [];
    let currentLine = '';
    
    words.forEach(word => {
        if ((currentLine + ' ' + word).length <= maxLength) {
            currentLine += (currentLine ? ' ' : '') + word;
        } else {
            if (currentLine) lines.push(currentLine);
            currentLine = word;
        }
    });
    
    if (currentLine) lines.push(currentLine);
    return lines;
}

// Load tickets function
function loadTickets(status = '0') {
    currentFilter = status;
    
    // Show loading, hide containers
    document.getElementById('loading').style.display = 'block';
    document.getElementById('ticketContainer').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
    
    // Update active tab
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
        if (tab.dataset.status === status) {
            tab.classList.add('active');
        }
    });

    fetch(`/my-lottaries/tickets?status=${status}`)
        .then(res => res.json())
        .then(res => {
            document.getElementById('loading').style.display = 'none';
            currentTickets = res.data || [];
            
            if (currentTickets.length === 0) {
                document.getElementById('emptyState').style.display = 'block';
                document.getElementById('ticketContainer').style.display = 'none';
            } else {
                document.getElementById('ticketContainer').style.display = 'block';
                document.getElementById('emptyState').style.display = 'none';
                renderTickets(currentTickets);
            }
        })
        .catch(error => {
            console.error('Error loading tickets:', error);
            document.getElementById('loading').style.display = 'none';
            document.getElementById('ticketContainer').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #e53e3e; background: #fed7d7; border-radius: 8px;">
                    <svg style="width: 40px; height: 40px; margin-bottom: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h4 style="margin: 0 0 8px 0;">{{ translate('Error Loading Tickets') }}</h4>
                    <p style="margin: 0; font-size: 14px;">{{ translate('Failed to load tickets. Please try again.') }}</p>
                    <button onclick="loadTickets(currentFilter)" style="margin-top: 16px; background: #e53e3e; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">
                        {{ translate('Retry') }}
                    </button>
                </div>
            `;
            document.getElementById('ticketContainer').style.display = 'block';
        });
}

// Render tickets function - One ticket per row
function renderTickets(tickets) {
    const container = document.getElementById('ticketContainer');
    container.innerHTML = '';
    
    tickets.forEach((ticket, index) => {
        const isDrawn = ticket.is_drew == 1;
        const publicUrl = generatePublicTicketUrl(ticket);
        const qrCodeUrl = generateQRCodeUrl(publicUrl, 150);
        
        const ticketRow = document.createElement('div');
        ticketRow.className = 'ticket-row';
        ticketRow.dataset.ticketId = ticket.ticket_number;
        
        ticketRow.innerHTML = `
            <div class="ticket-perforation"></div>
            <div class="ticket-content">
                <!-- Left Column: Main Ticket Info -->
                <div class="ticket-main-info">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px; flex-wrap: wrap;">
                        <span class="ticket-number-large">
                            #${formatTicketNumber(ticket.ticket_number)}
                        </span>

                        <span class="ticket-status ${isDrawn ? 'ticket-status-completed' : 'ticket-status-pending'}" 
                              style="padding:6px 16px; border-radius:20px; font-weight:500; font-size:14px; color:white; background: ${isDrawn ? 'linear-gradient(135deg,#10b981,#34d399)' : 'linear-gradient(135deg,#f59e0b,#fbbf24)'};">
                            ${isDrawn ? '🎉 {{ translate("Completed") }}' : '⏳ {{ translate("Pending") }}'}
                        </span>

                        ${isDrawn ? `
                        <span class="ticket-win-status ${ticket.win_status}" 
                              style="padding:6px 16px; border-radius:20px; font-weight:500; font-size:14px; color:white; background:${ticket.win_status === 'win' ? '#10b981' : '#ef4444'};">
                            ${ticket.win_status === 'win' ? '🏆 {{ translate("Win") }}' : '❌ {{ translate("Lose") }}'}
                        </span>
                        ` : ''}
                    </div>
                    
                    <h3 style="margin: 0; color: #2d3748; font-size: 18px; font-weight: 600;">${ticket.title}</h3>
                    <p style="margin: 0; color: #718096; font-size: 14px;">${truncateText(ticket.description || '{{ translate("No description available") }}', 60)}</p>
                    
                    <div class="ticket-details-grid">
                        <div class="ticket-detail-item">
                            <span class="ticket-detail-label">{{ translate('Ticket Holder') }}</span>
                            <span class="ticket-detail-value">${ticket.name}</span>
                        </div>
                        <div class="ticket-detail-item">
                            <span class="ticket-detail-label">{{ translate('Contact Email') }}</span>
                            <span class="ticket-detail-value" style="font-size: 12px;">${ticket.email}</span>
                        </div>
                        <div class="ticket-detail-item">
                            <span class="ticket-detail-label">{{ translate('Draw Date') }}</span>
                            <span class="ticket-detail-value">${formatDate(ticket.drew_date)}</span>
                        </div>
                        <div class="ticket-detail-item">
                            <span class="ticket-detail-label">{{ translate('Ticket Price') }}</span>
                            <span class="ticket-detail-value">{{ translate('Free on Purchases') }}</span>
                        </div>
                        <div class="ticket-detail-item">
                            <span class="ticket-detail-label">{{ translate('Got Ticket On') }}</span>
                            <span class="ticket-detail-value">${formatDate(ticket.ticket_buy_date)}</span>
                        </div>
                        <div class="ticket-detail-item">
                            <span class="ticket-detail-label">{{ translate('Contact Phone') }}</span>
                            <span class="ticket-detail-value">${ticket.phone || 'N/A'}</span>
                        </div>
                    </div>
                    
                    ${ticket.full_address ? `
                    <div style="margin-top: 8px;">
                        <span class="ticket-detail-label">{{ translate('Address') }}</span>
                        <span class="ticket-detail-value" style="font-size: 13px;">${ticket.full_address}</span>
                    </div>
                    ` : ''}
                    
                </div>
                
                <!-- Middle Column: QR Code -->
                <div class="ticket-qr-section">
                    <div class="ticket-qr-container">
                        <img src="${qrCodeUrl}" 
                             alt="QR Code" 
                             id="qr-${index}"
                             onload="storeQRCodeData('${index}', '${publicUrl}')"
                             onerror="this.src='data:image/svg+xml;base64,${btoa(`
                                <svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 150 150">
                                    <rect width="150" height="150" fill="#f8fafc"/>
                                    <text x="75" y="75" font-family="Arial" font-size="12" text-anchor="middle" fill="#718096">{{ translate('QR Code') }}</text>
                                </svg>
                             `)}'">
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 11px; color: #718096; margin-bottom: 4px;">{{ translate('Scan QR to view ticket') }}</div>
                        <div style="font-size: 10px; color: #4f46e5; font-weight: 500;">{{ translate('Valid Ticket') }}</div>
                    </div>
                </div>
                
                <!-- Right Column: Action Buttons -->
                <div class="ticket-actions">
                    <button onclick="event.stopPropagation(); shareTicket(currentTickets[${index}])" 
                            class="action-btn action-btn-share">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                        </svg>
                        {{ translate('Share Ticket') }}
                    </button>
                    <button onclick="event.stopPropagation(); downloadTicketScreenshot(currentTickets[${index}], ${index})" 
                            class="action-btn action-btn-download">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        {{ translate('Download Ticket') }}
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(ticketRow);
    });
}

// Store QR code data for download
function storeQRCodeData(index, publicUrl) {
    const qrImg = document.getElementById(`qr-${index}`);
    if (qrImg) {
        // Create a canvas to convert the QR code
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = qrImg.naturalWidth || 150;
        canvas.height = qrImg.naturalHeight || 150;
        
        try {
            // Draw the QR code image
            ctx.drawImage(qrImg, 0, 0, canvas.width, canvas.height);
            // Store the data URL
            ticketQRCodes[index] = canvas.toDataURL('image/png');
        } catch (e) {
            // If cross-origin issue, use online API
            ticketQRCodes[index] = generateQRCodeUrl(publicUrl, 300);
        }
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set active tab to "Active (Pending)" by default
    document.querySelectorAll('.filter-tab').forEach(tab => {
        if (tab.dataset.status === '0') {
            tab.classList.add('active');
        }
        
        tab.addEventListener('click', function() {
            const status = this.dataset.status;
            loadTickets(status);
        });
    });
    
    // Load active tickets by default
    loadTickets('0');
    
    document.getElementById('refreshBtn').addEventListener('click', function() {
        loadTickets(currentFilter);
    });
});
</script>

<!-- Include html2canvas library for screenshot functionality -->
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
@endsection