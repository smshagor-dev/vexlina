@extends('pickup_points.layouts.app')

@section('panel_content')
<style>
    .pickup-dashboard-shell {
        position: relative;
        width: 100%;
    }
    .pickup-dashboard-panel {
        border: 1px solid #e7ecf3;
        border-radius: 28px;
        padding: 22px;
        background:
            radial-gradient(circle at top right, rgba(250, 62, 0, .06), transparent 20%),
            linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        box-shadow: 0 24px 60px rgba(15, 23, 42, .06);
    }
    .pickup-dashboard-shell {
        position: relative;
    }
    .pickup-dashboard-loader {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 240px;
        border: 1px solid #eaecf0;
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    .pickup-dashboard-loader__card {
        text-align: center;
    }
    .pickup-dashboard-loader__spinner {
        width: 44px;
        height: 44px;
        margin: 0 auto 14px;
        border-radius: 50%;
        border: 3px solid #e5e7eb;
        border-top-color: #fa3e00;
        animation: pickup-dashboard-spin .8s linear infinite;
    }
    .pickup-dashboard-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 22px;
    }
    .pickup-dashboard-toolbar__meta {
        color: #667085;
        font-size: 13px;
        margin-bottom: 0;
    }
    .pickup-dashboard-toolbar__title {
        font-size: 24px;
        font-weight: 800;
        color: #101828;
        margin-bottom: 4px;
    }
    .pickup-dashboard-toolbar__subtext {
        color: #667085;
        font-size: 13px;
        margin-bottom: 0;
    }
    .pickup-dashboard-refresh {
        border-radius: 999px;
        padding: 11px 20px;
        font-weight: 700;
        border: 0;
        box-shadow: 0 12px 24px rgba(250, 62, 0, .18);
    }
    .pickup-dashboard-error {
        border: 1px solid rgba(239, 68, 68, .18);
        border-radius: 18px;
        background: #fff5f5;
        color: #b42318;
        padding: 18px;
    }
    @keyframes pickup-dashboard-spin {
        to {
            transform: rotate(360deg);
        }
    }
    .pickup-dashboard-hero {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background:
            radial-gradient(circle at top left, rgba(255,255,255,.14), transparent 26%),
            linear-gradient(135deg, #101828 0%, #182230 45%, #243145 100%);
        box-shadow: 0 22px 48px rgba(15, 23, 42, .16);
    }
    .pickup-dashboard-hero__eyebrow {
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(255,255,255,.72);
        font-size: 11px;
        font-weight: 700;
    }
    .pickup-dashboard-hero__title {
        color: #fff;
        font-size: 34px;
        font-weight: 800;
        margin-bottom: 8px;
        line-height: 1.12;
    }
    .pickup-dashboard-hero__meta {
        color: rgba(255,255,255,.74);
        margin-bottom: 0;
    }
    .pickup-dashboard-hero__pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        color: rgba(255,255,255,.9);
        font-size: 12px;
        font-weight: 700;
    }
    .pickup-earning-card {
        border: 0;
        border-radius: 22px;
        color: #fff;
        padding: 18px 20px;
        min-height: 100%;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.12), 0 16px 36px rgba(15, 23, 42, .10);
    }
    .pickup-earning-card__label {
        font-size: 12px;
        font-weight: 700;
        opacity: .9;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .pickup-earning-card__row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 6px;
        color: rgba(255,255,255,.88);
        font-size: 13px;
    }
    .pickup-earning-card__row strong {
        color: #fff;
        font-size: 14px;
    }
    .pickup-earning-card__headline {
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 10px;
        color: #fff;
        line-height: 1.2;
    }
    .pickup-earning-card__total {
        border-top: 1px solid rgba(255,255,255,.2);
        margin-top: 10px;
        padding-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-weight: 800;
        font-size: 13px;
    }
    .pickup-earning-card--today { background: linear-gradient(135deg, #fa3e00 0%, #ff7b38 100%); }
    .pickup-earning-card--week { background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); }
    .pickup-earning-card--month { background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%); }
    .pickup-earning-card--total { background: linear-gradient(135deg, #111827 0%, #374151 100%); }
    .pickup-stat-card {
        display: block;
        border-radius: 20px;
        padding: 22px;
        min-height: 100%;
        color: #fff !important;
        text-decoration: none !important;
        box-shadow: 0 16px 34px rgba(15, 23, 42, .10);
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .pickup-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 40px rgba(15, 23, 42, .14);
    }
    .pickup-stat-card::after {
        content: '';
        position: absolute;
        inset: auto -10% -20px auto;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }
    .pickup-stat-card p {
        margin-bottom: 10px;
        font-size: 14px;
        font-weight: 600;
        color: rgba(255,255,255,.88);
    }
    .pickup-stat-card h4 {
        margin-bottom: 0;
        font-size: 28px;
        font-weight: 800;
        color: #fff;
    }
    .pickup-station-card {
        border: 1px solid #eaecf0;
        border-radius: 22px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
        background: #fff;
    }
    .pickup-station-card__title {
        font-size: 18px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 10px;
    }
    .pickup-station-card__grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
    .pickup-station-card__meta {
        border-radius: 18px;
        padding: 16px;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border: 1px solid #edf2f7;
    }
    .pickup-station-card__label {
        color: #667085;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 8px;
    }
    .pickup-station-card__line {
        color: #101828;
        margin-bottom: 0;
        font-weight: 600;
        line-height: 1.5;
    }
    @media (max-width: 991.98px) {
        .pickup-dashboard-panel {
            padding: 16px;
            border-radius: 22px;
        }
        .pickup-dashboard-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }
        .pickup-dashboard-hero__title {
            font-size: 28px;
        }
        .pickup-station-card__grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="pickup-dashboard-shell" id="pickupDashboardShell" data-summary-url="{{ route('pickup-point.dashboard.summary') }}">
    <div class="pickup-dashboard-panel">
        <div class="pickup-dashboard-toolbar">
            <div>
                <div class="pickup-dashboard-toolbar__title">{{ translate('Pickup Point Dashboard') }}</div>
                <p class="pickup-dashboard-toolbar__subtext">{{ translate('Live overview of earnings, order stages, and pickup point activity.') }}</p>
                <p class="pickup-dashboard-toolbar__meta mt-2" id="pickupDashboardUpdatedAt">{{ translate('Loading latest dashboard data...') }}</p>
            </div>
            <button type="button" class="btn btn-primary pickup-dashboard-refresh" id="pickupDashboardRefresh">
                {{ translate('Refresh Dashboard') }}
            </button>
        </div>

        <div id="pickupDashboardContent" class="pickup-dashboard-loader">
            <div class="pickup-dashboard-loader__card">
                <div class="pickup-dashboard-loader__spinner"></div>
                <div class="fw-700 text-dark">{{ translate('Loading dashboard summary') }}</div>
                <div class="text-secondary fs-13">{{ translate('Please wait a moment...') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    (function () {
        const shell = document.getElementById('pickupDashboardShell');
        const content = document.getElementById('pickupDashboardContent');
        const updatedAt = document.getElementById('pickupDashboardUpdatedAt');
        const refreshButton = document.getElementById('pickupDashboardRefresh');

        if (!shell || !content) {
            return;
        }

        let isLoading = false;

        const setLoadingState = (loading) => {
            isLoading = loading;
            if (refreshButton) {
                refreshButton.disabled = loading;
            }
        };

        const renderError = (message) => {
            content.innerHTML = `
                <div class="pickup-dashboard-error w-100">
                    <div class="fw-700 mb-1">${message}</div>
                    <div class="fs-13">{{ translate('Try refreshing the dashboard again.') }}</div>
                </div>
            `;
        };

        const loadDashboardSummary = () => {
            if (isLoading) {
                return;
            }

            setLoadingState(true);

            $.get(shell.dataset.summaryUrl, function (response) {
                if (!response || !response.result || !response.html) {
                    renderError('{{ translate('Unable to load dashboard data right now.') }}');
                    return;
                }

                content.classList.remove('pickup-dashboard-loader');
                content.innerHTML = response.html;

                if (updatedAt) {
                    updatedAt.textContent = '{{ translate('Last updated') }}: ' + (response.generated_at || '{{ translate('Just now') }}');
                }
            }).fail(function () {
                renderError('{{ translate('Unable to load dashboard data right now.') }}');
            }).always(function () {
                setLoadingState(false);
            });
        };

        if (refreshButton) {
            refreshButton.addEventListener('click', loadDashboardSummary);
        }

        loadDashboardSummary();
        setInterval(loadDashboardSummary, 30000);
    })();
</script>
@endsection
