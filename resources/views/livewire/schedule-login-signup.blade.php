<div>
    @if($showLoginForm)
        <form wire:submit.prevent="login">
            <div>
                <label for="email" class="text-gray-800">Email:</label>
                <input
                    wire:model="loginForm.email"
                    required
                    id="email"
                    type="email"
                    placeholder="Email"
                    class="rounded-md h-11 px-4 w-full border border-1 {{ $errors->has('error') ? 'border-accentColor' : 'border-gray-300' }} mt-2"
                >
                @error('loginForm.email') <span class="text-accentColor text-sm mt-2">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <label for="password" class="text-gray-800">Password:</label>
                <input
                    wire:model="loginForm.password"
                    required
                    id="password"
                    type="password"
                    placeholder="Password"
                    class="rounded-md h-11 px-4 w-full border border-1 {{ $errors->has('error') ? 'border-accentColor' : 'border-gray-300' }}  mt-2"
                >
                @error('loginForm.password') <span class="text-accentColor text-sm mt-2">{{ $message }}</span> @enderror
            </div>
            @error('error') <span class="text-accentColor text-md mt-4">{{ $message }}</span> @enderror
            <div class="flex flex-row gap-2 mt-8">
                <button
                    type="submit"
                    class="rounded-md h-11 bg-red-100 hover:bg-red-300 w-1/2 ml-auto"
                >
                    Log in
                </button>
            </div>
        </form>
    @elseif($showSignupForm)
        <form wire:submit="signup">
            <div>
                <label for="name" class="text-gray-800">Name:</label>
                <input
                    wire:model="signupForm.name"
                    id="name"
                    type="text"
                    placeholder="Name"
                    class="rounded-md h-11 px-4 w-full border border-1 {{ $errors->has('signupForm.name') ? 'border-accentColor' : 'border-gray-300' }} mt-2"
                >
                @error('signupForm.name') <span class="text-accentColor text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <label for="signup-email" class="text-gray-800">Email:</label>
                <input
                    wire:model="signupForm.email"
                    id="signup-email"
                    type="email"
                    placeholder="Email"
                    class="rounded-md h-11 px-4 w-full border border-1 {{ $errors->has('signupForm.email') ? 'border-accentColor' : 'border-gray-300' }} mt-2"
                >
                @error('signupForm.email') <span class="text-accentColor text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-row gap-2">
                <div class="w-1/2">
                    <label for="signup-password" class="text-gray-800">Password:</label>
                    <input
                        wire:model="signupForm.password"
                        id="signup-password"
                        type="password"
                        placeholder="Password"
                        class="rounded-md h-11 px-4 w-full border border-1 {{ $errors->has('signupForm.password') ? 'border-accentColor' : 'border-gray-300' }} mt-2"
                    >
                    @error('signupForm.password') <span class="text-accentColor text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="w-1/2">
                    <label for="password-confirm" class="text-gray-800">Confirm Password:</label>
                    <input
                        wire:model="signupForm.password_confirmation"
                        id="password-confirm"
                        type="password"
                        placeholder="Confirm password"
                        class="rounded-md h-11 px-4 w-full border border-1 border-gray-300 mt-2"
                    >
                </div>
            </div>
            <div class="flex flex-row gap-2 mt-8">
                <button
                    wire:submit.prevent="signup"
                    class="rounded-md h-11 bg-red-100 hover:bg-red-300 w-1/2 ml-auto"
                >
                    Sign Up
                </button>
            </div>
        </form>
    @else
        @if($showLoginSignupButtons)
            <div class="text-gray-500 text-sm text-center">
                In order to book an appointment please log in or<br> if you do not have an account please sign up below.
            </div>
            <div class="flex flex-row gap-2 mt-4">
                <button
                    wire:click.prevent="showLogin"
                    class="rounded-md h-11 bg-red-100 hover:bg-red-300 w-1/2"
                >
                    Log In
                </button>
                <button
                    wire:click.prevent="showSignup"
                    class="rounded-md h-11 bg-red-100 hover:bg-red-300 w-1/2"
                >
                    Sign Up
                </button>
            </div>
        @else
            @if($disabled)
                <div class="text-gray-500 text-sm text-center">
                    Please select date and time
                </div>
            @endif
            <div class="flex flex-row justify-center mt-4">
                <button
                    wire:click.prevent="$toggle('showLoginSignupButtons')"
                    @disabled($disabled)
                    class="rounded-md h-11 w-1/2 {{ $disabled ? 'bg-gray-200' : 'bg-red-300 hover:bg-red-500' }}"
                >
                    Book Appointment
                </button>
            </div>
        @endif
    @endif
</div>
