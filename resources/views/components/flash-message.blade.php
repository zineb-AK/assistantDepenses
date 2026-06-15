@if (session('success'))
    <div class="mb-6 p-4 bg-gradient-to-r from-green-400 to-emerald-500 text-white rounded-xl shadow-lg flex items-center gap-3">
        <span class="text-xl">✅</span>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 p-4 bg-gradient-to-r from-pink-400 to-rose-500 text-white rounded-xl shadow-lg flex items-center gap-3">
        <span class="text-xl">❌</span>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
@endif
