@extends('layouts.auth')

@php
    $initialCategory = $menuCatalog->first();
    $initialItems = collect($initialCategory['items'] ?? []);
    $initialVisibleItems = $initialItems->take(9);
    $initialItem = $initialVisibleItems->first();
    $initialPages = max(1, (int) ceil($initialItems->count() / 9));
@endphp

@section('content')
    <div class="self-start w-full">
        <section
            id="menuBrowserShell"
            class="menu-browser-shell soft-panel rounded-[2.2rem] border border-white/10 p-4 sm:p-6 lg:p-8"
            data-view-mode="signature"
        >
            <header class="menu-browser-header border-b border-white/10 pb-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="cafe-mark">
                            CP
                        </div>

                        <div class="min-w-0">
                            <p class="menu-browser-kicker">Cafe landing</p>
                            <h1 class="cafe-display truncate text-3xl text-white sm:text-4xl">
                                {{ config('app.name', 'Restaurant POS') }}
                            </h1>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="hidden flex-wrap items-center gap-2 lg:flex">
                            <span class="menu-browser-pill">{{ $stats['menuItems'] }} menu items</span>
                            <span class="menu-browser-pill">{{ $stats['sections'] }} sections</span>
                            <span class="menu-browser-pill">{{ $stats['branches'] }} branches</span>
                        </div>

                        @auth
                            <a href="{{ route('cabinet') }}" class="menu-browser-login rounded-2xl px-6">Cabinet</a>
                        @else
                            <a href="{{ route('login') }}" class="menu-browser-login rounded-2xl px-6">Login</a>
                        @endauth
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                    <div class="max-w-2xl">
                        <p id="menuCue" class="menu-browser-kicker">
                            {{ $initialCategory['cue'] }}
                        </p>
                        <p id="menuSummary" class="menu-browser-lead mt-3">
                            {{ $initialCategory['summary'] }}
                        </p>
                    </div>

                    <nav class="pos-scroll flex gap-2 overflow-x-auto pb-2" id="menuTabs">
                        @foreach ($menuCatalog as $category)
                            <button
                                type="button"
                                class="menu-browser-tab {{ $category['slug'] === $initialCategory['slug'] ? 'is-active' : '' }} {{ $category['theme'] }}"
                                data-category="{{ $category['slug'] }}"
                                aria-pressed="{{ $category['slug'] === $initialCategory['slug'] ? 'true' : 'false' }}"
                            >
                                <span>{{ $category['name'] }}</span>
                                <strong>{{ $category['count'] }}</strong>
                            </button>
                        @endforeach
                    </nav>
                </div>
            </header>

            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
                <div class="min-w-0">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 id="menuTitle" class="cafe-display text-4xl text-white sm:text-5xl">
                                {{ $initialCategory['name'] }}
                            </h2>
                            <p class="menu-browser-caption mt-3">
                                Bo'limni almashtirsangiz, shu joydagi itemlar JavaScript orqali shu sahifada yangilanadi.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.25em]">
                            <span id="menuCountBadge" class="menu-browser-pill">{{ $initialCategory['count'] }} items</span>
                            <span id="menuPageBadge" class="menu-browser-pill">Page 1 / {{ $initialPages }}</span>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 2xl:flex-row 2xl:items-center 2xl:justify-between">
                        <div class="menu-browser-modebar" id="menuViewModes">
                            <button type="button" class="menu-browser-mode is-active" data-view-switch="signature" aria-pressed="true">
                                <span>Signature mode</span>
                                <strong>Atmosphere</strong>
                            </button>
                            <button type="button" class="menu-browser-mode" data-view-switch="express" aria-pressed="false">
                                <span>Express mode</span>
                                <strong>Fast scan</strong>
                            </button>
                        </div>

                        <div class="menu-browser-actionbar" id="menuMagicActions">
                            <button type="button" class="menu-browser-action is-accent" data-magic-action="random">
                                Surprise me
                            </button>
                            <button type="button" class="menu-browser-action" data-magic-action="cheapest">
                                Best deal
                            </button>
                            <button type="button" class="menu-browser-action" data-magic-action="premium">
                                Chef's pick
                            </button>
                        </div>
                    </div>

                    <p id="menuModeHint" class="menu-browser-caption mt-3">
                        Signature mode premium preview beradi, Express mode esa itemlarni tezroq topish uchun narx bo'yicha tartiblaydi.
                    </p>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3" id="menuGrid">
                        @foreach ($initialVisibleItems as $item)
                            <button
                                type="button"
                                class="menu-browser-card {{ $loop->first ? 'is-active' : '' }}"
                                data-item-id="{{ $item['id'] }}"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="menu-browser-card-title">{{ $item['name'] }}</p>
                                        <p class="menu-browser-card-copy">
                                            {{ $item['description'] ?: "Tavsif hozircha kiritilmagan." }}
                                        </p>
                                    </div>

                                    <span class="menu-browser-station">{{ $item['station'] }}</span>
                                </div>

                                <div class="mt-5 flex items-center justify-between gap-3">
                                    <span class="menu-browser-price">{{ number_format($item['price'], 0, '.', ' ') }} so'm</span>
                                    <span class="menu-browser-sku">{{ $item['sku'] }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p id="menuResultsInfo" class="menu-browser-caption">
                            Showing {{ $initialVisibleItems->count() }} of {{ $initialItems->count() }} items
                        </p>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" id="menuPrev" class="menu-browser-nav" {{ $initialPages <= 1 ? 'disabled' : '' }}>
                                Prev
                            </button>

                            <div class="flex flex-wrap items-center gap-2" id="menuPager">
                                @for ($page = 1; $page <= $initialPages; $page++)
                                    <button
                                        type="button"
                                        class="menu-browser-page {{ $page === 1 ? 'is-active' : '' }}"
                                        data-page="{{ $page }}"
                                    >
                                        {{ $page }}
                                    </button>
                                @endfor
                            </div>

                            <button type="button" id="menuNext" class="menu-browser-nav" {{ $initialPages <= 1 ? 'disabled' : '' }}>
                                Next
                            </button>
                        </div>
                    </div>
                </div>

                <aside class="menu-browser-aside">
                    <div class="menu-browser-detail">
                        <p class="menu-browser-kicker">Selected item</p>
                        <p id="menuSelectedCategory" class="menu-browser-subtle mt-3">
                            {{ $initialCategory['name'] }}
                        </p>
                        <h3 id="menuSelectedName" class="cafe-display mt-3 text-4xl text-white">
                            {{ $initialItem['name'] ?? 'Menu preview' }}
                        </h3>
                        <p id="menuSelectedDescription" class="menu-browser-caption mt-4">
                            {{ $initialItem['description'] ?? "Kategoriya tanlanganda shu yerda itemning description, narxi va boshqa kerakli qiymatlar ko'rinadi." }}
                        </p>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                            <div class="menu-browser-meta">
                                <span>Price</span>
                                <strong id="menuSelectedPrice">
                                    {{ $initialItem ? number_format($initialItem['price'], 0, '.', ' ') . " so'm" : 'No price' }}
                                </strong>
                            </div>
                            <div class="menu-browser-meta">
                                <span>Station</span>
                                <strong id="menuSelectedStation">{{ $initialItem['station'] ?? 'Kitchen' }}</strong>
                            </div>
                            <div class="menu-browser-meta">
                                <span>SKU</span>
                                <strong id="menuSelectedSku">{{ $initialItem['sku'] ?? 'N/A' }}</strong>
                            </div>
                            <div class="menu-browser-meta">
                                <span>Category</span>
                                <strong id="menuSelectedTheme">{{ $initialCategory['cue'] }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="menu-browser-note">
                        <p class="menu-browser-kicker">Mood control</p>
                        <p id="menuLiveTitle" class="menu-browser-note-title mt-3">Signature mode active</p>
                        <p id="menuLiveDescription" class="menu-browser-caption mt-3">
                            Premium atmosfera, kengroq preview va tanlangan item uchun boyroq fokus.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <span id="menuLiveBadge" class="menu-browser-chip">Atmosphere</span>
                            <span id="menuActionBadge" class="menu-browser-chip">Manual pick</span>
                        </div>
                        <p id="menuMagicFeedback" class="menu-browser-caption mt-4">
                            Surprise me random itemni topadi, Best deal eng hamyonbop variantni, Chef's pick esa kuchli signature itemni ko'rsatadi.
                        </p>
                    </div>
                </aside>
            </div>

            <div id="menuToast" class="menu-browser-toast" aria-live="polite" aria-atomic="true"></div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuCatalog = {{ Illuminate\Support\Js::from($menuCatalog->values()) }};

            if (!Array.isArray(menuCatalog) || menuCatalog.length === 0) {
                return;
            }

            const perPage = 9;
            const modeMeta = {
                signature: {
                    title: 'Signature mode active',
                    badge: 'Atmosphere',
                    description: "Premium atmosfera, kengroq preview va tanlangan item uchun boyroq fokus.",
                    hint: "Signature mode premium preview beradi, kartalar tabiiy tartibda qoladi va tanlov hissi kuchayadi.",
                },
                express: {
                    title: 'Express mode active',
                    badge: 'Fast scan',
                    description: "Tez tanlash uchun itemlar narx bo'yicha tartiblanadi va kartalar ixchamroq ishlaydi.",
                    hint: "Express mode itemlarni hamyonbopidan boshlab ko'rsatadi, user qisqa va tez tanlov qila oladi.",
                },
            };
            const state = {
                activeCategory: menuCatalog[0].slug,
                page: 1,
                selectedItemId: menuCatalog[0].items[0]?.id ?? null,
                viewMode: 'signature',
                lastActionLabel: 'Manual pick',
                experienceNote: "Surprise me random itemni topadi, Best deal eng hamyonbop variantni, Chef's pick esa kuchli signature itemni ko'rsatadi.",
            };

            const refs = {
                shell: document.getElementById('menuBrowserShell'),
                modeButtons: Array.from(document.querySelectorAll('[data-view-switch]')),
                actionButtons: Array.from(document.querySelectorAll('[data-magic-action]')),
                tabs: Array.from(document.querySelectorAll('[data-category]')),
                grid: document.getElementById('menuGrid'),
                pager: document.getElementById('menuPager'),
                prev: document.getElementById('menuPrev'),
                next: document.getElementById('menuNext'),
                cue: document.getElementById('menuCue'),
                summary: document.getElementById('menuSummary'),
                title: document.getElementById('menuTitle'),
                countBadge: document.getElementById('menuCountBadge'),
                pageBadge: document.getElementById('menuPageBadge'),
                resultsInfo: document.getElementById('menuResultsInfo'),
                modeHint: document.getElementById('menuModeHint'),
                selectedCategory: document.getElementById('menuSelectedCategory'),
                selectedName: document.getElementById('menuSelectedName'),
                selectedDescription: document.getElementById('menuSelectedDescription'),
                selectedPrice: document.getElementById('menuSelectedPrice'),
                selectedStation: document.getElementById('menuSelectedStation'),
                selectedSku: document.getElementById('menuSelectedSku'),
                selectedTheme: document.getElementById('menuSelectedTheme'),
                liveTitle: document.getElementById('menuLiveTitle'),
                liveDescription: document.getElementById('menuLiveDescription'),
                liveBadge: document.getElementById('menuLiveBadge'),
                actionBadge: document.getElementById('menuActionBadge'),
                magicFeedback: document.getElementById('menuMagicFeedback'),
                toast: document.getElementById('menuToast'),
            };

            const formatPrice = (value) => `${new Intl.NumberFormat('ru-RU').format(Math.round(Number(value || 0)))} so'm`;
            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');

            let toastTimer;
            const getCategory = () => menuCatalog.find((category) => category.slug === state.activeCategory) ?? menuCatalog[0];
            const getOrderedItems = (category) => {
                const items = [...(category?.items ?? [])];

                if (state.viewMode === 'express') {
                    return items.sort((left, right) => {
                        const leftPrice = Number(left.price || 0);
                        const rightPrice = Number(right.price || 0);

                        if (leftPrice !== rightPrice) {
                            return leftPrice - rightPrice;
                        }

                        return String(left.name ?? '').localeCompare(String(right.name ?? ''));
                    });
                }

                return items;
            };
            const getTotalPages = (category) => Math.max(1, Math.ceil(getOrderedItems(category).length / perPage));
            const getPageItems = (category) => {
                const items = getOrderedItems(category);
                const start = (state.page - 1) * perPage;
                return items.slice(start, start + perPage);
            };

            const syncSelection = (category, pageItems) => {
                const orderedItems = getOrderedItems(category);
                const selected = pageItems.find((item) => String(item.id) === String(state.selectedItemId));
                if (!selected) {
                    state.selectedItemId = pageItems[0]?.id ?? orderedItems[0]?.id ?? null;
                }
                return orderedItems.find((item) => String(item.id) === String(state.selectedItemId)) ?? null;
            };

            const renderTabs = (category) => {
                refs.tabs.forEach((tab) => {
                    const isActive = tab.dataset.category === category.slug;
                    tab.classList.toggle('is-active', isActive);
                    tab.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            };

            const renderExperience = () => {
                const activeMode = modeMeta[state.viewMode] ?? modeMeta.signature;

                refs.shell?.setAttribute('data-view-mode', state.viewMode);
                refs.modeButtons.forEach((button) => {
                    const isActive = button.dataset.viewSwitch === state.viewMode;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });

                refs.modeHint.textContent = activeMode.hint;
                refs.liveTitle.textContent = activeMode.title;
                refs.liveDescription.textContent = activeMode.description;
                refs.liveBadge.textContent = activeMode.badge;
                refs.actionBadge.textContent = state.lastActionLabel;
                refs.magicFeedback.textContent = state.experienceNote;
            };

            const updateExperience = (label, note) => {
                state.lastActionLabel = label;
                state.experienceNote = note;
            };

            const showToast = (message) => {
                if (!refs.toast) {
                    return;
                }

                refs.toast.textContent = message;
                refs.toast.classList.add('is-visible');

                window.clearTimeout(toastTimer);
                toastTimer = window.setTimeout(() => {
                    refs.toast.classList.remove('is-visible');
                }, 2200);
            };

            const moveSelectionToItem = (categorySlug, itemId) => {
                state.activeCategory = categorySlug;

                const category = getCategory();
                const orderedItems = getOrderedItems(category);
                const itemIndex = orderedItems.findIndex((item) => String(item.id) === String(itemId));

                state.page = itemIndex >= 0 ? Math.floor(itemIndex / perPage) + 1 : 1;
                state.selectedItemId = itemIndex >= 0 ? orderedItems[itemIndex].id : orderedItems[0]?.id ?? null;
            };

            const activateItem = (payload, label, note) => {
                if (!payload?.item || !payload.categorySlug) {
                    return;
                }

                updateExperience(label, note);
                moveSelectionToItem(payload.categorySlug, payload.item.id);
                render();
                showToast(`${label}: ${payload.item.name}`);
            };

            const renderGrid = (category, pageItems) => {
                if (!pageItems.length) {
                    refs.grid.innerHTML = `
                        <div class="menu-browser-empty sm:col-span-2 xl:col-span-3">
                            Hozircha bu bo'lim uchun itemlar mavjud emas.
                        </div>
                    `;
                    return;
                }

                refs.grid.innerHTML = pageItems.map((item) => {
                    const isActive = String(item.id) === String(state.selectedItemId);
                    return `
                        <button type="button" class="menu-browser-card ${isActive ? 'is-active' : ''}" data-item-id="${escapeHtml(item.id)}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="menu-browser-card-title">${escapeHtml(item.name)}</p>
                                    <p class="menu-browser-card-copy">${escapeHtml(item.description || "Tavsif hozircha kiritilmagan.")}</p>
                                </div>
                                <span class="menu-browser-station">${escapeHtml(item.station || 'Kitchen')}</span>
                            </div>
                            <div class="mt-5 flex items-center justify-between gap-3">
                                <span class="menu-browser-price">${formatPrice(item.price)}</span>
                                <span class="menu-browser-sku">${escapeHtml(item.sku || 'N/A')}</span>
                            </div>
                        </button>
                    `;
                }).join('');
            };

            const renderPager = (category) => {
                const totalPages = getTotalPages(category);
                refs.pager.innerHTML = Array.from({ length: totalPages }, (_, index) => {
                    const page = index + 1;
                    return `
                        <button type="button" class="menu-browser-page ${page === state.page ? 'is-active' : ''}" data-page="${page}">
                            ${page}
                        </button>
                    `;
                }).join('');

                refs.prev.disabled = state.page <= 1;
                refs.next.disabled = state.page >= totalPages;
            };

            const renderSelected = (category, selectedItem) => {
                refs.selectedCategory.textContent = category.name;
                refs.selectedTheme.textContent = category.cue;

                if (!selectedItem) {
                    refs.selectedName.textContent = 'Menu preview';
                    refs.selectedDescription.textContent = "Kategoriya tanlanganda shu yerda itemning description, narxi va boshqa kerakli qiymatlar ko'rinadi.";
                    refs.selectedPrice.textContent = 'No price';
                    refs.selectedStation.textContent = 'Kitchen';
                    refs.selectedSku.textContent = 'N/A';
                    return;
                }

                refs.selectedName.textContent = selectedItem.name;
                refs.selectedDescription.textContent = selectedItem.description || "Tavsif hozircha kiritilmagan.";
                refs.selectedPrice.textContent = formatPrice(selectedItem.price);
                refs.selectedStation.textContent = selectedItem.station || 'Kitchen';
                refs.selectedSku.textContent = selectedItem.sku || 'N/A';
            };

            const renderMeta = (category, pageItems) => {
                const totalPages = getTotalPages(category);
                const start = pageItems.length ? ((state.page - 1) * perPage) + 1 : 0;
                const end = pageItems.length ? start + pageItems.length - 1 : 0;
                const orderedItems = getOrderedItems(category);

                refs.cue.textContent = category.cue;
                refs.summary.textContent = category.summary;
                refs.title.textContent = category.name;
                refs.countBadge.textContent = `${category.count} items`;
                refs.pageBadge.textContent = `Page ${state.page} / ${totalPages}`;
                refs.resultsInfo.textContent = pageItems.length
                    ? `Showing ${start}-${end} of ${orderedItems.length} items`
                    : 'No items in this section';
            };

            const render = () => {
                const category = getCategory();
                state.page = Math.min(Math.max(1, state.page), getTotalPages(category));
                const pageItems = getPageItems(category);
                const selectedItem = syncSelection(category, pageItems);

                renderTabs(category);
                renderExperience();
                renderMeta(category, pageItems);
                renderGrid(category, pageItems);
                renderPager(category);
                renderSelected(category, selectedItem);
            };

            document.getElementById('menuViewModes')?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-view-switch]');
                if (!button) {
                    return;
                }

                const nextMode = button.dataset.viewSwitch;
                if (!nextMode || nextMode === state.viewMode) {
                    return;
                }

                state.viewMode = nextMode;
                const category = getCategory();
                const orderedItems = getOrderedItems(category);
                state.page = 1;
                state.selectedItemId = orderedItems[0]?.id ?? null;
                updateExperience(
                    'Mode switch',
                    nextMode === 'express'
                        ? "Express mode yoqildi: itemlar narx bo'yicha tartiblandi va tezroq scan qilish uchun tayyor."
                        : "Signature mode yoqildi: premium preview va tabiiy tartib qaytdi."
                );
                render();
                showToast(nextMode === 'express' ? 'Express mode on' : 'Signature mode on');
            });

            document.getElementById('menuTabs')?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-category]');
                if (!button) {
                    return;
                }

                const nextCategory = button.dataset.category;
                if (!nextCategory || nextCategory === state.activeCategory) {
                    return;
                }

                state.activeCategory = nextCategory;
                state.page = 1;
                state.selectedItemId = getOrderedItems(getCategory())[0]?.id ?? null;
                updateExperience('Section switch', "Bo'lim almashtirildi, shu panelning ichida yangi itemlar ko'rsatildi.");
                render();
            });

            refs.grid?.addEventListener('click', (event) => {
                const card = event.target.closest('[data-item-id]');
                if (!card) {
                    return;
                }

                state.selectedItemId = card.dataset.itemId;
                updateExperience('Manual pick', "Tanlangan item detail panelda darhol yangilandi.");
                render();
            });

            refs.pager?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-page]');
                if (!button) {
                    return;
                }

                state.page = Number(button.dataset.page || 1);
                const category = getCategory();
                state.selectedItemId = getPageItems(category)[0]?.id ?? getOrderedItems(category)[0]?.id ?? null;
                updateExperience('Page move', "Pagination orqali keyingi itemlar ham shu blokning o'zida ochildi.");
                render();
            });

            refs.prev?.addEventListener('click', () => {
                if (refs.prev.disabled) {
                    return;
                }

                state.page -= 1;
                const category = getCategory();
                state.selectedItemId = getPageItems(category)[0]?.id ?? getOrderedItems(category)[0]?.id ?? null;
                updateExperience('Page move', "Oldingi page ochildi va birinchi item avtomatik fokusga tushdi.");
                render();
            });

            refs.next?.addEventListener('click', () => {
                if (refs.next.disabled) {
                    return;
                }

                state.page += 1;
                const category = getCategory();
                state.selectedItemId = getPageItems(category)[0]?.id ?? getOrderedItems(category)[0]?.id ?? null;
                updateExperience('Page move', "Keyingi page ochildi va user oqimdan chiqmaydi.");
                render();
            });

            document.getElementById('menuMagicActions')?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-magic-action]');
                if (!button) {
                    return;
                }

                const action = button.dataset.magicAction;
                const currentCategory = getCategory();
                const currentItems = getOrderedItems(currentCategory);

                if (action === 'random') {
                    const allItems = menuCatalog.flatMap((category) => (category.items ?? []).map((item) => ({
                        categorySlug: category.slug,
                        categoryName: category.name,
                        item,
                    })));

                    if (!allItems.length) {
                        return;
                    }

                    const randomItem = allItems[Math.floor(Math.random() * allItems.length)];
                    activateItem(
                        randomItem,
                        'Surprise me',
                        `${randomItem.categoryName} bo'limidan ${randomItem.item.name} random tanlov sifatida chiqarildi.`
                    );
                    return;
                }

                if (!currentItems.length) {
                    return;
                }

                if (action === 'cheapest') {
                    const cheapestItem = currentItems.reduce((best, item) => {
                        if (!best) {
                            return item;
                        }

                        return Number(item.price || 0) < Number(best.price || 0) ? item : best;
                    }, null);

                    activateItem(
                        { categorySlug: currentCategory.slug, item: cheapestItem },
                        'Best deal',
                        `${currentCategory.name} bo'limidagi eng hamyonbop variant ${cheapestItem?.name ?? 'item'} sifatida topildi.`
                    );
                    return;
                }

                if (action === 'premium') {
                    const premiumItem = currentItems.reduce((best, item) => {
                        if (!best) {
                            return item;
                        }

                        return Number(item.price || 0) > Number(best.price || 0) ? item : best;
                    }, null);

                    activateItem(
                        { categorySlug: currentCategory.slug, item: premiumItem },
                        "Chef's pick",
                        `${currentCategory.name} bo'limida ko'proq premium taassurot beradigan ${premiumItem?.name ?? 'item'} ajratib ko'rsatildi.`
                    );
                }
            });

            refs.actionButtons.forEach((button) => {
                button.addEventListener('mouseleave', () => {
                    button.blur();
                });
            });

            render();
        });
    </script>
@endsection
