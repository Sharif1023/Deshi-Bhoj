<?php // Inputs: $name, $inputId, $type, $value, $label, $categories. ?>
<div class="space-y-2">
    <label class="block text-sm font-semibold text-stone-700" for="<?= e($inputId); ?>"><?= e($label); ?><?= $type === 'money' ? ' (BDT)' : ''; ?></label>
    <?php if (in_array($type, ['image', 'images'], true)):
        $fieldName = $name;
        $fieldValue = $type === 'images' ? implode("\n", json_decode($value ?: '[]', true) ?: []) : $value;
        $multiple = $type === 'images'; require __DIR__ . '/media-field.php';
    elseif (in_array($type, ['rich', 'rich-inline', 'textarea'], true)): ?>
        <textarea class="w-full rounded-xl border border-stone-300 bg-white p-3 text-sm text-ink" id="<?= e($inputId); ?>" name="<?= e($name); ?>" rows="4" <?= in_array($type, ['rich', 'rich-inline'], true) ? 'data-richtext="' . ($type === 'rich-inline' ? 'inline' : 'block') . '"' : ''; ?>><?= e($value); ?></textarea>
    <?php elseif ($type === 'checkbox'): ?>
        <input class="h-5 w-5 rounded border-stone-300 accent-emerald-800" id="<?= e($inputId); ?>" name="<?= e($name); ?>" type="checkbox" value="1" <?= $value ? 'checked' : ''; ?>>
    <?php elseif ($type === 'category'): ?>
        <select class="w-full rounded-xl border border-stone-300 bg-white p-3 text-sm text-ink" id="<?= e($inputId); ?>" name="<?= e($name); ?>" required>
            <option value="">Choose a category</option>
            <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id']; ?>" <?= (int) $value === (int) $c['id'] ? 'selected' : ''; ?>><?= e(plain($c['name'])); ?></option><?php endforeach; ?>
        </select>
    <?php else: ?>
        <input class="w-full rounded-xl border border-stone-300 bg-white p-3 text-sm text-ink" id="<?= e($inputId); ?>" name="<?= e($name); ?>" value="<?= e($value); ?>" type="<?= in_array($type, ['number', 'rating', 'money'], true) ? 'number' : (in_array($type, ['email', 'url'], true) ? $type : 'text'); ?>" <?= $type === 'money' ? 'step="0.01" min="0"' : ($type === 'rating' ? 'min="1" max="5"' : ($type === 'number' ? 'min="0"' : '')); ?> <?= !str_starts_with($name, 'translations[') && !str_ends_with($type, '?') && $type !== 'url' ? 'required' : ''; ?>>
    <?php endif; ?>
</div>
