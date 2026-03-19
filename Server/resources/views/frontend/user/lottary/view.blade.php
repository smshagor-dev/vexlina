@php
    $system_currency = get_system_currency();
@endphp

@extends('frontend.layouts.app')

@section('meta_title'){{ translate('Lottery System - Win Amazing Prizes') }}@stop

@section('meta_description'){{ translate('Participate in our lottery system by shopping! Every paid order gives you a lottery ticket. Win amazing cash prizes, gadgets, and more.') }}@stop

@section('meta_keywords'){{ translate('lottery, win prizes, shopping lottery, cash prizes, online lottery') }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ translate('Lottery System - Win Amazing Prizes') }}">
    <meta itemprop="description" content="{{ translate('Participate in our lottery system by shopping! Every paid order gives you a lottery ticket. Win amazing cash prizes, gadgets, and more.') }}">
    <meta itemprop="image" content="{{ asset('assets/img/lottery-meta.jpg') }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ translate('Lottery System - Win Amazing Prizes') }}">
    <meta name="twitter:description" content="{{ translate('Participate in our lottery system by shopping! Every paid order gives you a lottery ticket. Win amazing cash prizes, gadgets, and more.') }}">
    <meta name="twitter:image" content="{{ asset('assets/img/lottery-meta.jpg') }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ translate('Lottery System - Win Amazing Prizes') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="{{ asset('assets/img/lottery-meta.jpg') }}" />
    <meta property="og:description" content="{{ translate('Participate in our lottery system by shopping! Every paid order gives you a lottery ticket. Win amazing cash prizes, gadgets, and more.') }}" />
    <meta property="og:site_name" content="{{ config('app.name', 'Lottery System') }}" />
@endsection

