<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · IT Taskboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-paper font-sans text-ink-900 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">

    {{-- Left: a glimpse of the day's queue --}}
    <section class="hidden flex-col justify-between bg-ink-900 p-12 text-white lg:flex" aria-hidden="true">
        <p class="font-display text-xl font-bold tracking-tight">IT Taskboard</p>

        <div>
            <h1 class="max-w-md font-display text-4xl font-bold leading-tight">What the IT department is working on today.</h1>

            <ul class="mt-10 max-w-md divide-y divide-ink-800 rounded-lg border border-ink-800 bg-ink-800/40 text-sm">
                @foreach ([
                    ['Replace failed disk in NAS-02', 'Critical', 'bg-flare'],
                    ['Fix Wi-Fi drops on 3rd floor', 'In progress', 'bg-sky-400'],
                    ['Reset MFA for new finance hire', 'To do', 'bg-ink-300'],
                    ['Renew SSL certificate for staff portal', 'To do', 'bg-ink-300'],
                ] as [$title, $state, $dot])
                    <li class="flex items-center justify-between gap-4 px-4 py-3">
                        <span>{{ $title }}</span>
                        <span class="flex shrink-0 items-center gap-2 text-ink-300"><span class="size-2 rounded-full {{ $dot }}"></span>{{ $state }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-3 text-xs text-ink-300">Sample tasks. Sign in to see your own.</p>
        </div>

        <p class="text-sm text-ink-300">Staff, managers and admins each get their own dashboard.</p>
    </section>

    {{-- Right: forms --}}
    <section class="flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-sm">
            <p class="mb-8 font-display text-xl font-bold tracking-tight lg:hidden">IT Taskboard</p>

            <div role="tablist" aria-label="Account" class="mb-8 grid grid-cols-2 rounded-lg bg-ink-100 p-1 text-sm font-medium">
                <button type="button" role="tab" id="tab-login" data-mode="login" class="rounded-md px-3 py-2">Sign in</button>
                <button type="button" role="tab" id="tab-register" data-mode="register" class="rounded-md px-3 py-2">Create account</button>
            </div>

            {{-- SIGN IN --}}
            <form id="form-login" method="POST" action="{{ route('login.attempt') }}" class="space-y-4" novalidate>
                @csrf
                <h2 class="font-display text-2xl font-bold">Welcome back</h2>

                @if ($errors->login->any())
                    <div role="alert" class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900">
                        @foreach ($errors->login->all() as $e)<p>{{ $e }}</p>@endforeach
                    </div>
                @endif

                <div>
                    <label for="login-email" class="mb-1 block text-sm font-medium">Work email</label>
                    <input id="login-email" name="email" type="email" autocomplete="email" required
                           value="{{ $mode === 'login' ? old('email') : '' }}"
                           class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                </div>
                <div>
                    <label for="login-password" class="mb-1 block text-sm font-medium">Password</label>
                    <input id="login-password" name="password" type="password" autocomplete="current-password" required
                           class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" class="size-4 rounded border-ink-300"> Keep me signed in
                </label>
                <button class="w-full rounded-md bg-signal px-4 py-2.5 text-sm font-medium text-white hover:bg-signal-dark">Sign in</button>

                @if (app()->environment('local'))
                    <p class="rounded-md bg-ink-100 px-3 py-2 text-xs text-ink-700">
                        Demo accounts (password: <b>password</b>): admin@it.local, manager@it.local, staff@it.local
                    </p>
                @endif
            </form>

            {{-- CREATE ACCOUNT --}}
            <form id="form-register" method="POST" action="{{ route('register.store') }}" class="hidden space-y-4" novalidate>
                @csrf
                <h2 class="font-display text-2xl font-bold">Create your account</h2>
                <p class="text-sm text-ink-500">New accounts start as Staff. An admin can change your role later.</p>

                @if ($errors->register->any())
                    <div role="alert" class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900">
                        @foreach ($errors->register->all() as $e)<p>{{ $e }}</p>@endforeach
                    </div>
                @endif

                <div>
                    <label for="reg-name" class="mb-1 block text-sm font-medium">Full name</label>
                    <input id="reg-name" name="name" type="text" autocomplete="name" required
                           value="{{ $mode === 'register' ? old('name') : '' }}"
                           class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                </div>
                <div>
                    <label for="reg-email" class="mb-1 block text-sm font-medium">Work email</label>
                    <input id="reg-email" name="email" type="email" autocomplete="email" required
                           value="{{ $mode === 'register' ? old('email') : '' }}"
                           class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                </div>
                <div>
                    <label for="reg-password" class="mb-1 block text-sm font-medium">Password <span class="font-normal text-ink-500">(8+ characters)</span></label>
                    <input id="reg-password" name="password" type="password" autocomplete="new-password" required
                           class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                </div>
                <div>
                    <label for="reg-confirm" class="mb-1 block text-sm font-medium">Confirm password</label>
                    <input id="reg-confirm" name="password_confirmation" type="password" autocomplete="new-password" required
                           class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                </div>
                <button class="w-full rounded-md bg-signal px-4 py-2.5 text-sm font-medium text-white hover:bg-signal-dark">Create account</button>
            </form>
        </div>
    </section>
</div>

<script>
    // Switch between the two forms without reloading the page.
    const urls = { login: @json(route('login')), register: @json(route('register')) };

    function showMode(mode, updateUrl) {
        ['login', 'register'].forEach(m => {
            const active = m === mode;
            document.getElementById('form-' + m).classList.toggle('hidden', !active);
            const tab = document.getElementById('tab-' + m);
            tab.setAttribute('aria-selected', active);
            tab.classList.toggle('bg-white', active);
            tab.classList.toggle('shadow-sm', active);
            tab.classList.toggle('text-ink-500', !active);
        });
        document.title = (mode === 'login' ? 'Sign in' : 'Create account') + ' · IT Taskboard';
        if (updateUrl) history.replaceState({}, '', urls[mode]);
    }

    document.querySelectorAll('[data-mode]').forEach(btn =>
        btn.addEventListener('click', () => showMode(btn.dataset.mode, true)));

    showMode(@json($mode), false);
</script>
</body>
</html>
