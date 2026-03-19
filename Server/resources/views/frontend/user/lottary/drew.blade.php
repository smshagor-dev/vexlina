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
<div style="padding: 30px 0; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 100vh;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
        <!-- Page Header -->
        <div style="text-align: center; margin-bottom: 50px;">
            <h1 style="color: #2c3e50; font-weight: 800; margin-bottom: 15px; font-size: 2.8rem; letter-spacing: -0.5px;">{{ translate('Drawn Lotteries') }}</h1>
            <p style="color: #6c757d; font-size: 1.2rem; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                {{ translate('Discover all previously drawn lotteries and celebrate with our lucky winners') }}
            </p>
        </div>

        @if($lottaries->count() > 0)
        <!-- Lottery Cards Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; margin-bottom: 60px;">
            @foreach($lottaries as $lottery)
            <div class="lottery-card" 
                 style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); border: none; position: relative;">
                <!-- Decorative Accent -->
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #e62e04 0%, #ff5a2e 100%);"></div>
                
                <div style="padding: 30px;">
                    <!-- Lottery Header -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                            <span style="background: rgba(230, 46, 4, 0.1); color: #e62e04; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM5 6v2h14V6H5zm2 4h10v2H7zm0 4h7v2H7z"/>
                                </svg>
                                {{ \Carbon\Carbon::parse($lottery->drew_date)->format('M d, Y') }}
                            </span>
                        </div>
                        
                        <h3 style="color: #2c3e50; margin: 0 0 10px 0; font-size: 1.5rem; font-weight: 700; line-height: 1.3;">
                            {{ $lottery->title }}
                        </h3>
                        
                        @if($lottery->description)
                        <p style="color: #6c757d; margin: 0; font-size: 0.95rem; line-height: 1.5;">
                            {{ Str::limit($lottery->description, 100) }}
                        </p>
                        @endif
                    </div>

                    <!-- Lottery Photo (if available) -->
                    @if($lottery->photo)
                    <div style="margin-bottom: 25px; border-radius: 12px; overflow: hidden;">
                        <img src="{{ $lottery->photo ? url($lottery->photo) : url('/default-lottery.png') }}" 
                             alt="{{ $lottery->title }}"
                             style="width: 100%; height: 180px; object-fit: cover; transition: transform 0.4s ease;"
                             onmouseover="this.style.transform='scale(1.05)'"
                             onmouseout="this.style.transform='scale(1)'">
                    </div>
                    @endif

                    <!-- Action Button -->
                    <button class="view-winners-btn" 
                            data-lottery-id="{{ $lottery->id }}"
                            data-lottery-title="{{ $lottery->title }}"
                            style="background: linear-gradient(135deg, #e62e04 0%, #ff5a2e 100%); color: white; border: none; width: 100%; padding: 14px 0; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px; letter-spacing: 0.5px; text-transform: uppercase;">
                        <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                        {{ translate('View Winners') }}
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Empty State -->
        <div style="text-align: center; padding: 80px 20px; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
            <div style="width: 120px; height: 120px; background: linear-gradient(135deg, rgba(230, 46, 4, 0.1) 0%, rgba(255, 90, 46, 0.1) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                <svg style="width: 60px; height: 60px; color: #e62e04;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-4.86 8.86l-3 3.87L9 13.14 6 17h12l-3.86-5.14z"/>
                </svg>
            </div>
            <h2 style="color: #2c3e50; margin-bottom: 15px; font-size: 1.8rem; font-weight: 700;">{{ translate('No Drawn Lotteries Yet') }}</h2>
            <p style="color: #6c757d; font-size: 1.1rem; max-width: 500px; margin: 0 auto; line-height: 1.6;">
                {{ translate('Stay tuned! The first lottery drawing will be announced soon. Check back later for exciting results.') }}
            </p>
        </div>
        @endif

        <!-- Winners Modal -->
        <div id="winnersModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center; padding: 0; backdrop-filter: blur(10px);">
            <div id="modalContent" style="background: white; border-radius: 0; width: 100%; height: 100%; max-width: none; max-height: none; overflow: hidden; display: flex; flex-direction: column; box-shadow: none; animation: modalSlideIn 0.3s ease;">
                <!-- Modal Header -->
                <div style="padding: 20px; background: linear-gradient(135deg, #e62e04 0%, #ff5a2e 100%); color: white; position: relative; flex-shrink: 0;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #ffd700 0%, #ffed4e 100%);"></div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; gap: 10px;">
                        <div style="flex: 1; min-width: 0;">
                            <h2 style="margin: 0; font-size: 1.3rem; font-weight: 700; display: flex; align-items: center; gap: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <svg style="width: 22px; height: 22px; color: #ffd700; flex-shrink: 0;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                                <span id="modalTitle" style="overflow: hidden; text-overflow: ellipsis;">{{ translate('Lottery Winners') }}</span>
                            </h2>
                            <p id="modalSubtitle" style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ translate('Loading winners...') }}</p>
                        </div>
                        <button onclick="closeWinnersModal()" style="background: rgba(255,255,255,0.15); border: none; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; flex-shrink: 0; min-width: 44px;">
                            <svg style="width: 22px; height: 22px; color: white;" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Search Bar -->
                    <div id="searchContainer" style="display: none; margin-top: 12px;">
                        <div style="position: relative;">
                            <input type="text" 
                                   id="winnersSearch" 
                                   placeholder="{{ translate('Search winners...') }}"
                                   style="width: 100%; padding: 14px 50px 14px 16px; border-radius: 12px; border: none; background: rgba(255,255,255,0.95); color: #333; font-size: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.2); font-size: 16px; -webkit-appearance: none;">
                            <div style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #e62e04; display: flex; align-items: center; gap: 5px;">
                                <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5z"/>
                                </svg>
                                <span id="searchResultsCount" style="font-size: 0.85rem; font-weight: 600; background: #e62e04; color: white; padding: 2px 8px; border-radius: 10px; min-width: 20px; text-align: center; display: none;"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div style="flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                    <!-- Loading State -->
                    <div id="modalLoading" style="text-align: center; padding: 60px 20px; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <div style="width: 60px; height: 60px; border: 3px solid #f3f3f3; border-top: 3px solid #e62e04; border-radius: 50%; margin: 0 auto 20px; animation: spin 1s linear infinite;"></div>
                        <h3 style="color: #2c3e50; margin-bottom: 10px; font-weight: 600;">{{ translate('Loading Winners') }}</h3>
                        <p style="color: #6c757d;">{{ translate('Please wait while we fetch the winners...') }}</p>
                    </div>

                    <!-- Winners Content -->
                    <div id="winnersContent" style="display: none;">
                        <!-- Summary Card -->
                        <div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                                <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                                    <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #e62e04 0%, #ff5a2e 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg style="width: 22px; height: 22px; color: white;" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                                        </svg>
                                    </div>
                                    <div style="min-width: 0;">
                                        <h3 style="margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 600; color: #2c3e50; overflow: hidden; text-overflow: ellipsis;">{{ translate('Winners Summary') }}</h3>
                                        <p id="totalWinners" style="margin: 0; font-size: 0.9rem; color: #6c757d; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            {{ translate('Total Winners:') }}
                                            <span style="background: #e62e04; color: white; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.95rem;">0</span>
                                        </p>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px; flex-shrink: 0;">
                                    <button onclick="shareWinners()" style="background: #e62e04; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease; font-size: 0.9rem; white-space: nowrap;">
                                        <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/>
                                        </svg>
                                        {{ translate('Share') }}
                                    </button>
                                    <button onclick="exportWinners()" style="background: #28a745; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease; font-size: 0.9rem; white-space: nowrap;">
                                        <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                        </svg>
                                        {{ translate('Export') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Winners Grid -->
                        <div id="winnersGrid" style="padding: 20px; display: grid; grid-template-columns: 1fr; gap: 20px;">
                            <!-- Winners will be populated here -->
                        </div>

                        <!-- No Winners Message -->
                        <div id="noWinnersMessage" style="text-align: center; padding: 60px 20px; display: none; height: 60vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, rgba(230, 46, 4, 0.1) 0%, rgba(255, 90, 46, 0.1) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px;">
                                <svg style="width: 50px; height: 50px; color: #e62e04;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                                </svg>
                            </div>
                            <h3 style="color: #2c3e50; margin-bottom: 15px; font-size: 1.5rem; font-weight: 700;">{{ translate('No Winners Found') }}</h3>
                            <p style="color: #6c757d; max-width: 400px; margin: 0 auto 20px; line-height: 1.6;">
                                {{ translate('No winners have been announced for this lottery yet.') }}
                            </p>
                            <button onclick="closeWinnersModal()" style="background: #e62e04; color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                {{ translate('Close') }}
                            </button>
                        </div>

                        <!-- No Search Results Message -->
                        <div id="noSearchResults" style="text-align: center; padding: 60px 20px; display: none; height: 60vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, rgba(108, 117, 125, 0.1) 0%, rgba(173, 181, 189, 0.1) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px;">
                                <svg style="width: 50px; height: 50px; color: #6c757d;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5z"/>
                                    <path d="M12 10h-2v2H9v-2H7V9h2V7h1v2h2v1z"/>
                                </svg>
                            </div>
                            <h3 style="color: #2c3e50; margin-bottom: 15px; font-size: 1.5rem; font-weight: 700;">{{ translate('No Results Found') }}</h3>
                            <p style="color: #6c757d; max-width: 400px; margin: 0 auto 20px; line-height: 1.6;">
                                {{ translate('No winners match your search.') }}
                            </p>
                            <button onclick="clearSearch()" style="background: #e62e04; color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                {{ translate('Clear Search') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Mobile-First Responsive Design */
    @media (max-width: 767px) {
        div[style*="grid-template-columns: repeat(auto-fill, minmax(350px"] {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
            padding: 0 10px !important;
        }
        
        .lottery-card {
            padding: 20px !important;
            margin: 0 5px !important;
        }
        
        h1 {
            font-size: 2rem !important;
            padding: 0 10px !important;
        }
        
        p[style*="color: #6c757d; font-size: 1.2rem"] {
            font-size: 1rem !important;
            padding: 0 10px !important;
        }
        
        /* Full-screen modal for mobile */
        #winnersModal {
            padding: 0 !important;
        }
        
        #modalContent {
            border-radius: 0 !important;
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden !important;
            position: fixed !important;
            width: 100% !important;
            height: 100% !important;
        }
        
        /* Mobile-optimized winner cards */
        .winner-card {
            padding: 16px !important;
            margin: 0 5px !important;
        }
        
        .winner-avatar {
            width: 50px !important;
            height: 50px !important;
            font-size: 1.3rem !important;
        }
        
        /* Better touch targets */
        button {
            min-height: 44px !important;
        }
        
        /* Mobile-friendly search */
        #winnersSearch {
            font-size: 16px !important; /* Prevents iOS zoom */
            padding: 16px 50px 16px 16px !important;
            height: 52px !important;
        }
        
        /* Mobile-optimized grid */
        #winnersGrid {
            padding: 16px !important;
            gap: 16px !important;
        }
        
        /* Summary card mobile optimization */
        div[style*="padding: 20px; background: #f8f9fa"] {
            padding: 16px !important;
        }
        
        /* Modal header mobile optimization */
        div[style*="padding: 20px; background: linear-gradient"] {
            padding: 16px !important;
        }
    }
    
    /* Tablet and Desktop */
    @media (min-width: 768px) {
        #winnersModal {
            padding: 20px !important;
            background: rgba(0,0,0,0.7) !important;
        }
        
        #modalContent {
            border-radius: 20px !important;
            width: 90% !important;
            max-width: 1200px !important;
            height: 90vh !important;
            max-height: 90vh !important;
            animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        #winnersGrid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
        }
    }
    
    @media (min-width: 1024px) {
        #winnersGrid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
        }
    }
    
    /* Extra small devices */
    @media (max-width: 374px) {
        .lottery-card {
            padding: 16px !important;
        }
        
        h3[style*="color: #2c3e50; margin: 0 0 10px 0"] {
            font-size: 1.3rem !important;
        }
        
        .view-winners-btn {
            padding: 12px 0 !important;
            font-size: 0.9rem !important;
        }
    }
    
    /* Animations */
    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Hover Effects (Desktop only) */
    @media (hover: hover) and (pointer: fine) {
        .lottery-card:hover {
            transform: translateY(-10px) !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
        }
        
        .view-winners-btn:hover {
            background: linear-gradient(135deg, #d42900 0%, #ff4a1e 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 20px rgba(230, 46, 4, 0.3) !important;
        }
        
        button[onclick="closeWinnersModal()"]:hover {
            background: rgba(255,255,255,0.2) !important;
            transform: rotate(90deg) !important;
        }
        
        .winner-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
        }
    }
    
    /* Touch-friendly interactions for mobile */
    @media (max-width: 767px) {
        .view-winners-btn:active {
            transform: scale(0.98) !important;
            background: linear-gradient(135deg, #d42900 0%, #ff4a1e 100%) !important;
        }
        
        .winner-card:active {
            transform: scale(0.98) !important;
        }
        
        button:active {
            transform: scale(0.98) !important;
            opacity: 0.9 !important;
        }
    }
    
    /* Scrollbar Styling */
    #modalContent > div:nth-child(2)::-webkit-scrollbar {
        width: 6px;
    }
    
    #modalContent > div:nth-child(2)::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    #modalContent > div:nth-child(2)::-webkit-scrollbar-thumb {
        background: #e62e04;
        border-radius: 3px;
    }
    
    /* Search Highlight */
    .highlight {
        background-color: #fff3cd;
        color: #856404;
        padding: 2px 4px;
        border-radius: 3px;
        font-weight: bold;
    }
    
    /* iOS Safe Area Insets */
    @supports (padding: max(0px)) {
        #modalContent {
            padding-left: max(0px, env(safe-area-inset-left));
            padding-right: max(0px, env(safe-area-inset-right));
            padding-bottom: max(0px, env(safe-area-inset-bottom));
        }
        
        #modalContent > div:first-child {
            padding-top: max(20px, env(safe-area-inset-top));
        }
    }
