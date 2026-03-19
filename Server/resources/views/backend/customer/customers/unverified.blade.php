@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">Unverified Customers</h1>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-block d-md-flex">
        <h5 class="mb-0 h6">Unverified Customer List</h5>

        <form class="ml-auto d-flex gap-2" method="GET" action="">
            {{-- Docs Submitted Filter --}}
            <select class="form-control aiz-selectpicker" name="docs_submited" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="submitted" @if($docs_submited == 'submitted') selected @endif>
                    Docs Submitted
                </option>
                <option value="not_submitted" @if($docs_submited == 'not_submitted') selected @endif>
                    Docs Not Submitted
                </option>
            </select>

            {{-- Search --}}
            <div class="input-group">
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="Search name or email"
                       value="{{ $sort_search }}">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="las la-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Docs Status</th>
                    <th>Joined At</th>
                    <th class="text-right">Options</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $key => $user)
                    <tr>
                        <td>{{ ($key + 1) + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>

                        <td>
                            @if($user->verification_info)
                                <span class="badge badge-soft-success">Submitted</span>
                            @else
                                <span class="badge badge-soft-danger">Not Submitted</span>
                            @endif
                        </td>

                        <td>{{ $user->created_at->format('d M Y') }}</td>

                        <td class="text-right">
                            <button class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                    data-toggle="modal"
                                    data-target="#verificationModal{{ $user->id }}"
                                    title="View Verification Info">
                                <i class="las la-eye"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Verification Info Modal -->
                    <div class="modal fade"
                         id="verificationModal{{ $user->id }}"
                         tabindex="-1"
                         role="dialog"
                         aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">Verification Information</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    @if($user->verification_info)

                                        @php
                                            $info = is_array($user->verification_info)
                                                ? $user->verification_info
                                                : json_decode($user->verification_info, true);
                                        @endphp

                                        <table class="table table-bordered aiz-table">
                                            <tbody>
                                                @foreach($info as $key => $value)
                                                    <tr>
                                                        <th class="w-25 text-capitalize">
                                                            {{ str_replace('_', ' ', $key) }}
                                                        </th>
                                                        <td>
                                                            @if(is_array($value))
                                                                <pre class="mb-0">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>

                                                            @elseif(is_string($value) && str_contains($value, 'uploads/'))
                                                                <a href="{{ uploaded_asset($value) }}"
                                                                   target="_blank"
                                                                   class="btn btn-sm btn-soft-info">
                                                                    View File
                                                                </a>

                                                            @elseif(filter_var($value, FILTER_VALIDATE_URL))
                                                                <a href="{{ $value }}" target="_blank">
                                                                    {{ $value }}
                                                                </a>

                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                    @else
                                        <div class="text-center text-muted py-4">
                                            No verification info submitted.
                                        </div>
                                    @endif
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        Close
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            No unverified customers found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $users->appends(request()->input())->links() }}
    </div>
</div>
@endsection
