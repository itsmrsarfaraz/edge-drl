<x-layouts.guest title="Login">
    <div class="w-full max-w-md px-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
            <div class="mb-8 text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <x-ui.application-logo />
                </div>
                <h1 class="text-2xl font-bold text-slate-100">Edge DRL</h1>
                <p class="text-sm text-slate-400 mt-1">Sign in to your account</p>
            </div>
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                           placeholder="you@example.com">
                    @error('email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                           placeholder="••••••••">
                    @error('password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-400">
                        <input type="checkbox" name="remember" class="rounded bg-slate-800 border-slate-700 text-primary-500">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-primary-400 hover:text-primary-300">Forgot password?</a>
                    @endif
                </div>
                <button type="submit"
                        class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-500 text-white font-medium rounded-lg text-sm transition-colors">
                    Sign In
                </button>
            </form>
            @if (Route::has('register'))
            <p class="mt-6 text-center text-sm text-slate-400">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-primary-400 hover:text-primary-300 font-medium">Register</a>
            </p>
            @endif
        </div>
    </div>
</x-layouts.guest>
