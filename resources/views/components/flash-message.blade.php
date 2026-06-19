@if (session('success'))
    <div class="mb-4 p-4 bg-accent-50 border border-accent-200 text-accent-800 rounded-lg tracking-wide">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg tracking-wide">
        {{ session('error') }}
    </div>
@endif
