@extends('layouts.auth')

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 overflow-y-auto h-screen scroll-smooth">

    <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="soft-panel rounded-[2rem] border border-white/10 p-8 lg:p-10">
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full border border-amber-300/20 bg-amber-400/10 px-4 py-2 text-xs uppercase tracking-[0.3em] text-amber-200">
                    Restaurant main page
                </span>
                <span class="rounded-full border border-white/10 bg-slate-950/50 px-4 py-2 text-xs uppercase tracking-[0.25em] text-slate-300">
                    Role based operation
                </span>
            </div>

            <h1 class="mt-6 max-w-3xl text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight text-white">
                Restoran uchun bitta asosiy sahifa, bitta login va har bir rolga mos kabinet.
            </h1>

            <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-300 lg:text-base">
                Bu landing sahifa manager, cashier, waiter, chef va barmen oqimlarini bir joyda tanishtiradi.
                Login orqali har bir xodim o'z roliga mos kabinetga kiradi, u yerdan esa kerakli panelga tez o'tadi.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                @auth
                    <a href="{{ route('cabinet') }}" class="btn btn-warning rounded-2xl px-6">Open cabinet</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-warning rounded-2xl px-6">Login</a>
                @endauth

                <a href="#role-map" class="btn btn-outline rounded-2xl px-6">Role map</a>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Orders today</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $stats['ordersToday'] }}</p>
                    <p class="mt-2 text-sm text-slate-400">Bugungi umumiy buyurtmalar soni</p>
                </div>

                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Ready items</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-300">{{ $stats['readyItems'] }}</p>
                    <p class="mt-2 text-sm text-slate-400">Servisga tayyor aktiv itemlar</p>
                </div>

                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Active menu</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $stats['menuItems'] }}</p>
                    <p class="mt-2 text-sm text-slate-400">Menyudagi aktiv pozitsiyalar</p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">System overview</p>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-sm text-slate-400">Branches</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ $stats['branches'] }}</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-sm text-slate-400">Roles</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ $stats['roles'] }}</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-sm text-slate-400">Staff</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ $stats['staff'] }}</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-sm text-slate-400">Flow</p>
                        <p class="mt-2 text-2xl font-semibold text-white">Waiter → station → cashier</p>
                    </div>
                </div>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Webhook note</p>

                <p class="mt-3 text-sm leading-7 text-slate-300">
                    Hozirgi ichki restoran oqimi uchun webhook shart emas.
                    Webhook faqat tashqi manbadan, masalan sayt, Telegram bot yoki delivery servisdan zakaz kiradigan bo'lsa kerak bo'ladi.
                </p>
            </div>
        </div>
    </section>

    <section class="soft-panel rounded-[2rem] border border-white/10 p-8" id="role-map">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Role map</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">
                    Har bir rol uchun alohida ish maydoni
                </h2>
            </div>

            <p class="max-w-xl text-sm leading-7 text-slate-300">
                Login bo'lgandan keyin foydalanuvchi o'z kabinetiga tushadi.
                Kabinet tezkor actionlar, monitoring va o'z roli uchun kerakli sahifalarni ko'rsatadi.
            </p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($roleHighlights as $role)
                <article class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                    <h3 class="text-xl font-semibold text-white">{{ $role['label'] }}</h3>

                    <p class="mt-3 text-sm leading-7 text-slate-300">
                        {{ $role['summary'] }}
                    </p>

                    <div class="mt-5 space-y-2">
                        @foreach ($role['points'] as $point)
                            <div class="rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-3 text-sm text-slate-200">
                                {{ $point }}
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-4">
        @foreach ($serviceFlow as $flow)
            <article class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">
                    {{ $flow['step'] }}
                </p>

                <h3 class="mt-3 text-xl font-semibold text-white">
                    {{ $flow['title'] }}
                </h3>

                <p class="mt-3 text-sm leading-7 text-slate-300">
                    {{ $flow['description'] }}
                </p>
            </article>
        @endforeach
    </section>

</div>

<!-- Scroll to Top Button -->
<button id="scrollTopBtn" class="fixed bottom-8 right-8 rounded-full bg-amber-400/80 hover:bg-amber-500 p-4 text-white shadow-lg transition">
    ↑
</button>

<script>
    const scrollBtn = document.getElementById('scrollTopBtn');
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
@endsection