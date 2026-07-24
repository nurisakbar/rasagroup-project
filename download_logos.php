<?php

use Illuminate\Support\Facades\Storage;
use App\Models\Expedition;

$expeditions = Expedition::whereNotNull('logo')->get();

foreach ($expeditions as $exp) {
    if (str_starts_with($exp->logo, 'http')) {
        echo "Downloading logo for {$exp->code} from {$exp->logo}\n";
        try {
            $contents = file_get_contents($exp->logo);
            if ($contents) {
                $ext = pathinfo(parse_url($exp->logo, PHP_URL_PATH), PATHINFO_EXTENSION);
                if (empty($ext)) $ext = 'png';
                
                $filename = "expeditions/{$exp->code}.{$ext}";
                Storage::disk('public')->put($filename, $contents);
                
                $exp->logo = $filename;
                $exp->save();
                echo "Saved locally as {$filename}\n";
            } else {
                echo "Failed to download content.\n";
            }
        } catch (\Exception $e) {
            echo "Error downloading: " . $e->getMessage() . "\n";
        }
    }
}
