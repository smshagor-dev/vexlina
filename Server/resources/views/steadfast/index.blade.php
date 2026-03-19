@extends('backend.layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Steadfast API Settings</h3>

    <form id="steadfastForm">
        @csrf
        <input type="hidden" id="steadfast_id">

        <div class="mb-3">
            <label class="form-label">API Key</label>
            <input type="text" class="form-control" id="steadfast_api_key" value="{{ $steadfastKey->steadfast_api_key ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Secret Key</label>
            <input type="text" class="form-control" id="steadfast_secret_key" value="{{ $steadfastKey->steadfast_secret_key ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Base URL</label>
            <input type="url" class="form-control" id="steadfast_base_url" value="{{ $steadfastKey->steadfast_base_url ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Webhook Token</label>
            <div class="input-group">
                <input type="text" class="form-control" id="steadfast_webhook_token" value="{{ $steadfastKey->steadfast_webhook_token ?? '' }}" readonly required>
                <button type="button" class="btn btn-secondary" onclick="generateToken()">
                    Generate
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            Save
        </button>
    </form>
</div>

<script>
   function generateToken(length = 64) {
        if (length < 64 || length > 128) {
            throw new Error('Token length must be between 64 and 128 characters');
        }

        const array = new Uint8Array(length);
        crypto.getRandomValues(array);

        const token = Array.from(array, b =>
            b.toString(16).padStart(2, '0')
        ).join('').slice(0, length);

        document.getElementById('steadfast_webhook_token').value = token;
    }

    document.getElementById('steadfastForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const payload = {
            steadfast_api_key: document.getElementById('steadfast_api_key').value,
            steadfast_secret_key: document.getElementById('steadfast_secret_key').value,
            steadfast_base_url: document.getElementById('steadfast_base_url').value,
            steadfast_webhook_token: document.getElementById('steadfast_webhook_token').value,
        };

        const response = await fetch(`{{ route('steadfast.update') }}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.status) {
            alert(result.message);
        } else {
            alert('Something went wrong');
        }
    });
</script>
@endsection