</style>

<script>
// Global variables
let currentLotteryId = null;
let currentLotteryTitle = null;
let allWinners = [];
let currentDisplayedWinners = [];
let isMobile = window.innerWidth <= 767;

// Check and update mobile state on resize
window.addEventListener('resize', () => {
    isMobile = window.innerWidth <= 767;
});

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to all "View Winners" buttons
    document.querySelectorAll('.view-winners-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentLotteryId = this.getAttribute('data-lottery-id');
            currentLotteryTitle = this.getAttribute('data-lottery-title');
            showWinnersModal();
        });
    });
    
    // Add click event to close modal when clicking outside (desktop only)
    document.getElementById('winnersModal').addEventListener('click', function(e) {
        if (!isMobile && e.target === this) {
            closeWinnersModal();
        }
    });
    
    // Add keyboard event to close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('winnersModal').style.display === 'flex') {
            closeWinnersModal();
        }
    });
    
    // Add search input event listener
    const searchInput = document.getElementById('winnersSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            searchWinners(e.target.value);
        });
        
        // Prevent form submission on enter
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }
    
    // Add keyboard shortcut for search (Desktop only)
    document.addEventListener('keydown', function(e) {
        if (!isMobile && (e.ctrlKey || e.metaKey) && e.key === 'f' && document.getElementById('winnersModal').style.display === 'flex') {
            e.preventDefault();
            searchInput?.focus();
        }
    });
    
    // Handle iOS keyboard
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            setTimeout(() => {
                this.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        });
    }
});

