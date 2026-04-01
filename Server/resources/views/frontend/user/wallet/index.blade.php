@extends('frontend.layouts.user_panel')

@section('panel_content')
    @php
        $user = Auth::user();
        $user->ensureWalletCardDetails();
        $cardSeed = preg_replace('/\D/', '', $user->wallet_card_number ?? '');
        $cardSeed = str_pad(substr($cardSeed, 0, 16), 16, '8');
        $cardNumber = trim(chunk_split($cardSeed, 4, ' '));
        $expiryMonth = str_pad((string) ($user->wallet_card_expiry_month ?? ''), 2, '0', STR_PAD_LEFT);
        $expiryYear = str_pad((string) ($user->wallet_card_expiry_year ?? ''), 2, '0', STR_PAD_LEFT);
        $expiryText = $expiryMonth . '/' . $expiryYear;
        $ccv = str_pad((string) ($user->wallet_card_cvv ?? ''), 3, '0', STR_PAD_LEFT);
        $maskedCardNumber = substr($cardSeed, 0, 4) . '****' . substr($cardSeed, -4);
        $maskedExpiryText = '**/**';
        $maskedCcv = '***';
    @endphp
    <style>
        .wallet-bank-card-wrap {
            perspective: 1200px;
        }

        .wallet-bank-card {
            position: relative;
            min-height: 250px;
            height: clamp(230px, 32vw, 280px);
            transform-style: preserve-3d;
            transition: transform .55s ease;
            cursor: pointer;
        }

        .wallet-bank-card.is-flipped {
            transform: rotateY(180deg);
        }

        .wallet-bank-card__face {
            position: absolute;
            inset: 0;
            backface-visibility: hidden;
            border-radius: 28px;
            overflow: hidden;
            padding: 28px;
            box-shadow: 0 18px 40px rgba(250, 90, 30, .18);
        }

        .wallet-card-title {
            font-size: 34px;
            line-height: 1.05;
        }

        .wallet-card-number {
            font-size: 26px;
            line-height: 1.2;
            letter-spacing: 2px;
            word-spacing: 2px;
        }

        .wallet-bank-card__face--front {
            background: linear-gradient(135deg, #fa5a1e 0%, #fe8b4b 100%);
            color: #fff;
        }

        .wallet-bank-card__face--back {
            background: linear-gradient(135deg, #141b34 0%, #28345e 100%);
            color: #fff;
            transform: rotateY(180deg);
        }

        .wallet-chip {
            width: 54px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(255,255,255,.95), rgba(255,255,255,.55));
        }

        .wallet-copy-btn {
            border: 0;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
        }

        .wallet-action-card {
            border: 0;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
            min-height: 132px;
        }

        .wallet-action-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            background: rgba(250, 90, 30, .10);
            color: #fa5a1e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .wallet-transaction-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
        }

        .wallet-details-card {
            border: 0;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
        }

        .wallet-detail-box {
            border: 1px solid #eef1f5;
            border-radius: 18px;
            background: #fbfcfe;
            padding: 18px 20px;
            height: 100%;
        }

        .wallet-inline-copy {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 12px;
            background: rgba(250, 90, 30, .10);
            color: #fa5a1e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .wallet-toggle-btn {
            border: 0;
            border-radius: 999px;
            background: rgba(250, 90, 30, .12);
            color: #fa5a1e;
            padding: 9px 16px;
            font-size: 12px;
            font-weight: 700;
        }

        .wallet-transaction-badge {
            border-radius: 999px;
            min-width: 92px !important;
        }

        #wallet-qr-reader {
            width: 100%;
        }

        .wallet-actions-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        @media (max-width: 575.98px) {
            .wallet-bank-card__face {
                padding: 18px;
            }

            .wallet-bank-card {
                min-height: 212px;
                height: 212px;
            }

            .wallet-actions-row {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
            }

            .wallet-action-card {
                min-height: 116px;
                border-radius: 18px;
                padding: 16px 8px !important;
            }

            .wallet-detail-box {
                padding: 14px 16px;
                border-radius: 16px;
            }

            .wallet-action-icon {
                width: 42px;
                height: 42px;
                border-radius: 14px;
                font-size: 20px;
            }

            .wallet-action-card .fs-15 {
                font-size: 13px !important;
            }

            .wallet-action-card .fs-12 {
                font-size: 10px !important;
                line-height: 1.35;
            }

            .wallet-card-title {
                font-size: 18px !important;
            }

            .wallet-bank-card__face .fs-28 {
                font-size: 24px !important;
                margin-bottom: 10px !important;
            }

            .wallet-card-number {
                font-size: 12px !important;
                line-height: 1.35;
                letter-spacing: 1.2px;
                word-spacing: 1px;
                margin-bottom: 12px !important;
            }

            .wallet-bank-card__face .fs-14 {
                font-size: 11px !important;
            }

            .wallet-bank-card__face .fs-13 {
                font-size: 11px !important;
            }

            .wallet-bank-card__face .fs-10 {
                font-size: 9px !important;
            }

            .wallet-bank-card__face .badge {
                padding: 6px 10px !important;
                font-size: 10px !important;
                line-height: 1 !important;
            }

            .wallet-chip {
                width: 46px;
                height: 34px;
                border-radius: 10px;
            }

            .wallet-copy-btn {
                padding: 6px 10px;
                font-size: 10px;
            }

            .wallet-inline-copy {
                width: 32px;
                height: 32px;
                border-radius: 10px;
                font-size: 14px;
            }

            .wallet-bank-card__face--back .bg-dark {
                height: 40px !important;
            }

            .wallet-bank-card__face--back .bg-white {
                min-width: 126px !important;
                padding: 10px 12px !important;
            }
        }
    </style>
    <div class="aiz-titlebar mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="fs-20 fw-700 text-dark">{{ translate('My Wallet') }}</h1>
            </div>
        </div>
    </div>

    <div class="wallet-bank-card-wrap mb-4">
        <div class="wallet-bank-card" id="walletBankCard">
            <div class="wallet-bank-card__face wallet-bank-card__face--front d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-1 text-white fw-700 wallet-card-title">{{ translate('Vexlina Pay') }}</h3>
                        <div class="fs-13 opacity-80">{{ translate('Available Balance') }}</div>
                    </div>
                    <span class="badge badge-inline badge-light text-dark px-3 py-2">{{ translate('Virtual Card') }}</span>
                </div>
                <div class="mt-4 wallet-chip"></div>
                <div class="mt-auto">
                    <div class="fs-28 fw-700 text-white mb-3">{{ single_price($user->balance) }}</div>
                    <div class="fw-700 text-white mb-4 wallet-card-number wallet-secret-card-number" data-masked="{{ $maskedCardNumber }}" data-raw="{{ $cardNumber }}">{{ $maskedCardNumber }}</div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div class="pr-3">
                            <div class="fs-10 opacity-70 text-uppercase mb-1">{{ translate('Card Holder') }}</div>
                            <div class="fs-14 fw-700 text-uppercase text-truncate">{{ $user->name ?? 'VEXLINA USER' }}</div>
                        </div>
                        <button type="button" class="wallet-copy-btn" onclick="event.stopPropagation(); copyWalletCardNumber()">
                            <i class="las la-copy mr-1"></i>{{ translate('Copy') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="wallet-bank-card__face wallet-bank-card__face--back d-flex flex-column">
                <div class="bg-dark rounded-lg mt-2" style="height: 48px;"></div>
                <div class="d-flex justify-content-end mt-4">
                    <div class="bg-white text-dark rounded-lg px-4 py-3" style="min-width: 160px;">
                        <div class="fs-12 fw-700 mb-2">MM/YY <span class="wallet-secret-expiry" data-masked="{{ $maskedExpiryText }}" data-raw="{{ $expiryText }}">{{ $maskedExpiryText }}</span></div>
                        <div class="fs-12 fw-700">CCV <span class="wallet-secret-cvv" data-masked="{{ $maskedCcv }}" data-raw="{{ $ccv }}">{{ $maskedCcv }}</span></div>
                    </div>
                </div>
                <div class="mt-auto">
                    <div class="fs-12 opacity-80 mb-2">{{ translate('Tap card to flip back') }}</div>
                    <div class="fs-16 fw-700 text-white wallet-secret-card-number" data-masked="{{ $maskedCardNumber }}" data-raw="{{ $cardNumber }}" style="letter-spacing: 1.8px;">{{ $maskedCardNumber }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="wallet-actions-row mb-4">
        <div>
            <div class="wallet-action-card c-pointer p-4 text-center" onclick="openWalletQrScanner()">
                <span class="wallet-action-icon mb-3"><i class="las la-qrcode"></i></span>
                <div class="fs-15 fw-700 text-dark">{{ translate('Pay QR') }}</div>
                <div class="fs-12 text-secondary mt-1">{{ translate('Scan and pay from wallet') }}</div>
            </div>
        </div>
        <div>
            <div class="wallet-action-card c-pointer p-4 text-center" onclick="show_wallet_modal()">
                <span class="wallet-action-icon mb-3"><i class="las la-plus-circle"></i></span>
                <div class="fs-15 fw-700 text-dark">{{ translate('Add Money') }}</div>
                <div class="fs-12 text-secondary mt-1">{{ translate('Recharge your wallet balance') }}</div>
            </div>
        </div>
        <div>
            <div class="wallet-action-card c-pointer p-4 text-center" onclick="showSendMoneyModal()">
                <span class="wallet-action-icon mb-3"><i class="las la-exchange-alt"></i></span>
                <div class="fs-15 fw-700 text-dark">{{ translate('Send Money') }}</div>
                <div class="fs-12 text-secondary mt-1">{{ translate('Transfer money to another user') }}</div>
            </div>
        </div>
    </div>

    <div class="card wallet-details-card shadow-none mb-4">
        <div class="card-header border-bottom-0 pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0 fs-20 fw-700 text-dark text-center text-md-left">{{ translate('Card Details') }}</h5>
                <button type="button" class="wallet-toggle-btn mt-3 mt-md-0" id="walletSecretToggleBtn" onclick="handleWalletSecretToggle()">{{ translate('Show Details') }}</button>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="row gutters-16">
                <div class="col-md-6 mb-3">
                    <div class="wallet-detail-box d-flex justify-content-between align-items-center">
                        <div class="pr-3">
                            <div class="fs-11 text-uppercase text-secondary mb-2">{{ translate('Card Number') }}</div>
                            <div class="fs-16 fw-700 text-dark wallet-secret-card-number" data-masked="{{ $maskedCardNumber }}" data-raw="{{ $cardNumber }}" style="letter-spacing: 1.2px;">{{ $maskedCardNumber }}</div>
                        </div>
                        <button type="button" class="wallet-inline-copy" onclick="copyWalletCardNumber()">
                            <i class="las la-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="wallet-detail-box d-flex justify-content-between align-items-center">
                        <div class="pr-3">
                            <div class="fs-11 text-uppercase text-secondary mb-2">MM/YY</div>
                            <div class="fs-16 fw-700 text-dark wallet-secret-expiry" data-masked="{{ $maskedExpiryText }}" data-raw="{{ $expiryText }}">{{ $maskedExpiryText }}</div>
                        </div>
                        <button type="button" class="wallet-inline-copy" onclick="copyWalletExpiry()">
                            <i class="las la-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="wallet-detail-box d-flex justify-content-between align-items-center">
                        <div class="pr-3">
                            <div class="fs-11 text-uppercase text-secondary mb-2">CVV</div>
                            <div class="fs-16 fw-700 text-dark wallet-secret-cvv" data-masked="{{ $maskedCcv }}" data-raw="{{ $ccv }}">{{ $maskedCcv }}</div>
                        </div>
                        <button type="button" class="wallet-inline-copy" onclick="copyWalletCvv()">
                            <i class="las la-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (addon_is_activated('offline_payment'))
        <div class="alert alert-soft-info mb-4">
            {{ translate('Offline recharge is still available from the existing offline recharge modal.') }}
        </div>
    @endif

    <div class="card wallet-transaction-card shadow-none">
        <div class="card-header border-bottom-0">
            <h5 class="mb-0 fs-20 fw-700 text-dark text-center text-md-left">{{ translate('Transaction History') }}</h5>
        </div>
        <div class="card-body py-0">
            <table class="table aiz-table mb-4">
                <thead class="text-gray fs-12">
                    <tr>
                        <th class="pl-0">#</th>
                        <th data-breakpoints="lg">{{ translate('Date') }}</th>
                        <th data-breakpoints="lg">{{ translate('Transaction Number') }}</th>
                        <th>{{ translate('Amount') }}</th>
                        <th data-breakpoints="lg">{{ translate('Payment Method') }}</th>
                        <th class="text-right pr-0">{{ translate('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="fs-14">
                    @foreach ($wallets as $key => $wallet)
                        <tr>
                            <td class="pl-0">{{ sprintf('%02d', ($key+1)) }}</td>
                            <td>{{ date('d-m-Y', strtotime($wallet->created_at)) }}</td>
                            <td>{{ $wallet->ensureTransactionNumber() }}</td>
                            <td class="fw-700 {{ $wallet->amount < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $wallet->amount < 0 ? '-' : '+' }}{{ single_price(abs($wallet->amount)) }}
                            </td>
                            <td>
                                <div>{{ $wallet->displayPaymentMethod() }}</div>
                                @if ($wallet->counterpartyLabel())
                                    <div class="fs-12 text-secondary mt-1">{{ $wallet->counterpartyLabel() }}</div>
                                @endif
                            </td>
                            <td class="text-right pr-0">
                                @if ($wallet->displayStatus() === translate('Pending'))
                                    <span class="badge badge-inline badge-info p-3 fs-12 wallet-transaction-badge">{{ $wallet->displayStatus() }}</span>
                                @else
                                    <span class="badge badge-inline badge-success p-3 fs-12 wallet-transaction-badge">{{ $wallet->displayStatus() }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
            <!-- Pagination -->
            <div class="aiz-pagination mb-4">
                {{ $wallets->links() }}
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <!-- Wallet Recharge Modal -->
    @include('frontend.partials.wallet_modal')

    <!-- Offline Wallet Recharge Modal -->
    <div class="modal fade" id="offline_wallet_recharge_modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Offline Recharge Wallet') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="offline_wallet_recharge_modal_body"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="wallet_action_info_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-3 border-0">
                <div class="modal-body p-4">
                    <h5 class="fw-700 text-dark mb-2" id="wallet_action_info_title"></h5>
                    <p class="text-secondary mb-0" id="wallet_action_info_message"></p>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-primary rounded-0" data-dismiss="modal">{{ translate('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="wallet_send_money_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 rounded-3 overflow-hidden">
                <form action="{{ route('wallet.send_money') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-700 text-dark">{{ translate('Send Money') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <div class="alert alert-soft-warning mb-3">
                            {{ translate('Transfer from your wallet card to another wallet card number.') }}
                        </div>
                        <div class="form-group">
                            <label class="fs-13 fw-600 text-dark">{{ translate('Receiver Card Number') }}</label>
                            <input type="text" name="receiver_card_number" class="form-control" placeholder="5217 0000 0000 0000" value="{{ old('receiver_card_number') }}" required>
                            @error('receiver_card_number')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="fs-13 fw-600 text-dark">{{ translate('Amount') }}</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="0.00" value="{{ old('amount') }}" required>
                            @error('amount')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-0" data-dismiss="modal">{{ translate('Close') }}</button>
                        <button type="submit" class="btn btn-primary rounded-0">{{ translate('Send Money') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="wallet_qr_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 rounded-3 overflow-hidden">
                <div class="modal-header bg-dark border-0">
                    <h5 class="modal-title text-white">{{ translate('Pay QR Scanner') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4 bg-black">
                    <div id="wallet-qr-reader"></div>
                    <div class="mt-3 text-center text-white-50">{{ translate('Align the QR code inside the frame to scan and pay.') }}</div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script type="text/javascript">
        let walletQrScanner = null;
        let walletSecretsVisible = false;

        async function canUseWalletBiometric() {
            if (!window.PublicKeyCredential || !PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable) {
                return false;
            }

            try {
                return await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
            } catch (error) {
                return false;
            }
        }

        function setWalletSecretVisibility(visible) {
            walletSecretsVisible = visible;

            document.querySelectorAll('.wallet-secret-card-number, .wallet-secret-expiry, .wallet-secret-cvv').forEach(function (element) {
                element.textContent = visible ? element.dataset.raw : element.dataset.masked;
            });

            const toggleBtn = document.getElementById('walletSecretToggleBtn');
            if (toggleBtn) {
                toggleBtn.textContent = visible ? '{{ translate('Hide Details') }}' : '{{ translate('Show Details') }}';
            }
        }

        async function handleWalletSecretToggle() {
            if (walletSecretsVisible) {
                setWalletSecretVisibility(false);
                return;
            }

            const hasBiometricSupport = await canUseWalletBiometric();

            if (!hasBiometricSupport) {
                setWalletSecretVisibility(true);
                return;
            }

            show_wallet_action_info('{{ translate('Biometric Ready') }}', '{{ translate('Biometric support is available on this device. Wallet biometric verification is not configured yet, so details are being shown normally.') }}');
            setWalletSecretVisibility(true);
        }

        function show_wallet_modal() {
            $('#wallet_modal').modal('show');
        }

        function show_make_wallet_recharge_modal() {
            $.post('{{ route('offline_wallet_recharge_modal') }}', {
                _token: '{{ csrf_token() }}'
            }, function(data) {
                $('#offline_wallet_recharge_modal_body').html(data);
                $('#offline_wallet_recharge_modal').modal('show');
            });
        }

        function copyWalletCardNumber() {
            copyWalletValue('{{ $cardNumber }}', '{{ translate('Card number copied') }}');
        }

        function copyWalletExpiry() {
            copyWalletValue('{{ $expiryText }}', 'MM/YY copied');
        }

        function copyWalletCvv() {
            copyWalletValue('{{ $ccv }}', 'CVV copied');
        }

        function copyWalletValue(value, successMessage) {
            navigator.clipboard.writeText(value).then(function () {
                AIZ.plugins.notify('success', successMessage);
            });
        }

        function show_wallet_action_info(title, message) {
            $('#wallet_action_info_title').text(title);
            $('#wallet_action_info_message').text(message);
            $('#wallet_action_info_modal').modal('show');
        }

        function showSendMoneyModal() {
            $('#wallet_send_money_modal').modal('show');
        }

        function openWalletQrScanner() {
            $('#wallet_qr_modal').modal('show');
            setTimeout(function () {
                if (walletQrScanner) {
                    return;
                }

                walletQrScanner = new Html5Qrcode("wallet-qr-reader");
                walletQrScanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    function (decodedText) {
                        walletQrScanner.stop().then(function () {
                            walletQrScanner.clear();
                            walletQrScanner = null;
                            $('#wallet_qr_modal').modal('hide');
                            show_wallet_action_info('QR Scanned', decodedText);
                        });
                    }
                ).catch(function () {
                    show_wallet_action_info('{{ translate('Pay QR') }}', '{{ translate('Camera permission or QR scanner is not available on this device.') }}');
                });
            }, 250);
        }

        $('#wallet_qr_modal').on('hidden.bs.modal', function () {
            if (walletQrScanner) {
                walletQrScanner.stop().then(function () {
                    walletQrScanner.clear();
                    walletQrScanner = null;
                }).catch(function () {
                    walletQrScanner = null;
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const card = document.getElementById('walletBankCard');
            if (card) {
                card.addEventListener('click', function () {
                    card.classList.toggle('is-flipped');
                });
            }

            @if ($errors->has('receiver_card_number') || $errors->has('amount'))
                showSendMoneyModal();
            @endif
        });
    </script>
@endsection
