<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4" data-content-language data-initial-language="<?= e($editingLocale ?? App\Services\Locale::current()); ?>">
    <p class="mb-3 text-sm font-medium text-emerald-950">Content language</p>
    <div class="flex flex-wrap gap-2" role="group" aria-label="Content language">
        <button type="button" class="rounded-full border border-emerald-200 bg-white px-5 py-2.5 text-sm font-semibold text-emerald-950 aria-pressed:border-emerald-900 aria-pressed:bg-emerald-900 aria-pressed:text-white" data-content-locale="en" aria-pressed="false">English</button>
        <button type="button" class="rounded-full border border-emerald-200 bg-white px-5 py-2.5 text-sm font-semibold text-emerald-950 aria-pressed:border-emerald-900 aria-pressed:bg-emerald-900 aria-pressed:text-white" data-content-locale="bn" aria-pressed="false" lang="bn">বাংলা</button>
    </div>
    <p class="mt-3 text-sm leading-6 text-emerald-900">Write each language separately. Switching tabs keeps your unsaved text. Save changes saves both languages.</p>
    <input type="hidden" name="content_locale" value="<?= e($editingLocale ?? App\Services\Locale::current()); ?>">
</div>
