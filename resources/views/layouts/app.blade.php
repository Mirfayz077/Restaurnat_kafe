<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Restaurant POS') }}</title>

    @livewireStyles
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="app-shell text-slate-100 antialiased" data-theme="night" data-realtime="enabled">
    <div class="mx-auto flex min-h-screen w-full max-w-[1700px] flex-col px-4 py-4 sm:px-6 lg:flex-row lg:px-8">
        <aside class="soft-panel mb-4 rounded-[2rem] border border-white/10 p-4 lg:mb-0 lg:w-80 lg:p-6">
            <div class="rounded-[1.5rem] border border-amber-400/20 bg-amber-400/10 p-4">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Restaurant POS</p>
                <h1 class="mt-2 text-2xl font-semibold text-white">MVP Control Room</h1>
                <p class="mt-2 text-sm leading-6 text-slate-300">
                    {{ auth()->user()->name }} | {{ auth()->user()->role?->label ?? 'Role not assigned' }}
                </p>
                <p class="text-sm text-slate-400">
                    {{ auth()->user()->branch?->name ?? 'Branch not assigned' }}
                </p>
            </div>

            <nav class="mt-6 space-y-2 text-sm">
                <a href="{{ route('cabinet') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('cabinet') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                    <span>Cabinet</span>
                    <span class="badge badge-outline">00</span>
                </a>

                @can('dashboard.view')
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('dashboard') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Dashboard</span>
                        <span class="badge badge-outline">01</span>
                    </a>
                @endcan

                @can('waiter.panel')
                    <a href="{{ route('waiter.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('waiter.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Waiter Panel</span>
                        <span class="badge badge-outline">02</span>
                    </a>
                @endcan

                @can('orders.create')
                    <a href="{{ route('pos.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('pos.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>POS Terminal</span>
                        <span class="badge badge-outline">03</span>
                    </a>
                @endcan

                @can('kitchen.view')
                    <a href="{{ route('kitchen.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('kitchen.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Kitchen Queue</span>
                        <span class="badge badge-outline">04</span>
                    </a>
                @endcan

                @can('bar.view')
                    <a href="{{ route('bar.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('bar.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Bar Queue</span>
                        <span class="badge badge-outline">05</span>
                    </a>
                @endcan

                @can('staff.manage')
                    <a href="{{ route('staff.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('staff.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Staff</span>
                        <span class="badge badge-outline">06</span>
                    </a>
                @endcan

                @can('roles.manage')
                    <a href="{{ route('roles.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('roles.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Roles</span>
                        <span class="badge badge-outline">07</span>
                    </a>
                @endcan

                @can('branches.manage')
                    <a href="{{ route('branches.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('branches.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Branches</span>
                        <span class="badge badge-outline">08</span>
                    </a>
                @endcan

                @can('tables.manage')
                    <a href="{{ route('tables.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('tables.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Tables</span>
                        <span class="badge badge-outline">09</span>
                    </a>
                @endcan

                @can('categories.manage')
                    <a href="{{ route('categories.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('categories.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Categories</span>
                        <span class="badge badge-outline">10</span>
                    </a>
                @endcan

                @can('products.manage')
                    <a href="{{ route('products.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('products.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Products</span>
                        <span class="badge badge-outline">11</span>
                    </a>
                @endcan

                @can('reports.view')
                    <a href="{{ route('reports.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 transition {{ request()->routeIs('reports.*') ? 'border-amber-300/40 bg-amber-400/10 text-white' : 'border-white/10 bg-slate-950/40 text-slate-300 hover:border-white/20 hover:text-white' }}">
                        <span>Reports</span>
                        <span class="badge badge-outline">12</span>
                    </a>
                @endcan
            </nav>

            <form action="{{ route('logout') }}" method="POST" class="mt-6">
                @csrf
                <button type="submit" class="btn btn-outline btn-warning w-full rounded-2xl">
                    Logout
                </button>
            </form>
        </aside>

        <main class="flex-1 lg:pl-6">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>
</html>