@section('content')
<div class="container lottery-container">
    <!-- Header -->
    <div class="lottery-header">
        <h1>{{ translate('Our Lottery') }}</h1>
        <p class="subtitle">{{ translate('Win Amazing Prizes While You Shop!') }}</p>
    </div>
    
    
    <div style="text-align:center; margin-bottom:16px;">
        <a href="{{ route('user.lottary.drawn') }}"
           style="
                display:inline-flex;
                align-items:center;
                gap:8px;
                padding:10px 20px;
                background:linear-gradient(90deg, #16a34a, #15803d);
                color:#ffffff;
                text-decoration:none;
                border-radius:9999px;
                box-shadow:0 4px 10px rgba(0,0,0,0.15);
                font-weight:500;
                transition:all 0.3s ease;
           "
           onmouseover="this.style.background='linear-gradient(90deg, #15803d, #166534)'"
           onmouseout="this.style.background='linear-gradient(90deg, #16a34a, #15803d)'"
        >
            <i class="las la-trophy" style="font-size:18px;"></i>
            {{ translate('View Previously Drawn Lotteries') }}
        </a>
    </div>



    <!-- Loading State -->
    <div id="loading" class="loading-state">
        <div class="spinner"></div>
        <p>{{ translate('Loading lotteries...') }}</p>
    </div>

    <!-- Current Lottery Container -->
    <div id="currentLotteryContainer" class="current-lottery-container"></div>

    <!-- Upcoming Lottery Container -->
    <div id="upcomingLotteryContainer" class="upcoming-lottery-container" style="display: none;">
        <div class="upcoming-header">
            <h2>
                <i class="las la-calendar-plus"></i>
                {{ translate('Upcoming Lotteries') }}
                <span class="upcoming-count" id="upcomingCount"></span>
            </h2>
            <button class="toggle-upcoming-btn" onclick="toggleAllUpcomingLotteries()">
                <span class="toggle-text">{{ translate('Show All Upcoming') }}</span>
                <i class="las la-angle-down"></i>
            </button>
        </div>
        
        <div class="upcoming-content" id="upcomingContent">
            <!-- All upcoming lotteries will be loaded here -->
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="empty-state">
        <div class="empty-icon">
            <i class="las la-ticket-alt"></i>
        </div>
        <h3>{{ translate('No Active Lotteries') }}</h3>
        <p>{{ translate('There are no active lotteries at the moment. Check back later or start shopping to get tickets for future draws.') }}</p>
        <a href="{{ route('inhouse.all') }}" class="shop-button">
            <i class="las la-shopping-cart"></i>
            {{ translate('Start Shopping') }}
        </a>
    </div>
    
    <!-- Instructions Section -->
    <div class="instructions-section">
        <div class="instructions-header">
            <div class="instructions-icon">
                <i class="las la-gift"></i>
            </div>
            <h2>{{ translate('How to Get Lottery Tickets') }}</h2>
        </div>
        
        <div class="steps-grid">
            <!-- Step 1 -->
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>{{ translate('Shop Products') }}</h3>
                    <p>{{ translate('Browse and shop from our product catalog. Add items to your cart and proceed to checkout.') }}</p>
                </div>
            </div>
            
            <!-- Step 2 -->
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>{{ translate('Complete Payment') }}</h3>
                    <p>{{ translate('Complete your payment. For online payments, you get tickets instantly. For COD, tickets are issued after order delivery.') }}</p>
                </div>
            </div>
            
            <!-- Step 3 -->
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>{{ translate('Get Lottery Ticket') }}</h3>
                    <p>{{ translate('Once payment is confirmed, you\'ll receive a unique lottery ticket automatically. Check "My Lottery Tickets" section.') }}</p>
                </div>
            </div>
            
            <!-- Step 4 -->
            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>{{ translate('Wait for Draw') }}</h3>
                    <p>{{ translate('Your ticket enters the current lottery draw. Check draw dates and wait for results. Winners are notified automatically.') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Important Notes -->
        <div class="notes-section">
            <div class="notes-header">
                <i class="las la-exclamation-circle"></i>
                <h4>{{ translate('Important Notes:') }}</h4>
            </div>
            <div class="notes-grid">
                <div class="note-item">
                    <i class="las la-ticket-alt"></i>
                    <span><strong>{{ translate('One ticket per order:') }}</strong> {{ translate('Each paid order gives you ONE lottery ticket') }}</span>
                </div>
                <div class="note-item">
                    <i class="las la-credit-card"></i>
                    <span><strong>{{ translate('Payment required:') }}</strong> {{ translate('Tickets are issued only for paid orders') }}</span>
                </div>
                <div class="note-item">
                    <i class="las la-truck"></i>
                    <span><strong>{{ translate('COD orders:') }}</strong> {{ translate('Tickets issued after order delivery confirmation') }}</span>
                </div>
                <div class="note-item">
                    <i class="las la-boxes"></i>
                    <span><strong>{{ translate('Multiple orders:') }}</strong> {{ translate('Every order = New lottery ticket') }}</span>
                </div>
                <div class="note-item">
                    <i class="las la-ban"></i>
                    <span><strong>{{ translate('No direct purchase:') }}</strong> {{ translate('Cannot buy tickets directly - only through shopping') }}</span>
                </div>
            </div>
        </div>
        
        <!-- Start Shopping Button -->
        <div class="cta-section">
            <a href="{{ route('inhouse.all') }}" class="cta-button">
                <i class="las la-shopping-cart"></i>
                {{ translate('Start Shopping & Get Tickets') }}
            </a>
            <p class="cta-note">
                <i class="las la-info-circle"></i> {{ translate('Start shopping to participate in the lottery') }}
            </p>
        </div>
    </div>
    
    <!-- View Your Tickets -->
    <div style="text-align:center; padding:30px 20px; background:#f9fafb; border-radius:12px; margin-top:30px;">
    
        <h3 style="font-size:22px; font-weight:600; margin-bottom:8px;">
            {{ translate('Check Your Lottery Tickets') }}
        </h3>
    
        <p style="color:#555; margin-bottom:20px;">
            {{ translate('View all your lottery tickets, check draw dates, and see if you\'ve won!') }}
        </p>
    
        <!-- View My Tickets Button -->
        <a href="{{ route('user.lottary.index') }}"
           style="
                display:inline-flex;
                align-items:center;
                gap:8px;
                padding:10px 22px;
                background:#2563eb;
                color:#fff;
                text-decoration:none;
                border-radius:8px;
                font-weight:500;
                box-shadow:0 4px 10px rgba(0,0,0,0.12);
                transition:all 0.3s ease;
           "
           onmouseover="this.style.background='#1e40af'"
           onmouseout="this.style.background='#2563eb'"
        >
            <i class="las la-ticket-alt" style="font-size:18px;"></i>
            {{ translate('View My Tickets') }}
        </a>
    
        <br><br>
    
        <!-- View Drawn Lotteries Button -->
        <a href="{{ route('user.lottary.drawn') }}"
           style="
                display:inline-flex;
                align-items:center;
                gap:8px;
                padding:10px 22px;
                background:linear-gradient(90deg,#16a34a,#15803d);
                color:#fff;
                text-decoration:none;
                border-radius:8px;
                font-weight:500;
                box-shadow:0 4px 10px rgba(0,0,0,0.12);
                transition:all 0.3s ease;
           "
           onmouseover="this.style.background='linear-gradient(90deg,#15803d,#166534)'"
           onmouseout="this.style.background='linear-gradient(90deg,#16a34a,#15803d)'"
        >
            <i class="las la-trophy" style="font-size:18px;"></i>
            {{ translate('View Previously Drawn Lotteries') }}
        </a>
    
    </div>
</div>

<style>
/* Base Styles */
.lottery-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Header */
.lottery-header {
    text-align: center;
    margin-bottom: 40px;
}

.lottery-header h1 {
    margin-bottom: 10px;
    color: #2d3748;
    font-weight: 700;
    font-size: 2.5rem;
}

.lottery-header .subtitle {
    color: #718096;
    font-size: 1.125rem;
}

/* Loading State */
.loading-state {
    text-align: center;
    padding: 60px 20px;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #e2e8f0;
    border-top: 4px solid #fa3e00;
    border-radius: 50%;
    margin: 0 auto;
    animation: spin 1s linear infinite;
}

.loading-state p {
    margin-top: 20px;
    color: #718096;
    font-size: 1rem;
}

/* Current Lottery */
.current-lottery-container {
    margin-bottom: 50px;
}

.lottery-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    border: 2px solid #e2e8f0;
    transition: all 0.4s ease;
    position: relative;
    margin-bottom: 30px;
}

.lottery-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
    border-color: #fa3e00;
}

.current-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background: linear-gradient(135deg, #fa3e00, #ff6b35);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
    z-index: 2;
    box-shadow: 0 6px 20px rgba(250, 62, 0, 0.25);
    display: flex;
    align-items: center;
    gap: 8px;
}

.upcoming-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
    z-index: 2;
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.25);
    display: flex;
    align-items: center;
    gap: 8px;
}

