<x-layouts.app title="Profile Settings">
    <div class="w-full max-w-6xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            
            <!-- Profile Information Section -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-100">Profile Information</h2>
                    <p class="text-sm text-slate-400 mt-1">Update your account's profile information and email address.</p>
                </div>

                <!-- Session Status Alert -->
                @if (session('status') === 'profile-updated')
                    <div class="mb-5 p-3.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-sm text-emerald-400 text-center">
                        Saved successfully.
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                    @csrf
                    @method('patch')

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name ?? auth()->user()->name) }}" required autofocus autocomplete="name"
                               class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email ?? auth()->user()->email) }}" required autocomplete="username"
                               class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        @error('email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit"
                                class="py-2.5 px-5 bg-primary-600 hover:bg-primary-500 text-white font-medium rounded-lg text-sm transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>

                <!-- Delete Account Trigger Base Panel -->
                <div class="mt-8 pt-8 border-t border-slate-800">
                    <h2 class="text-xl font-bold text-rose-500">Delete Account</h2>
                    <p class="text-sm text-slate-400 mt-1 mb-5">
                        Once your account is deleted, all of its resources and data will be permanently deleted.
                    </p>
                    <button type="button"
                            x-data=""
                            x-on:click="$dispatch('open-modal', { name: 'confirm-user-deletion' })"
                            class="py-2.5 px-5 bg-rose-600 hover:bg-rose-500 text-white font-medium rounded-lg text-sm transition-colors">
                        Delete Account
                    </button>
                </div>
            </div>

            <!-- Update Password Section -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-100">Update Password</h2>
                    <p class="text-sm text-slate-400 mt-1">Ensure your account is using a long, random password to stay secure.</p>
                </div>

                @if (session('status') === 'password-updated')
                    <div class="mb-5 p-3.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-sm text-emerald-400 text-center">
                        Password updated successfully.
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    @method('put')

                    <!-- Current Password -->
                    <div>
                        <label for="update_password_current_password" class="block text-sm font-medium text-slate-300 mb-1.5">Current Password</label>
                        <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                               class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                               placeholder="••••••••">
                        @error('current_password', 'updatePassword')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="update_password_password" class="block text-sm font-medium text-slate-300 mb-1.5">New Password</label>
                        <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                               class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                               placeholder="••••••••">
                        @error('password', 'updatePassword')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">Confirm Password</label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                               class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                               placeholder="••••••••">
                        @error('password_confirmation', 'updatePassword')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit"
                                class="py-2.5 px-5 bg-primary-600 hover:bg-primary-500 text-white font-medium rounded-lg text-sm transition-colors">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Deletion Confirmation Modal -->
    <x-ui.modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="POST" action="{{ route('profile.destroy') }}" class="p-2 text-left">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-100">
                Are you sure you want to delete your account?
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
            </p>

            <div class="mt-5">
                <label for="password" class="sr-only">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                    placeholder="Confirm your password"
                    required
                />
                @error('password', 'userDeletion')
                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" 
                        x-on:click="$dispatch('close-modal', { name: 'confirm-user-deletion' })"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-medium transition-colors">
                    Cancel
                </button>

                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-sm font-medium transition-colors">
                    Delete Account
                </button>
            </div>
        </form>
    </x-ui.modal>
</x-layouts.app>