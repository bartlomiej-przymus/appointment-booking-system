<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <h1 class="text-3xl font-bold text-gray-800 mb-4">Payment Successful!</h1>

                    <p class="text-gray-600 text-lg mb-6">
                        Thank you for your payment. Your appointment has been confirmed.
                    </p>

                    @if(isset($order))
                        <div class="bg-gray-50 p-6 rounded-lg max-w-md mx-auto mb-8 text-left">
                            <h2 class="text-xl font-semibold mb-4 text-gray-700">Order Summary</h2>

                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Order ID:</span>
                                    <span class="font-medium">{{ $order->id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Amount:</span>
                                    <span class="font-medium">
                                    @if($order->currency === 'gbp')
                                            £{{ number_format($order->amount, 2) }}
                                        @elseif($order->currency === 'usd')
                                            ${{ number_format($order->amount, 2) }}
                                        @elseif($order->currency === 'eur')
                                            €{{ number_format($order->amount, 2) }}
                                        @endif
                                </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date:</span>
                                    <span class="font-medium">{{ $order->created_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-8">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
