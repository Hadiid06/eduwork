<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold leading-tight text-slate-900">
            Dashboard NusaMart
        </h2>
    </x-slot>

    <div class="px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-blue-500 text-white shadow-xl shadow-indigo-900/10">
                <div class="flex flex-col gap-8 p-6 sm:p-10 lg:flex-row lg:items-center lg:justify-between lg:p-12">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-100">Dashboard pelanggan</p>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Selamat Datang di NusaMart</h1>
                        <p class="mt-4 text-lg text-indigo-100">
                            Halo, <span class="font-semibold text-white">{{ auth()->user()->name }}</span>. Senang melihat Anda kembali.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-white px-5 py-3 font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50 focus:outline-none focus:ring-4 focus:ring-white/40 sm:w-auto">
                            Logout
                        </button>
                    </form>
                </div>
            </section>

            <section class="mt-8 grid gap-5 md:grid-cols-2">
                <a href="{{ route('home') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                    <h2 class="text-lg font-semibold text-slate-900">Lanjutkan belanja</h2>
                    <p class="mt-2 text-sm text-slate-600">Kembali ke beranda untuk melihat produk pilihan.</p>
                    <span class="mt-4 inline-flex font-semibold text-indigo-600 group-hover:text-indigo-800">Kunjungi toko <span class="ms-1" aria-hidden="true">&rarr;</span></span>
                </a>
                <a href="{{ route('products.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                    <h2 class="text-lg font-semibold text-slate-900">Jelajahi produk</h2>
                    <p class="mt-2 text-sm text-slate-600">Temukan produk yang sesuai dengan kebutuhan Anda.</p>
                    <span class="mt-4 inline-flex font-semibold text-indigo-600 group-hover:text-indigo-800">Lihat produk <span class="ms-1" aria-hidden="true">&rarr;</span></span>
                </a>
            </section>
        </div>
    </div>
</x-app-layout>