.lottery-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-bottom: 2px solid #e2e8f0;
}

.lottery-content {
    padding: 30px;
}

.lottery-title-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 20px;
}

.lottery-title-section h3 {
    margin: 0 0 12px 0;
    color: #2d3748;
    font-size: 2rem;
    font-weight: 700;
    flex: 1;
    min-width: 300px;
}

.lottery-description {
    margin: 0;
    color: #718096;
    font-size: 1.1rem;
    line-height: 1.6;
    flex-basis: 100%;
}

.ticket-price {
    text-align: right;
    min-width: 150px;
}

.ticket-price .label {
    font-size: 0.875rem;
    color: #718096;
    margin-bottom: 6px;
}

.ticket-price .amount {
    font-size: 2.25rem;
    font-weight: bold;
    color: #fa3e00;
    line-height: 1;
}

.ticket-price .note {
    font-size: 0.75rem;
    color: #718096;
    margin-top: 4px;
}

/* Draw Date */
.draw-date-card {
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border-radius: 15px;
    border: 2px solid #bae6fd;
    display: flex;
    align-items: center;
    gap: 20px;
}

.start-date-card {
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    border-radius: 15px;
    border: 2px solid #a5b4fc;
    display: flex;
    align-items: center;
    gap: 20px;
}

.draw-date-info {
    flex: 1;
}

.timer {
    margin-left: auto;
    padding: 8px 14px;
    border-radius: 10px;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: #ffffff;
    font-weight: 700;
    font-size: 1rem;
    white-space: nowrap;
    box-shadow: 0 4px 10px rgba(34, 197, 94, 0.35);
}

.start-timer {
    margin-left: auto;
    padding: 8px 14px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #ffffff;
    font-weight: 700;
    font-size: 1rem;
    white-space: nowrap;
    box-shadow: 0 4px 10px rgba(139, 92, 246, 0.35);
}

@media (max-width: 576px) {
    .draw-date-card, .start-date-card {
        flex-wrap: wrap;
    }

    .timer, .start-timer {
        margin-left: 0;
        margin-top: 10px;
        width: 100%;
        text-align: center;
    }
}

.draw-date-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.start-date-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.draw-date-icon i, .start-date-icon i {
    color: white;
    font-size: 24px;
}

.draw-date-info .label {
    font-size: 0.875rem;
    color: #0c4a6e;
    font-weight: 600;
    margin-bottom: 6px;
    letter-spacing: 0.5px;
}

.start-date-info .label {
    font-size: 0.875rem;
    color: #3730a3;
    font-weight: 600;
    margin-bottom: 6px;
    letter-spacing: 0.5px;
}

.draw-date-info .date, .start-date-info .date {
    font-size: 1.125rem;
    color: #1e293b;
    font-weight: 500;
}

/* Prizes Section - Redesigned */
.prizes-section {
    margin-bottom: 30px;
}

.prizes-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.prizes-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #10b981, #34d399);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.prizes-icon i {
    color: white;
    font-size: 22px;
}

.prizes-header h2 {
    margin: 0;
    color: #2d3748;
    font-size: 1.75rem;
    font-weight: 600;
}

.prizes-subtitle {
    color: #64748b;
    font-size: 1rem;
    margin-top: 5px;
}

/* Prizes Grid - Responsive Card Layout */
.prizes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.prize-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
    position: relative;
}

.prize-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    border-color: #fa3e00;
}

.prize-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.prize-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.prize-card:hover .prize-image {
    transform: scale(1.05);
}

.prize-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
}

.prize-content {
    padding: 20px;
}

.prize-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    line-height: 1.3;
}

.prize-description {
    color: #64748b;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 15px;
    min-height: 42px;
}

.prize-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-top: 15px;
    border-top: 1px solid #e2e8f0;
}

.prize-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #fa3e00;
}

.winners-badge {
    background: #4f46e5;
    color: white;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
}

.guaranteed-badge {
    background: #d1fae5;
    color: #065f46;
    padding: 8px 15px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
}

