<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\WACloudService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $driippreneurPointRate = Setting::get('driippreneur_point_rate', 1000);
        $wacloudApiKey = Setting::get('wacloud_api_key', '');
        $wacloudDeviceId = Setting::get('wacloud_device_id', '');
        
        // Get WACloud quota if configured
        $wacloudQuota = null;
        if (!empty($wacloudApiKey) && !empty($wacloudDeviceId)) {
            try {
                $waCloud = new WACloudService();
                $accountInfo = $waCloud->getAccount();
                
                if ($accountInfo && is_array($accountInfo)) {
                    // Extract quota from nested structure
                    $quotaBalance = null;
                    $quotaText = null;
                    $quotaMultimedia = null;
                    $quotaFreeText = null;
                    $quotaTotalText = null;
                    
                    if (isset($accountInfo['quota']) && is_array($accountInfo['quota'])) {
                        $quotaBalance = isset($accountInfo['quota']['balance']) ? (float)$accountInfo['quota']['balance'] : null;
                        $quotaText = isset($accountInfo['quota']['text_quota']) ? (int)$accountInfo['quota']['text_quota'] : null;
                        $quotaMultimedia = isset($accountInfo['quota']['multimedia_quota']) ? (int)$accountInfo['quota']['multimedia_quota'] : null;
                        $quotaFreeText = isset($accountInfo['quota']['free_text_quota']) ? (int)$accountInfo['quota']['free_text_quota'] : null;
                        $quotaTotalText = isset($accountInfo['quota']['total_text_quota']) ? (int)$accountInfo['quota']['total_text_quota'] : null;
                    } elseif (isset($accountInfo['quota_balance'])) {
                        // Already extracted by service
                        $quotaBalance = is_numeric($accountInfo['quota_balance']) ? (float)$accountInfo['quota_balance'] : null;
                        $quotaText = isset($accountInfo['quota_text']) ? (int)$accountInfo['quota_text'] : null;
                        $quotaMultimedia = isset($accountInfo['quota_multimedia']) ? (int)$accountInfo['quota_multimedia'] : null;
                        $quotaFreeText = isset($accountInfo['quota_free_text']) ? (int)$accountInfo['quota_free_text'] : null;
                        $quotaTotalText = isset($accountInfo['quota_total_text']) ? (int)$accountInfo['quota_total_text'] : null;
                    }
                    
                    // Extract plan name
                    $planValue = null;
                    if (isset($accountInfo['plan'])) {
                        if (is_string($accountInfo['plan']) || is_numeric($accountInfo['plan'])) {
                            $planValue = (string)$accountInfo['plan'];
                        } elseif (is_array($accountInfo['plan']) || is_object($accountInfo['plan'])) {
                            $planArr = (array)$accountInfo['plan'];
                            $planValue = $planArr['name'] ?? $planArr['label'] ?? $planArr['value'] ?? json_encode($accountInfo['plan']);
                        }
                    } elseif (isset($accountInfo['package'])) {
                        if (is_string($accountInfo['package']) || is_numeric($accountInfo['package'])) {
                            $planValue = (string)$accountInfo['package'];
                        } elseif (is_array($accountInfo['package']) || is_object($accountInfo['package'])) {
                            $packageArr = (array)$accountInfo['package'];
                            $planValue = $packageArr['name'] ?? $packageArr['label'] ?? $packageArr['value'] ?? json_encode($accountInfo['package']);
                        }
                    }
                    
                    // Format quota data
                    $wacloudQuota = [
                        'quota' => $quotaBalance,
                        'quota_balance' => $quotaBalance,
                        'quota_text' => $quotaText,
                        'quota_multimedia' => $quotaMultimedia,
                        'quota_free_text' => $quotaFreeText,
                        'quota_total_text' => $quotaTotalText,
                        'plan' => $planValue,
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Failed to get WACloud quota', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return view('admin.settings.index', compact('driippreneurPointRate', 'wacloudApiKey', 'wacloudDeviceId', 'wacloudQuota'));
    }

    public function updateDriippreneurPointRate(Request $request)
    {
        $request->validate([
            'point_rate' => 'required|integer|min:0',
        ], [
            'point_rate.required' => 'Point rate wajib diisi.',
            'point_rate.integer' => 'Point rate harus berupa angka.',
            'point_rate.min' => 'Point rate minimal 0.',
        ]);

        Setting::set(
            'driippreneur_point_rate',
            $request->point_rate,
            'Point yang diberikan per item untuk DRiiPPreneur saat belanja'
        );

        return back()->with('success', 'Point rate DRiiPPreneur berhasil diperbarui.');
    }

    public function updateWACloudSettings(Request $request)
    {
        $request->validate([
            'wacloud_api_key' => 'required|string',
            'wacloud_device_id' => 'required|string',
        ], [
            'wacloud_api_key.required' => 'API Key WACloud wajib diisi.',
            'wacloud_api_key.string' => 'API Key WACloud harus berupa teks.',
            'wacloud_device_id.required' => 'Device ID WACloud wajib diisi.',
            'wacloud_device_id.string' => 'Device ID WACloud harus berupa teks.',
        ]);

        Setting::set(
            'wacloud_api_key',
            $request->wacloud_api_key,
            'API Key untuk integrasi WACloud WhatsApp Gateway'
        );

        Setting::set(
            'wacloud_device_id',
            $request->wacloud_device_id,
            'Device ID untuk integrasi WACloud WhatsApp Gateway'
        );

        // Get quota after saving
        try {
            $waCloud = new WACloudService();
            $accountInfo = $waCloud->getAccount();
            
                if ($accountInfo && is_array($accountInfo)) {
                    // Extract quota from nested structure
                    $quotaBalance = null;
                    $quotaText = null;
                    $quotaMultimedia = null;
                    $quotaFreeText = null;
                    $quotaTotalText = null;
                    
                    if (isset($accountInfo['quota']) && is_array($accountInfo['quota'])) {
                        $quotaBalance = isset($accountInfo['quota']['balance']) ? (float)$accountInfo['quota']['balance'] : null;
                        $quotaText = isset($accountInfo['quota']['text_quota']) ? (int)$accountInfo['quota']['text_quota'] : null;
                        $quotaMultimedia = isset($accountInfo['quota']['multimedia_quota']) ? (int)$accountInfo['quota']['multimedia_quota'] : null;
                        $quotaFreeText = isset($accountInfo['quota']['free_text_quota']) ? (int)$accountInfo['quota']['free_text_quota'] : null;
                        $quotaTotalText = isset($accountInfo['quota']['total_text_quota']) ? (int)$accountInfo['quota']['total_text_quota'] : null;
                    } elseif (isset($accountInfo['quota_balance'])) {
                        // Already extracted by service
                        $quotaBalance = is_numeric($accountInfo['quota_balance']) ? (float)$accountInfo['quota_balance'] : null;
                        $quotaText = isset($accountInfo['quota_text']) ? (int)$accountInfo['quota_text'] : null;
                        $quotaMultimedia = isset($accountInfo['quota_multimedia']) ? (int)$accountInfo['quota_multimedia'] : null;
                        $quotaFreeText = isset($accountInfo['quota_free_text']) ? (int)$accountInfo['quota_free_text'] : null;
                        $quotaTotalText = isset($accountInfo['quota_total_text']) ? (int)$accountInfo['quota_total_text'] : null;
                    }
                    
                    // Extract plan name
                    $planValue = null;
                    if (isset($accountInfo['plan'])) {
                        if (is_string($accountInfo['plan']) || is_numeric($accountInfo['plan'])) {
                            $planValue = (string)$accountInfo['plan'];
                        } elseif (is_array($accountInfo['plan']) || is_object($accountInfo['plan'])) {
                            $planArr = (array)$accountInfo['plan'];
                            $planValue = $planArr['name'] ?? $planArr['label'] ?? $planArr['value'] ?? json_encode($accountInfo['plan']);
                        }
                    } elseif (isset($accountInfo['package'])) {
                        if (is_string($accountInfo['package']) || is_numeric($accountInfo['package'])) {
                            $planValue = (string)$accountInfo['package'];
                        } elseif (is_array($accountInfo['package']) || is_object($accountInfo['package'])) {
                            $packageArr = (array)$accountInfo['package'];
                            $planValue = $packageArr['name'] ?? $packageArr['label'] ?? $packageArr['value'] ?? json_encode($accountInfo['package']);
                        }
                    }
                    
                    // Format quota data
                    $quotaData = [
                        'quota' => $quotaBalance,
                        'quota_balance' => $quotaBalance,
                        'quota_text' => $quotaText,
                        'quota_multimedia' => $quotaMultimedia,
                        'quota_free_text' => $quotaFreeText,
                        'quota_total_text' => $quotaTotalText,
                        'plan' => $planValue,
                    ];
                
                return back()->with([
                    'success' => 'Pengaturan WACloud berhasil diperbarui.',
                    'wacloud_quota' => $quotaData
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get WACloud quota after save', ['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Pengaturan WACloud berhasil diperbarui.');
    }

    /**
     * Get WACloud quota via AJAX
     */
    public function getWACloudQuota()
    {
        $waCloud = new WACloudService();
        
        if (!$waCloud->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'WACloud belum dikonfigurasi'
            ], 400);
        }

        $accountInfo = $waCloud->getAccount();
        
        if ($accountInfo && is_array($accountInfo)) {
                // Extract quota from nested structure
                $quotaBalance = null;
                $quotaText = null;
                $quotaMultimedia = null;
                $quotaFreeText = null;
                $quotaTotalText = null;
                
                if (isset($accountInfo['quota']) && is_array($accountInfo['quota'])) {
                    $quotaBalance = isset($accountInfo['quota']['balance']) ? (float)$accountInfo['quota']['balance'] : null;
                    $quotaText = isset($accountInfo['quota']['text_quota']) ? (int)$accountInfo['quota']['text_quota'] : null;
                    $quotaMultimedia = isset($accountInfo['quota']['multimedia_quota']) ? (int)$accountInfo['quota']['multimedia_quota'] : null;
                    $quotaFreeText = isset($accountInfo['quota']['free_text_quota']) ? (int)$accountInfo['quota']['free_text_quota'] : null;
                    $quotaTotalText = isset($accountInfo['quota']['total_text_quota']) ? (int)$accountInfo['quota']['total_text_quota'] : null;
                } elseif (isset($accountInfo['quota_balance'])) {
                    // Already extracted by service
                    $quotaBalance = is_numeric($accountInfo['quota_balance']) ? (float)$accountInfo['quota_balance'] : null;
                    $quotaText = isset($accountInfo['quota_text']) ? (int)$accountInfo['quota_text'] : null;
                    $quotaMultimedia = isset($accountInfo['quota_multimedia']) ? (int)$accountInfo['quota_multimedia'] : null;
                    $quotaFreeText = isset($accountInfo['quota_free_text']) ? (int)$accountInfo['quota_free_text'] : null;
                    $quotaTotalText = isset($accountInfo['quota_total_text']) ? (int)$accountInfo['quota_total_text'] : null;
                }
                
                // Extract plan name
                $planValue = null;
                if (isset($accountInfo['plan'])) {
                    if (is_string($accountInfo['plan']) || is_numeric($accountInfo['plan'])) {
                        $planValue = (string)$accountInfo['plan'];
                    } elseif (is_array($accountInfo['plan']) || is_object($accountInfo['plan'])) {
                        $planArr = (array)$accountInfo['plan'];
                        $planValue = $planArr['name'] ?? $planArr['label'] ?? $planArr['value'] ?? json_encode($accountInfo['plan']);
                    }
                } elseif (isset($accountInfo['package'])) {
                    if (is_string($accountInfo['package']) || is_numeric($accountInfo['package'])) {
                        $planValue = (string)$accountInfo['package'];
                    } elseif (is_array($accountInfo['package']) || is_object($accountInfo['package'])) {
                        $packageArr = (array)$accountInfo['package'];
                        $planValue = $packageArr['name'] ?? $packageArr['label'] ?? $packageArr['value'] ?? json_encode($accountInfo['package']);
                    }
                }
                
                // Format response data
                $formattedData = [
                    'quota' => $quotaBalance,
                    'quota_balance' => $quotaBalance,
                    'quota_text' => $quotaText,
                    'quota_multimedia' => $quotaMultimedia,
                    'quota_free_text' => $quotaFreeText,
                    'quota_total_text' => $quotaTotalText,
                    'plan' => $planValue,
                ];
            
            return response()->json([
                'success' => true,
                'data' => $formattedData
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mendapatkan informasi quota. Pastikan API Key dan Device ID valid.'
        ], 400);
    }
}
