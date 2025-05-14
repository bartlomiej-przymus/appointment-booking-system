<div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="md:flex">
            <!-- Left Column - Appointment Information (50% on desktop) -->
            <div class="md:w-1/2 p-6 bg-gray-50 border-r border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Appointment Details</h2>

                @if($appointment)
                    <div class="mb-6">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Date:</span>
                            <span class="font-medium">{{ $appointment->date->format('l, F j, Y') }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Time:</span>
                            <span class="font-medium">{{ $appointment->time_slot->format('g:i A') }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Duration:</span>
                            <span class="font-medium">{{ $appointment->duration }} minutes</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Service:</span>
                            <span class="font-medium">{{ $appointment->schedule->name }}</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center text-lg font-bold mb-4">
                            <span>Total:</span>
                            <span>
                            @if($currency === 'gbp')
                                    £{{ number_format($amount, 2) }}
                                @elseif($currency === 'usd')
                                    ${{ number_format($amount, 2) }}
                                @elseif($currency === 'eur')
                                    €{{ number_format($amount, 2) }}
                                @endif
                        </span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Select Currency</label>
                            <select wire:model.live="currency" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="gbp">British Pound (£)</option>
                                <option value="usd">US Dollar ($)</option>
                                <option value="eur">Euro (€)</option>
                            </select>
                        </div>
                    </div>
                @else
                    <div class="bg-amber-100 p-4 rounded-md text-amber-700">
                        No appointment selected. Please start the booking process from the beginning.
                    </div>
                @endif
            </div>

            <!-- Right Column - Payment Form (50% on desktop) -->
            <div class="md:w-1/2 p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Information</h2>

                @if($stripeError)
                    <div class="bg-red-100 text-red-700 p-4 rounded-md mb-4">
                        {{ $stripeError }}
                    </div>
                @endif

                @if($appointment)
                    <form wire:submit="processPayment" id="payment-form">
                        <div class="mb-6">
                            <label for="card-element" class="block text-gray-700 mb-2">Credit or debit card</label>
                            <div id="card-element" class="border rounded-md p-3 bg-white"></div>
                            <div id="card-errors" class="text-red-600 text-sm mt-2"></div>
                        </div>

                        <button
                            type="submit"
                            id="submit-button"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                            wire:loading.attr="disabled"
                            wire:target="processPayment"
                        >
                            <span wire:loading.remove wire:target="processPayment">Pay Now</span>
                            <span wire:loading wire:target="processPayment" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://js.stripe.com/basil/stripe.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const stripe = Stripe('{{ config('cashier.key') }}');
            const elements = stripe.elements();
            const cardElement = elements.create('card');

            cardElement.mount('#card-element');

            const cardErrors = document.getElementById('card-errors');
            cardElement.addEventListener('change', (event) => {
                if (event.error) {
                    cardErrors.textContent = event.error.message;
                } else {
                    cardErrors.textContent = '';
                }
            });

            const form = document.getElementById('payment-form');
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const submitButton = document.getElementById('submit-button');
                submitButton.disabled = true;

                const {paymentMethod, error} = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                });

                if (error) {
                    cardErrors.textContent = error.message;
                    submitButton.disabled = false;
                } else {
                    @this.set('paymentMethod', paymentMethod.id);
                    @this.processPayment();
                }
            });
        });
    </script>
@endpush
