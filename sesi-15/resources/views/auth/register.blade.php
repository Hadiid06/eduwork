<x-guest-layout>
    <div class="mb-7 text-center">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Buat Akun Baru</h1>
        <p class="mt-2 text-sm text-slate-600">Daftar untuk mulai berbelanja di NusaMart.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="space-y-2">
            <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            @error('name')
                <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4 space-y-2">
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            @error('email')
                <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4 space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-700">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            @error('password')
                <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4 space-y-2">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            @error('password_confirmation')
                <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">
            Register
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-800">Kembali ke login</a>
    </p>
</x-guest-layout>
