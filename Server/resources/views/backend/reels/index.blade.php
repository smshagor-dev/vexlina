@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header row gutters-5 align-items-center">
        <div class="col">
            <h5 class="mb-0 h6">{{ translate('Reels') }}</h5>
        </div>
        <div class="col-md-4">
            <form action="" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="{{ translate('Search by caption, creator, or product') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">{{ translate('Search') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Preview') }}</th>
                    <th>{{ translate('Caption') }}</th>
                    <th data-breakpoints="lg">{{ translate('Creator') }}</th>
                    <th data-breakpoints="lg">{{ translate('Product') }}</th>
                    <th data-breakpoints="lg">{{ translate('Stats') }}</th>
                    <th data-breakpoints="lg">{{ translate('Status') }}</th>
                    <th data-breakpoints="lg">{{ translate('Date') }}</th>
                    <th class="text-right">{{ translate('Options') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reels as $key => $reel)
                    <tr>
                        <td>{{ ($key + 1) + ($reels->currentPage() - 1) * $reels->perPage() }}</td>
                        <td>
                            <img src="{{ $reel->thumbnail_upload_id ? uploaded_asset($reel->thumbnail_upload_id) : static_asset('assets/img/placeholder.jpg') }}"
                                 alt="{{ translate('Reel thumbnail') }}"
                                 class="h-50px w-50px object-fit-cover rounded">
                        </td>
                        <td>
                            <div class="text-truncate-2" style="max-width: 260px;">
                                {{ $reel->caption ?: translate('No caption added') }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-600">{{ optional($reel->user)->name ?: translate('Unknown') }}</div>
                            <small class="text-muted">{{ optional($reel->user)->email }}</small>
                        </td>
                        <td>
                            {{ optional($reel->product)->name ?: translate('No linked product') }}
                        </td>
                        <td>
                            <small class="d-block">{{ $reel->views_count }} {{ translate('views') }}</small>
                            <small class="d-block">{{ $reel->likes_count }} {{ translate('likes') }}</small>
                            <small class="d-block">{{ $reel->comments_count }} {{ translate('comments') }}</small>
                        </td>
                        <td>
                            @if ($reel->status === 'published')
                                <span class="badge badge-inline badge-success">{{ translate('Published') }}</span>
                            @elseif ($reel->status === 'deleted')
                                <span class="badge badge-inline badge-danger">{{ translate('Deleted') }}</span>
                            @else
                                <span class="badge badge-inline badge-secondary">{{ ucfirst($reel->status) }}</span>
                            @endif
                            <div class="mt-1">
                                <small class="text-muted">
                                    {{ $reel->allow_comments ? translate('Comments on') : translate('Comments off') }}
                                </small>
                            </div>
                        </td>
                        <td>{{ optional($reel->created_at)->format('d-m-Y h:i A') }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                               href="{{ route('reels.show', $reel->id) }}"
                               target="_blank"
                               title="{{ translate('Show') }}">
                                <i class="las la-eye"></i>
                            </a>
                            @can('delete_reels')
                                @if ($reel->status !== 'deleted')
                                    <a href="#"
                                       class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                       data-href="{{ route('admin.reels.destroy', $reel->id) }}"
                                       title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">{{ translate('No reels found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $reels->links() }}
        </div>
    </div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
