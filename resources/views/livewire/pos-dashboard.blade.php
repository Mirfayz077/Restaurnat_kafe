<div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 shadow-2xl shadow-black/40 backdrop-blur">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(251,191,36,0.16),transparent_28%),radial-gradient(circle_at_top_right,rgba(59,130,246,0.14),transparent_24%)]"></div>

    <div class="relative grid gap-6 p-5 lg:grid-cols-[1.7fr_1fr] lg:p-8" x-data="{ sidebarOpen: true, note: 'Kitchen is clear', spotlight: 0 }">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 rounded-[1.75rem] border border-white/10 bg-white/5 p-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-amber-300/80">Restaurant POS</p>
                    <h1 class="mt-2 text-3xl font-semibold text-white sm:text-4xl">Local-first dashboard with Livewire, Alpine and local assets</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        This layout is designed as an offline-friendly demo surface for the Laravel stack. The data, controls, and component states all stay inside the app.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="stats shadow bg-slate-900 text-white border border-white/10">
                        <div class="stat p-4">
                            <div class="stat-title text-slate-400">Served</div>
                            <div class="stat-value text-amber-300">{{ $servedToday }}</div>
                        </div>
                    </div>
                    <div class="stats shadow bg-slate-900 text-white border border-white/10">
                        <div class="stat p-4">
                            <div class="stat-title text-slate-400">Tickets</div>
                            <div class="stat-value text-emerald-300">{{ $ticketsOpen }}</div>
                        </div>
                    </div>
                    <div class="stats shadow bg-slate-900 text-white border border-white/10">
                        <div class="stat p-4">
                            <div class="stat-title text-slate-400">Rush mode</div>
                            <div class="stat-value text-sky-300">{{ $rushMode ? 'ON' : 'OFF' }}</div>
                        </div>
                    </div>
                    <div class="stats shadow bg-slate-900 text-white border border-white/10">
                        <div class="stat p-4">
                            <div class="stat-title text-slate-400">Total</div>
                            <div class="stat-value text-fuchsia-300">{{ number_format($cartTotal) }} so'm</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-[1.75rem] border border-white/10 bg-slate-900/80 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="badge badge-warning badge-lg">daisyUI</div>
                            <div class="badge badge-outline badge-lg text-white/80">Livewire</div>
                        </div>

                        <label class="input input-bordered flex items-center gap-2 bg-slate-950 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.3-4.3m1.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                            <input type="text" class="grow bg-transparent outline-none" placeholder="Search menu items..." wire:model.live.debounce.300ms="search">
                        </label>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($categories as $key => $label)
                            <button
                                type="button"
                                wire:click="setCategory('{{ $key }}')"
                                class="btn btn-sm {{ $category === $key ? 'btn-primary' : 'btn-ghost text-white/70' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @forelse ($visibleMenu as $item)
                            <article class="card border border-white/10 bg-slate-950/70 shadow-xl">
                                <div class="card-body p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="card-title text-lg text-white">{{ $item['name'] }}</h3>
                                            <p class="mt-1 text-sm text-slate-400">{{ $item['description'] }}</p>
                                        </div>
                                        <div class="badge badge-secondary">{{ $item['tag'] }}</div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $item['category'] }}</p>
                                            <p class="text-xl font-semibold text-amber-300">{{ number_format($item['price']) }} so'm</p>
                                        </div>
                                        <button type="button" wire:click="addToCart({{ $item['id'] }})" class="btn btn-warning">Add</button>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="md:col-span-2">
                                <div class="alert alert-info border border-sky-400/30 bg-sky-400/10 text-sky-100">
                                    No menu items match the current filter.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <aside class="space-y-5">
                    <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Alpine.js</p>
                                <h2 class="mt-1 text-xl font-semibold text-white">Quick notes</h2>
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm text-white" @click="sidebarOpen = !sidebarOpen">
                                <span x-text="sidebarOpen ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>

                        <div x-show="sidebarOpen" x-transition class="mt-4 space-y-3">
                            <div class="rounded-2xl border border-white/10 bg-slate-950/70 p-4 text-sm text-slate-300">
                                <p class="font-medium text-white">Shift note</p>
                                <p class="mt-1" x-text="note"></p>
                            </div>

                            <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/70 p-4">
                                <div>
                                    <p class="font-medium text-white">Rush mode</p>
                                    <p class="text-sm text-slate-400">Fast lane for visible orders</p>
                                </div>
                                <input type="checkbox" class="toggle toggle-warning" wire:model.live="rushMode">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] border border-white/10 bg-slate-900/80 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Flowbite</p>
                                <h2 class="mt-1 text-xl font-semibold text-white">Accordion block</h2>
                            </div>
                            <button
                                type="button"
                                class="btn btn-ghost btn-sm text-white"
                                data-collapse-toggle="prep-accordion"
                                aria-controls="prep-accordion"
                                aria-expanded="false"
                            >
                                Toggle
                            </button>
                        </div>

                        <div id="prep-accordion" class="mt-4 hidden space-y-3">
                            <div class="rounded-2xl border border-white/10 bg-slate-950/70 p-4 text-sm text-slate-300">
                                Flowbite-ready data attributes are present here for a local JS bundle to activate.
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/70 p-4 text-sm text-slate-300">
                                No external CDN is used in this demo surface.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] border border-white/10 bg-slate-900/80 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Swiper</p>
                                <h2 class="mt-1 text-xl font-semibold text-white">Featured slides</h2>
                            </div>
                        </div>

                        <div class="mt-4 space-y-4" data-swiper-wrapper>
                            <div class="hero-swiper swiper overflow-hidden rounded-3xl border border-white/10 bg-slate-950/80 p-4" data-swiper="hero">
                                <div class="swiper-wrapper">
                                    @foreach ($slides as $index => $slide)
                                        <div class="swiper-slide">
                                            <div class="rounded-3xl border border-white/10 bg-gradient-to-br from-amber-400/20 to-sky-400/10 p-5">
                                                <p class="text-xs uppercase tracking-[0.35em] text-amber-200/80">{{ $slide['title'] }}</p>
                                                <p class="mt-3 text-3xl font-semibold text-white">{{ $slide['value'] }}</p>
                                                <p class="mt-2 text-sm leading-6 text-slate-200">{{ $slide['copy'] }}</p>
                                                <div class="mt-4 inline-flex rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs text-white/80">
                                                    Slide {{ $index + 1 }} of {{ count($slides) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <div class="swiper-pagination !static !w-auto" data-swiper-pagination></div>

                                <div class="join">
                                    <button type="button" class="btn btn-sm join-item btn-ghost text-white" data-swiper-prev wire:click="previousSlide">Prev</button>
                                    <button type="button" class="btn btn-sm join-item btn-ghost text-white" data-swiper-next wire:click="nextSlide">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="space-y-5">
            <div class="rounded-[1.75rem] border border-white/10 bg-slate-900/80 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Livewire</p>
                        <h2 class="mt-1 text-xl font-semibold text-white">Ticket board</h2>
                    </div>
                    <div class="badge badge-success badge-outline">{{ count($cartItems) }} items</div>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($cartItems as $item)
                        <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/70 p-4">
                            <div>
                                <p class="font-medium text-white">{{ $item['name'] }}</p>
                                <p class="text-sm text-slate-400">{{ $item['quantity'] }} x {{ number_format($item['price']) }} so'm</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" class="btn btn-xs btn-ghost text-white" wire:click="removeFromCart({{ $item['id'] }})">-</button>
                                <span class="min-w-6 text-center text-sm font-semibold text-amber-300">{{ $item['quantity'] }}</span>
                                <button type="button" class="btn btn-xs btn-warning" wire:click="addToCart({{ $item['id'] }})">+</button>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-warning border border-amber-400/30 bg-amber-400/10 text-amber-50">
                            Ticket board is empty. Add something from the menu.
                        </div>
                    @endforelse
                </div>

                <div class="mt-5 rounded-2xl border border-white/10 bg-gradient-to-r from-emerald-400/20 to-cyan-400/10 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-300">Estimated total</p>
                        <p class="text-2xl font-semibold text-white">{{ number_format($cartTotal) }} so'm</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">State snapshot</p>
                <div class="mt-3 grid gap-3 text-sm text-slate-300">
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/70 p-4">
                        <span>Current category</span>
                        <span class="badge badge-outline text-white">{{ $category }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/70 p-4">
                        <span>Search term</span>
                        <span class="text-white">{{ $search === '' ? 'empty' : $search }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/70 p-4">
                        <span>Active slide index</span>
                        <span class="text-white">{{ $activeSlide }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/70 p-4">
                        <span>Spotlight title</span>
                        <span class="text-white">{{ $slides[$activeSlide]['title'] ?? 'none' }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
