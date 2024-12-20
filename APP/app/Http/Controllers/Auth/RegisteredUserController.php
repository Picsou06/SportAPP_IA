<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'birth' => ['required', 'date', 'before:-16 years'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (!$validatedData['birth'])
            return redirect()->back()->withErrors("Vous devez avoir + de 16 ans pour vous inscrire!")->withInput();
        if (!$validatedData['email'])
            return redirect()->back()->withErrors("Email déjà utilisé!")->withInput();
        if (!$validatedData['password'])
            return redirect()->back()->withErrors("Mot de passe incorrect!")->withInput();

        $user = User::create([
            'name' => $request->name,
            'birth' => $request->birth,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('registration_form', absolute: false));
    }
}
