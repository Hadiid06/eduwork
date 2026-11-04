<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>NusaMart</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 px-4 py-10 sm:px-6">
            <div class="pointer-events-none absolute -left-24 -top-24 size-72 rounded-full bg-blue-200/40 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-20 size-80 rounded-full bg-indigo-200/50 blur-3xl"></div>

            <section class="relative w-full max-w-md">
                <div class="mb-7 flex justify-center">
                    <a href="{{ route('home') }}" aria-label="NusaMart, kembali ke beranda">
                        <x-application-logo class="h-12 w-auto" />
                    </a>
                </div>

                <div class="rounded-3xl border border-white/80 bg-white/95 p-6 shadow-xl shadow-indigo-900/10 sm:p-9">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">
                    Belanja mudah, pilihan tepat.
                </p>
            </section>
        </main>
    </body>
</html>
