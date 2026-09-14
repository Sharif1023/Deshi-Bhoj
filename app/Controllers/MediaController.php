<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Auth,DB,ValidationException};
use App\Services\MediaService;
final class MediaController
{
    public function listing(): void { Auth::requireUser(); header('Content-Type: application/json'); echo json_encode(['items'=>DB::all('SELECT * FROM media ORDER BY id DESC LIMIT 200')], JSON_THROW_ON_ERROR); }
    public function upload(): void { Auth::requireUser(); Auth::throttle('upload:'.user()['id'],150,3600); if(!isset($_FILES['file']))throw new ValidationException('Choose an image.'); $item=MediaService::upload($_FILES['file']); header('Content-Type: application/json'); echo json_encode($item,JSON_THROW_ON_ERROR); }
}
