@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('OTP Login Configuration') }}</h5>
    </div>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>{{ translate('OTP Type') }}</th>
                    <th class="text-center">{{ translate('Enable') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($otp_configurations as $otp)
                    @if(Str::contains($otp->type, 'login'))
                        <tr>
                            <td>{{ strtoupper(str_replace('_', ' ', $otp->type)) }}</td>
                            <td class="text-center">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input
                                        type="checkbox"
                                        value="{{ $otp->type }}"
                                        onchange="updateOtpActivation(this)"
                                        {{ $otp->value == 1 ? 'checked' : '' }}
                                    >
                                    <span></span>
                                </label>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('script')
<script>
    function updateOtpActivation(el){
        $.post('{{ route('otp_configurations.update.activation') }}', {
            _token: '{{ csrf_token() }}',
            type: el.value,
            value: el.checked ? 1 : 0
        }, function (data) {
            if (data == 1) {
                AIZ.plugins.notify('success', '{{ translate('OTP setting updated successfully') }}');
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        });
    }
</script>
@endsection
