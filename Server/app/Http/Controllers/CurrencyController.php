<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:currency_setup'])->only('currency','create','edit');
    }

    public function changeCurrency(Request $request)
    {
        $currency = Currency::where('code', $request->currency_code)->first();
        $request->session()->put('currency_code', $request->currency_code);
        $request->session()->put('currency_symbol', $currency->symbol);
        $request->session()->put('currency_exchange_rate', $currency->exchange_rate);
    	flash(translate('Currency changed to ').$currency->name)->success();
    }

    public function currency(Request $request)
    {
        $sort_search =null;
        $currencies = Currency::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $currencies = $currencies->where('name', 'like', '%'.$sort_search.'%');
        }
        $currencies = $currencies->paginate(10);

        $active_currencies = Currency::where('status', 1)->get();
        return view('backend.setup_configurations.currencies.index', compact('currencies', 'active_currencies','sort_search'));
    }

    public function updateYourCurrency(Request $request)
    {
        $currency = Currency::findOrFail($request->id);
        $currency->name = $request->name;
        $currency->symbol = $request->symbol;
        $currency->code = $request->code;
        $currency->exchange_rate = $request->exchange_rate;
        $currency->status = $currency->status;
        if($currency->save()){
            flash(translate('Currency updated successfully'))->success();
            return redirect()->route('currency.index');
        }
        else {
            flash(translate('Something went wrong'))->error();
            return redirect()->route('currency.index');
        }
    }

    public function create()
    {
        return view('backend.setup_configurations.currencies.create');
    }

    public function edit(Request $request)
    {
        $currency = Currency::findOrFail($request->id);
        return view('backend.setup_configurations.currencies.edit', compact('currency'));
    }

    public function store(Request $request)
    {
        $currency = new Currency;
        $currency->name = $request->name;
        $currency->symbol = $request->symbol;
        $currency->code = $request->code;
        $currency->exchange_rate = $request->exchange_rate;
        $currency->status = '0';
        if($currency->save()){
            flash(translate('Currency updated successfully'))->success();
            return redirect()->route('currency.index');
        }
        else {
            flash(translate('Something went wrong'))->error();
            return redirect()->route('currency.index');
        }
    }

    public function update_status(Request $request)
    {
        $currency = Currency::findOrFail($request->id);
        if($request->status == 0){
            if (get_setting('system_default_currency') == $currency->id) {
                return 0;
            }
        }
        $currency->status = $request->status;
        $currency->save();
        return 1;
    }
    
    public function currencyList()
    {
        $apiKey = 'adf1768380ff09c9da819f47';
        $baseCurrency = 'USD';
    
        $response = Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/{$baseCurrency}");
    
        if (! $response->successful()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch exchange rates'
            ], 500);
        }
    
        $data = $response->json();
    
        if (($data['result'] ?? null) !== 'success') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API response'
            ], 500);
        }
    
        foreach ($data['conversion_rates'] as $code => $rate) {
            Currency::updateOrCreate(
                ['code' => $code],
                ['exchange_rate' => $rate]
            );
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'Currencies synced successfully',
            'total' => count($data['conversion_rates']),
        ]);
    }
}
