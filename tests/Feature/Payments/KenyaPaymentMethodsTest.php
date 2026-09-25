<?php

namespace Tests\Feature\Payments;

use App\Models\BankAccount;
use App\Models\PaymentSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KenyaPaymentMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_kenyan_destination_accounts_enable_local_payment_methods(): void
    {
        $setting = PaymentSetting::factory()->create([
            'bank_transfer_enabled' => true,
        ]);

        BankAccount::create([
            'bank_name' => 'M-Pesa Paybill',
            'account_number' => '123456',
            'account_name' => 'FAY COLLECTION',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $gatewayValues = collect($setting->enabledGateways())->pluck('value')->all();

        $this->assertContains(PaymentSetting::GATEWAY_BANK_TRANSFER, $gatewayValues);
        $this->assertContains(PaymentSetting::GATEWAY_MPESA, $gatewayValues);
        $this->assertContains(PaymentSetting::GATEWAY_AIRTEL_MONEY, $gatewayValues);
        $this->assertTrue($setting->isGatewayReady(PaymentSetting::GATEWAY_MPESA));
        $this->assertTrue($setting->isGatewayReady(PaymentSetting::GATEWAY_AIRTEL_MONEY));
    }

    public function test_local_mobile_money_methods_are_not_available_without_a_destination_account(): void
    {
        $setting = PaymentSetting::factory()->create([
            'bank_transfer_enabled' => true,
        ]);

        $this->assertFalse($setting->isGatewayReady(PaymentSetting::GATEWAY_MPESA));
        $this->assertFalse($setting->isGatewayReady(PaymentSetting::GATEWAY_AIRTEL_MONEY));
    }
}