// Show Winners Modal
function showWinnersModal() {
    const modal = document.getElementById('winnersModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    // Update modal title and subtitle
    modalTitle.textContent = currentLotteryTitle;
    modalSubtitle.textContent = '{{ translate("Loading winners...") }}';
    
    // Show modal with animation
    modal.style.display = 'flex';
    
    // Prevent body scroll on mobile
    document.body.classList.add('modal-open');
    
    // Reset modal content
    document.getElementById('modalLoading').style.display = 'flex';
    document.getElementById('winnersContent').style.display = 'none';
    document.getElementById('noWinnersMessage').style.display = 'none';
    document.getElementById('noSearchResults').style.display = 'none';
    document.getElementById('searchContainer').style.display = 'none';
    document.getElementById('winnersSearch').value = '';
    document.getElementById('winnersGrid').innerHTML = '';
    
    // On mobile, ensure modal takes full viewport
    if (isMobile) {
        document.getElementById('modalContent').style.height = '100vh';
        document.getElementById('modalContent').style.maxHeight = '100vh';
    }
    
    // Fetch winners data via AJAX
    setTimeout(() => {
        fetchWinnersData();
    }, 300);
}

// Close Winners Modal
function closeWinnersModal() {
    const modal = document.getElementById('winnersModal');
    const modalContent = document.getElementById('modalContent');
    
    // Add closing animation
    modalContent.style.animation = isMobile ? 'slideUp 0.3s ease reverse' : 'modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) reverse';
    
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        modalContent.style.animation = isMobile ? 'slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1)' : 'modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        // Reset search
        allWinners = [];
        currentDisplayedWinners = [];
    }, 200);
}

