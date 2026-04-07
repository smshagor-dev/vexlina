@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Update Pickup Point Information')}}</h5>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill language-bar">
      				@foreach (get_all_active_language() as $key => $language)
      					<li class="nav-item">
      						<a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3" href="{{ route('pick_up_points.edit', ['id'=>$pickup_point->id, 'lang'=> $language->code] ) }}">
      							<img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
      							<span>{{$language->name}}</span>
      						</a>
      					</li>
  		            @endforeach
      			</ul>

                <form class="p-4" action="{{ route('pick_up_points.update',$pickup_point->id) }}" method="POST">
                	<input name="_method" type="hidden" value="PATCH">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    @csrf

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Name')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" value="{{ $pickup_point->getTranslation('name', $lang) }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="address">{{translate('Location')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                        <div class="col-sm-9">
                            <textarea name="address" rows="8" class="form-control" required>{{ $pickup_point->getTranslation('address', $lang) }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="phone">{{translate('Phone')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Phone')}}" id="phone" name="phone" value="{{ $pickup_point->phone }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="internal_code">{{translate('Internal Code')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Branch / Station Code')}}" id="internal_code" name="internal_code" value="{{ $pickup_point->internal_code }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{translate('Working Hours')}}</label>
                        <div class="col-sm-4">
                            <input type="text" placeholder="{{translate('Opening Time')}}" name="opening_time" value="{{ $pickup_point->opening_time }}" class="form-control">
                        </div>
                        <div class="col-sm-5">
                            <input type="text" placeholder="{{translate('Closing Time')}}" name="closing_time" value="{{ $pickup_point->closing_time }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="pickup_hold_days">{{translate('Pickup Hold Days')}}</label>
                        <div class="col-sm-9">
                            <input type="number" min="1" max="30" placeholder="{{translate('Pickup Hold Days')}}" id="pickup_hold_days" name="pickup_hold_days" value="{{ $pickup_point->pickup_hold_days ?? 5 }}" class="form-control" required>
                            <small class="text-muted">{{ translate('Number of calendar days a reached order stays at the pickup point before return due.') }}</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{translate('Map Coordinates')}}</label>
                        <div class="col-sm-4">
                            <input type="number" step="any" placeholder="{{translate('Latitude')}}" name="latitude" value="{{ $pickup_point->latitude }}" class="form-control">
                        </div>
                        <div class="col-sm-5">
                            <input type="number" step="any" placeholder="{{translate('Longitude')}}" name="longitude" value="{{ $pickup_point->longitude }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="instructions">{{translate('Pickup Instructions')}}</label>
                        <div class="col-sm-9">
                            <textarea name="instructions" rows="4" class="form-control" placeholder="{{ translate('Special handover notes, landmarks, customer instructions, or return desk notes') }}">{{ $pickup_point->instructions }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{translate('Service Options')}}</label>
                        <div class="col-sm-9">
                            <div class="d-flex flex-wrap" style="gap:20px;">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" type="checkbox" name="supports_return" @if(($pickup_point->supports_return ?? 1) == 1) checked @endif>
                                    <span class="slider round"></span>
                                </label>
                                <span class="mr-4">{{ translate('Supports Return') }}</span>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" type="checkbox" name="supports_cod" @if(($pickup_point->supports_cod ?? 1) == 1) checked @endif>
                                    <span class="slider round"></span>
                                </label>
                                <span>{{ translate('Supports COD Collection') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="commission_type">{{translate('Commission Type')}}</label>
                        <div class="col-sm-9">
                            <select name="commission_type" id="commission_type" required class="form-control aiz-selectpicker">
                                <option value="percent" @if ($pickup_point->commission_type == 'percent') selected @endif>{{ translate('Percentage') }}</option>
                                <option value="flat" @if ($pickup_point->commission_type == 'flat') selected @endif>{{ translate('Flat Amount') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="commission_amount">{{translate('Commission Amount')}}</label>
                        <div class="col-sm-9">
                            <input type="number" min="0" step="0.01" placeholder="{{translate('Commission Amount')}}" id="commission_amount" name="commission_amount" value="{{ $pickup_point->commission_amount ?? 0 }}" class="form-control" required>
                            <small class="text-muted">{{ translate('Use percentage value for percentage type, or fixed amount for flat type.') }}</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="return_commission_type">{{translate('Return Commission Type')}}</label>
                        <div class="col-sm-9">
                            <select name="return_commission_type" id="return_commission_type" required class="form-control aiz-selectpicker">
                                <option value="percent" @if (($pickup_point->return_commission_type ?? 'percent') == 'percent') selected @endif>{{ translate('Percentage') }}</option>
                                <option value="flat" @if (($pickup_point->return_commission_type ?? null) == 'flat') selected @endif>{{ translate('Flat Amount') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="return_commission_amount">{{translate('Return Commission Amount')}}</label>
                        <div class="col-sm-9">
                            <input type="number" min="0" step="0.01" placeholder="{{translate('Return Commission Amount')}}" id="return_commission_amount" name="return_commission_amount" value="{{ $pickup_point->return_commission_amount ?? 0 }}" class="form-control" required>
                            <small class="text-muted">{{ translate('Use percentage value for percentage type, or fixed amount for flat type.') }}</small>
                        </div>
                    </div>
                    <div class="border rounded p-3 mb-4" style="background: #f8fbff; border-color: #dbeafe !important;">
                        <h6 class="fw-700 mb-3">{{ translate('Payout Control') }}</h6>
                        <div class="form-group row mb-3">
                            <label class="col-sm-3 col-from-label" for="payout_frequency_days">{{translate('Payout Schedule')}}</label>
                            <div class="col-sm-9">
                                <select name="payout_frequency_days" id="payout_frequency_days" class="form-control aiz-selectpicker" required>
                                    <option value="7" @if(($pickup_point->payout_frequency_days ?? 7) == 7) selected @endif>{{ translate('Every 7 Days') }}</option>
                                    <option value="15" @if(($pickup_point->payout_frequency_days ?? 7) == 15) selected @endif>{{ translate('Every 15 Days') }}</option>
                                    <option value="30" @if(($pickup_point->payout_frequency_days ?? 7) == 30) selected @endif>{{ translate('Every 30 Days') }}</option>
                                </select>
                                <small class="text-muted">{{ translate('Pickup point managers can request payout only after this admin-selected cycle.') }}</small>
                            </div>
                        </div>
                        <div class="mb-0 fs-12 text-muted">
                            {{ translate('Pickup point manager payout account info and payout requests will be visible in the admin pickup point activity page.') }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{translate('Pickup Point Status')}}</label>
                        <div class="col-sm-3">
                            <label class="aiz-switch aiz-switch-success mb-0" style="margin-top:5px;">
                        		<input value="1" type="checkbox" name="pick_up_status"@if ($pickup_point->pick_up_status == 1) checked @endif>
                        		<span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Pick-up Point Manager')}}</label>
                        <div class="col-sm-9">
                            <select name="staff_id" required class="form-control aiz-selectpicker">
                                @foreach(\App\Models\Staff::all() as $staff)
                                    @if ($staff->user!=null )
                                        <option value="{{$staff->id}}" @if ($pickup_point->staff_id == $staff->id) selected @endif>{{$staff->user->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
