@extends('frontend.layouts.app')

@section('content')
    <style>
        .home-slider {
            max-width: 100% !important;
        }
        .home-banner-area .aiz-category-menu .sub-cat-menu {
            width: calc(100% - 290px);
            left: calc(280px);
        }
        /* #auction_products .slick-slider .slick-list .slick-slide, */
        #section_home_categories .slick-slider .slick-list .slick-slide {
            margin-bottom: -4px;
        }
        .home-category-banner .home-category-name{
            bottom: -50px;
        }
        @media (min-width: 992px){
            .todays_deal{
                width: 230px;
            }
            .todays_deal .c-scrollbar-light{
               scrollbar-width: auto !important;
               padding-right: 5px !important;
            }
        }
        @media (max-width: 991px){
            .home-banner-area .container{
                min-width: 0;
                padding-left: 15px !important;
                padding-right: 15px!important;
            }
        }
        @media (max-width: 767px){
            #flash_deal .flash-deals-baner{
                height: 203px !important;
            }
        }
        
        #products-section .border {
        border-color: #e5e5e5 !important;
        }
        #products-section .shadow-sm {
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
        }
        #view-more-btn {
            transition: all 0.3s ease;
        }
        #view-more-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        .section-title {
            position: relative;
            padding-bottom: 10px;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            width: 40px;
            height: 3px;
            background: #3498db;
            border-radius: 3px;
        }
        
        /* Mobile Quick Navigation */
        .mobile-quick-nav {
            background: white;
            border-radius: 16px;
            padding: 20px 15px;
            margin: 15px;
            box-shadow: 0 4px 20px rgba(230, 46, 4, 0.1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(230, 46, 4, 0.1);
        }
        
        .quick-nav-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 10px;
        }
        
        .quick-nav-scroll::-webkit-scrollbar {
            display: none;
        }
        
        .quick-nav-grid {
            display: flex;
            gap: 20px;
            padding: 0 5px;
            min-width: max-content;
        }
        
        .quick-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            transition: transform 0.3s ease;
            min-width: 70px;
            flex-shrink: 0;
        }
        
        .quick-nav-item:hover {
            transform: translateY(-5px);
        }
        
        .quick-nav-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #e62e04 0%, #ff9900 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            box-shadow: 0 4px 15px rgba(230, 46, 4, 0.3);
            transition: all 0.3s ease;
        }
        
        .quick-nav-icon.special {
            background: linear-gradient(135deg, #e62e04 0%, #ff6600 100%);
            box-shadow: 0 4px 15px rgba(230, 46, 4, 0.4);
        }
        
        /* Color variations using your site's color palette */
        .quick-nav-icon.blue {
            background: linear-gradient(135deg, #e62e04 0%, #ff7733 100%);
            box-shadow: 0 4px 15px rgba(230, 46, 4, 0.3);
        }
        
        .quick-nav-icon.green {
            background: linear-gradient(135deg, #e62e04 0%, #ff5500 100%);
            box-shadow: 0 4px 15px rgba(230, 46, 4, 0.3);
        }
        
        .quick-nav-icon.orange {
            background: linear-gradient(135deg, #e62e04 0%, #ff7700 100%);
            box-shadow: 0 4px 15px rgba(230, 46, 4, 0.3);
        }
        
        .quick-nav-icon.purple {
            background: linear-gradient(135deg, #e62e04 0%, #ff4400 100%);
            box-shadow: 0 4px 15px rgba(230, 46, 4, 0.3);
        }
        
        .quick-nav-icon.red {
            background: linear-gradient(135deg, #e62e04 0%, #ff3300 100%);
            box-shadow: 0 4px 15px rgba(230, 46, 4, 0.3);
        }
        
        .quick-nav-label {
            font-size: 12px;
            font-weight: 600;
            color: #333;
            line-height: 1.3;
            margin-top: 5px;
            text-align: center;
            white-space: nowrap;
        }
        
        /* Scroll indicators */
        .mobile-quick-nav::before,
        .mobile-quick-nav::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 20px;
            pointer-events: none;
            z-index: 1;
        }
        
        .mobile-quick-nav::before {
            left: 0;
            background: linear-gradient(to right, white, transparent);
        }
        
        .mobile-quick-nav::after {
            right: 0;
            background: linear-gradient(to left, white, transparent);
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .quick-nav-icon {
                width: 55px;
                height: 55px;
            }
            
            .quick-nav-grid {
                gap: 15px;
            }
            
            .quick-nav-label {
                font-size: 11px;
            }
        }
        
        /* Animation for new items */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .quick-nav-item {
            animation: slideIn 0.3s ease forwards;
        }
        
        /* Hover effects */
        .quick-nav-item:hover .quick-nav-icon {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(230, 46, 4, 0.2);
        }
        
        .quick-nav-item:hover .quick-nav-label {
            color: #e62e04;
            font-weight: 700;
        }
        
        /* Active state */
        .quick-nav-item.active .quick-nav-icon {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(230, 46, 4, 0.25);
        }
        
        .quick-nav-item.active .quick-nav-label {
            color: #e62e04;
            font-weight: 700;
        }
        
        /* Mobile Categories Before Banner - Inline Icon+Name Layout */
        .mobile-categories-before-banner {
            background: white;
            border-radius: 12px;
            padding: 12px 15px;
            margin: 0 10px 15px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            position: relative;
            overflow: hidden;
        }
        
        .mobile-categories-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 5px;
        }
        
        .mobile-categories-scroll::-webkit-scrollbar {
            display: none;
        }
        
        .mobile-categories-grid {
            display: flex;
            gap: 15px;
            padding: 0 3px;
            min-width: max-content;
        }
        
        .mobile-category-item {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            flex-shrink: 0;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 12px;
            border: 1px solid #f0f0f0;
            min-height: 42px;
        }
        
        .mobile-category-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: #e0e0e0;
            background: white;
        }
        
        .mobile-category-icon-text {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .mobile-category-icon {
            width: 25px;
            height: 25px;
            min-width: 25px;
            background: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            border: 1px solid #e8eaf6;
        }
        
        .mobile-category-icon.view-all {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.2);
        }
        
        .mobile-category-icon.view-all svg {
            fill: white;
        }
        
        .mobile-category-icon img {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }
        
        .mobile-category-name {
            font-size: 12px;
            font-weight: 500;
            color: #444;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100px;
            letter-spacing: -0.2px;
        }
        
        /* Scroll indicators for categories */
        .mobile-categories-before-banner::before,
        .mobile-categories-before-banner::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 15px;
            pointer-events: none;
            z-index: 1;
        }
        
        .mobile-categories-before-banner::before {
            left: 0;
            background: linear-gradient(to right, white, transparent);
        }
        
        .mobile-categories-before-banner::after {
            right: 0;
            background: linear-gradient(to left, white, transparent);
        }
        
        /* Hover effects */
        .mobile-category-item:hover .mobile-category-icon {
            transform: scale(1.1);
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        
        .mobile-category-item:hover .mobile-category-name {
            color: #667eea;
            font-weight: 600;
        }
        
        .mobile-category-item:hover .mobile-category-icon.view-all {
            box-shadow: 0 3px 8px rgba(102, 126, 234, 0.3);
        }
        
        /* Category icon variants */
        .mobile-category-item:nth-child(1) .mobile-category-icon {
            border-left: 3px solid #667eea;
        }
        
        .mobile-category-item:nth-child(2) .mobile-category-icon {
            border-left: 3px solid #f093fb;
        }
        
        .mobile-category-item:nth-child(3) .mobile-category-icon {
            border-left: 3px solid #4facfe;
        }
        
        .mobile-category-item:nth-child(4) .mobile-category-icon {
            border-left: 3px solid #43e97b;
        }
        
        .mobile-category-item:nth-child(5) .mobile-category-icon {
            border-left: 3px solid #fa709a;
        }
        
        .mobile-category-item:nth-child(6) .mobile-category-icon {
            border-left: 3px solid #a18cd1;
        }
        
        .mobile-category-item:nth-child(7) .mobile-category-icon {
            border-left: 3px solid #ff758c;
        }
        
        .mobile-category-item:nth-child(8) .mobile-category-icon {
            border-left: 3px solid #00f2fe;
        }
        
        .mobile-category-item:nth-child(9) .mobile-category-icon {
            border-left: 3px solid #fee140;
        }
        
        .mobile-category-item:nth-child(10) .mobile-category-icon {
            border-left: 3px solid #38f9d7;
        }
        
        .mobile-category-item:nth-child(11) .mobile-category-icon {
            border-left: 3px solid #ff9a9e;
        }
        
        .mobile-category-item:nth-child(12) .mobile-category-icon {
            border-left: 3px solid #fad0c4;
        }
        
        .mobile-category-item:nth-child(13) .mobile-category-icon {
            border-left: 3px solid #a6c1ee;
        }
        
        .mobile-category-item:nth-child(14) .mobile-category-icon {
            border-left: 3px solid #fbc2eb;
        }
        
        .mobile-category-item:nth-child(15) .mobile-category-icon {
            border-left: 3px solid #84fab0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .mobile-categories-before-banner {
                margin: 0 8px 12px;
                padding: 10px 8px;
                border-radius: 10px;
            }
            
            .mobile-category-item {
                padding: 6px 10px;
                gap: 6px;
                min-height: 38px;
            }
            
            .mobile-category-icon {
                width: 22px;
                height: 22px;
                min-width: 22px;
            }
            
            .mobile-category-icon-text {
                gap: 6px;
            }
            
            .mobile-categories-grid {
                gap: 10px;
            }
            
            .mobile-category-name {
                font-size: 11px;
                max-width: 80px;
            }
        }
        
        @media (max-width: 360px) {
            .mobile-category-item {
                padding: 5px 8px;
                min-height: 36px;
            }
            
            .mobile-category-icon {
                width: 20px;
                height: 20px;
                min-width: 20px;
            }
            
            .mobile-categories-grid {
                gap: 8px;
            }
            
            .mobile-category-name {
                font-size: 10px;
                max-width: 70px;
            }
        }
        
        /* Animation for category items */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .mobile-category-item {
            animation: slideIn 0.2s ease forwards;
        }
        
        /* Stagger animation for multiple items */
        .mobile-category-item:nth-child(1) { animation-delay: 0.05s; }
        .mobile-category-item:nth-child(2) { animation-delay: 0.1s; }
        .mobile-category-item:nth-child(3) { animation-delay: 0.15s; }
        .mobile-category-item:nth-child(4) { animation-delay: 0.2s; }
        .mobile-category-item:nth-child(5) { animation-delay: 0.25s; }
        .mobile-category-item:nth-child(6) { animation-delay: 0.3s; }
        .mobile-category-item:nth-child(7) { animation-delay: 0.35s; }
        .mobile-category-item:nth-child(8) { animation-delay: 0.4s; }
        .mobile-category-item:nth-child(9) { animation-delay: 0.45s; }
        .mobile-category-item:nth-child(10) { animation-delay: 0.5s; }
        .mobile-category-item:nth-child(11) { animation-delay: 0.55s; }
        .mobile-category-item:nth-child(12) { animation-delay: 0.6s; }
        .mobile-category-item:nth-child(13) { animation-delay: 0.65s; }
        .mobile-category-item:nth-child(14) { animation-delay: 0.7s; }
        .mobile-category-item:nth-child(15) { animation-delay: 0.75s; }
        
        /* Compact layout for many categories */
        @media (max-width: 767px) and (min-width: 576px) {
            .mobile-categories-grid {
                gap: 12px;
            }
            
            .mobile-category-item {
                padding: 8px 14px;
            }
            
            .mobile-category-icon {
                width: 28px;
                height: 28px;
                min-width: 28px;
            }
            
            .mobile-category-name {
                font-size: 13px;
                max-width: 120px;
            }
        }
        
        /* Active state */
        .mobile-category-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }
        
        .mobile-category-item.active .mobile-category-icon {
            background: white;
            border-color: white;
        }
        
        .mobile-category-item.active .mobile-category-icon img {
            filter: brightness(1) invert(0);
        }
        
        .mobile-category-item.active .mobile-category-name {
            color: white;
            font-weight: 600;
        }
        
        .mobile-category-item.active .mobile-category-icon.view-all {
            background: white;
        }
        
        .mobile-category-item.active .mobile-category-icon.view-all svg {
            fill: #667eea;
        }
        
        /* Make sure banner height adjusts for mobile */
        @media (max-width: 991px) {
            .home-slider .img-fit {
                max-height: 250px !important;
            }
        }
        
        /* Category count badge */
        .mobile-category-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            font-size: 8px;
            font-weight: 700;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }
        
        .mobile-category-icon {
            position: relative;
        }
        
        /* Mobile Coupon Section */
        .mobile-coupon-section {
            padding: 20px 15px;
            margin: 20px 0;
            position: relative;
            overflow: hidden;
        }
        
        .mobile-coupon-container {
            position: relative;
            z-index: 2;
        }
        
        /* Main Coupon Banner */
        .mobile-coupon-banner {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
        }
        
        .coupon-banner-content {
            position: relative;
            z-index: 2;
        }
        
        .coupon-banner-title {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fa3e00 0%, #ff6b35 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .coupon-banner-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        
        .coupon-discount-badge {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(135deg, #fa3e00 0%, #ff6b35 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 6px 20px rgba(250, 62, 0, 0.3);
        }
        
        .discount-amount {
            font-size: 32px;
            font-weight: 900;
            line-height: 1;
        }
        
        .discount-label {
            font-size: 14px;
            font-weight: 600;
            opacity: 0.9;
        }
        
        .view-coupons-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .view-coupons-btn:hover {
            background: #fa3e00;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(250, 62, 0, 0.3);
        }
        
        /* Decoration */
        .coupon-banner-decoration {
            position: absolute;
            right: 20px;
            bottom: 20px;
            z-index: 1;
        }
        
        .coupon-icon {
            width: 80px;
            height: 80px;
            opacity: 0.8;
        }
        
        .decoration-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(250, 62, 0, 0.1);
            animation: float 3s infinite ease-in-out;
        }
        
        .circle-1 {
            width: 40px;
            height: 40px;
            top: -10px;
            right: 40px;
        }
        
        .circle-2 {
            width: 25px;
            height: 25px;
            top: 30px;
            right: -10px;
            animation-delay: 0.5s;
        }
        
        .circle-3 {
            width: 35px;
            height: 35px;
            bottom: 40px;
            right: 60px;
            animation-delay: 1s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        /* Tags */
        .coupon-tag {
            position: absolute;
            top: 15px;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 3;
        }
        
        .coupon-tag.hot {
            right: 15px;
            background: var(--primary) !important;
        }
        
        .coupon-tag.new {
            right: 15px;
            top: 45px;
            background: linear-gradient(135deg, #2ed573 0%, #1e90ff 100%);
        }
        
        /* Coupon List */
        .mobile-coupon-list {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .coupon-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .coupon-list-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        
        .coupon-count {
            font-size: 12px;
            color: #fa3e00;
            font-weight: 600;
            background: rgba(250, 62, 0, 0.1);
            padding: 4px 10px;
            border-radius: 12px;
        }
        
        /* Coupon Items */
        .coupon-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }
        
        .coupon-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            border-color: #fa3e00;
        }
        
        .coupon-item-left {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }
        
        .coupon-discount-badge.small {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fa3e00 0%, #ff6b35 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            min-width: 70px;
            text-align: center;
        }
        
        .coupon-discount-badge.small span {
            font-size: 10px;
            opacity: 0.9;
        }
        
        .coupon-details {
            flex: 1;
        }
        
        .coupon-code {
            margin-bottom: 5px;
        }
        
        .code-label {
            font-size: 12px;
            color: #666;
            margin-right: 5px;
        }
        
        .code-value {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            letter-spacing: 1px;
            font-family: monospace;
        }
        
        .coupon-minimum {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            color: #666;
        }
        
        .copy-coupon-btn {
            background: linear-gradient(135deg, #fa3e00 0%, #ff6b35 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 70px;
        }
        
        .copy-coupon-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(250, 62, 0, 0.3);
        }
        
        /* No Coupons Message */
        .no-coupons-message {
            text-align: center;
            padding: 30px 20px;
            color: #999;
        }
        
        .no-coupons-message svg {
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .no-coupons-message p {
            font-size: 14px;
            margin: 0;
        }
        
        /* View All Link */
        .view-all-coupons-link {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .view-all-coupons-link a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #fa3e00;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .view-all-coupons-link a:hover {
            gap: 12px;
        }
        
        /* Features */
        .coupon-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .feature-item {
            text-align: center;
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(250, 62, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        
        .feature-text h5 {
            font-size: 12px;
            font-weight: 700;
            color: #333;
            margin: 0 0 4px;
        }
        
        .feature-text p {
            font-size: 10px;
            color: #666;
            margin: 0;
            line-height: 1.3;
        }
        
        /* Success Message */
        .coupon-copied-message {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #2ed573 0%, #1e90ff 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 20px rgba(46, 213, 115, 0.3);
            z-index: 9999;
            animation: slideUp 0.3s ease, fadeOut 0.3s ease 2s forwards;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .coupon-copied-message strong {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
        }
        
        @keyframes slideUp {
            from {
                bottom: -50px;
                opacity: 0;
            }
            to {
                bottom: 20px;
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .mobile-coupon-section {
                padding: 15px 10px;
            }
            
            .mobile-coupon-banner {
                padding: 15px;
            }
            
            .coupon-banner-title {
                font-size: 20px;
            }
            
            .coupon-banner-subtitle {
                font-size: 13px;
            }
            
            .coupon-discount-badge {
                padding: 12px 20px;
            }
            
            .discount-amount {
                font-size: 28px;
            }
            
            .mobile-coupon-list,
            .coupon-features {
                padding: 15px;
            }
            
            .coupon-item {
                padding: 12px;
            }
            
            .coupon-discount-badge.small {
                min-width: 60px;
                padding: 8px 12px;
            }
            
            .code-value {
                font-size: 14px;
            }
            
            .coupon-features {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .feature-item {
                display: flex;
                align-items: center;
                text-align: left;
                gap: 15px;
            }
            
            .feature-icon {
                margin: 0;
                flex-shrink: 0;
            }
        }
        
        @media (max-width: 360px) {
            .coupon-item-left {
                gap: 10px;
            }
            
            .coupon-discount-badge.small {
                min-width: 55px;
                padding: 6px 10px;
            }
            
            .copy-coupon-btn {
                padding: 8px 15px;
                min-width: 60px;
            }
        }
        /* Mobile Flash Deal Styles */
        .flash-deal-header-mobile {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe8cc 100%);
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 2px solid #ffd8a8;
        }

        .flash-badge-mobile {
            background: var(--primary) !important;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 8px;
            text-transform: uppercase;
        }

        /* Mobile Products Scroll */
        .flash-deal-products-mobile {
            margin: 0 -15px;
            padding: 0 15px;
        }

        .flash-products-scroll {
            display: flex;
            overflow-x: auto;
            gap: 12px;
            padding: 10px 0 15px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .flash-products-scroll::-webkit-scrollbar {
            display: none;
        }

        .flash-product-card {
            flex: 0 0 150px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .flash-product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }

        .flash-product-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .discount-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #ff4757;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            z-index: 1;
        }

        /* Mobile Reviews Section */
        .product-rating-mobile {
            margin-bottom: 8px;
            min-height: 20px;
        }

        .product-rating-mobile .stars {
            display: inline-flex;
            align-items: center;
        }

        /* Desktop Reviews Section */
        .product-rating-desktop {
            min-height: 20px;
            margin: 8px 0;
        }

        .product-rating-desktop .stars {
            display: inline-flex;
            align-items: center;
        }

        /* Mobile Countdown */
        #flash_deal .aiz-count-down.mobile-countdown {
            display: flex;
            gap: 8px;
            font-size: 12px;
        }

        #flash_deal .aiz-count-down.mobile-countdown .countdown-box {
            background: white;
            border-radius: 6px;
            padding: 6px 8px;
            min-width: 35px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #flash_deal .aiz-count-down.mobile-countdown .countdown-box .countdown-digit {
            font-size: 16px;
            font-weight: 700;
            color: #fa3e00;
            display: block;
            line-height: 1;
        }

        #flash_deal .aiz-count-down.mobile-countdown .countdown-box .countdown-text {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            display: block;
            margin-top: 2px;
        }

        /* Mobile Banner */
        .flash-banner-mobile {
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }
        
        /* Discount Badge for Mobile */
        .discount-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: var(--primary) !important;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            z-index: 1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            line-height: 1;
        }

        /* Discount Badge for Desktop */
        .discount-badge-desktop {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--primary) !important;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 6px;
            z-index: 2;
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
            line-height: 1;
        }

        /* Responsive Adjustments */
        @media (max-width: 480px) {
            .flash-product-card {
                flex: 0 0 140px;
            }

            .flash-product-card img {
                height: 110px;
            }

            .flash-deal-header-mobile {
                padding: 10px 12px;
            }

            .flash-badge-mobile {
                font-size: 9px;
                padding: 2px 6px;
            }
            
            .product-rating-mobile .stars span {
                font-size: 9px !important;
            }
            
            .product-rating-mobile span[style*="font-size: 11px"] {
                font-size: 10px !important;
            }
            
            .product-rating-mobile span[style*="font-size: 10px"] {
                font-size: 9px !important;
            }
        }

        @media (max-width: 360px) {
            .flash-product-card {
                flex: 0 0 130px;
                padding: 8px;
            }

            .flash-product-card img {
                height: 100px;
            }
            
            .flash-product-card .p-2 {
                padding: 6px !important;
            }
            
            .flash-product-card .fs-12 {
                font-size: 11px !important;
            }
            
            .flash-product-card .fs-14 {
                font-size: 13px !important;
            }
            
            .flash-product-card .fs-11 {
                font-size: 10px !important;
            }
            
            .product-rating-mobile .stars span {
                font-size: 8px !important;
            }
            
            .product-rating-mobile span[style*="font-size: 11px"],
            .product-rating-mobile span[style*="font-size: 10px"] {
                font-size: 9px !important;
            }
        }

        /* Animation */
        @keyframes pulseFlash {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .flash-deal-header-mobile {
            animation: pulseFlash 2s infinite;
        }
        
        /* Flash Deal Item Adjustment */
        .flash-deal-item {
            padding-bottom: 10px;
        }
        
         .todays-deal-discount-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--primary) !important;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            z-index: 2;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            line-height: 1;
            animation: discountPulse 1.5s infinite;
        }
        
        /* Discount badge animation */
        @keyframes discountPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 767px) {
            .todays-deal-discount-badge {
                font-size: 10px;
                padding: 3px 6px;
                top: 8px;
                left: 8px;
            }
        }
        
        @media (max-width: 480px) {
            .todays-deal-discount-badge {
                font-size: 9px;
                padding: 2px 5px;
                top: 6px;
                left: 6px;
            }
        }
        
        /* Today's Deal Stars */
        .todays_deal .stars {
            display: inline-flex;
            align-items: center;
            gap: 1px;
        }
        
        /* Hover effect */
        .todays_deal .col.mb-2 a {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .todays_deal .col.mb-2 a:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>

    @php $lang = get_system_language()->code;  @endphp

    <!-- home banner area -->
    <div class="home-banner-area mb-3" style="">
        <div class="container">
            <div class="row gutters-12 position-relative">
                <!-- category menu -->
                <div class="position-static d-none d-xl-block col-auto">
                    @include('frontend.'.get_setting("homepage_select").'.partials.category_menu')
                </div>

                <div class="col-lg mt-4">
                    <!-- Mobile Categories Before Banner -->
                    <div class="d-block d-lg-none mobile-categories-before-banner mb-3">
                        <div class="mobile-categories-scroll">
                            <div class="mobile-categories-grid">
                                @foreach ($featured_categories->take(15) as $category)
                                <a href="{{ route('products.category', $category->slug) }}" class="mobile-category-item">
                                    <div class="mobile-category-icon-text">
                                        <div class="mobile-category-icon">
                                            <img src="{{ isset($category->bannerImage->file_name) ? my_asset($category->bannerImage->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                                class="img-fit h-100 w-100"
                                                alt="{{ $category->getTranslation('name') }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                        </div>
                                        <span class="mobile-category-name">{{ $category->getTranslation('name') }}</span>
                                    </div>
                                </a>
                                @endforeach
                                
                                <!-- View All Categories -->
                                <a href="{{ route('categories.all') }}" class="mobile-category-item">
                                    <div class="mobile-category-icon-text">
                                        <div class="mobile-category-icon view-all">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#667eea">
                                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                            </svg>
                                        </div>
                                        <span class="mobile-category-name">View All</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Sliders -->
                    @if (get_setting('home_slider_images', null, $lang) != null)
                        <div class="home-slider">
                            <div class="aiz-carousel overflow-hidden rounded-2" data-autoplay="true" data-infinite="true">
                                @php
                                    $decoded_slider_images = json_decode(get_setting('home_slider_images', null, $lang), true);
                                    $sliders = get_slider_images($decoded_slider_images);
                                    $home_slider_links = get_setting('home_slider_links', null, $lang);
                                @endphp
                                @foreach ($sliders as $key => $slider)
                                    <div class="carousel-box">
                                        <a class="d-block" href="{{ isset(json_decode($home_slider_links, true)[$key]) ? json_decode($home_slider_links, true)[$key] : '' }}">
                                            <img
                                                class="d-block mw-100 img-fit h-180px h-md-320px @if(count($featured_categories) == 0) h-lg-530px @else h-lg-350px @endif"
                                                src="{{ $slider ? my_asset($slider->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                                alt="{{ env('APP_NAME')}} promo"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                            >
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Desktop Featured Categories -->
                    @if (count($featured_categories) > 0)
                        <div class="bg-whit mt-4 d-none d-lg-block">
                            <div class="aiz-carousel slick-left arrow-inactive-none arrow-x-0" data-items="6.5" data-xxl-items="6.5" data-xl-items="4.5"
                                data-lg-items="5" data-md-items="5" data-sm-items="3" data-xs-items="3" data-arrows="true">
                                @foreach ($featured_categories as $key => $category)
                                    @php
                                        $category_name = $category->getTranslation('name');
                                    @endphp
                                    <div class="carousel-box">
                                        <div class="d-flex flex-column align-items-center overflow-hidden" style="width: 110px; height: 155px;border-radius: 8px;background: #f5f6f7; box-shadow: 0px 0px 25px -15px rgba(171,169,171,1);">
                                            <div class="overflow-hidden hov-scale-img" style="width: 110px; height:100px; min-height:100px;">
                                                <a class="d-block h-100" href="{{ route('products.category', $category->slug) }}">
                                                    <img src="{{ isset($category->bannerImage->file_name) ? my_asset($category->bannerImage->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                                        class="lazyload img-fit h-100 mx-auto has-transition"
                                                        alt="{{ $category->getTranslation('name') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                </a>
                                            </div>
                                            <p class="mt-1 mb-0 fs-12 fw-500 text-center text-truncate-2 px-2">
                                                <a class="text-reset hov-text-primary"
                                                    href="{{ route('products.category', $category->slug) }}"
                                                    style="width: max-content;">
                                                    {{ $category_name }}
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Mobile Quick Navigation -->
                <div class="d-block d-lg-none mobile-quick-nav">
                    <div class="quick-nav-scroll">
                        <div class="quick-nav-grid">
                            <!-- Categories -->
                            <a href="{{ route('categories.all') }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M10 3H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zm10 0h-6a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zM10 13H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1zm10 0h-6a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Categories</span>
                            </a>
                            
                            <!-- Flash Deals -->
                            <a href="{{ route('flash-deals') }}" class="quick-nav-item">
                                <div class="quick-nav-icon special">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Flash Sale</span>
                            </a>
                            
                            <!-- Today's Deal -->
                            <a href="{{ route('todays-deal') }}" class="quick-nav-item">
                                <div class="quick-nav-icon special">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Today's Deal</span>
                            </a>
                            
                            <!-- Lottery -->
                            <a href="{{ route('lottery.view') }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm-5 6h-4v1h3c.55 0 1 .45 1 1v3c0 .55-.45 1-1 1h-1v1h-2v-1H9v-2h4v-1h-3c-.55 0-1-.45-1-1V9c0-.55.45-1 1-1h1V7h2v1h2v2z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Lottery</span>
                            </a>
                            
                            <!-- Best Selling -->
                            <a href="{{ route('search', ['sort_by' => 'top']) }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Best Sales</span>
                            </a>
                            
                            <!-- New Arrivals -->
                            <a href="{{ route('search', ['sort_by' => 'newest']) }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M23 12l-2.44-2.78.34-3.68-3.61-.82-1.89-3.18L12 3 8.6 1.54 6.71 4.72l-3.61.81.34 3.68L1 12l2.44 2.78-.34 3.69 3.61.82 1.89 3.18L12 21l3.4 1.46 1.89-3.18 3.61-.82-.34-3.68L23 12zm-10 5h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">New Arrivals</span>
                            </a>
                            
                            <!-- Coupons -->
                            @if (get_setting('coupon_system') == 1)
                            <a href="{{ route('coupons.all') }}" class="quick-nav-item">
                                <div class="quick-nav-icon special">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-4h11v4zm0-5H4V9h11v4zm5 5h-4V9h4v9z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Coupons</span>
                            </a>
                            @endif
                            
                            <!-- Top Brands -->
                            <a href="{{ route('brands.all') }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Brands</span>
                            </a>
                            
                            <!-- Auction -->
                            @if (addon_is_activated('auction'))
                            <a href="{{ route('auction_products.all') }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="19.627" height="20" viewBox="0 0 19.627 20">
                                        <g id="cb3bc0b728579e634f654dfaf5995832" transform="translate(-8 -7.122)">
                                          <rect id="Rectangle_21402" data-name="Rectangle 21402" width="2.455" height="5.729" rx="1.228" transform="translate(10.102 16.386) rotate(-45)" fill="#fff"/>
                                          <rect id="Rectangle_21403" data-name="Rectangle 21403" width="2.455" height="5.729" rx="1.228" transform="translate(17.623 8.858) rotate(-45)" fill="#fff"/>
                                          <rect id="Rectangle_21404" data-name="Rectangle 21404" width="4.91" height="6.547" rx="2" transform="translate(12.702 13.203) rotate(-45)" fill="#fff"/>
                                          <rect id="Rectangle_21405" data-name="Rectangle 21405" width="1.637" height="4.092" transform="translate(12.414 15.225) rotate(-45)" fill="#fff"/>
                                          <rect id="Rectangle_21406" data-name="Rectangle 21406" width="1.637" height="4.092" transform="translate(17.043 10.599) rotate(-45)" fill="#fff"/>
                                          <path id="Path_41554" data-name="Path 41554" d="M21.721,14.563l.577.577L21.14,16.3l-.577-.577a.819.819,0,1,1,1.158-1.158Z" transform="translate(-7.281 -4.255)" fill="#fff"/>
                                          <rect id="Rectangle_21407" data-name="Rectangle 21407" width="1.637" height="4.501" transform="translate(18.489 16.673) rotate(-45)" fill="#fff"/>
                                          <path id="Path_41555" data-name="Path 41555" d="M41.235,36.393l1.158-1.158a.409.409,0,0,1,.581,0L46.833,39.1a1.228,1.228,0,0,1,0,1.735h0a1.228,1.228,0,0,1-1.735,0l-3.863-3.859a.409.409,0,0,1,0-.581Z" transform="translate(-19.564 -16.538)" fill="#fff"/>
                                          <rect id="Rectangle_21408" data-name="Rectangle 21408" width="12.276" height="1.637" transform="translate(8 25.485)" fill="#fff"/>
                                          <path id="Path_41556" data-name="Path 41556" d="M11.637,48H19a1.637,1.637,0,0,1,1.637,1.637H10A1.637,1.637,0,0,1,11.637,48Z" transform="translate(-1.182 -24.151)" fill="#fff"/>
                                        </g>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Auction</span>
                            </a>
                            @endif
                            
                            <!-- Preorder -->
                            @if (addon_is_activated('preorder'))
                            <a href="{{ route('all_preorder_products') }}" class="quick-nav-item">
                                <div class="quick-nav-icon special">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Preorder</span>
                            </a>
                            @endif
                            
                            <!-- Classified -->
                            @if (get_setting('classified_product') == 1)
                            <a href="{{ route('customer.products') }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Classified</span>
                            </a>
                            @endif
                            
                            <!-- Top Sellers -->
                            @if (get_setting('vendor_system_activation') == 1)
                            <a href="{{ route('sellers') }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Top Sellers</span>
                            </a>
                            @endif
                            
                            <!-- All Products -->
                            <a href="{{ route('inhouse.all') }}" class="quick-nav-item">
                                <div class="quick-nav-icon special">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">All Products</span>
                            </a>
                            
                            <!-- Wishlist -->
                            <a href="{{ route('wishlists.index') }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Wishlist</span>
                            </a>
                            
                            <!-- Compare -->
                            <a href="{{ route('compare') }}" class="quick-nav-item">
                                <div class="quick-nav-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M10 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h5v2h2V1h-2v2zm0 15H5l5-6v6zm9-15h-5v2h5v13l-5-6v9h5c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
                                    </svg>
                                </div>
                                <span class="quick-nav-label">Compare</span>
                            </a>
                        </div>
                    </div>
                </div>

                @php
                    $todays_deal_products = filter_products(App\Models\Product::where('todays_deal', '1'))->orderBy('id', 'desc')->get();
                @endphp
                @if(count($todays_deal_products) > 0)
                    <div class="col-12 col-lg-auto mt-4">
                        <div class="todays_deal bg-white rounded-2 overflow-hidden">
                            <div class="bg-soft-primary p-2 px-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="fw-700 fs-16 mr-2 text-truncate">
                                        {{ translate('Todays Deal') }}
                                    </span>
                                    <span class="badge badge-primary badge-inline rounded-1 fs-11">{{ translate('Hot') }}</span>
                                </div>
                                <a href="{{ route('todays-deal') }}" class="fs-11 fw-600" style="color: #e62e04;">
                                    {{ translate('View All') }}
                                    <i class="las la-arrow-right ml-1"></i>
                                </a>
                            </div>
                            <div class="c-scrollbar-light overflow-auto h-lg-470px p-2 bg-primary">
                                <div class="gutters-5 lg-no-gutters row row-cols-2 row-cols-md-3 row-cols-lg-1">
                                    @foreach ($todays_deal_products as $key => $product)
                                        @if ($product != null)
                                        @php
                                            // Calculate discount percentage
                                            $discount_percentage = 0;
                                            if (home_base_price($product) != home_discounted_base_price($product)) {
                                                $original_price = home_base_price($product);
                                                $discounted_price = home_discounted_base_price($product);
                                                $original_numeric = floatval(str_replace(['$', '৳', ',', ' '], '', $original_price));
                                                $discounted_numeric = floatval(str_replace(['$', '৳', ',', ' '], '', $discounted_price));
                                                $discount_percentage = $original_numeric > 0 ? round((($original_numeric - $discounted_numeric) / $original_numeric) * 100) : 0;
                                            }
                                            
                                            // Get reviews
                                            $ratingAvg = \App\Models\Review::where('product_id', $product->id)->avg('rating') ?? 0;
                                            $ratingAvg = round($ratingAvg, 1); 
                                        
                                            $reviewCount = \App\Models\Review::where('product_id', $product->id)->count();
                                        
                                            $fullStars = floor($ratingAvg);
                                            $hasHalfStar = ($ratingAvg - $fullStars) >= 0.5;
                                        @endphp
                                        <div class="col mb-2">
                                            <a href="{{ route('product', $product->slug) }}" class="d-block p-2 text-reset bg-white h-100 rounded position-relative">
                                                <!-- Discount Badge -->
                                                @if($discount_percentage > 0)
                                                    <div class="todays-deal-discount-badge">
                                                        -{{ $discount_percentage }}%
                                                    </div>
                                                @endif
                                                
                                                <div class="row gutters-5 align-items-center" style="margin-bottom: 1.5px;">
                                                    <div class="col-md">
                                                        <div class="img position-relative">
                                                            <img
                                                                class="lazyload img-fit h-140px h-lg-90px"
                                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                                data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                                                alt="{{ $product->getTranslation('name') }}"
                                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                            >
                                                        </div>
                                                    </div>
                                                    <div class="col-md mt-2 mt-md-0 text-center">
                                                        <!-- Price Section -->
                                                        <div class="fs-14">
                                                            <span class="d-block text-primary fw-700">{{ home_discounted_base_price($product) }}</span>
                                                            @if(home_base_price($product) != home_discounted_base_price($product))
                                                                <del class="d-block opacity-70">{{ home_base_price($product) }}</del>
                                                            @endif
                                                        </div>
                                                        
                                                        <!-- Reviews Section -->
                                                        <div class="text-center mt-2" style="font-size:13px; color:#444;">
                                                            <!-- Stars -->
                                                            <div class="stars">
                                                                @for ($i = 1; $i <= 5; $i++)
                                                                    @if ($ratingAvg > 0)
                                                                        @if ($i <= $fullStars)
                                                                            <span style="color:#f5c518;">&#9733;</span>
                                                                        @elseif ($i == $fullStars + 1 && $hasHalfStar)
                                                                            <span style="color:#f5c518;">&#9733;</span>
                                                                        @else
                                                                            <span style="color:#ddd;">&#9733;</span>
                                                                        @endif
                                                                    @else
                                                                        <span style="color:#ddd;">&#9733;</span>
                                                                    @endif
                                                                @endfor
                                                            </div>
                                                        
                                                            <!-- Rating number -->
                                                            @if($ratingAvg > 0)
                                                                <span style="margin-left:6px; font-weight:600;">
                                                                    {{ $ratingAvg }}
                                                                </span>
                                                            
                                                                <!-- Review count -->
                                                                @if($reviewCount > 0)
                                                                    <span style="color:#777;">
                                                                        ({{ $reviewCount }})
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span style="color:#777;">
                                                                    0.0 (0)
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Banner section 1 -->
    @php $homeBanner1Images = get_setting('home_banner1_images', null, $lang);   @endphp
    @if ($homeBanner1Images != null)
        <div class="mb-2 mb-md-3 mt-2 mt-md-3">
            <div class="container">
                @php
                    $banner_1_imags = json_decode($homeBanner1Images);
                    $data_md = count($banner_1_imags) >= 2 ? 2 : 1;
                    $home_banner1_links = get_setting('home_banner1_links', null, $lang);
                @endphp
                <div class="w-100">
                    <div class="aiz-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15"
                        data-items="{{ count($banner_1_imags) }}" data-xxl-items="{{ count($banner_1_imags) }}"
                        data-xl-items="{{ count($banner_1_imags) }}" data-lg-items="{{ $data_md }}"
                        data-md-items="{{ $data_md }}" data-sm-items="1" data-xs-items="1" data-arrows="true"
                        data-dots="false">
                        @foreach ($banner_1_imags as $key => $value)
                            <div class="carousel-box overflow-hidden hov-scale-img">
                                <a href="{{ isset(json_decode($home_banner1_links, true)[$key]) ? json_decode($home_banner1_links, true)[$key] : '' }}"
                                    class="d-block text-reset rounded-2 overflow-hidden">
                                    <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                        data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                                        class="img-fluid lazyload w-100 has-transition"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Deal -->
    @php
        $flash_deal = get_featured_flash_deal();
        $flash_deal_bg = get_setting('flash_deal_bg_color');
    @endphp
    @if ($flash_deal != null)
        <section class="mb-2 mb-md-3 mt-2 mt-md-3" id="flash_deal">
            <div class="container">
                <div class="rounded-2 overflow-hidden p-3 p-md-2rem @if(get_setting('flash_deal_section_outline') == 1) border @endif" style="background: {{ $flash_deal_bg != null ? $flash_deal_bg : '#fff9ed' }}; border-color: {{ get_setting('flash_deal_section_outline_color') }} !important;">
                    <!-- Top Section - Mobile Optimized -->
                    <div class="flash-deal-header-mobile d-block d-lg-none">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h3 class="fs-18 fw-700 mb-1">
                                    <span class="d-inline-block">{{ translate('Flash Sale') }}</span>
                                    <span class="flash-badge-mobile">HOT</span>
                                </h3>
                                <div class="aiz-count-down align-items-center" data-date="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}"></div>
                            </div>
                            <a href="{{ route('flash-deals') }}" class="fs-12 fw-600 text-primary">
                                {{ translate('View All') }}
                            </a>
                        </div>
                    </div>
    
                    <!-- Top Section - Desktop -->
                    <div class="d-none d-lg-flex flex-wrap align-items-baseline justify-content-center justify-content-sm-between mb-2 mb-md-3 position-relative">
                        <div class="d-flex flex-wrap align-items-center">
                            <!-- Title -->
                            <h3 class="fs-22 fs-md-20 fw-700 mb-2 mb-sm-0">
                                <span class="d-inline-block">{{ translate('Flash Sale') }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="24" viewBox="0 0 16 24"
                                    class="ml-2">
                                    <path id="Path_28795" data-name="Path 28795"
                                        d="M30.953,13.695a.474.474,0,0,0-.424-.25h-4.9l3.917-7.81a.423.423,0,0,0-.028-.428.477.477,0,0,0-.4-.207H21.588a.473.473,0,0,0-.429.263L15.041,18.151a.423.423,0,0,0,.034.423.478.478,0,0,0,.4.2h4.593l-2.229,9.683a.438.438,0,0,0,.259.5.489.489,0,0,0,.571-.127L30.9,14.164a.425.425,0,0,0,.054-.469Z"
                                        transform="translate(-15 -5)" fill="#fcc201" />
                                </svg>
                            </h3>
                            <!-- Countdown -->
                            <div class="aiz-count-down align-items-center ml-2 mb-2 mb-lg-0" data-date="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}"></div>
                        </div>
    
                        <!-- Links -->
                        <div>
                            <div class="text-dark d-flex align-items-center mb-0">
                                <a href="{{ route('flash-deals') }}"
                                    class="fs-10 fs-md-12 fw-700 has-transition text-reset opacity-60 hov-opacity-100 hov-text-primary animate-underline-primary mr-3">{{ translate('View All Flash Sale') }}</a>
                                <span class=" border-left border-soft-light border-width-2 pl-3">
                                    <a href="{{ route('flash-deal-details', $flash_deal->slug) }}"
                                        class="fs-10 fs-md-12 fw-700 has-transition text-reset opacity-60 hov-opacity-100 hov-text-primary animate-underline-primary">{{ translate('View All Products from This Flash Sale') }}</a>
                                </span>
                            </div>
                        </div>
                    </div>
    
                    <div class="row gutters-16 align-items-center">
                        <!-- Flash Deals Banner - Mobile Hidden -->
                        <div class="col-auto d-none d-lg-block">
                            <a href="{{ route('flash-deal-details', $flash_deal->slug) }}">
                                <div class="size-180px size-md-200px size-lg-280px rounded-2 overflow-hidden"
                                    style="background-image: url('{{ uploaded_asset($flash_deal->banner) }}'); background-size: cover; background-position: center center;">
                                </div>
                            </a>
                        </div>
    
                        <div class="col">
                            <!-- Flash Deals Products - Mobile Scrollable -->
                            <div class="flash-deal-products-mobile d-block d-lg-none">
                                <div class="flash-products-scroll">
                                    @php
                                        $flash_deal_products = get_flash_deal_products($flash_deal->id);
                                    @endphp
                                    @foreach ($flash_deal_products as $key => $flash_deal_product)
                                        <div class="flash-product-card">
                                            @if ($flash_deal_product->product != null && $flash_deal_product->product->published != 0)
                                                @php
                                                    $product_url = route('product', $flash_deal_product->product->slug);
                                                    if ($flash_deal_product->product->auction_product == 1) {
                                                        $product_url = route('auction-product', $flash_deal_product->product->slug);
                                                    }
                                                    
                                                    // Get reviews for mobile
                                                    $ratingAvg = \App\Models\Review::where('product_id', $flash_deal_product->product->id)->avg('rating') ?? 0;
                                                    $ratingAvg = round($ratingAvg, 1);
                                                    $reviewCount = \App\Models\Review::where('product_id', $flash_deal_product->product->id)->count();
                                                    $fullStars = floor($ratingAvg);
                                                    $hasHalfStar = ($ratingAvg - $fullStars) >= 0.5;
                                                    
                                                    // Calculate discount percentage for mobile
                                                    $discount_percentage = 0;
                                                    if ($flash_deal_product->auction_product == 0) {
                                                        if (home_base_price($flash_deal_product->product) != home_discounted_base_price($flash_deal_product->product)) {
                                                            $original_price = home_base_price($flash_deal_product->product);
                                                            $discounted_price = home_discounted_base_price($flash_deal_product->product);
                                                            $original_numeric = floatval(str_replace(['$', '৳', ',', ' '], '', $original_price));
                                                            $discounted_numeric = floatval(str_replace(['$', '৳', ',', ' '], '', $discounted_price));
                                                            $discount_percentage = $original_numeric > 0 ? round((($original_numeric - $discounted_numeric) / $original_numeric) * 100) : 0;
                                                        }
                                                    }
                                                @endphp
                                                <a href="{{ $product_url }}" class="text-reset">
                                                    <div class="position-relative">
                                                        @if ($flash_deal_product->auction_product == 0)
                                                            @if (home_base_price($flash_deal_product->product) != home_discounted_base_price($flash_deal_product->product))
                                                                <span class="discount-badge"> -{{ $discount_percentage }}%</span>
                                                            @endif
                                                        @endif
                                                        <img src="{{ get_image($flash_deal_product->product->thumbnail) }}"
                                                            class="img-fluid rounded"
                                                            alt="{{ $flash_deal_product->product->getTranslation('name') }}"
                                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                    </div>
                                                    <div class="p-2">
                                                        <h6 class="fs-12 text-dark text-truncate mb-1">
                                                            {{ $flash_deal_product->product->getTranslation('name') }}
                                                        </h6>
                                                        
                                                        <!-- Reviews Section for Mobile -->
                                                        <div class="product-rating-mobile mb-2">
                                                            <div class="d-flex align-items-center">
                                                                <div class="stars mr-1">
                                                                    @for ($i = 1; $i <= 5; $i++)
                                                                        @if ($ratingAvg > 0)
                                                                            @if ($i <= $fullStars)
                                                                                <span style="color:#f5c518; font-size: 10px;">★</span>
                                                                            @elseif ($i == $fullStars + 1 && $hasHalfStar)
                                                                                <span style="color:#f5c518; font-size: 10px;">★</span>
                                                                            @else
                                                                                <span style="color:#ddd; font-size: 10px;">★</span>
                                                                            @endif
                                                                        @else
                                                                            <span style="color:#ddd; font-size: 10px;">★</span>
                                                                        @endif
                                                                    @endfor
                                                                </div>
                                                                @if($ratingAvg > 0)
                                                                    <span style="font-size: 11px; font-weight: 600; color: #333; margin-left: 2px;">
                                                                        {{ $ratingAvg }}
                                                                    </span>
                                                                    @if($reviewCount > 0)
                                                                        <span style="font-size: 10px; color: #777; margin-left: 2px;">
                                                                            ({{ $reviewCount }})
                                                                        </span>
                                                                    @endif
                                                                @else
                                                                    <span style="font-size: 10px; color: #777;">
                                                                        0.0 (0) reviews
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fs-14 fw-700 text-primary">{{ home_discounted_base_price($flash_deal_product->product) }}</span>
                                                            @if (home_base_price($flash_deal_product->product) != home_discounted_base_price($flash_deal_product->product))
                                                                <del class="fs-11 text-secondary">{{ home_base_price($flash_deal_product->product) }}</del>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
    
                            <!-- Flash Deals Products - Desktop -->
                            <div class="pr-md-3 d-none d-lg-block">
                                @php
                                    $flash_deal_products = get_flash_deal_products($flash_deal->id);
                                @endphp
                                <div class="aiz-carousel gutters-16 arrow-inactive-none arrow-x-0"
                                data-items="5" data-xxl-items="5" data-xl-items="4" data-lg-items="3" data-md-items="2.5"
                                data-sm-items="2.3" data-xs-items="1" data-arrows="true" data-dots="false">
                                @foreach ($flash_deal_products as $key => $flash_deal_product)
                                    <div class="carousel-box position-relative has-transition hov-animate-outline">
                                        @if ($flash_deal_product->product != null && $flash_deal_product->product->published != 0)
                                            @php
                                                $product_url = route('product', $flash_deal_product->product->slug);
                                                if ($flash_deal_product->product->auction_product == 1) {
                                                    $product_url = route('auction-product', $flash_deal_product->product->slug);
                                                }
                                                
                                                // Get reviews for desktop
                                                $ratingAvg = \App\Models\Review::where('product_id', $flash_deal_product->product->id)->avg('rating') ?? 0;
                                                $ratingAvg = round($ratingAvg, 1);
                                                $reviewCount = \App\Models\Review::where('product_id', $flash_deal_product->product->id)->count();
                                                $fullStars = floor($ratingAvg);
                                                $hasHalfStar = ($ratingAvg - $fullStars) >= 0.5;
                                                
                                                // Calculate discount percentage for mobile
                                                $discount_percentage = 0;
                                                if ($flash_deal_product->auction_product == 0) {
                                                    if (home_base_price($flash_deal_product->product) != home_discounted_base_price($flash_deal_product->product)) {
                                                        $original_price = home_base_price($flash_deal_product->product);
                                                        $discounted_price = home_discounted_base_price($flash_deal_product->product);
                                                        $original_numeric = floatval(str_replace(['$', '৳', ',', ' '], '', $original_price));
                                                        $discounted_numeric = floatval(str_replace(['$', '৳', ',', ' '], '', $discounted_price));
                                                        $discount_percentage = $original_numeric > 0 ? round((($original_numeric - $discounted_numeric) / $original_numeric) * 100) : 0;
                                                    }
                                                }
                                            @endphp
                                            <div
                                                class="aiz-card-box h-180px h-md-200px h-lg-280px flash-deal-item position-relative text-center">
                                                <a href="{{ $product_url }}"
                                                    class="d-block overflow-hidden hov-scale-img"
                                                    title="{{ $flash_deal_product->product->getTranslation('name') }}">
                                                    <!-- Discount Badge for Desktop -->
                                                    @if ($flash_deal_product->auction_product == 0)
                                                        @if (home_base_price($flash_deal_product->product) != home_discounted_base_price($flash_deal_product->product) && $discount_percentage > 0)
                                                            <div class="discount-badge-desktop">
                                                                -{{ $discount_percentage }} %
                                                            </div>
                                                        @endif
                                                    @endif
                                                    <!-- Image -->
                                                    <img src="{{ get_image($flash_deal_product->product->thumbnail) }}"
                                                        class="lazyload h-100px h-md-120px h-lg-170px mw-100 mx-auto has-transition rounded-2"
                                                        alt="{{ $flash_deal_product->product->getTranslation('name') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                    <!-- Product name -->
                                                    <h3 class="fw-400 fs-13 text-truncate-2 lh-1-4 mb-0 h-40px text-center pt-1 px-1 mt-1">
                                                        <a href="{{ $product_url }}" class="d-block text-reset hov-text-primary"
                                                            title="{{ $flash_deal_product->product->getTranslation('name') }}">{{ $flash_deal_product->product->getTranslation('name') }}</a>
                                                    </h3>
                                                    
                                                    <!-- Reviews Section for Desktop -->
                                                    <div class="product-rating-desktop mt-1 mb-2">
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <div class="stars mr-1">
                                                                @for ($i = 1; $i <= 5; $i++)
                                                                    @if ($ratingAvg > 0)
                                                                        @if ($i <= $fullStars)
                                                                            <span style="color:#f5c518; font-size: 11px;">★</span>
                                                                        @elseif ($i == $fullStars + 1 && $hasHalfStar)
                                                                            <span style="color:#f5c518; font-size: 11px;">★</span>
                                                                        @else
                                                                            <span style="color:#ddd; font-size: 11px;">★</span>
                                                                        @endif
                                                                    @else
                                                                        <span style="color:#ddd; font-size: 11px;">★</span>
                                                                    @endif
                                                                @endfor
                                                            </div>
                                                            @if($ratingAvg > 0)
                                                                <span style="font-size: 12px; font-weight: 600; color: #333; margin-left: 4px;">
                                                                    {{ $ratingAvg }}
                                                                </span>
                                                                @if($reviewCount > 0)
                                                                    <span style="font-size: 11px; color: #777; margin-left: 2px;">
                                                                        ({{ $reviewCount }}) reviews
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span style="font-size: 11px; color: #777;">
                                                                    0.0 (0) reviews
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Price -->
                                                    <h4 class="fs-14 d-flex justify-content-center mt-2">
                                                        @if ($flash_deal_product->auction_product == 0)
                                                            <!-- Previous price -->
                                                            @if (home_base_price($flash_deal_product->product) != home_discounted_base_price($flash_deal_product->product))
                                                                <span class="disc-amount has-transition">
                                                                    <del class="fw-400 text-secondary mr-1">{{ home_base_price($flash_deal_product->product) }}</del>
                                                                </span>
                                                            @endif
                                                            <!-- price -->
                                                            <span class="fw-700 text-primary">{{ home_discounted_base_price($flash_deal_product->product) }}</span>
                                                        @endif
                                                    </h4>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- Mobile Banner -->
                    <div class="d-block d-lg-none mt-3">
                        <a href="{{ route('flash-deal-details', $flash_deal->slug) }}" class="d-block rounded overflow-hidden">
                            <div class="flash-banner-mobile" style="background-image: url('{{ uploaded_asset($flash_deal->banner) }}'); background-size: cover; background-position: center; height: 120px;">
                                <div class="h-100 d-flex flex-column justify-content-center align-items-center text-white" style="background: rgba(0,0,0,0.3);">
                                    <h5 class="fs-16 fw-700 mb-1">{{ translate('Limited Time Offer') }}</h5>
                                    <p class="fs-12 mb-0">{{ translate('Shop Now') }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Featured Products -->
    <div id="section_featured">

    </div>


    @if (addon_is_activated('preorder'))
        <!-- Banner Section 2 -->
        @php $homepreorder_banner_1Images = get_setting('home_preorder_banner_1_images', null, $lang);   @endphp
        @if ($homepreorder_banner_1Images != null)
            <div class="mb-2 mb-md-3 mt-2 mt-md-3">
                <div class="container">
                    @php
                        $banner_2_imags = json_decode($homepreorder_banner_1Images);
                        $data_md = count($banner_2_imags) >= 2 ? 2 : 1;
                        $home_preorder_banner_1_links = get_setting('home_preorder_banner_1_links', null, $lang);
                    @endphp
                    <div class="rounded-2 overflow-hidden">
                        <div class="aiz-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15"
                        data-items="{{ count($banner_2_imags) }}" data-xxl-items="{{ count($banner_2_imags) }}"
                        data-xl-items="{{ count($banner_2_imags) }}" data-lg-items="{{ $data_md }}"
                        data-md-items="{{ $data_md }}" data-sm-items="1" data-xs-items="1" data-arrows="true"
                        data-dots="false">
                        @foreach ($banner_2_imags as $key => $value)
                            <div class="carousel-box overflow-hidden hov-scale-img">
                                <a href="{{ isset(json_decode($home_preorder_banner_1_links, true)[$key]) ? json_decode($home_preorder_banner_1_links, true)[$key] : '' }}"
                                    class="d-block text-reset rounded-2 overflow-hidden">
                                    <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                        data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                                        class="img-fluid lazyload w-100 has-transition"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                </a>
                            </div>
                        @endforeach
                    </div>
                    </div>
                </div>
            </div>
        @endif
    


        <!-- Featured Preorder Products -->
        <div id="section_featured_preorder_products">

        </div>
    @endif

    <!-- Banner Section 2 -->
    @php $homeBanner2Images = get_setting('home_banner2_images', null, $lang);   @endphp
    @if ($homeBanner2Images != null)
        <div class="mb-2 mb-md-3 mt-2 mt-md-3">
            <div class="container">
                @php
                    $banner_2_imags = json_decode($homeBanner2Images);
                    $data_md = count($banner_2_imags) >= 2 ? 2 : 1;
                    $home_banner2_links = get_setting('home_banner2_links', null, $lang);
                @endphp
                <div class="rounded-2 overflow-hidden">
                    <div class="aiz-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15"
                    data-items="{{ count($banner_2_imags) }}" data-xxl-items="{{ count($banner_2_imags) }}"
                    data-xl-items="{{ count($banner_2_imags) }}" data-lg-items="{{ $data_md }}"
                    data-md-items="{{ $data_md }}" data-sm-items="1" data-xs-items="1" data-arrows="true"
                    data-dots="false">
                    @foreach ($banner_2_imags as $key => $value)
                        <div class="carousel-box overflow-hidden hov-scale-img">
                            <a href="{{ isset(json_decode($home_banner2_links, true)[$key]) ? json_decode($home_banner2_links, true)[$key] : '' }}"
                                class="d-block text-reset rounded-2 overflow-hidden">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                                    class="img-fluid lazyload w-100 has-transition"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                            </a>
                        </div>
                    @endforeach
                </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Best Selling  -->
    <div id="section_best_selling">

    </div>

    <!-- New Products -->
    <div id="section_newest">

    </div>

    <!-- Banner Section 3 -->
    @php $homeBanner3Images = get_setting('home_banner3_images', null, $lang);   @endphp
    @if ($homeBanner3Images != null)
        <div class="mb-2 mb-md-3 mt-2 mt-md-3">
            <div class="container">
                @php
                    $banner_3_imags = json_decode($homeBanner3Images);
                    $data_md = count($banner_3_imags) >= 2 ? 2 : 1;
                    $home_banner3_links = get_setting('home_banner3_links', null, $lang);
                @endphp
                <div class="aiz-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15"
                    data-items="{{ count($banner_3_imags) }}" data-xxl-items="{{ count($banner_3_imags) }}"
                    data-xl-items="{{ count($banner_3_imags) }}" data-lg-items="{{ $data_md }}"
                    data-md-items="{{ $data_md }}" data-sm-items="1" data-xs-items="1" data-arrows="true"
                    data-dots="false">
                    @foreach ($banner_3_imags as $key => $value)
                        <div class="carousel-box overflow-hidden hov-scale-img">
                            <a href="{{ isset(json_decode($home_banner3_links, true)[$key]) ? json_decode($home_banner3_links, true)[$key] : '' }}"
                                class="d-block text-reset rounded-2 overflow-hidden">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                                    class="img-fluid lazyload w-100 has-transition"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Auction Product -->
    @if (addon_is_activated('auction'))
        <div id="auction_products">

        </div>
    @endif

    <!-- Coupon Section -->
    @if (get_setting('coupon_system') == 1)
        <!-- Desktop Coupon Section -->
        <div class="d-none d-lg-block" style="background-color: {{ get_setting('cupon_background_color', '#fff9ed') }}">
            <div class="container">
                <div class="position-relative py-5">
                    <div class="text-center text-xl-left position-relative z-5">
                        <div class="d-lg-flex">
                            <div class="mb-3 mb-lg-0">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="109.602" height="93.34" viewBox="0 0 109.602 93.34">
                                    <defs>
                                      <clipPath id="clip-path">
                                        <path id="Union_10" data-name="Union 10" d="M12263,13778v-15h64v-41h12v56Z" transform="translate(-11966 -8442.865)" fill="none" stroke="var(--{{ get_setting('cupon_text_color') }})" stroke-width="2"/>
                                      </clipPath>
                                    </defs>
                                    <g id="Group_25375" data-name="Group 25375" transform="translate(-274.201 -5254.611)">
                                      <g id="Mask_Group_23" data-name="Mask Group 23" transform="translate(-3652.459 1785.452) rotate(-45)" clip-path="url(#clip-path)">
                                        <g id="Group_24322" data-name="Group 24322" transform="translate(207 18.136)">
                                          <g id="Subtraction_167" data-name="Subtraction 167" transform="translate(-12177 -8458)" fill="none">
                                            <path d="M12335,13770h-56a8.009,8.009,0,0,1-8-8v-8a8,8,0,0,0,0-16v-8a8.009,8.009,0,0,1,8-8h56a8.009,8.009,0,0,1,8,8v8a8,8,0,0,0,0,16v8A8.009,8.009,0,0,1,12335,13770Z" stroke="none"/>
                                            <path d="M 12335.0009765625 13768.0009765625 C 12338.3095703125 13768.0009765625 12341.0009765625 13765.30859375 12341.0009765625 13762 L 12341.0009765625 13755.798828125 C 12336.4423828125 13754.8701171875 12333.0009765625 13750.8291015625 12333.0009765625 13746 C 12333.0009765625 13741.171875 12336.4423828125 13737.130859375 12341.0009765625 13736.201171875 L 12341.0009765625 13729.9990234375 C 12341.0009765625 13726.6904296875 12338.3095703125 13723.9990234375 12335.0009765625 13723.9990234375 L 12278.9990234375 13723.9990234375 C 12275.6904296875 13723.9990234375 12272.9990234375 13726.6904296875 12272.9990234375 13729.9990234375 L 12272.9990234375 13736.201171875 C 12277.5576171875 13737.1298828125 12280.9990234375 13741.1708984375 12280.9990234375 13746 C 12280.9990234375 13750.828125 12277.5576171875 13754.869140625 12272.9990234375 13755.798828125 L 12272.9990234375 13762 C 12272.9990234375 13765.30859375 12275.6904296875 13768.0009765625 12278.9990234375 13768.0009765625 L 12335.0009765625 13768.0009765625 M 12335.0009765625 13770.0009765625 L 12278.9990234375 13770.0009765625 C 12274.587890625 13770.0009765625 12270.9990234375 13766.412109375 12270.9990234375 13762 L 12270.9990234375 13754 C 12275.4111328125 13753.9990234375 12278.9990234375 13750.4111328125 12278.9990234375 13746 C 12278.9990234375 13741.5888671875 12275.41015625 13738 12270.9990234375 13738 L 12270.9990234375 13729.9990234375 C 12270.9990234375 13725.587890625 12274.587890625 13721.9990234375 12278.9990234375 13721.9990234375 L 12335.0009765625 13721.9990234375 C 12339.412109375 13721.9990234375 12343.0009765625 13725.587890625 12343.0009765625 13729.9990234375 L 12343.0009765625 13738 C 12338.5888671875 13738.0009765625 12335.0009765625 13741.5888671875 12335.0009765625 13746 C 12335.0009765625 13750.4111328125 12338.58984375 13754 12343.0009765625 13754 L 12343.0009765625 13762 C 12343.0009765625 13766.412109375 12339.412109375 13770.0009765625 12335.0009765625 13770.0009765625 Z" stroke="none" fill="var(--{{ get_setting('cupon_text_color') }})"/>
                                          </g>
                                        </g>
                                      </g>
                                      <g id="Group_24321" data-name="Group 24321" transform="translate(-3514.477 1653.317) rotate(-45)">
                                        <g id="Subtraction_167-2" data-name="Subtraction 167" transform="translate(-12177 -8458)" fill="none">
                                          <path d="M12335,13770h-56a8.009,8.009,0,0,1-8-8v-8a8,8,0,0,0,0-16v-8a8.009,8.009,0,0,1,8-8h56a8.009,8.009,0,0,1,8,8v8a8,8,0,0,0,0,16v8A8.009,8.009,0,0,1,12335,13770Z" stroke="none"/>
                                          <path d="M 12335.0009765625 13768.0009765625 C 12338.3095703125 13768.0009765625 12341.0009765625 13765.30859375 12341.0009765625 13762 L 12341.0009765625 13755.798828125 C 12336.4423828125 13754.8701171875 12333.0009765625 13750.8291015625 12333.0009765625 13746 C 12333.0009765625 13741.171875 12336.4423828125 13737.130859375 12341.0009765625 13736.201171875 L 12341.0009765625 13729.9990234375 C 12341.0009765625 13726.6904296875 12338.3095703125 13723.9990234375 12335.0009765625 13723.9990234375 L 12278.9990234375 13723.9990234375 C 12275.6904296875 13723.9990234375 12272.9990234375 13726.6904296875 12272.9990234375 13729.9990234375 L 12272.9990234375 13736.201171875 C 12277.5576171875 13737.1298828125 12280.9990234375 13741.1708984375 12280.9990234375 13746 C 12280.9990234375 13750.828125 12277.5576171875 13754.869140625 12272.9990234375 13755.798828125 L 12272.9990234375 13762 C 12272.9990234375 13765.30859375 12275.6904296875 13768.0009765625 12278.9990234375 13768.0009765625 L 12335.0009765625 13768.0009765625 M 12335.0009765625 13770.0009765625 L 12278.9990234375 13770.0009765625 C 12274.587890625 13770.0009765625 12270.9990234375 13766.412109375 12270.9990234375 13762 L 12270.9990234375 13754 C 12275.4111328125 13753.9990234375 12278.9990234375 13750.4111328125 12278.9990234375 13746 C 12278.9990234375 13741.5888671875 12275.41015625 13738 12270.9990234375 13738 L 12270.9990234375 13729.9990234375 C 12270.9990234375 13725.587890625 12274.587890625 13721.9990234375 12278.9990234375 13721.9990234375 L 12335.0009765625 13721.9990234375 C 12339.412109375 13721.9990234375 12343.0009765625 13725.587890625 12343.0009765625 13729.9990234375 L 12343.0009765625 13738 C 12338.5888671875 13738.0009765625 12335.0009765625 13741.5888671875 12335.0009765625 13746 C 12335.0009765625 13750.4111328125 12338.58984375 13754 12343.0009765625 13754 L 12343.0009765625 13762 C 12343.0009765625 13766.412109375 12339.412109375 13770.0009765625 12335.0009765625 13770.0009765625 Z" stroke="none" fill="var(--{{ get_setting('cupon_text_color') }})"/>
                                        </g>
                                        <g id="Group_24325" data-name="Group 24325">
                                          <rect id="Rectangle_18578" data-name="Rectangle 18578" width="8" height="2" transform="translate(120 5287)" fill="var(--{{ get_setting('cupon_text_color') }})"/>
                                          <rect id="Rectangle_18579" data-name="Rectangle 18579" width="8" height="2" transform="translate(132 5287)" fill="var(--{{ get_setting('cupon_text_color') }})"/>
                                          <rect id="Rectangle_18581" data-name="Rectangle 18581" width="8" height="2" transform="translate(144 5287)" fill="var(--{{ get_setting('cupon_text_color') }})"/>
                                          <rect id="Rectangle_18580" data-name="Rectangle 18580" width="8" height="2" transform="translate(108 5287)" fill="var(--{{ get_setting('cupon_text_color') }})"/>
                                        </g>
                                      </g>
                                    </g>
                                </svg>
                            </div>
                            <div class="ml-lg-3">
                                <h5 class="fs-36 fw-700 text-{{ get_setting('cupon_text_color') }} mb-3">{{ translate(get_setting('cupon_title')) }}</h5>
                                <h5 class="fs-20 fw-400 text-{{ get_setting('cupon_text_color') }}">{{ translate(get_setting('cupon_subtitle')) }}</h5>
                                <div class="mt-5 pt-5">
                                    <a href="{{ route('coupons.all') }}" class="btn btn-secondary rounded-2 fs-16 px-4"
                                        style="box-shadow: 0px 20px 30px rgba(0, 0, 0, 0.16);">{{ translate('View All Coupons') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="position-absolute right-0 bottom-0 h-100">
                        <img class="img-fit h-100" src="{{ uploaded_asset(get_setting('coupon_background_image', null, $lang)) }}"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/coupon.svg') }}';"
                            alt="{{ env('APP_NAME') }} promo">
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Mobile Coupon Section -->
        @php
            $coupons = \App\Models\Coupon::where('start_date', '<=', strtotime(date('d-m-Y')))
                ->where('end_date', '>=', strtotime(date('d-m-Y')))
                ->orderBy('discount', 'desc')
                ->take(4)
                ->get();
            
            $top_discount = $coupons->isNotEmpty() ? $coupons->first() : null;
            $discount_text = $top_discount ? ($top_discount->discount_type == 'percent' ? $top_discount->discount . '%' : 'Up to ' . single_price($top_discount->discount)) : '30%';
        @endphp
        
        <div class="d-block d-lg-none mobile-coupon-section">
            <div class="mobile-coupon-container">
                <!-- Main Coupon Banner -->
                    <div class="mobile-coupon-banner">
                        <div class="coupon-banner-content">
                            <h3 class="coupon-banner-title">{{ translate(get_setting('cupon_title', 'Exclusive Coupons')) }}</h3>
                            <p class="coupon-banner-subtitle">{{ translate(get_setting('cupon_subtitle', 'Grab amazing discounts')) }}</p>
                        
                        <div class="coupon-banner-decoration">
                            <!-- Animated circles -->
                            <div class="decoration-circle circle-1"></div>
                            <div class="decoration-circle circle-2"></div>
                            <div class="decoration-circle circle-3"></div>
                            
                            <!-- Coupon SVG Icon -->
                            <svg class="coupon-icon" viewBox="0 0 100 100" fill="none">
                                <path d="M30 20C30 8.954 38.954 0 50 0H70C81.046 0 90 8.954 90 20V80C90 91.046 81.046 100 70 100H50C38.954 100 30 91.046 30 80V20Z" 
                                      fill="rgba(255,255,255,0.2)"/>
                                <path d="M40 50C40 45.581 43.581 42 48 42C52.419 42 56 45.581 56 50C56 54.419 52.419 58 48 58C43.581 58 40 54.419 40 50Z" 
                                      fill="#fff"/>
                                <path d="M46 47H50V53" stroke="#fa3e00" stroke-width="2" stroke-linecap="round"/>
                                <path d="M50 53L54 47" stroke="#fa3e00" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="50" cy="50" r="2" fill="#fa3e00"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Active Coupons List -->
                    <div class="mobile-coupon-list">
                        <div class="coupon-list-header">
                            <h4 class="coupon-list-title">{{ translate('Active Coupons') }}</h4>
                            <span class="coupon-count">{{ $coupons->count() }} {{ translate('available') }}</span>
                        </div>
                        
                        @if($coupons->isNotEmpty())
                            @foreach($coupons as $coupon)
                            <div class="coupon-item" data-id="{{ $coupon->id }}">
                                <div class="coupon-item-left">
                                    <div class="coupon-discount-badge small">
                                        @if($coupon->discount_type == 'percent')
                                            {{ $coupon->discount }}%
                                        @else
                                            {{ single_price($coupon->discount) }}
                                        @endif
                                        <span>{{ translate('OFF') }}</span>
                                    </div>
                                    <div class="coupon-details">
                                        <div class="coupon-code">
                                            <span class="code-label">{{ translate('Code:') }}</span>
                                            <span class="code-value">{{ $coupon->code }}</span>
                                        </div>
                                        @if($coupon->min_buy > 0)
                                        <div class="coupon-minimum">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="#666">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                            {{ translate('Min. purchase') }} {{ single_price($coupon->min_buy) }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <button class="copy-coupon-btn" data-code="{{ $coupon->code }}">
                                    {{ translate('COPY') }}
                                </button>
                            </div>
                            @endforeach
                        @else
                            <div class="no-coupons-message">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="#999">
                                    <path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-4.86 8.86l-3 3.87L9 13.14 6 17h12l-3.86-5.14z"/>
                                </svg>
                                <p>{{ translate('No active coupons available at the moment') }}</p>
                            </div>
                        @endif
                        
                        @if($coupons->isNotEmpty())
                        <div class="view-all-coupons-link">
                            <a href="{{ route('coupons.all') }}">
                                {{ translate('View all coupons') }}
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="#fa3e00">
                                    <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                                </svg>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Category wise Products -->
    <div id="section_home_categories">

    </div>


    @if (addon_is_activated('preorder'))
        <!-- Newest Preorder Products -->
        @include('preorder.frontend.home_page.newest_preorder')
    @endif


    <!-- Classified Product -->
    @if (get_setting('classified_product') == 1)
        @php
            $classified_products = get_home_page_classified_products(6);
            $classified_section_bg = get_setting('classified_section_bg_color');
        @endphp
        @if (count($classified_products) > 0)
            <section class="mb-2 mb-md-3 mt-2rem">
                <div class="container">
                    <div class="p-3 p-md-2rem rounded-2 overflow-hidden @if(get_setting('classified_section_outline') == 1) border @endif"
                        style="background: {{ $classified_section_bg != null ? $classified_section_bg : '#fff9ed' }}; border-color: {{ get_setting('classified_section_outline_color') }} !important;">
                        <!-- Top Section -->
                        <div class="d-flex mb-2 mb-md-3 align-items-baseline justify-content-between">
                            <!-- Title -->
                            <h3 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">
                                <span class="">{{ translate('Classified Ads') }}</span>
                            </h3>
                            <!-- Links -->
                            <div class="d-flex">
                                <a class="text-blue fs-10 fs-md-12 fw-700 hov-text-primary animate-underline-primary"
                                    href="{{ route('customer.products') }}">{{ translate('View All Products') }}</a>
                            </div>
                        </div>
                        <!-- Banner -->
                        @php
                            $classifiedBannerImage = get_setting('classified_banner_image', null, $lang);
                            $classifiedBannerImageSmall = get_setting('classified_banner_image_small', null, $lang);
                        @endphp
                        @if ($classifiedBannerImage != null || $classifiedBannerImageSmall != null)
                            <div class="mb-3 rounded-2 overflow-hidden hov-scale-img d-none d-md-block">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ uploaded_asset($classifiedBannerImage) }}"
                                    alt="{{ env('APP_NAME') }} promo" class="lazyload img-fit h-100 has-transition"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                            </div>
                            <div class="mb-3 rounded-2 overflow-hidden hov-scale-img d-md-none">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ $classifiedBannerImageSmall != null ? uploaded_asset($classifiedBannerImageSmall) : uploaded_asset($classifiedBannerImage) }}"
                                    alt="{{ env('APP_NAME') }} promo" class="lazyload img-fit h-100 has-transition"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                            </div>
                        @endif
                        <!-- Products Section -->
                        <div class="">
                            <div class="row gutters-16">
                                @foreach ($classified_products as $key => $classified_product)
                                    <div
                                        class="col-xxl-4 col-md-6 has-transition hov-shadow-out z-1">
                                        <div class="aiz-card-box py-2 has-transition">
                                            <div class="row hov-scale-img">
                                                <div class="col-4 col-md-5 mb-3 mb-md-0">
                                                    <a href="{{ route('customer.product', $classified_product->slug) }}"
                                                        class="d-block rounded-2 overflow-hidden h-auto h-md-150px text-center">
                                                        <img class="img-fluid lazyload mx-auto has-transition"
                                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                            data-src="{{ isset($classified_product->thumbnail->file_name) ? my_asset($classified_product->thumbnail->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                                            alt="{{ $classified_product->getTranslation('name') }}"
                                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                    </a>
                                                </div>
                                                <div class="col py-2">
                                                    <h3
                                                        class="fw-400 fs-14 text-dark text-truncate-2 lh-1-4 mb-3 h-35px d-none d-sm-block">
                                                        <a href="{{ route('customer.product', $classified_product->slug) }}"
                                                            class="d-block text-reset hov-text-primary">{{ $classified_product->getTranslation('name') }}</a>
                                                    </h3>
                                                    <div class="d-md-flex d-lg-block justify-content-between">
                                                        <div class="fs-14 mb-3">
                                                            <span
                                                                class="text-secondary">{{ $classified_product->user ? $classified_product->user->name : '' }}</span><br>
                                                            <span
                                                                class="fw-700 text-primary">{{ single_price($classified_product->unit_price) }}</span>
                                                        </div>
                                                        @if ($classified_product->conditon == 'new')
                                                            <span
                                                                class="badge badge-md badge-inline badge-soft-info fs-13 fw-700 px-3 text-info"
                                                                style="border-radius: 20px;">{{ translate('New') }}</span>
                                                        @elseif($classified_product->conditon == 'used')
                                                            <span
                                                                class="badge badge-md badge-inline badge-soft-danger fs-13 fw-700 px-3 text-danger"
                                                                style="border-radius: 20px;">{{ translate('Used') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    @endif
    
    <!-- Products Section -->
    <section id="products-section" class="my-5">
        <div class="container">
            <div class="border rounded-3 p-4 bg-white shadow-sm">
                <!-- Section Header with View All Button -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <h2 class="section-title mb-0 fs-20 fw-700 text-dark">
                        {{ translate('All Products') }}
                    </h2>
                    <a href="{{ route('inhouse.all') }}" 
                       class="btn btn-outline-primary btn-sm fs-12 fw-600 px-3 py-1">
                        {{ translate('View All') }}
                        <i class="las la-arrow-right ml-1"></i>
                    </a>
                </div>
                
                @php
                    use App\Models\Product;
                @endphp
        
                <div id="new_products" class="row g-4">
                    @php
                        $initial_products = Cache::remember('new_products_36', 3600, function () {
                            return filter_products(Product::latest())->take(36)->get();
                        });
                    @endphp
                
                    @include('frontend.' . get_setting('homepage_select') . '.partials.new_products_section', ['new_products' => $initial_products])
                </div>
        
                <!-- Load More Button -->
                <div class="text-center mt-4 pt-3 border-top">
                    <button type="button"
                        class="btn btn-primary btn-md fs-14 fw-600 px-5 py-2"
                        id="view-more-btn">
                        {{ translate('Load More') }}
                        <i id="spinner-icon" class="las la-lg la-spinner la-spin d-none ml-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>



    <!-- Top Sellers -->
    @if (get_setting('vendor_system_activation') == 1)
        @php
            $best_selers = get_best_sellers(6);
            $sellers_section_bg = get_setting('sellers_section_bg_color');
        @endphp
        @if (count($best_selers) > 0)
        <section class="mb-2 mb-md-3 mt-2 mt-md-3">
            <div class="container">
                <div class="p-3 p-md-2rem rounded-2 @if(get_setting('sellers_section_outline') == 1) border @endif"
                    style="background: {{ $sellers_section_bg != null ? $sellers_section_bg : '#fff9ed' }}; border-color: {{ get_setting('sellers_section_outline_color') }} !important; padding-bottom: 1rem !important;">
                    <!-- Top Section -->
                    <div class="d-flex mb-2 mb-md-3 align-items-baseline justify-content-between">
                        <!-- Title -->
                        <h3 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">
                            <span class="pb-3">{{ translate('Top Sellers') }}</span>
                        </h3>
                        <!-- Links -->
                        <div class="d-flex">
                            <a class="text-blue fs-10 fs-md-12 fw-700 hov-text-primary animate-underline-primary"
                                href="{{ route('sellers') }}">{{ translate('View All Sellers') }}</a>
                        </div>
                    </div>
                    <!-- Sellers Section -->
                    <div class="row gutters-16">
                        @foreach ($best_selers as $key => $seller)
                        <div class="col-xl-4 col-md-6 py-3 py-md-4 has-transition hov-shadow-out z-1">
                            <div class="d-flex align-items-center">
                                <!-- Shop logo & Verification Status -->
                                <div class="position-relative">
                                    <a href="{{ route('shop.visit', $seller->slug) }}"
                                        class="d-block mx-auto size-100px size-lg-120px border overflow-hidden hov-scale-img"
                                        tabindex="0"
                                        style="border: 1px solid #e5e5e5; border-radius: 50%; box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.06);">
                                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                            data-src="{{ uploaded_asset($seller->logo) }}" alt="{{ $seller->name }}"
                                            class="img-fit h-100 lazyload has-transition"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                    </a>
                                    <div class="absolute-top-left z-1 ml-2 mt-1 rounded-content bg-white">
                                        @if ($seller->verification_status == 1)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24.001" height="24"
                                                viewBox="0 0 24.001 24">
                                                <g id="Group_25929" data-name="Group 25929"
                                                    transform="translate(-480 -345)">
                                                    <circle id="Ellipse_637" data-name="Ellipse 637" cx="12"
                                                        cy="12" r="12" transform="translate(480 345)"
                                                        fill="#fff" />
                                                    <g id="Group_25927" data-name="Group 25927"
                                                        transform="translate(480 345)">
                                                        <path id="Union_5" data-name="Union 5"
                                                            d="M0,12A12,12,0,1,1,12,24,12,12,0,0,1,0,12Zm1.2,0A10.8,10.8,0,1,0,12,1.2,10.812,10.812,0,0,0,1.2,12Zm1.2,0A9.6,9.6,0,1,1,12,21.6,9.611,9.611,0,0,1,2.4,12Zm5.115-1.244a1.083,1.083,0,0,0,0,1.529l3.059,3.059a1.081,1.081,0,0,0,1.529,0l5.1-5.1a1.084,1.084,0,0,0,0-1.53,1.081,1.081,0,0,0-1.529,0L11.339,13.05,9.045,10.756a1.082,1.082,0,0,0-1.53,0Z"
                                                            transform="translate(0 0)" fill="#85b567" />
                                                    </g>
                                                </g>
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24.001" height="24"
                                                viewBox="0 0 24.001 24">
                                                <g id="Group_25929" data-name="Group 25929"
                                                    transform="translate(-480 -345)">
                                                    <circle id="Ellipse_637" data-name="Ellipse 637" cx="12"
                                                        cy="12" r="12" transform="translate(480 345)"
                                                        fill="#fff" />
                                                    <g id="Group_25927" data-name="Group 25927"
                                                        transform="translate(480 345)">
                                                        <path id="Union_5" data-name="Union 5"
                                                            d="M0,12A12,12,0,1,1,12,24,12,12,0,0,1,0,12Zm1.2,0A10.8,10.8,0,1,0,12,1.2,10.812,10.812,0,0,0,1.2,12Zm1.2,0A9.6,9.6,0,1,1,12,21.6,9.611,9.611,0,0,1,2.4,12Zm5.115-1.244a1.083,1.083,0,0,0,0,1.529l3.059,3.059a1.081,1.081,0,0,0,1.529,0l5.1-5.1a1.084,1.084,0,0,0,0-1.53,1.081,1.081,0,0,0-1.529,0L11.339,13.05,9.045,10.756a1.082,1.082,0,0,0-1.53,0Z"
                                                            transform="translate(0 0)" fill="red" />
                                                    </g>
                                                </g>
                                            </svg>
                                        @endif
                                    </div>
                                </div>

                                <div class="ml-2 ml-lg-4">
                                    <!-- Shop name -->
                                    <h2 class="fs-14 fw-700 text-dark text-truncate-2 mb-1">
                                        <a href="{{ route('shop.visit', $seller->slug) }}"
                                            class="text-reset hov-text-primary" tabindex="0">{{ $seller->name }}</a>
                                    </h2>
                                    <!-- Shop Rating -->
                                    <div class="rating rating-mr-1 text-dark mb-2">
                                        {{ renderStarRating($seller->rating) }}
                                        <span class="opacity-60 fs-14">({{ $seller->num_of_reviews }}
                                            {{ translate('Reviews') }})</span>
                                    </div>
                                    <!-- Visit Button -->
                                    <a href="{{ route('shop.visit', $seller->slug) }}" class="visite-btn">
                                        <span class="button-text">{{ ucfirst(translate('Visit Store')) }}</span>
                                        <span class="icon-arrow"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        @endif
    @endif

    <!-- Top Brands -->
    @if (get_setting('top_brands') != null)
        @php
            $brands_section_bg = get_setting('brands_section_bg_color');
        @endphp
        <section class="mb-2 mb-md-3 mt-2 mt-md-3 pb-2 pb-md-3">
            <div class="container">
                <div class="p-3 p-md-2rem rounded-2 @if(get_setting('brands_section_outline') == 1) border @endif"
                    style="background: {{ $brands_section_bg != null ? $brands_section_bg : '#f0f2f5' }}; border-color: {{ get_setting('brands_section_outline_color') }} !important; padding-bottom: 1rem !important;">
                    <!-- Top Section -->
                    <div class="d-flex mb-2 mb-md-3 align-items-baseline justify-content-between">
                        <!-- Title -->
                        <h3 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">{{ translate('Top Brands') }}</h3>
                        <!-- Links -->
                        <div class="d-flex">
                            <a class="text-blue fs-10 fs-md-12 fw-700 hov-text-primary animate-underline-primary"
                                href="{{ route('brands.all') }}">{{ translate('View All Brands') }}</a>
                        </div>
                    </div>
                    <!-- Brands Section -->
                    <div class="row gutters-16">
                        @php
                            $top_brands = json_decode(get_setting('top_brands'));
                            $brands = get_brands($top_brands);
                        @endphp
                        @foreach ($brands as $brand)
                            <div class="col-xl-3 col-lg-4 col-6 my-3">
                                <a href="{{ route('products.brand', $brand->slug) }}" class="d-block has-transition hov-shadow-out z-1 hov-scale-img rounded-2 overflow-hidden">
                                    <span class="d-flex flex-column flex-sm-row align-items-center">
                                        <span class="d-flex align-items-center bg-white size-80px p-2 rounded-2 overflow-hidden">
                                            <img src="{{ $brand->logo != null ? uploaded_asset($brand->logo) : static_asset('assets/img/placeholder.jpg') }}"
                                            class="lazyload w-100 has-transition"
                                            alt="{{ $brand->getTranslation('name') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                        </span>
                                        <span class="text-center text-dark fs-12 fs-md-14 fw-700 mt-2 mt-sm-0 ml-sm-4">
                                            {{ $brand->getTranslation('name') }}
                                        </span>
                                    </span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection

@section('script')
<script>
let page = 1;

$(document).ready(function() {

    // Load More Button Click
    $(document).on('click', '#view-more-btn', function() {
        const $button = $(this);
        const originalText = $button.html(); 

        page++;

        // Show loading spinner
        $button.html('{{ translate("Loading...") }} <i id="spinner-icon" class="las la-lg la-spinner la-spin ml-2"></i>');
        $button.prop('disabled', true); 

        $.post('{{ route('home.section.new_products') }}', {
            _token: '{{ csrf_token() }}',
            page: page
        }, function(data) {
            $button.prop('disabled', false);
            $button.html(originalText);

            if ($.trim(data) === '') {
                $button.prop('disabled', true).text('{{ translate("No More Products") }}');
            } else {
                $('#new_products').append(data); // inject into correct div
                if (typeof AIZ !== 'undefined' && AIZ.plugins.slickCarousel) {
                    AIZ.plugins.slickCarousel(); // re-init carousel if needed
                }
            }
        }).fail(function() {
            $button.prop('disabled', false);
            $button.html('{{ translate("Error, Try Again") }} <i id="spinner-icon" class="las la-lg la-spinner la-spin d-none ml-2"></i>');
        });
    });

    // Optional: styling hot-category boxes on page load
    $(window).on('load', function() {
        $('.hot-category-box').addClass('d-flex flex-column justify-content-center align-items-center');
    });

    // Toggle View More Button visibility based on content
    function toggleViewMoreButton() {
        if ($.trim($('#new_products').html()).length > 0) {
            $('#view-more-btn').closest('.text-center').removeClass('d-none').addClass('d-block');
        } else {
            $('#view-more-btn').closest('.text-center').removeClass('d-block').addClass('d-none');
        }
    }

    toggleViewMoreButton(); // initial check
});
</script>

<script>
    
    $(document).ready(function() {
    // Copy coupon code functionality
    $(document).on('click', '.copy-coupon-btn', function() {
        const couponCode = $(this).data('code');
        const $btn = $(this);
        const originalText = $btn.text();
        
        // Copy to clipboard
        navigator.clipboard.writeText(couponCode).then(() => {
            // Update button
            $btn.html('✓ ' + '{{ translate("COPIED") }}');
            $btn.css({
                'background': 'linear-gradient(135deg, #2ed573 0%, #1e90ff 100%)'
            });
            
            // Show success message
            $('body').append(`
                <div class="coupon-copied-message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    {{ translate("Coupon code") }} <strong>${couponCode}</strong> {{ translate("copied!") }}
                </div>
            `);
            
            // Remove message after delay
            setTimeout(() => {
                $('.coupon-copied-message').remove();
            }, 2500);
            
            // Reset button after 2 seconds
            setTimeout(() => {
                $btn.html(originalText);
                $btn.css({
                    'background': 'linear-gradient(135deg, #fa3e00 0%, #ff6b35 100%)'
                });
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            $btn.html('{{ translate("ERROR") }}');
            setTimeout(() => {
                $btn.html(originalText);
            }, 2000);
        });
    });
});
</script>

<script>
    $(document).ready(function() {
        // Initialize mobile countdown if not already initialized
        @if ($flash_deal != null)
        if ($(window).width() < 992) {
            $('.flash-deal-header-mobile .aiz-count-down').each(function() {
                if (!$(this).hasClass('initialized')) {
                    $(this).addClass('mobile-countdown initialized');
                    // The existing aiz-count-down plugin will handle the rest
                }
            });
        }
        @endif
    });
</script>
@endsection