/* Countdown Timer */
.countdown-timer {
    background: linear-gradient(135deg, #1e40af, #3730a3);
    color: white;
    padding: 25px;
    border-radius: 16px;
    text-align: center;
    margin: 30px 0;
    box-shadow: 0 10px 30px rgba(30, 64, 175, 0.2);
}

.timer-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-bottom: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.timer-value {
    font-size: 1.75rem;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    letter-spacing: 2px;
}

/* Shop Button */
.shop-button-large {
    background: linear-gradient(135deg, #fa3e00, #ff6b35);
    color: white;
    border: none;
    padding: 18px 40px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1.125rem;
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
    margin-top: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    box-shadow: 0 8px 25px rgba(250, 62, 0, 0.25);
    text-decoration: none;
}

.shop-button-large:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(250, 62, 0, 0.35);
}

/* Upcoming Lottery Container - FIXED */
.upcoming-lottery-container {
    margin-bottom: 50px;
}

.upcoming-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #f8fafc, #ffffff);
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    margin-bottom: 0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upcoming-header:hover {
    border-color: #3b82f6;
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.1);
}

.upcoming-header h2 {
    margin: 0;
    color: #3b82f6;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.upcoming-count {
    background: #3b82f6;
    color: white;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.toggle-upcoming-btn {
    background: none;
    border: none;
    color: #3b82f6;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.toggle-upcoming-btn:hover {
    background: rgba(59, 130, 246, 0.1);
}

.toggle-upcoming-btn i {
    transition: transform 0.3s ease;
}

.toggle-upcoming-btn.expanded i {
    transform: rotate(180deg);
}

.upcoming-content {
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    transition: max-height 0.5s ease, opacity 0.3s ease, padding 0.3s ease;
}

.upcoming-content.expanded {
    max-height: 5000px;
    opacity: 1;
    padding-top: 20px;
    overflow: visible;
}

/* Empty State */
.empty-state {
    display: none;
    text-align: center;
    padding: 60px 30px;
    background: #f8fafc;
    border-radius: 16px;
    margin: 30px 0;
    border: 2px dashed #cbd5e0;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #e2e8f0, #cbd5e0);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
}

.empty-icon i {
    color: #94a3b8;
    font-size: 32px;
}

.empty-state h3 {
    color: #4a5568;
    margin-bottom: 15px;
    font-size: 1.5rem;
}

.empty-state p {
    color: #718096;
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

/* Instructions Section */
.instructions-section {
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    border: 2px solid #fa3e00;
    border-radius: 20px;
    padding: 40px;
    margin: 50px 0;
    position: relative;
    overflow: hidden;
}

.instructions-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 40px;
}

.instructions-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #fa3e00, #ff6b35);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 25px rgba(250, 62, 0, 0.2);
}

.instructions-icon i {
    color: white;
    font-size: 28px;
}

.instructions-section h2 {
    margin: 0;
    color: #9a3412;
    font-size: 2rem;
    font-weight: 600;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.step-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    border: 2px solid #ffedd5;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.step-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    border-color: #fa3e00;
}

.step-number {
    position: absolute;
    top: -15px;
    left: -15px;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #fa3e00, #ff6b35);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.25rem;
    box-shadow: 0 6px 15px rgba(250, 62, 0, 0.3);
}

.step-content {
    margin-top: 10px;
}

.step-content h3 {
    margin: 0 0 12px 0;
    color: #9a3412;
    font-size: 1.25rem;
    font-weight: 600;
}

.step-content p {
    margin: 0;
    color: #7c2d12;
    font-size: 0.9rem;
    line-height: 1.6;
}

/* Notes Section */
.notes-section {
    background: rgba(250, 62, 0, 0.05);
    border-radius: 16px;
    border-left: 4px solid #fa3e00;
    padding: 25px;
    margin-bottom: 40px;
}

.notes-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.notes-header i {
    color: #fa3e00;
    font-size: 22px;
}

.notes-header h4 {
    margin: 0;
    color: #9a3412;
    font-size: 1.25rem;
}

.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.note-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 10px;
    border: 1px solid #ffedd5;
}

.note-item i {
    color: #fa3e00;
    font-size: 16px;
    flex-shrink: 0;
}

.note-item span {
    color: #7c2d12;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* CTA Section */
.cta-section {
    text-align: center;
}

.cta-button {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #fa3e00, #ff6b35);
    color: white;
    padding: 18px 45px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.125rem;
    transition: all 0.3s;
    box-shadow: 0 8px 25px rgba(250, 62, 0, 0.3);
}

.cta-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(250, 62, 0, 0.4);
}

.cta-note {
    margin-top: 15px;
    color: #9a3412;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* Tickets Section */
.tickets-section {
    text-align: center;
    padding: 40px;
    background: linear-gradient(135deg, #f8fafc, #ffffff);
    border-radius: 20px;
    border: 2px solid #e2e8f0;
    margin: 50px 0;
}

.tickets-section h3 {
    margin: 0 0 15px 0;
    color: #2d3748;
    font-size: 1.75rem;
}

.tickets-section p {
    color: #718096;
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.view-tickets-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
    padding: 16px 40px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.125rem;
    transition: all 0.3s;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.2);
}

.view-tickets-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
}

