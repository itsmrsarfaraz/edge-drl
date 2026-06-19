<x-layouts.guest title="Forgot Password">
    <div class="w-full max-w-md px-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
            <div class="mb-8 text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <x-ui.application-logo />
                </div>
                <h1 class="text-2xl font-bold text-slate-100">Reset Password</h1>
                <p class="text-sm text-slate-400 mt-2 px-2">
                    Forgot your password? No problem. Let us know your email address and we will email you a password reset link.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-5 p-3.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-sm text-emerald-400 text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                           placeholder="you@example.com">
                    @error('email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-500 text-white font-medium rounded-lg text-sm transition-colors">
                    Email Password Reset Link
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-400">
                Remember your password? 
                <a href="{{ route('login') }}" class="text-primary-400 hover:text-primary-300 font-medium">Back to Sign In</a>
            </p>
        </div>
    </div>
</x-layouts.guest>