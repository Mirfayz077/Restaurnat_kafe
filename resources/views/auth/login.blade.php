@extends('layouts.auth')

@section('content')
<div class="grid w-full gap-6 lg:grid-cols-[1.08fr_0.92fr]">

    <section class="soft-panel rounded-[2rem] border border-white/10 p-8 lg:p-10">
        <div class="flex flex-wrap items-center gap-3">
            <span class="rounded-full border border-amber-300/20 bg-amber-400/10 px-4 py-2 text-xs uppercase tracking-[0.3em] text-amber-200">
                Existing login
            </span>

            <a href="{{ route('home') }}"
               class="rounded-full border border-white/10 bg-slate-950/40 px-4 py-2 text-xs uppercase tracking-[0.25em] text-slate-300 transition hover:text-white">
                Back to main page
            </a>
        </div>

        <h1 class="mt-6 max-w-xl text-4xl font-semibold text-white">
            Login qiling va tizim sizni rolingizga mos kabinetga olib kiradi.
        </h1>

        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
            Manager, cashier, waiter, chef va bartender uchun alohida kabinetlar tayyor.
            Bu yerda demo foydalanuvchilar ham ko'rsatilgan, shuning uchun test qilish oson bo'ladi.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

            @foreach([
                ['role'=>'Admin','login'=>'admin','password'=>'admin456'],
                ['role'=>'Manager','login'=>'manager','password'=>'manager456'],
                ['role'=>'Cashier','login'=>'cashier','password'=>'cashier456'],
                ['role'=>'Waiter','login'=>'waiter','password'=>'waiter456'],
                ['role'=>'Chef','login'=>'chef','password'=>'chef456'],
                ['role'=>'Bartender','login'=>'bartender','password'=>'bartender456'],
            ] as $account)

            <button type="button"
                    class="demo-account rounded-[1.5rem] border border-white/10 bg-slate-950/55 p-4 text-left transition hover:border-amber-300/30 hover:bg-amber-400/10"
                    data-login="{{ $account['login'] }}"
                    data-password="{{ $account['password'] }}">

                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">
                    {{ $account['role'] }}
                </p>

                <p class="mt-3 text-base font-semibold text-white">
                    {{ $account['login'] }}
                </p>

                <p class="mt-1 text-sm text-slate-400">
                    {{ $account['password'] }}
                </p>

            </button>

            @endforeach

        </div>
    </section>


    <section class="soft-panel rounded-[2rem] border border-white/10 p-8">

        <p class="text-xs uppercase tracking-[0.35em] text-amber-200">
            Secure access
        </p>

        <h2 class="mt-3 text-3xl font-semibold text-white">
            Accountga kiring
        </h2>

        <p class="mt-3 text-sm leading-7 text-slate-300">
            Login tugagach, sizga kerakli actionlar ko'rinadigan kabinet ochiladi.
        </p>

        <form id="loginForm"
              action="{{ route('login.store') }}"
              method="POST"
              class="mt-8 space-y-5">

            @csrf

            <label class="block">
                <span class="mb-2 block text-sm text-slate-300">
                    Login
                </span>

                <input
                    id="login"
                    type="text"
                    name="login"
                    value="{{ old('login') }}"
                    required
                    autocomplete="username"
                    class="input input-bordered w-full rounded-2xl bg-slate-950/70 text-white"
                    placeholder="manager"
                >

                @error('login')
                <span class="mt-2 block text-sm text-rose-300">
                    {{ $message }}
                </span>
                @enderror

            </label>


            <label class="block">
                <span class="mb-2 block text-sm text-slate-300">
                    Parol
                </span>

                <div class="relative">

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="input input-bordered w-full rounded-2xl bg-slate-950/70 pr-14 text-white"
                        placeholder="password"
                    >

                    <button id="togglePassword"
                            type="button"
                            class="absolute inset-y-0 right-0 px-4 text-sm text-slate-400 hover:text-white">
                        Show
                    </button>

                </div>

            </label>


            <label class="flex items-center gap-3 text-sm text-slate-300">
                <input type="checkbox"
                       name="remember"
                       value="1"
                       class="checkbox checkbox-sm">
                Meni eslab qol
            </label>


            <button type="submit"
                    class="btn btn-warning w-full rounded-2xl text-base">
                Kirish
            </button>

        </form>

    </section>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const loginInput = document.getElementById('login');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    if (togglePassword) {
        togglePassword.addEventListener('click', function () {

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                togglePassword.innerText = 'Hide';
            } else {
                passwordInput.type = 'password';
                togglePassword.innerText = 'Show';
            }

        });
    }

    const accounts = document.querySelectorAll('.demo-account');

    accounts.forEach(function(btn){

        btn.addEventListener('click', function(){

            const login = this.dataset.login;
            const password = this.dataset.password;

            if(loginInput && passwordInput){
                loginInput.value = login;
                passwordInput.value = password;
            }

        });

    });

});
</script>

@endsection