// Fetch Winners Data via AJAX
function fetchWinnersData() {
    const url = `{{ route('frontend.lottary.winners', ['id' => '__ID__']) }}`.replace('__ID__', currentLotteryId);
    
    document.getElementById('modalLoading').style.display = 'flex';
    document.getElementById('winnersContent').style.display = 'none';
    document.getElementById('noWinnersMessage').style.display = 'none';
    document.getElementById('noSearchResults').style.display = 'none';
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('modalLoading').style.display = 'none';
            
            if (data.success && data.data && data.data.length > 0) {
                allWinners = data.data;
                currentDisplayedWinners = [...allWinners];
                
                // Update modal subtitle
                document.getElementById('modalSubtitle').textContent = `${data.total_winners || data.data.length} {{ translate("Lucky Winners") }}`;
                
                // Update total winners count
                document.getElementById('totalWinners').innerHTML = 
                    `{{ translate("Total Winners") }}: <span style="background: #e62e04; color: white; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.95rem;">${data.total_winners || data.data.length}</span>`;
                
                // Show search container
                document.getElementById('searchContainer').style.display = 'block';
                
                // Populate winners grid
                populateWinnersGrid(allWinners);
                
                // Show content with animation
                document.getElementById('winnersContent').style.display = 'block';
                
                // On mobile, focus search after short delay
                if (isMobile && allWinners.length > 5) {
                    setTimeout(() => {
                        document.getElementById('winnersSearch')?.focus();
                    }, 500);
                }
            } else {
                document.getElementById('noWinnersMessage').style.display = 'flex';
                document.getElementById('winnersContent').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching winners:', error);
            document.getElementById('modalLoading').style.display = 'none';
            
            document.getElementById('noWinnersMessage').innerHTML = `
                <div style="width: 100px; height: 100px; background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.2) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px;">
                    <svg style="width: 50px; height: 50px; color: #dc3545;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </div>
                <h3 style="color: #dc3545; margin-bottom: 15px; font-size: 1.5rem; font-weight: 700;">{{ translate("Error Loading Winners") }}</h3>
                <p style="color: #6c757d; max-width: 400px; margin: 0 auto 20px; line-height: 1.6;">
                    {{ translate("Unable to load winners at the moment.") }}
                </p>
                <div style="display: flex; gap: 10px;">
                    <button onclick="fetchWinnersData()" style="background: #e62e04; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease;">
                        <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                        </svg>
                        {{ translate("Retry") }}
                    </button>
                    <button onclick="closeWinnersModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                        {{ translate("Close") }}
                    </button>
                </div>
            `;
            document.getElementById('noWinnersMessage').style.display = 'flex';
            document.getElementById('winnersContent').style.display = 'block';
        });
}

