<x-layouts.guest title="Reset Password">
    <div class="w-full max-w-md px-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
            <div class="mb-8 text-center">
                <!-- Lock Open Icon to symbolize setting a new password -->
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <x-ui.application-logo />
                </div>
                <h1 class="text-2xl font-bold text-slate-100">Update Password</h1>
                <p class="text-sm text-slate-400 mt-1">Enter your new security credentials below</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                           placeholder="you@example.com">
                    @error('email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">New Password</label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                           placeholder="••••••••">
                    @error('password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                           placeholder="••••••••">
                    @error('password_confirmation')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-500 text-white font-medium rounded-lg text-sm transition-colors pt-2">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</x-layouts.guest>