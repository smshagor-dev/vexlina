@php
    $system_currency = get_system_currency();
    $isDrawn = $ticket->is_drew == 1;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View lottery ticket {{ $ticket->ticket_number }} for {{ $ticket->title }}">
    <title>Lottery Ticket: {{ $ticket->title }} | {{ config('app.name', 'Lottery System') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: transparent;
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #fef3c7 0%, #ffedd5 100%);
        }
        
        .ticket-wrapper {
            max-width: 900px;
            width: 100%;
            position: relative;
        }
        
        .ticket-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: none;
            position: relative;
            border: 2px solid #fa3e00;
        }
        
        .ticket-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #fa3e00 0%, #ff6b35 50%, #ff9e6d 100%);
        }
        
        .verified-ribbon {
            position: absolute;
            top: 20px;
            right: -30px;
            background: linear-gradient(135deg, #28a745, #71dd8a);
            color: white;
            padding: 10px 40px;
            font-weight: 600;
            font-size: 14px;
            transform: rotate(45deg);
            box-shadow: 0 4px 15px rgba(250, 62, 0, 0.2);
            z-index: 2;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #fa3e00 0%, #ff6b35 100%);
            color: white;
            padding: 40px 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .ticket-number-display {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            padding: 25px;
            margin: 30px auto;
            max-width: 500px;
        }
        
        .ticket-number {
            font-family: 'Courier New', monospace;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 2px;
            color: white;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .status-pending {
            background: linear-gradient(135deg, #ff6b35, #ff9e6d);
            color: white;
        }
        
        .status-completed {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 40px;
        }
        
        .info-item {
            padding: 20px;
            background: #fff7ed;
            border-radius: 12px;
            border: 1px solid #ffedd5;
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(250, 62, 0, 0.1);
            background: white;
            border-color: #fa3e00;
        }
        
        .info-label {
            font-size: 12px;
            color: #9a3412;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .info-value {
            font-size: 16px;
            color: #431407;
            font-weight: 500;
            line-height: 1.5;
        }
        
        .highlight-card {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            border: 2px solid #fa3e00;
            border-radius: 16px;
            padding: 30px;
            margin: 0 40px 40px;
            text-align: center;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #fff7ed, #ffffff);
            padding: 40px;
            border-top: 2px solid #ffedd5;
            text-align: center;
        }
        
        .btn-get-ticket {
            background: linear-gradient(135deg, #fa3e00, #ff6b35);
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 4px 20px rgba(250, 62, 0, 0.3);
        }
        
        .btn-get-ticket:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(250, 62, 0, 0.4);
            color: white;
            background: linear-gradient(135deg, #ff6b35, #fa3e00);
        }
        
        .btn-share {
            background: white;
            color: #fa3e00;
            border: 2px solid #fa3e00;
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .btn-share:hover {
            background: #fa3e00;
            color: white;
            border-color: #fa3e00;
        }
        
        .suggestion-card {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 2px solid #f59e0b;
            border-radius: 16px;
            padding: 25px;
            margin: 30px 40px;
            position: relative;
            overflow: hidden;
        }
        
        .suggestion-card::before {
            content: '';
            position: absolute;
            top: -10px;
            right: -10px;
            width: 60px;
            height: 60px;
            background: #fa3e00;
            border-radius: 50%;
            opacity: 0.1;
        }
        
        .suggestion-card::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: -20px;
            width: 80px;
            height: 80px;
            background: #fa3e00;
            border-radius: 50%;
            opacity: 0.1;
        }
        
        .price-tag {
            background: linear-gradient(135deg, #fa3e00, #ff6b35);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(250, 62, 0, 0.2);
        }
        
        .footer-note {
            background: #fff7ed;
            padding: 20px 40px;
            text-align: center;
            color: #9a3412;
            font-size: 14px;
            border-top: 2px solid #ffedd5;
        }
        
        .ticket-dots {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px 0;
            gap: 10px;
        }
        
        .ticket-dot {
            width: 8px;
            height: 8px;
            background: #fa3e00;
            border-radius: 50%;
            opacity: 0.5;
        }
        
        .lottery-icon {
            color: #fa3e00;
        }
        
        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #10b981;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .ticket-header {
                padding: 30px 20px;
            }
            
            .ticket-number {
                font-size: 24px;
                letter-spacing: 1px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 15px;
            }
            
            .highlight-card,
            .suggestion-card,
            .cta-section {
                margin: 20px;
                padding: 20px;
            }
            
            .btn-get-ticket,
            .btn-share {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .verified-ribbon {
                right: -35px;
                padding: 8px 35px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-wrapper">
        <div class="ticket-card">
            <!-- Verified Ribbon -->
            <div class="verified-ribbon">
                <i class="fas fa-shield-check me-2"></i> VERIFIED
            </div>
            
            <!-- Ticket Header -->
            <div class="ticket-header">
                <div class="mb-3">
                    <i class="fas fa-ticket-alt fa-3x text-white opacity-75"></i>
                </div>
                <h1 class="h3 mb-2">{{ $ticket->title }}</h1>
                <p class="text-white opacity-75 mb-4">Official Lottery Ticket</p>
                
                <!-- Ticket Number Display -->
                <div class="ticket-number-display">
                    <p class="small mb-2 text-white opacity-75">TICKET NUMBER</p>
                    <h2 class="ticket-number">{{ $ticket->ticket_number }}</h2>
                </div>
                
                <!-- Status Badge -->
                <div>
                    <span class="status-badge {{ $isDrawn ? 'status-completed' : 'status-pending' }}">
                        <i class="fas {{ $isDrawn ? 'fa-check-circle' : 'fa-clock' }}"></i>
                        {{ $isDrawn ? 'Draw Completed' : 'Pending Draw' }}
                    </span>
                </div>
            </div>
            
            <!-- Ticket Information -->
            <div class="info-grid">
                <!-- Ticket Holder -->
                <div class="info-item">
                    <div class="info-label">Ticket Holder</div>
                    <div class="info-value">{{ $ticket->name }}</div>
                </div>
                
                <!-- Contact Email -->
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $ticket->email }}</div>
                </div>
                
                <!-- Contact Phone -->
                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">{{ $ticket->phone ?: 'Not Provided' }}</div>
                </div>
                
                <!-- Purchase Date -->
                <div class="info-item">
                    <div class="info-label">Purchased On</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($ticket->ticket_buy_date)->format('F d, Y \a\t h:i A') }}</div>
                </div>
                
                <!-- Draw Date -->
                <div class="info-item">
                    <div class="info-label">Draw Date</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($ticket->drew_date)->format('F d, Y \a\t h:i A') }}</div>
                </div>
                
                <!-- Address -->
                @if($ticket->full_address && $ticket->full_address != 'Address not provided')
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value">{{ $ticket->full_address }}</div>
                </div>
                @endif
            </div>
            
            <!-- Price Highlight -->
            <div class="highlight-card">
                <div class="mb-3">
                    <i class="fas fa-tag fa-3x lottery-icon"></i>
                </div>
                <h4 class="mb-3" style="color: #9a3412;">Ticket Price</h4>
                <div class="price-tag mb-3">
                    <i class="fas fa-tag"></i>
                    Free on Purchases
                </div>
                <p class="mb-0" style="color: #9a3412;">
                    <i class="fas fa-lock me-1"></i> Paid and verified on purchase
                </p>
            </div>
            
            <!-- Suggestion for New Tickets -->
            <div class="suggestion-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-2" style="color: #9a3412;">
                            <i class="fas fa-bolt lottery-icon me-2"></i> Want More Tickets?
                        </h4>
                        <p class="mb-0" style="color: #7c2d12;">
                            Every purchase gets you a new ticket! Start shopping now to get more chances to win.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="/" class="btn-get-ticket">
                            <i class="fas fa-shopping-cart me-2"></i> Get Tickets
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Dots Separator -->
            <div class="ticket-dots">
                <div class="ticket-dot"></div>
                <div class="ticket-dot"></div>
                <div class="ticket-dot"></div>
                <div class="ticket-dot"></div>
                <div class="ticket-dot"></div>
            </div>
            
            <!-- Call to Action -->
            <div class="cta-section">
                <h3 class="mb-3" style="color: #9a3412;">Get Your Next Ticket</h3>
                <p class="mb-4" style="color: #7c2d12;">
                    Purchase a new lottery ticket and get instant entry. Each ticket gives you a chance to win!
                </p>
                <div class="row justify-content-center g-3">
                    <div class="col-md-6">
                        <a href="/" class="btn-get-ticket w-100">
                            <i class="fas fa-ticket-alt me-2"></i> Get New Ticket
                        </a>
                    </div>
                    <div class="col-md-6">
                        <button onclick="shareTicket()" class="btn-share w-100">
                            <i class="fas fa-share-alt me-2"></i> Share This Ticket
                        </button>
                    </div>
                </div>
                <p class="mt-4 small" style="color: #9a3412;">
                    <i class="fas fa-info-circle lottery-icon me-1"></i>
                    Each purchase automatically generates a new ticket with unique number
                </p>
            </div>
            
            <!-- Footer -->
            <div class="footer-note">
                <div class="row align-items-center">
                    <div class="col-md-6 text-md-start mb-3 mb-md-0">
                        <div class="secure-badge">
                            <i class="fas fa-lock"></i>
                            Secured by {{ config('app.name', 'Lottery System') }}
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small>
                            <i class="fas fa-calendar-check lottery-icon me-1"></i>
                            Verified on {{ \Carbon\Carbon::now()->format('M d, Y \a\t h:i A') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Back Link -->
        <div class="text-center mt-4">
            <a href="{{ route('user.lottary.index') }}" class="text-decoration-none" style="color: #9a3412;">
                <i class="fas fa-arrow-left me-2"></i> {{ translate('My Lottery Tickets') }}
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function shareTicket() {
            const ticketData = {
                title: "{{ addslashes($ticket->title) }}",
                number: "{{ $ticket->ticket_number }}",
                drawDate: "{{ \Carbon\Carbon::parse($ticket->drew_date)->format('M d, Y') }}",
                url: window.location.href
            };
            
            const shareText = `Check out my lottery ticket for "${ticketData.title}"! 🎫\nTicket Number: ${ticketData.number}\nDraw Date: ${ticketData.drawDate}\n\nView ticket: ${ticketData.url}`;
            
            if (navigator.share) {
                // Use Web Share API if available (mobile)
                navigator.share({
                    title: `Lottery Ticket: ${ticketData.title}`,
                    text: shareText,
                    url: ticketData.url
                });
            } else {
                // Fallback for desktop - copy to clipboard
                navigator.clipboard.writeText(shareText).then(() => {
                    showCustomToast('Ticket information copied to clipboard! You can now paste it anywhere to share.');
                }).catch(() => {
                    // Final fallback
                    prompt('Copy this text to share:', shareText);
                });
            }
        }
        
        function showCustomToast(message) {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-3';
            toast.style.zIndex = '1060';
            toast.innerHTML = `
                <div class="d-flex align-items-center p-3 rounded shadow-lg" style="background: linear-gradient(135deg, #fa3e00, #ff6b35); color: white; min-width: 300px;">
                    <i class="fas fa-check-circle me-2"></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 3000);
        }
    </script>
</body>
</html>