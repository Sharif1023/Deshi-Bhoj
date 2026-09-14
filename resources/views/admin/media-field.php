<?php
// Inputs: $fieldName, $fieldValue (newline-separated), $multiple (bool).
$multiple = $multiple ?? false;
?>
<div class="media-picker" data-media-picker data-multiple="<?= $multiple ? 'true' : 'false'; ?>">
    <input type="hidden" name="<?= e($fieldName); ?>" value="<?= e($fieldValue); ?>" data-media-value>
    <div data-media-items class="media-items grid grid-cols-2 gap-3"></div>
    <div class="flex flex-wrap gap-3 mt-3">
        <label class="media-upload-label inline-flex min-h-11 cursor-pointer items-center rounded-full bg-ink px-5 py-2 text-sm font-semibold text-white [&_input]:sr-only">Upload <?= $multiple ? 'images' : 'image'; ?><input type="file" data-media-upload accept="image/jpeg,image/png,image/webp" <?= $multiple ? 'multiple' : ''; ?>></label>
        <button type="button" class="btn-outline inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-stone-300 bg-white px-5 py-2 text-sm font-medium text-ink hover:bg-stone-100" data-media-library>Choose from library</button>
    </div>
    <p class="mt-2 text-xs text-stone-500">JPG, PNG, WebP · 5 MB each<?= $multiple ? ' · up to 12 images. First image is the cover.' : ''; ?></p>
    <p data-media-status role="status" class="mt-2 text-sm text-red-700"></p>
</div>
