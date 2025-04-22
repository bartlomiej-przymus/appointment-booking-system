<?php

namespace App\Livewire;

use App\Livewire\Forms\LoginForm;
use App\Livewire\Forms\SignUpForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ScheduleLoginSignup extends Component
{
    public LoginForm $loginForm;
    public SignupForm $signupForm;

    #[Reactive]
    public bool $disabled = false;

    public bool $showLoginSignupButtons = false;
    public bool $showLoginForm = false;
    public bool $showSignupForm = false;

    #[Reactive]
    public ?string $parentSelectedDate = null;

    #[Reactive]
    public ?string $parentSelectedTime = null;

    #[Reactive]
    public ?string $parentCalendarDate = null;


//    public function reset(): void
//    {
//        $this->showLoginForm = false;
//        $this->showSignupForm = false;
//        $this->resetValidation();
//        $this->loginForm->reset();
//        $this->signupForm->reset();
//        $this->showLoginSignupButtons = true;
//    }

    public function showLogin(): void
    {
        $this->showLoginSignupButtons = false;
        $this->showSignupForm = false;
        $this->showLoginForm = true;
    }

    public function showSignup(): void
    {
        $this->showLoginSignupButtons = false;
        $this->showLoginForm = false;
        $this->showSignupForm = true;
    }

    public function login()
    {
        $this->loginForm->validate();

        $this->storeAppointmentSelectionInSession();

        if (Auth::attempt([
            'email' => $this->loginForm->email,
            'password' => $this->loginForm->password
        ])) {
            session()->regenerate();

            $this->dispatch('login-successful');

//            return redirect()->to(request()->header('Referer'));

            return;
        }

        $this->addError('error', 'The provided credentials do not match our records.');
    }

    public function signup()
    {
        $this->signupForm->validate();

        $this->storeAppointmentSelectionInSession();

        $user = User::create([
            'name' => $this->signupForm->name,
            'email' => $this->signupForm->email,
            'password' => Hash::make($this->signupForm->password),
        ]);

//        $user->sendEmailVerificationNotification();

        Auth::login($user);
        session()->regenerate();

        $this->dispatch('user-created', message: 'Account created successfully! Please verify your email to continue.');
//        return redirect()->to(request()->header('Referer'));
        $this->dispatch('login-successful', message: 'Logged in successfully!');

        return;
    }

    private function storeAppointmentSelectionInSession(): void
    {
        if (filled($this->parentSelectedDate)) {
            Session::put('appointment_selected_date', $this->parentSelectedDate);
        }

        if (filled($this->parentSelectedTime)) {
            Session::put('appointment_selected_time', $this->parentSelectedTime);
        }

        if (filled($this->parentCalendarDate)) {
            Session::put('appointment_calendar_date', $this->parentCalendarDate);
        }
    }


    public function render()
    {
        return view('livewire.schedule-login-signup');
    }
}
