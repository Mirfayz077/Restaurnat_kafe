@extends('layouts.app')

@section('content')
    <div class="min-h-screen overflow-y-auto space-y-6">
        <section class="soft-panel rounded-[2rem] border border-white/10 p-6 lg:p-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-amber-200">{{ $hero['eyebrow'] }}</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-semibold text-white lg:text-4xl">{{ $hero['title'] }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">
                        {{ $hero['description'] }}
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">User</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $user->name }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Role</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $user->role?->label ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Branch</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $user->branch?->name ?? 'Global' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <article class="soft-panel rounded-[1.75rem] border border-white/10 p-5">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $card['value'] }}</p>
                    <p class="mt-2 text-sm text-slate-400">{{ $card['hint'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Quick actions</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Kabinetdan kerakli bo'limga tez o'tish</h3>
                </div>
                <a href="{{ route('home') }}" class="btn btn-outline btn-sm rounded-2xl">Main page</a>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($quickLinks as $link)
                    <a href="{{ route($link['route']) }}" class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5 transition hover:border-amber-300/30 hover:bg-amber-400/10">
                        <p class="text-lg font-semibold text-white">{{ $link['label'] }}</p>
                        <p class="mt-3 text-sm leading-7 text-slate-300">{{ $link['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        @if (in_array($roleName, ['admin', 'manager'], true))
            <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Live station monitor</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">Kitchen va bar holati</h3>
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn btn-warning btn-sm rounded-2xl">Open dashboard</a>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ($stationSnapshots as $snapshot)
                            <article class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-lg font-semibold text-white">{{ $snapshot['label'] }}</h4>
                                    <span class="badge badge-outline">{{ $snapshot['orders'] }} orders</span>
                                </div>
                                <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                                    <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Queued</p>
                                        <p class="mt-2 text-xl font-semibold text-white">{{ $snapshot['queued'] }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Prep</p>
                                        <p class="mt-2 text-xl font-semibold text-amber-200">{{ $snapshot['preparing'] }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Ready</p>
                                        <p class="mt-2 text-xl font-semibold text-emerald-300">{{ $snapshot['ready'] }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Team breakdown</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">Rollar bo'yicha xodimlar</h3>
                        </div>
                        @if (auth()->user()->hasPermission('staff.manage'))
                            <a href="{{ route('staff.index') }}" class="btn btn-outline btn-sm rounded-2xl">Staff</a>
                        @endif
                    </div>

                    <div class="mt-5 space-y-3">
                        @foreach ($staffBreakdown as $role)
                            <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 px-4 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-white">{{ $role->label }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ $role->name }}</p>
                                    </div>
                                    <span class="text-2xl font-semibold text-amber-200">{{ $role->users_count }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Orders to watch</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Aktiv va to'lovga yaqin zakazlar</h3>

                    <div class="mt-5 space-y-3">
                        @forelse ($managerOrders as $order)
                            <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-medium text-white">{{ $order->order_number }}</p>
                                        <p class="mt-1 text-sm text-slate-400">
                                            {{ $order->branch?->name }} | {{ $order->diningTable?->name ?? 'Takeaway / delivery' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Waiter: {{ $order->waiter?->name ?? 'N/A' }} | Cashier: {{ $order->cashier?->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <span class="badge {{ $order->status === 'ready' ? 'badge-success' : ($order->status === 'in_service' ? 'badge-warning' : ($order->status === 'paid' ? 'badge-primary' : 'badge-outline')) }}">
                                        {{ $order->serviceStatusLabel() }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                                Hozircha monitoring uchun aktiv zakaz yo'q.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Recent menu items</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Yaqinda qo'shilgan itemlar</h3>

                    <div class="mt-5 space-y-3">
                        @forelse ($recentProducts as $product)
                            <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-white">{{ $product->name }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ $product->category?->name ?? 'No category' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge {{ $product->station === 'bar' ? 'badge-info' : 'badge-warning' }}">
                                            {{ $product->stationLabel() }}
                                        </span>
                                        <p class="mt-2 text-sm text-amber-200">{{ number_format((float) $product->price) }} so'm</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                                Hozircha yangi menu item yo'q.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        @endif

        @if ($roleName === 'waiter')
            <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">My service orders</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">Sizga biriktirilgan aktiv stollar</h3>
                        </div>
                        <a href="{{ route('waiter.index') }}" class="btn btn-warning btn-sm rounded-2xl">Open waiter panel</a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($waiterOrders as $order)
                            @php
                                $readyCount = $order->items->where('preparation_status', 'ready')->sum('quantity');
                                $queuedCount = $order->items->whereIn('preparation_status', ['queued', 'preparing'])->sum('quantity');
                            @endphp
                            <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $order->diningTable?->name ?? 'No table' }}</p>
                                        <p class="mt-2 text-lg font-semibold text-white">{{ $order->order_number }}</p>
                                        <p class="mt-2 text-sm text-slate-400">{{ $order->serviceStatusLabel() }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 text-center text-sm">
                                        <div class="rounded-2xl bg-slate-900/70 px-4 py-3">
                                            <p class="text-slate-500">Queue</p>
                                            <p class="mt-1 font-semibold text-white">{{ $queuedCount }}</p>
                                        </div>
                                        <div class="rounded-2xl bg-slate-900/70 px-4 py-3">
                                            <p class="text-slate-500">Ready</p>
                                            <p class="mt-1 font-semibold text-emerald-300">{{ $readyCount }}</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                                Sizda hozircha aktiv stol orderi yo'q.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Station snapshot</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Kitchen va bar umumiy holati</h3>

                    <div class="mt-5 space-y-3">
                        @foreach ($stationSnapshots as $snapshot)
                            <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-lg font-semibold text-white">{{ $snapshot['label'] }}</h4>
                                    <span class="badge badge-outline">{{ $snapshot['orders'] }} orders</span>
                                </div>
                                <div class="mt-4 grid grid-cols-3 gap-3 text-center text-sm">
                                    <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                                        <p class="text-slate-500">Queued</p>
                                        <p class="mt-1 font-semibold text-white">{{ $snapshot['queued'] }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                                        <p class="text-slate-500">Prep</p>
                                        <p class="mt-1 font-semibold text-amber-200">{{ $snapshot['preparing'] }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                                        <p class="text-slate-500">Ready</p>
                                        <p class="mt-1 font-semibold text-emerald-300">{{ $snapshot['ready'] }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if (in_array($roleName, ['chef', 'bartender'], true))
            <section class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Incoming station items</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">Kelgan zakazlar navbati</h3>
                        </div>
                        <a href="{{ $roleName === 'chef' ? route('kitchen.index') : route('bar.index') }}" class="btn btn-warning btn-sm rounded-2xl">
                            Open queue
                        </a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($stationItems as $item)
                            <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-medium text-white">{{ $item->product_name }}</p>
                                        <p class="mt-1 text-sm text-slate-400">
                                            {{ $item->order?->order_number }} | {{ $item->order?->diningTable?->name ?? 'Takeaway / delivery' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ optional($item->sent_to_station_at)->format('d.m.Y H:i') }} | {{ $item->order?->branch?->name }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge {{ $item->preparation_status === 'ready' ? 'badge-success' : ($item->preparation_status === 'preparing' ? 'badge-warning' : 'badge-outline') }}">
                                            {{ $item->preparationStatusLabel() }}
                                        </span>
                                        <p class="mt-2 text-lg font-semibold text-white">{{ $item->quantity }} pcs</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                                Hozircha stansiyangiz uchun aktiv zakaz yo'q.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">New menu items</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Sizning stansiyangizga tegishli yangi itemlar</h3>

                        <div class="mt-5 space-y-3">
                            @forelse ($stationProducts as $product)
                                <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-white">{{ $product->name }}</p>
                                            <p class="mt-1 text-sm text-slate-400">{{ $product->category?->name ?? 'No category' }}</p>
                                        </div>
                                        <p class="text-sm font-semibold text-amber-200">{{ number_format((float) $product->price) }} so'm</p>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                                    Hozircha yangi menu item topilmadi.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                        <p class="text-xs uppercase tracking-[0.3em] text-amber-200">Webhook note</p>
                        <p class="mt-3 text-sm leading-7 text-slate-300">{{ $webhookNote }}</p>
                    </div>
                </div>
            </section>
        @endif

        @if ($roleName === 'cashier')
            <section class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Settlement queue</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">To'lov va close kutayotgan stollar</h3>
                        </div>
                        <a href="{{ route('pos.index') }}" class="btn btn-warning btn-sm rounded-2xl">Open POS</a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($cashierOrders as $order)
                            <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-medium text-white">{{ $order->order_number }}</p>
                                        <p class="mt-1 text-sm text-slate-400">
                                            {{ $order->diningTable?->name ?? 'No table' }} | Waiter: {{ $order->waiter?->name ?? 'N/A' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Items: {{ $order->items->sum('quantity') }} | Paid: {{ number_format((float) $order->payments->sum('amount')) }} so'm
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge {{ $order->status === 'paid' ? 'badge-primary' : ($order->status === 'served' ? 'badge-success' : ($order->status === 'ready' ? 'badge-info' : 'badge-warning')) }}">
                                            {{ $order->serviceStatusLabel() }}
                                        </span>
                                        <p class="mt-2 text-lg font-semibold text-amber-200">{{ number_format((float) $order->total) }} so'm</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.5rem] border border-dashed border-white/10 bg-slate-950/40 p-6 text-center text-slate-400">
                                Hozircha settlement kutayotgan order yo'q.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Branch station monitor</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Cashier uchun tezkor operatsion ko'rinish</h3>

                    <div class="mt-5 space-y-3">
                        @foreach ($stationSnapshots as $snapshot)
                            <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="font-medium text-white">{{ $snapshot['label'] }}</h4>
                                    <span class="badge badge-outline">{{ $snapshot['orders'] }} orders</span>
                                </div>
                                <div class="mt-4 grid grid-cols-3 gap-3 text-center text-sm">
                                    <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                                        <p class="text-slate-500">Queued</p>
                                        <p class="mt-1 font-semibold text-white">{{ $snapshot['queued'] }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                                        <p class="text-slate-500">Prep</p>
                                        <p class="mt-1 font-semibold text-amber-200">{{ $snapshot['preparing'] }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-900/70 px-3 py-3">
                                        <p class="text-slate-500">Ready</p>
                                        <p class="mt-1 font-semibold text-emerald-300">{{ $snapshot['ready'] }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
