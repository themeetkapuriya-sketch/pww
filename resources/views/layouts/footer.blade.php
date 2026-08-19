<!-- Application Footer Component -->
<footer class="mt-auto py-4 px-6 md:px-8 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-start bg-transparent">
    <div class="flex items-center space-x-1.5 text-slate-500 dark:text-slate-400">
        <span>&copy; {{ date('Y') }}</span>
        <span class="font-bold text-slate-700 dark:text-slate-200">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</span>
        <span class="text-slate-300 dark:text-slate-600">•</span>
        <span>Proudly created <span class="text-rose-500">❤️</span> by <strong class="text-slate-700 dark:text-slate-200 font-bold">Kapuriya Meet</strong></span>
    </div>
</footer>
