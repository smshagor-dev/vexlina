@extends('delivery_boys.layouts.app')

@section('panel_content')
    @php
        $participant = $conversation->sender_id == Auth::id() ? $conversation->receiver : $conversation->sender;
    @endphp

    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="fs-20 fw-700 text-dark">{{ translate('Order Chat') }}: {{ $order->code }}</h3>
            </div>
        </div>
    </div>

    <div class="card shadow-none rounded-0 border">
        <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h5 class="card-title fs-14 fw-700 mb-1">{{ $conversation->title }}</h5>
                <p class="mb-0 fs-14 text-secondary">
                    {{ translate('Chat with') }} {{ $participant?->name }}
                </p>
            </div>
            @if (!empty($participant?->phone))
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $participant->phone) }}"
                   class="btn btn-primary btn-sm rounded-0">
                    <i class="las la-phone mr-1"></i>{{ translate('Call Customer') }}
                </a>
            @endif
        </div>

        <div class="card-body">
            <div id="messages">
                @include('frontend.partials.messages', ['conversation', $conversation])
            </div>

            <form class="pt-4" action="{{ route('messages.store') }}" method="POST">
                @csrf
                <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                <div class="form-group">
                    <textarea class="form-control rounded-0" rows="4" name="message" placeholder="{{ translate('Type your reply') }}" required></textarea>
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary rounded-0 w-150px">{{ translate('Send') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function refresh_messages() {
            $.post('{{ route('conversations.refresh') }}', {
                _token: '{{ csrf_token() }}',
                id: '{{ encrypt($conversation->id) }}'
            }, function(data) {
                $('#messages').html(data);
            });
        }

        refresh_messages();
        setInterval(function() {
            refresh_messages();
        }, 5000);
    </script>
@endsection
