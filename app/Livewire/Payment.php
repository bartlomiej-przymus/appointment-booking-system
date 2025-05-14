<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Appointment;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Livewire\Component;

class Payment extends Component
{
    public $appointmentId;

    public $appointment;

    public $currency = 'gbp'; // Default currency is pounds

    public $amount;

    public $paymentMethod;

    public $stripeError = '';

    public $processing = false;

    protected $rules = [
        'paymentMethod' => 'required',
        'currency' => 'required|in:gbp,usd,eur',
    ];

    public function mount($appointmentId = null)
    {
        $this->appointmentId = $appointmentId;
        if ($appointmentId) {
            $this->appointment = Appointment::with('schedule')->findOrFail($appointmentId);
            $this->amount = $this->appointment->schedule->price_in_gbp;
        }
    }

    public function updatedCurrency(): void
    {
        switch ($this->currency) {
            case 'usd':
                $this->amount = $this->appointment->schedule->price_in_usd;
                break;
            case 'eur':
                $this->amount = $this->appointment->schedule->price_in_eur;
                break;
            default:
                $this->amount = $this->appointment->schedule->price_in_gbp;
        }
    }

    public function processPayment()
    {
        $this->validate();
        $this->processing = true;
        $this->stripeError = '';

        try {
            $user = Auth::user();
            $amountInCents = (int) ($this->amount * 100);

            $order = Order::create([
                'user_id' => $user->id,
                'amount' => $this->amount,
                'currency' => $this->currency,
                'status' => OrderStatus::Pending,
            ]);

            $this->appointment->order_id = $order->id;
            $this->appointment->save();

            $paymentIntent = $user->charge(
                $amountInCents,
                $this->paymentMethod,
                [
                    'currency' => $this->currency,
                    'description' => 'Appointment Booking - #'.$this->appointment->id,
                    'metadata' => [
                        'order_id' => $order->id,
                        'appointment_id' => $this->appointment->id,
                    ],
                ]
            );

            $order->update([
                'transaction_id' => $paymentIntent->id,
                'status' => OrderStatus::Completed,
            ]);

            return redirect()->route('payment.success', ['orderId' => $order->id]);

        } catch (IncompletePayment $exception) {
            return redirect()->route('cashier.payment', [
                $exception->payment->id,
                'redirect' => route('payment.success', ['orderId' => $order->id ?? null]),
            ]);
        } catch (\Exception $e) {
            $this->stripeError = $e->getMessage();
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.payment');
    }
}
