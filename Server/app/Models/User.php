<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Cart;
use App\Notifications\EmailVerificationNotification;
use App\Traits\PreventDemoModeChanges;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens, HasRoles;

    protected static function booted()
    {
        static::created(function (self $user) {
            $user->ensureWalletCardDetails();
        });
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerificationNotification());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'address', 'city', 'postal_code', 'phone', 'country', 'provider_id', 'email_verified_at', 'verification_code', 'verification_status', 'wallet_card_number', 'wallet_card_expiry_month', 'wallet_card_expiry_year', 'wallet_card_cvv'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function affiliate_user()
    {
        return $this->hasOne(AffiliateUser::class);
    }

    public function affiliate_withdraw_request()
    {
        return $this->hasMany(AffiliateWithdrawRequest::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }
    public function seller()
    {
        return $this->hasOne(Seller::class);
    }


    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function seller_orders()
    {
        return $this->hasMany(Order::class, "seller_id");
    }
    public function seller_sales()
    {
        return $this->hasMany(OrderDetail::class, "seller_id");
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class)->orderBy('created_at', 'desc');
    }

    public function club_point()
    {
        return $this->hasOne(ClubPoint::class);
    }

    public function customer_package()
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function customer_package_payments()
    {
        return $this->hasMany(CustomerPackagePayment::class);
    }

    public function customer_products()
    {
        return $this->hasMany(CustomerProduct::class);
    }

    public function seller_package_payments()
    {
        return $this->hasMany(SellerPackagePayment::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function product_bids()
    {
        return $this->hasMany(AuctionProductBid::class);
    }

    public function product_queries(){
        return $this->hasMany(ProductQuery::class,'customer_id');
    }

    public function uploads(){
        return $this->hasMany(Upload::class);
    }

    public function userCoupon(){
        return $this->hasOne(UserCoupon::class);
    }

    public function preorderProducts()
    {
        return $this->hasMany(PreorderProduct::class);
    }
    public function preorders()
    {
        return $this->hasMany(Preorder::class);
    }
    public function lottaryTickets()
    {
        return $this->hasMany(LottaryTicket::class);
    }
    
    public function getFullAddressAttribute()
    {
        return trim(
            ($this->address ?? '') . ', ' .
            ($this->city ?? '') . ', ' .
            ($this->state ?? '') . ', ' .
            ($this->country ?? '') . ' - ' .
            ($this->postal_code ?? '')
        );
    }

    public function ensureWalletCardNumber()
    {
        return $this->ensureWalletCardDetails()['number'];
    }

    public function ensureWalletCardDetails()
    {
        $cardNumber = preg_replace('/\D/', '', (string) $this->wallet_card_number);
        $expiryMonth = trim((string) $this->wallet_card_expiry_month);
        $expiryYear = trim((string) $this->wallet_card_expiry_year);
        $cvv = trim((string) $this->wallet_card_cvv);
        $shouldSave = false;

        if ($cardNumber === '') {
            $cardNumber = static::generateWalletCardNumber($this->id);
            $this->wallet_card_number = $cardNumber;
            $shouldSave = true;
        }

        if ($expiryMonth === '') {
            $expiryMonth = static::generateWalletCardExpiryMonth($this->id);
            $this->wallet_card_expiry_month = $expiryMonth;
            $shouldSave = true;
        }

        if ($expiryYear === '') {
            $expiryYear = static::generateWalletCardExpiryYear();
            $this->wallet_card_expiry_year = $expiryYear;
            $shouldSave = true;
        }

        if ($cvv === '') {
            $cvv = static::generateWalletCardCvv($this->id);
            $this->wallet_card_cvv = $cvv;
            $shouldSave = true;
        }

        if ($shouldSave) {
            $this->saveQuietly();
        }

        return [
            'number' => $cardNumber,
            'expiry_month' => $expiryMonth,
            'expiry_year' => $expiryYear,
            'cvv' => $cvv,
        ];
    }

    public static function generateWalletCardNumber($userId)
    {
        $middle = substr(str_pad((string) abs(crc32('wallet-card-' . $userId)), 8, '0', STR_PAD_LEFT), 0, 8);
        $suffix = str_pad((string) $userId, 4, '0', STR_PAD_LEFT);

        return '5217' . $middle . $suffix;
    }

    public static function generateWalletCardExpiryMonth($userId)
    {
        return str_pad(((abs(crc32('wallet-month-' . $userId)) % 12) + 1), 2, '0', STR_PAD_LEFT);
    }

    public static function generateWalletCardExpiryYear()
    {
        return date('y', strtotime('+10 years'));
    }

    public static function generateWalletCardCvv($userId)
    {
        return str_pad((string) ((abs(crc32('wallet-cvv-' . $userId)) % 900) + 100), 3, '0', STR_PAD_LEFT);
    }

}
