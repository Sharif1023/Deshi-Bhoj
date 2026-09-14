<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\ValidationException;
final class UploadService
{
    public static function save(array $file): string
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            throw new ValidationException(match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'ছবিটি সার্ভারের upload limit ছাড়িয়েছে। php.ini-তে upload_max_filesize=5M এবং post_max_size=6M দিয়ে PHP restart করুন।',
                UPLOAD_ERR_PARTIAL => 'ছবিটি সম্পূর্ণ আসেনি। আবার upload করুন।',
                UPLOAD_ERR_NO_TMP_DIR => 'PHP temporary upload folder নেই। Hosting support-কে upload_tmp_dir ঠিক করতে বলুন।',
                UPLOAD_ERR_CANT_WRITE => 'সার্ভার temporary file লিখতে পারছে না। Disk space ও folder permission পরীক্ষা করুন।',
                UPLOAD_ERR_EXTENSION => 'সার্ভারের একটি PHP extension upload বন্ধ করেছে। Hosting support-এর সঙ্গে যোগাযোগ করুন।',
                default => 'একটি JPG, PNG বা WebP ছবি নির্বাচন করুন।',
            });
        }
        $tmp = $file['tmp_name'] ?? '';
        if (!is_string($tmp) || !is_uploaded_file($tmp)) throw new ValidationException('Invalid uploaded file.');
        $size = filesize($tmp);
        if (!$size || $size > 5 * 1024 * 1024) throw new ValidationException('ছবির সর্বোচ্চ আকার 5 MB।');
        $info = @getimagesize($tmp);
        $types = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'];
        if (!$info || !isset($types[$info['mime']]) || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 24000000) {
            throw new ValidationException('সঠিক JPG, PNG বা WebP দিন; সর্বোচ্চ 24 megapixels।');
        }
        $dir = ROOT . '/storage/uploads';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) throw new ValidationException('storage/uploads folder তৈরি করা যাচ্ছে না। PHP user-কে write permission দিন।');
        if (!is_writable($dir)) throw new ValidationException('storage/uploads-এ write permission নেই। PHP user-কে এই folder-এ write permission দিন।');
        // GD is optional. Validated originals remain outside the public directory,
        // are given random names and served only by the image-only media route.
        $name = bin2hex(random_bytes(20)) . '.' . $types[$info['mime']];
        if (!move_uploaded_file($tmp, $dir . '/' . $name)) throw new ValidationException('ছবি সংরক্ষণ হয়নি। Disk space ও storage/uploads permission পরীক্ষা করুন।');
        @chmod($dir . '/' . $name, 0644);
        return '/media/' . $name;
    }
}
