@extends('layouts.auth')

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

body, html {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #fff;
    overflow-x: hidden;
}

.login-wrapper {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 2rem;
    min-height: 100vh;
    align-items: center;
    padding: 2rem;
}

.soft-panel, .form-panel {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius: 2rem;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 8px 30px rgba(0,0,0,0.4);
    padding: 2.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.soft-panel:hover, .form-panel:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
}

.role-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 2rem;
}

.role-card {
    padding: 1rem;
    border-radius: 1.5rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.2);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    text-align: center;
    font-weight: 500;
    transition: all 0.3s ease;
}

.role-card:hover {
    background: rgba(250,204,21,0.15);
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 10px 25px rgba(250,204,21,0.4);
}

.form-inner {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.form-subtitle {
    font-size: 0.95rem;
    color: #f0e68c;
}

.field-group {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.field-group input {
    padding: 0.75rem 3rem 0.75rem 1rem; /* toggle button uchun right padding */
    border-radius: 1rem;
    border: none;
    background: rgba(255,255,255,0.1);
    color: #fff;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s ease;
    width: 100%;
    backdrop-filter: blur(10px);
}

.field-group input::placeholder {
    color: rgba(255,255,255,0.6);
}

input:focus {
    box-shadow: 0 0 0 3px rgba(250,204,21,0.4);
}

.toggle-pw {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #facc15;
    cursor: pointer;
    font-size: 1.1rem;
    transition: color 0.3s ease;
    z-index: 10;
}

.toggle-pw:hover {
    color: #ffd700;
}

.submit-btn {
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    background: linear-gradient(135deg, #facc15, #fcd34d);
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    background: linear-gradient(135deg, #eab308, #fbbf24);
    transform: translateY(-2px) scale(1.02);
}

#particles-js {
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: -1;
    top: 0;
    left: 0;
}

@media (max-width: 1024px) {
    .login-wrapper {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1rem;
    }
}
</style>

<div id="particles-js"></div>

<div class="login-wrapper">

    <section class="soft-panel">
        <p class="text-xs uppercase tracking-wide text-amber-300">Restaurant POS MVP</p>
        <h1 class="mt-4 text-2xl font-semibold text-white">
            Login, branch flow va checkout bilan ishlaydigan birinchi versiya
        </h1>
        <p class="mt-4 text-sm leading-7 text-slate-300">
            Tizimda admin, manager, cashier, waiter, chef va bartender rollari tayyor. Front-of-house, kitchen, bar, payment va basic report oqimi bitta loyihada jamlangan.
        </p>

        <div class="role-cards">
            @foreach ([
                ['role'=>'Admin','user'=>'admin','pass'=>'admin456'],
                ['role'=>'Manager','user'=>'manager','pass'=>'manager456'],
                ['role'=>'Cashier','user'=>'cashier','pass'=>'cashier456'],
                ['role'=>'Waiter','user'=>'waiter','pass'=>'waiter456'],
                ['role'=>'Chef','user'=>'chef','pass'=>'chef456'],
                ['role'=>'Bartender','user'=>'bartender','pass'=>'bartender456'],
            ] as $cred)
            <button type="button" class="role-card"
                    data-login="{{ $cred['user'] }}"
                    data-password="{{ $cred['pass'] }}">
                <span class="role-label">{{ $cred['role'] }}</span>
                <span class="role-user">{{ $cred['user'] }}</span>
            </button>
            @endforeach
        </div>
    </section>

    <section class="form-panel">
        <div class="form-inner">
            <h2 class="form-title">Accountga kiring</h2>
            <p class="form-subtitle">Role asosida kerakli bo'limga yo'naltirilasiz</p>

            <form id="loginForm" action="{{ route('login.store') }}" method="POST">
                @csrf

                <div class="field-group">
                    <label for="login">Login</label>
                    <input id="login" type="text" name="login" placeholder="admin" required>
                </div>

                <div class="field-group">
                    <label for="password">Parol</label>
                    <input id="password" type="password" name="password" placeholder="••••••••" required>
                    <button type="button" class="toggle-pw">👁️</button>
                </div>

                <br>
                <button type="submit" class="submit-btn">Kirish</button>
            </form>
        </div>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js" defer></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Toggle password ko'rsatish/yashirish
    const toggle = document.querySelector(".toggle-pw");
    const passwordInput = document.getElementById("password");

    toggle.addEventListener("click", function() {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggle.textContent = "🙈"; // icon o'zgartirish
        } else {
            passwordInput.type = "password";
            toggle.textContent = "👁️"; // iconni qaytarish
        }
    });

    // Role card bosilganda login formga auto-fill va submit
    document.querySelectorAll(".role-card").forEach(card => {
        card.addEventListener("click", function() {
            document.getElementById("login").value = this.dataset.login;
            document.getElementById("password").value = this.dataset.password;
            document.getElementById("loginForm").submit();
        });
    });

    // Particles.js
    particlesJS('particles-js', {
        "particles": {
            "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
            "color": { "value": "#ffffff" },
            "shape": { "type": "circle" },
            "opacity": { "value": 0.2, "random": true },
            "size": { "value": 3, "random": true },
            "line_linked": { "enable": true, "distance": 120, "color": "#ffffff", "opacity": 0.5, "width": 1 },
            "move": { "enable": true, "speed": 1, "direction": "none", "random": true, "straight": false, "out_mode": "out" }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": { "onhover": { "enable": true, "mode": "repulse" }, "onclick": { "enable": true, "mode": "push" } },
            "modes": { "repulse": { "distance": 100 }, "push": { "particles_nb": 4 } }
        },
        "retina_detect": true
    });
});
</script>

@endsection 