// Search Winners
function searchWinners(searchTerm) {
    const searchResultsCount = document.getElementById('searchResultsCount');
    
    if (!searchTerm.trim()) {
        // If search is empty, show all winners
        currentDisplayedWinners = [...allWinners];
        populateWinnersGrid(currentDisplayedWinners);
        searchResultsCount.style.display = 'none';
        document.getElementById('noSearchResults').style.display = 'none';
        return;
    }
    
    const searchLower = searchTerm.toLowerCase().trim();
    
    // Filter winners based on search criteria
    const filteredWinners = allWinners.filter(winner => {
        const winnerName = (winner.user_info?.name || '').toLowerCase();
        const location = (winner.user_info?.location || '').toLowerCase();
        const ticketNumber = (winner.ticket_number || '').toLowerCase();
        const prizeValue = (winner.prize?.prize_value || '').toLowerCase();
        
        return winnerName.includes(searchLower) ||
               location.includes(searchLower) ||
               ticketNumber.includes(searchLower) ||
               prizeValue.includes(searchLower);
    });
    
    currentDisplayedWinners = filteredWinners;
    
    // Update search results count
    searchResultsCount.textContent = filteredWinners.length;
    searchResultsCount.style.display = filteredWinners.length > 0 ? 'inline-block' : 'none';
    
    if (filteredWinners.length === 0) {
        // Show no results message
        document.getElementById('winnersGrid').innerHTML = '';
        document.getElementById('noWinnersMessage').style.display = 'none';
        document.getElementById('noSearchResults').style.display = 'flex';
    } else {
        // Populate filtered winners with highlighting
        document.getElementById('noSearchResults').style.display = 'none';
        populateWinnersGrid(filteredWinners, searchLower);
        
        // Scroll to top of results on mobile
        if (isMobile && filteredWinners.length > 0) {
            document.getElementById('winnersGrid').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

// Clear Search
function clearSearch() {
    document.getElementById('winnersSearch').value = '';
    searchWinners('');
    document.getElementById('winnersSearch').focus();
}

// Populate Winners Grid with optional search highlighting
function populateWinnersGrid(winners, searchTerm = '') {
    const winnersGrid = document.getElementById('winnersGrid');
    winnersGrid.innerHTML = '';
    
    if (winners.length === 0) return;
    
    const prizeColors = {
        'cash': '#28a745',
        'iphone': '#007bff',
        'galaxy': '#6f42c1',
        'default': '#e62e04'
    };
    
    winners.forEach((winner, index) => {
        const winnerName = winner.user_info?.name || '{{ translate("Anonymous") }}';
        const location = winner.user_info?.location || 'N/A';
        const ticketNumber = winner.ticket_number || 'N/A';
        const prizeValue = winner.prize?.prize_value || 'N/A';
        const prizePhoto = winner.prize?.photo || '{{ asset("assets/img/default-prize.jpg") }}';
        
        let prizeColor = prizeColors.default;
        if (prizeValue.toLowerCase().includes('cash')) prizeColor = prizeColors.cash;
        else if (prizeValue.toLowerCase().includes('iphone')) prizeColor = prizeColors.iphone;
        else if (prizeValue.toLowerCase().includes('galaxy')) prizeColor = prizeColors.galaxy;
        
        // Highlight search terms
        const highlightText = (text, term) => {
            if (!term || !text) return text;
            const regex = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return text.replace(regex, '<span class="highlight">$1</span>');
        };
        
        const highlightedName = searchTerm ? highlightText(winnerName, searchTerm) : winnerName;
        const highlightedLocation = searchTerm ? highlightText(location, searchTerm) : location;
        const highlightedTicket = searchTerm ? highlightText(ticketNumber, searchTerm) : ticketNumber;
        const highlightedPrize = searchTerm ? highlightText(prizeValue, searchTerm) : prizeValue;
        
        const card = document.createElement('div');
        card.className = 'winner-card';
        card.style.cssText = `
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            animation: fadeInUp 0.3s ease ${Math.min(index * 0.05, 0.5)}s both;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        `;
        
        card.innerHTML = `
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: ${prizeColor};"></div>
            
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <div class="winner-avatar" style="width: 50px; height: 50px; background: linear-gradient(135deg, ${prizeColor} 0%, ${prizeColor}80 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.4rem; flex-shrink: 0;">
                    ${winnerName.charAt(0).toUpperCase()}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <h4 style="margin: 0 0 5px 0; color: #2c3e50; font-weight: 700; font-size: 1.1rem; overflow: hidden; text-overflow: ellipsis;">${highlightedName}</h4>
                    <div style="display: flex; align-items: center; gap: 6px; color: #6c757d; font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis;">
                        <svg style="width: 14px; height: 14px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                        <span style="overflow: hidden; text-overflow: ellipsis;">${highlightedLocation}</span>
                    </div>
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <div style="color: #6c757d; font-size: 0.85rem; margin-bottom: 5px;">{{ translate("Ticket Number") }}</div>
                        <code style="background: #f8f9fa; padding: 8px 12px; border-radius: 8px; font-size: 0.9rem; font-weight: 700; color: ${prizeColor}; letter-spacing: 1px; display: inline-block; word-break: break-all;">
                            ${highlightedTicket}
                        </code>
                    </div>
                    <div>
                        <div style="color: #6c757d; font-size: 0.85rem; margin-bottom: 5px;">{{ translate("Prize") }}</div>
                        <div style="font-weight: 700; color: ${prizeColor}; font-size: 1rem; overflow: hidden; text-overflow: ellipsis;">${highlightedPrize}</div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center;">
                <img src="${prizePhoto}" 
                     alt="${prizeValue}"
                     style="width: 100%; height: 120px; object-fit: contain; border-radius: 12px; background: #f8f9fa; padding: 10px; transition: transform 0.3s ease;"
                     onerror="this.src='{{ asset("assets/img/default-prize.jpg") }}'"
                     ${isMobile ? '' : 'onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'"'}>
            </div>
            
            <div style="position: absolute; top: 10px; right: 10px; background: ${prizeColor}; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; gap: 4px;">
                                <svg style="width: 10px; height: 10px;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                                #${index + 1}
                            </div>
                        `;
        
        // Touch interactions for mobile
        if (isMobile) {
            card.addEventListener('touchstart', () => {
                card.style.transform = 'scale(0.98)';
            });
            
            card.addEventListener('touchend', () => {
                card.style.transform = 'scale(1)';
            });
        } else {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 15px 35px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
            });
        }
        
        winnersGrid.appendChild(card);
    });
}

// Export Winners
function exportWinners() {
    if (currentDisplayedWinners.length === 0) {
        alert('{{ translate("No winners to export!") }}');
        return;
    }
    
    // Create CSV content
    let csvContent = "{{ translate('Name,Location,Ticket Number,Prize,Date') }}\n";
    
    currentDisplayedWinners.forEach(winner => {
        const name = winner.user_info?.name || '{{ translate("Anonymous") }}';
        const location = winner.user_info?.location || 'N/A';
        const ticket = winner.ticket_number || 'N/A';
        const prize = winner.prize?.prize_value || 'N/A';
        const date = winner.created_at || new Date().toISOString().split('T')[0];
        
        // Escape commas and quotes
        const escapeCSV = (str) => `"${str.replace(/"/g, '""')}"`;
        
        csvContent += `${escapeCSV(name)},${escapeCSV(location)},${escapeCSV(ticket)},${escapeCSV(prize)},${date}\n`;
    });
    
    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `${currentLotteryTitle.replace(/[^a-z0-9]/gi, '_')}_winners_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Show success message
    const exportBtn = document.querySelector('button[onclick="exportWinners()"]');
    const originalHTML = exportBtn.innerHTML;
    
    exportBtn.innerHTML = `
        <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
        </svg>
        {{ translate('Exported!') }}
    `;
    exportBtn.style.background = '#28a745';
    
    setTimeout(() => {
        exportBtn.innerHTML = originalHTML;
        exportBtn.style.background = '#28a745';
    }, 2000);
}

// Share functionality
function shareWinners() {
    const shareData = {
        title: `${currentLotteryTitle} - {{ translate('Winners List') }}`,
        text: `{{ translate('Check out the winners of') }} ${currentLotteryTitle}! ${currentDisplayedWinners.length} {{ translate('lucky winners.') }}`,
        url: window.location.href
    };
    
    if (navigator.share && isMobile) {
        navigator.share(shareData)
            .then(() => console.log('{{ translate('Successful share') }}'))
            .catch(error => console.log('{{ translate('Error sharing:') }}', error));
    } else {
        navigator.clipboard.writeText(window.location.href)
            .then(() => {
                const shareBtn = document.querySelector('button[onclick="shareWinners()"]');
                const originalHTML = shareBtn.innerHTML;
                
                shareBtn.innerHTML = `
                    <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                    {{ translate('Copied!') }}
                `;
                shareBtn.style.background = '#ffc107';
                shareBtn.style.color = '#212529';
                
                setTimeout(() => {
                    shareBtn.innerHTML = originalHTML;
                    shareBtn.style.background = '#e62e04';
                    shareBtn.style.color = 'white';
                }, 2000);
            })
            .catch(err => {
                console.error('{{ translate('Failed to copy:') }} ', err);
                alert('{{ translate('Link copied to clipboard!') }}');
            });
    }
}

// Handle back button on mobile
window.addEventListener('popstate', function() {
    if (document.getElementById('winnersModal').style.display === 'flex') {
        closeWinnersModal();
    }
});
</script>
@endsection