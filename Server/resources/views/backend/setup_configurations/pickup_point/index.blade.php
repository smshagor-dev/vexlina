@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Pick-up Points')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('pick_up_points.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New Pick-up Point')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
	<div class="card-header row gutters-5">
		<div class="col text-center text-md-left">
			<h5 class="mb-md-0 h6">{{ translate('Pick-up Points') }}</h5>
		</div>
		<div class="col-md-4">
			<form class="" id="sort_pickup_points" action="" method="GET">
				<div class="input-group input-group-sm">
					<input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
				</div>
			</form>
		</div>
	</div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg" width="10%">#</th>
                    <th>{{translate('Name')}}</th>
                    <th data-breakpoints="lg">{{translate('Manager')}}</th>
                    <th data-breakpoints="lg">{{translate('Branch Meta')}}</th>
                    <th data-breakpoints="lg">{{translate('Location')}}</th>
                    <th data-breakpoints="lg">{{translate('Pickup Station Contact')}}</th>
                    <th data-breakpoints="lg">{{translate('Commission')}}</th>
                    <th data-breakpoints="lg">{{translate('Return Commission')}}</th>
                    <th>{{translate('Status')}}</th>
                    <th width="10%" class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pickup_points as $key => $pickup_point)
                    <tr>
						<td>{{ ($key+1) + ($pickup_points->currentPage() - 1)*$pickup_points->perPage() }}</td>
                        <td>{{$pickup_point->getTranslation('name')}}</td>
                        @if ($pickup_point->staff != null && $pickup_point->staff->user != null)
                            <td>{{$pickup_point->staff->user->name}}</td>
                        @else
                            <td><div class="badge badge-inline badge-danger">
                                {{ translate('No Manager') }}
                            </div></td>
                        @endif
                        <td>
                            <div class="fs-12 text-secondary">
                                <div><strong>{{ translate('Code') }}:</strong> {{ $pickup_point->internal_code ?: '-' }}</div>
                                <div><strong>{{ translate('Hours') }}:</strong> {{ $pickup_point->workingHoursLabel() }}</div>
                                <div><strong>{{ translate('Hold') }}:</strong> {{ $pickup_point->holdDays() }} {{ translate('days') }}</div>
                            </div>
                        </td>
                        <td>{{$pickup_point->getTranslation('address')}}</td>
                        <td>{{$pickup_point->phone}}</td>
                        <td>
                            @if ($pickup_point->commission_type == 'percent')
                                {{ $pickup_point->commission_amount + 0 }}%
                            @else
                                {{ single_price($pickup_point->commission_amount) }}
                            @endif
                        </td>
                        <td>
                            @if (($pickup_point->return_commission_type ?? 'percent') == 'percent')
                                {{ ($pickup_point->return_commission_amount ?? 0) + 0 }}%
                            @else
                                {{ single_price($pickup_point->return_commission_amount ?? 0) }}
                            @endif
                        </td>
                        <td>
                            @if ($pickup_point->pick_up_status != 1)
                                <div class="badge badge-inline badge-danger">
                                    {{ translate('Close') }}
                                </div>
                            @else
                                <div class="badge badge-inline badge-success">
                                    {{ translate('Open') }}
                                </div>
                            @endif
                        </td>
						<td class="text-right">
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('pick_up_points.show', $pickup_point->id) }}" title="{{ translate('View Activity') }}">
                                <i class="las la-eye"></i>
                            </a>
							<a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('pick_up_points.edit', ['id'=>$pickup_point->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ translate('Edit') }}">
								<i class="las la-edit"></i>
							</a>
							<a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('pick_up_points.destroy', $pickup_point->id)}}" title="{{ translate('Delete') }}">
								<i class="las la-trash"></i>
							</a>
						</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
		<div class="aiz-pagination">
			{{ $pickup_points->appends(request()->input())->links() }}
		</div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        function sort_pickup_points(el){
            $('#sort_pickup_points').submit();
        }
    </script>
@endsection
