<x-guest-layout>
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Selamat Datang Kembali</h1>
        <p class="mt-2 text-sm text-slate-600">Masuk untuk melanjutkan pengalaman belanja Anda.</p>
    </div>

    <x-auth-session-status class="mb-5 text-sm text-emerald-700" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            @error('email')
                <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-5 space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-700">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            @error('password')
                <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-5 flex items-center justify-between gap-3">
            <label for="remember" class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))
                    class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-indigo-600 transition hover:text-indigo-800" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <button type="submit" class="mt-7 inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">
            Login
        </button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-600">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-800">Daftar sekarang</a>
    </p>
</x-guest-layout>