/* Animations */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .lottery-container {
        padding: 15px;
    }
    
    .lottery-header h1 {
        font-size: 2rem;
    }
    
    .lottery-header .subtitle {
        font-size: 1rem;
    }
    
    .lottery-image {
        height: 200px;
    }
    
    .lottery-content {
        padding: 20px;
    }
    
    .lottery-title-section h3 {
        font-size: 1.5rem;
        min-width: 100%;
    }
    
    .ticket-price {
        text-align: left;
        min-width: 100%;
    }
    
    .current-badge, .upcoming-badge {
        top: 15px;
        left: 15px;
        padding: 6px 15px;
        font-size: 0.75rem;
    }
    
    .draw-date-card, .start-date-card {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .draw-date-icon, .start-date-icon {
        width: 50px;
        height: 50px;
    }
    
    .prizes-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .prizes-header {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .prizes-header h2 {
        font-size: 1.5rem;
    }
    
    .prize-image-container {
        height: 160px;
    }
    
    .timer-value {
        font-size: 1.5rem;
    }
    
    .upcoming-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .toggle-upcoming-btn {
        width: 100%;
        justify-content: center;
    }
    
    .instructions-section {
        padding: 25px;
        margin: 30px 0;
    }
    
    .instructions-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .instructions-section h2 {
        font-size: 1.5rem;
    }
    
    .steps-grid {
        grid-template-columns: 1fr;
    }
    
    .notes-grid {
        grid-template-columns: 1fr;
    }
    
    .shop-button-large,
    .cta-button,
    .view-tickets-btn {
        padding: 16px 30px;
        font-size: 1rem;
    }
    
    .tickets-section {
        padding: 30px 20px;
    }
}

@media (max-width: 480px) {
    .prize-card {
        margin: 0 -10px;
        border-radius: 0;
        border-left: none;
        border-right: none;
    }
    
    .prize-content {
        padding: 15px;
    }
    
    .timer-value {
        font-size: 1.25rem;
        letter-spacing: 1px;
    }
    
    .cta-button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Global variable to track upcoming lottery expansion state
let upcomingExpanded = false;
let upcomingLotteries = [];

// Format date function
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
}

// Format currency
function formatCurrency(amount) {
    return '{{ $system_currency->symbol }}' + parseFloat(amount).toFixed(2);
}

// Countdown timer function for draw date
function startDrawCountdown(drawDate, elementId) {
    function updateTimer() {
        const now = new Date().getTime();
        const drawTime = new Date(drawDate).getTime();
        const distance = drawTime - now;

        if (distance < 0) {
            document.getElementById(elementId).innerHTML = `
                <div class="timer-label">{{ translate("Draw Completed") }}</div>
                <div class="timer-value">00:00:00:00</div>
            `;
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById(elementId).innerHTML = `
            <div class="timer-label">{{ translate("Draw In") }}</div>
            <div class="timer-value">
                ${days.toString().padStart(2, '0')}d ${hours.toString().padStart(2, '0')}h ${minutes.toString().padStart(2, '0')}m ${seconds.toString().padStart(2, '0')}s
            </div>
        `;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}

// Countdown timer function for start date (for upcoming lottery)
function startStartDateCountdown(startDate, elementId) {
    function updateTimer() {
        const now = new Date().getTime();
        const startTime = new Date(startDate).getTime();
        const distance = startTime - now;

        if (distance < 0) {
            document.getElementById(elementId).innerHTML = `
                <div class="timer-label">{{ translate("Starts Now") }}</div>
                <div class="timer-value">00:00:00:00</div>
            `;
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById(elementId).innerHTML = `
            <div class="timer-label">{{ translate("Starts In") }}</div>
            <div class="timer-value">
                ${days.toString().padStart(2, '0')}d ${hours.toString().padStart(2, '0')}h ${minutes.toString().padStart(2, '0')}m ${seconds.toString().padStart(2, '0')}s
            </div>
        `;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}

// Toggle all upcoming lotteries
function toggleAllUpcomingLotteries() {
    const content = document.getElementById('upcomingContent');
    const button = document.querySelector('.toggle-upcoming-btn');
    const toggleText = button.querySelector('.toggle-text');
    const icon = button.querySelector('i');
    
    if (upcomingExpanded) {
        // Collapse
        content.classList.remove('expanded');
        toggleText.textContent = upcomingLotteries.length > 1 ? `{{ translate("Show") }} ${upcomingLotteries.length} {{ translate("Upcoming Lotteries") }}` : '{{ translate("Show Upcoming Lottery") }}';
        button.classList.remove('expanded');
    } else {
        // Expand
        content.classList.add('expanded');
        toggleText.textContent = upcomingLotteries.length > 1 ? `{{ translate("Hide") }} ${upcomingLotteries.length} {{ translate("Upcoming Lotteries") }}` : '{{ translate("Hide Upcoming Lottery") }}';
        button.classList.add('expanded');
    }
    
    upcomingExpanded = !upcomingExpanded;
}

// Load lotteries
function loadLotteries() {
    fetch('/lottery-with-prize')
        .then(res => res.json())
        .then(res => {
            document.getElementById('loading').style.display = 'none';
            
            if (!res.success || res.data.length === 0) {
                document.getElementById('emptyState').style.display = 'block';
                return;
            }

            const lotteries = res.data;
            
            // Find current lottery
            const currentLottery = lotteries.find(l => l.type === 'current');
            // Find upcoming lotteries
            upcomingLotteries = lotteries.filter(l => l.type === 'upcoming');

            // Render current lottery
            if (currentLottery) {
                document.getElementById('currentLotteryContainer').style.display = 'block';
                renderCurrentLottery(currentLottery);
            } else {
                // Show no active lottery message with upcoming countdown
                showNoActiveLotteryMessage(upcomingLotteries);
            }

            // Render upcoming lotteries
            if (upcomingLotteries.length > 0) {
                document.getElementById('upcomingLotteryContainer').style.display = 'block';
                document.getElementById('upcomingCount').textContent = upcomingLotteries.length;
                renderAllUpcomingLotteries(upcomingLotteries);
            }

            // If no lotteries at all, show empty state
            if (!currentLottery && upcomingLotteries.length === 0) {
                document.getElementById('emptyState').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading lotteries:', error);
            document.getElementById('loading').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
            document.getElementById('emptyState').innerHTML = `
                <div class="empty-icon">
                    <i class="las la-exclamation-triangle"></i>
                </div>
                <h3>{{ translate("Error Loading Lotteries") }}</h3>
                <p>{{ translate("Failed to load lotteries. Please try again.") }}</p>
                <button onclick="loadLotteries()" class="shop-button-large" style="margin-top: 20px;">
                    <i class="las la-redo"></i>
                    {{ translate("Retry") }}
                </button>
            `;
        });
}

// Show no active lottery message with upcoming countdown
function showNoActiveLotteryMessage(upcomingLotteries) {
    const container = document.getElementById('currentLotteryContainer');
    
    if (upcomingLotteries.length === 0) {
        container.innerHTML = `
            <div class="lottery-card" style="text-align: center; padding: 40px 20px;">
                <div class="empty-icon" style="margin: 0 auto 20px;">
                    <i class="las la-hourglass-half"></i>
                </div>
                <h3 style="color: #4a5568; margin-bottom: 10px;">{{ translate("No Active Lottery Right Now") }}</h3>
                <p style="color: #718096; margin-bottom: 30px; max-width: 500px; margin-left: auto; margin-right: auto;">
                    {{ translate("There is no active lottery at the moment. Please check back later for upcoming draws.") }}
                </p>
                <a href="{{ route('inhouse.all') }}" class="shop-button-large" style="max-width: 300px; margin: 0 auto;">
                    <i class="las la-shopping-cart"></i>
                    {{ translate("Start Shopping for Future Lotteries") }}
                </a>
            </div>
        `;
    } else {
        // Find the nearest upcoming lottery
        const nearestUpcoming = upcomingLotteries.reduce((nearest, current) => {
            const nearestTime = new Date(nearest.start_date).getTime();
            const currentTime = new Date(current.start_date).getTime();
            const now = new Date().getTime();
            
            // Calculate time differences
            const nearestDiff = nearestTime - now;
            const currentDiff = currentTime - now;
            
            // Only consider future lotteries
            if (currentDiff > 0 && (nearestDiff <= 0 || currentDiff < nearestDiff)) {
                return current;
            }
            return nearest;
        });
        
        const timerId = 'nextLotteryCountdown';
        
        container.innerHTML = `
            <div class="lottery-card" style="text-align: center; padding: 40px 20px;">
                <div class="upcoming-badge" style="position: relative; top: 0; left: 0; margin: 0 auto 20px; width: fit-content;">
                    <i class="las la-hourglass-half"></i>
                    {{ translate("NEXT UPCOMING LOTTERY") }}
                </div>
                
                <div class="empty-icon" style="margin: 0 auto 20px;">
                    <i class="las la-calendar-plus"></i>
                </div>
                
                <h3 style="color: #4a5568; margin-bottom: 10px;">{{ translate("No Active Lottery Right Now") }}</h3>
                <p style="color: #718096; margin-bottom: 20px; max-width: 500px; margin-left: auto; margin-right: auto;">
                    {{ translate("Please wait for the next upcoming lottery") }}
                </p>
                
                <!-- Countdown Timer -->
                <div class="countdown-timer" style="max-width: 400px; margin: 20px auto;">
                    <div class="timer-label">{{ translate("Next Lottery Starts In") }}</div>
                    <div class="timer-value" id="${timerId}">{{ translate("Loading...") }}</div>
                </div>
                
                <p style="color: #718096; margin-bottom: 30px; max-width: 500px; margin-left: auto; margin-right: auto;">
                    <strong>${nearestUpcoming.title}</strong><br>
                    {{ translate("Starts on:") }} ${formatDate(nearestUpcoming.start_date)}
                </p>
                
                <a href="{{ route('inhouse.all') }}" class="shop-button-large" style="max-width: 300px; margin: 0 auto;">
                    <i class="las la-shopping-cart"></i>
                    {{ translate("Start Shopping for Future Lotteries") }}
                </a>
            </div>
        `;

        // Start countdown timer for the next upcoming lottery
        startStartDateCountdown(nearestUpcoming.start_date, timerId);
    }
}

// Render current lottery
function renderCurrentLottery(lottery) {
    const container = document.getElementById('currentLotteryContainer');
    
    container.innerHTML = `
        <div class="lottery-card">
            <div class="current-badge">
                <i class="las la-bolt"></i>
                {{ translate("CURRENT LOTTERY") }}
            </div>
            
            <!-- Lottery Image -->
            <img src="${lottery.photo_url || 'https://images.unsplash.com/photo-1578894381163-e72c17f2d45f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'}" 
                 alt="${lottery.title}" 
                 class="lottery-image">
            
            <div class="lottery-content">
                <!-- Title and Price -->
                <div class="lottery-title-section">
                    <div>
                        <h3>${lottery.title}</h3>
                        <p class="lottery-description">${lottery.description}</p>
                    </div>
                    <div class="ticket-price">
                        <div class="label">{{ translate("Ticket Price") }}</div>
                        <div class="amount">${formatCurrency(lottery.price)}</div>
                        <div class="note">{{ translate("Per paid order") }}</div>
                    </div>
                </div>

                <!-- Draw Date -->
                <div class="draw-date-card">
                    <div class="draw-date-icon">
                        <i class="las la-calendar-alt"></i>
                    </div>
                    <div class="draw-date-info">
                        <div class="label">{{ translate("DRAW DATE & TIME") }}</div>
                        <div class="date">${formatDate(lottery.drew_date)}</div>
                    </div>
                    <div class="timer" id="currentDrawTimer"></div>
                </div>

                <!-- Prizes Section -->
                <div class="prizes-section">
                    <div class="prizes-header">
                        <div class="prizes-icon">
                            <i class="las la-trophy"></i>
                        </div>
                        <div>
                            <h2>{{ translate("Amazing Prizes To Win") }}</h2>
                            <p class="prizes-subtitle">{{ translate("Guaranteed winners in every draw!") }}</p>
                        </div>
                    </div>
                    
                    <div class="prizes-grid">
                        ${lottery.prizes.map(prize => `
                            <div class="prize-card">
                                <div class="prize-image-container">
                                    <img src="${prize.photo ? (prize.photo.startsWith('http') ? prize.photo : 'https://vexlina.com/' + prize.photo) : 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'}" 
                                         alt="${prize.name}" 
                                         class="prize-image">
                                    <div class="prize-badge">
                                        <i class="las la-gift"></i>
                                        {{ translate("Prize") }}
                                    </div>
                                </div>
                                
                                <div class="prize-content">
                                    <h3 class="prize-name">${prize.name}</h3>
                                    <p class="prize-description">${prize.description || '{{ translate("Amazing prize waiting for you!") }}'}</p>
                                    
                                    <div class="prize-meta">
                                        <div class="prize-value">${prize.prize_value}</div>
                                        <div class="winners-badge">
                                            <i class="las la-users"></i>
                                            ${prize.winner_number} {{ translate("Winner") }}${prize.winner_number > 1 ? 's' : ''}
                                        </div>
                                    </div>
                                    
                                    <div class="guaranteed-badge">
                                        <i class="las la-check-circle"></i>
                                        {{ translate("Guaranteed Prize") }}
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <!-- Shop Button -->
                <a href="{{ route('inhouse.all') }}" class="shop-button-large">
                    <i class="las la-shopping-cart"></i>
                    {{ translate("Start Shopping to Get Ticket") }}
                </a>
                
                <!-- Note -->
                <p style="text-align: center; color: #718096; font-size: 0.875rem; margin-top: 20px;">
                    <i class="las la-info-circle"></i> {{ translate("Remember: One lottery ticket per paid order. Cannot buy tickets directly.") }}
                </p>
            </div>
        </div>
    `;

    // Start countdown timer for current lottery draw
    startDrawCountdown(lottery.drew_date, 'currentDrawTimer');
}

// Render all upcoming lotteries
function renderAllUpcomingLotteries(upcomingLotteries) {
    const container = document.getElementById('upcomingContent');
    
    if (upcomingLotteries.length === 0) return;
    
    container.innerHTML = upcomingLotteries.map((lottery, index) => {
        // Check if the start date has already passed
        const now = new Date().getTime();
        const startTime = new Date(lottery.start_date).getTime();
        const isRunning = startTime <= now;
        
        const timerId = `upcomingStartTimer_${index}`;
        const drawTimerId = `upcomingDrawTimer_${index}`;
        
        return `
            <div class="lottery-card" style="animation: fadeIn 0.5s ease ${index * 0.1}s both;">
                <div class="upcoming-badge">
                    <i class="las la-calendar-plus"></i>
                    {{ translate("UPCOMING LOTTERY") }}
                </div>
                
                <!-- Lottery Image -->
                <img src="${lottery.photo_url || 'https://images.unsplash.com/photo-1578894381163-e72c17f2d45f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'}" 
                     alt="${lottery.title}" 
                     class="lottery-image">
                
                <div class="lottery-content">
                    <!-- Title and Price -->
                    <div class="lottery-title-section">
                        <div>
                            <h3>${lottery.title}</h3>
                            <p class="lottery-description">${lottery.description}</p>
                        </div>
                        <div class="ticket-price">
                            <div class="label">{{ translate("Ticket Price") }}</div>
                            <div class="amount">${formatCurrency(lottery.price)}</div>
                            <div class="note">{{ translate("Per paid order") }}</div>
                        </div>
                    </div>

                    <!-- Start Date (Countdown to start) -->
                    <div class="start-date-card">
                        <div class="start-date-icon">
                            <i class="las la-hourglass-start"></i>
                        </div>
                        <div class="start-date-info">
                            <div class="label">${isRunning ? '{{ translate("LOTTERY IS RUNNING") }}' : '{{ translate("STARTS ON") }}'}</div>
                            <div class="date">${formatDate(lottery.start_date)}</div>
                        </div>
                        <div class="start-timer" id="${timerId}"></div>
                    </div>

                    <!-- Draw Date -->
                    <div class="draw-date-card">
                        <div class="draw-date-icon">
                            <i class="las la-calendar-alt"></i>
                        </div>
                        <div class="draw-date-info">
                            <div class="label">{{ translate("DRAW DATE & TIME") }}</div>
                            <div class="date">${formatDate(lottery.drew_date)}</div>
                        </div>
                        <div class="timer" id="${drawTimerId}"></div>
                    </div>

                    <!-- Prizes Section -->
                    <div class="prizes-section">
                        <div class="prizes-header">
                            <div class="prizes-icon">
                                <i class="las la-trophy"></i>
                            </div>
                            <div>
                                <h2>{{ translate("Amazing Prizes To Win") }}</h2>
                                <p class="prizes-subtitle">{{ translate("Guaranteed winners in every draw!") }}</p>
                            </div>
                        </div>
                        
                        <div class="prizes-grid">
                            ${lottery.prizes.map(prize => `
                                <div class="prize-card">
                                    <div class="prize-image-container">
                                        <img src="${prize.photo ? (prize.photo.startsWith('http') ? prize.photo : 'https://vexlina.com/' + prize.photo) : 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'}" 
                                             alt="${prize.name}" 
                                             class="prize-image">
                                        <div class="prize-badge">
                                            <i class="las la-gift"></i>
                                            {{ translate("Prize") }}
                                        </div>
                                    </div>
                                    
                                    <div class="prize-content">
                                        <h3 class="prize-name">${prize.name}</h3>
                                        <p class="prize-description">${prize.description || '{{ translate("Amazing prize waiting for you!") }}'}</p>
                                        
                                        <div class="prize-meta">
                                            <div class="prize-value">${prize.prize_value}</div>
                                            <div class="winners-badge">
                                                <i class="las la-users"></i>
                                                ${prize.winner_number} {{ translate("Winner") }}${prize.winner_number > 1 ? 's' : ''}
                                            </div>
                                        </div>
                                        
                                        <div class="guaranteed-badge">
                                            <i class="las la-check-circle"></i>
                                            {{ translate("Guaranteed Prize") }}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <!-- Info Message -->
                    <div class="countdown-timer" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <div class="timer-label">${isRunning ? '{{ translate("Lottery is now running!") }}' : '{{ translate("Get Ready!") }}'}</div>
                        <div class="timer-value">${isRunning ? '{{ translate("Start shopping to get tickets!") }}' : '{{ translate("Coming soon...") }}'}</div>
                    </div>
                    
                    <!-- Note -->
                    <p style="text-align: center; color: #718096; font-size: 0.875rem; margin-top: 20px;">
                        <i class="las la-info-circle"></i>
                        ${isRunning 
                            ? '{{ translate("This lottery is now running! Start shopping to get tickets.") }}' 
                            : '{{ translate("This lottery will start soon. Start shopping now to get ready!") }}'}
                    </p>
                </div>
            </div>
        `;
    }).join('');

    // Start countdown timers for each upcoming lottery
    upcomingLotteries.forEach((lottery, index) => {
        const timerId = `upcomingStartTimer_${index}`;
        const drawTimerId = `upcomingDrawTimer_${index}`;
        
        startStartDateCountdown(lottery.start_date, timerId);
        startDrawCountdown(lottery.drew_date, drawTimerId);
    });
}

// Start shopping function
function startShopping() {
    window.location.href = "{{ route('inhouse.all') }}";
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadLotteries();
});
</script>
@endsection