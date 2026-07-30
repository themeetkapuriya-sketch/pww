@extends('layouts.app')

@section('title', 'Profile Information')

@section('content')
<div class="space-y-6 flex flex-col min-h-full">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold text-slate-800">Profile Information</h1>
            <a href="{{ route('overview') }}" class="flex items-center space-x-1.5 bg-white hover:bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-700 shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Panel</span>
            </a>
        </div>
    </div>

    <!-- Forms Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Form 1: Profile Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Profile Information</h2>
                <p class="text-xs text-slate-400 mt-1 mb-6">Update your account's profile information and email address.</p>
                
                <form action="{{ route('profile.update') }}" method="POST" class="ajax-form no-reset space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name</label>
                        <input type="text" name="name" value="{{ Auth::user()->name }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Form 2: Update Password -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Update Password</h2>
                <p class="text-xs text-slate-400 mt-1 mb-6">Ensure your account is using a long, random password to stay secure.</p>
                
                <form action="{{ route('profile.password') }}" method="POST" class="ajax-form no-reset space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Current Password</label>
                        <div class="relative">
                            <input type="password" id="currPasswordInput" name="current_password" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-4 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                            <button type="button" onclick="togglePasswordVisibility('currPasswordInput', 'currEye', 'currEyeOff')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <svg id="currEye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg id="currEyeOff" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">New Password</label>
                        <div class="relative">
                            <input type="password" id="newPasswordInput" name="new_password" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-4 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                            <button type="button" onclick="togglePasswordVisibility('newPasswordInput', 'newEye', 'newEyeOff')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <svg id="newEye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg id="newEyeOff" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Confirm Password</label>
                        <div class="relative">
                            <input type="password" id="confirmPasswordInput" name="new_password_confirmation" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-4 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                            <button type="button" onclick="togglePasswordVisibility('confirmPasswordInput', 'confEye', 'confEyeOff')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <svg id="confEye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg id="confEyeOff" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

<script>
    function togglePasswordVisibility(inputId, eyeIconId, eyeOffIconId) {
        const input = document.getElementById(inputId);
        const eyeIcon = document.getElementById(eyeIconId);
        const eyeOffIcon = document.getElementById(eyeOffIconId);

        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            if (eyeIcon) eyeIcon.classList.add('hidden');
            if (eyeOffIcon) eyeOffIcon.classList.remove('hidden');
        } else {
            input.type = 'password';
            if (eyeIcon) eyeIcon.classList.remove('hidden');
            if (eyeOffIcon) eyeOffIcon.classList.add('hidden');
        }
    }
</script>
@endsection
