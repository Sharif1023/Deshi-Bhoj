<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\{DB, ValidationException};
final class MediaService
{
    public static function upload(array $file): array
    {
        $url = UploadService::save($file);
        $info = getimagesize(ROOT . '/storage/uploads/' . basename($url));
        try {
            $id = DB::insert('media', ['url'=>$url,'original_name'=>mb_substr(basename($file['name']),0,190),'alt'=>'','width'=>$info[0],'height'=>$info[1],'created_at'=>date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) { @unlink(ROOT . '/storage/uploads/' . basename($url)); throw $e; }
        return DB::one('SELECT * FROM media WHERE id=?', [$id]);
    }
    public static function validateImages(string $raw, int $max = 12): array
    {
        $items = array_values(array_unique(array_filter(array_map('trim', explode("\n",$raw)))));
        if (count($items) > $max) throw new ValidationException("Use at most $max images.");
        foreach ($items as $url) {
            if (safeImage($url) !== $url) throw new ValidationException('Use a valid uploaded image or HTTPS image URL.');
            if (str_starts_with($url,'/media/') && !is_file(ROOT.'/storage/uploads/'.basename($url))) throw new ValidationException('An uploaded image no longer exists. Please upload it again.');
            if (str_starts_with($url,'/assets/') && !is_file(ROOT.'/public'.$url)) throw new ValidationException('Demo image not found.');
        }
        return $items;
    }
}
