<?php

namespace App\Helpers;

use App\Services\WACloudService;
use Illuminate\Support\Facades\Log;

class WACloudHelper
{
    /**
     * Send WhatsApp text message
     * 
     * @param string $phone Phone number (can be in any format, will be auto-formatted)
     * @param string $message Message text
     * @return array|null Returns message data on success, null on failure
     */
    public static function sendText(string $phone, string $message): ?array
    {
        try {
            $waCloud = new WACloudService();
            
            if (!$waCloud->isConfigured()) {
                Log::warning('WACloud not configured. Cannot send text message.');
                return null;
            }

            // Format phone number
            $formattedPhone = $waCloud->formatPhoneNumber($phone);
            
            // Send message
            $result = $waCloud->sendTextMessage($formattedPhone, $message);
            
            if ($result) {
                Log::info('WhatsApp text message sent via helper', [
                    'phone' => $formattedPhone,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp text message via helper', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send WhatsApp document message
     * 
     * @param string $phone Phone number (can be in any format, will be auto-formatted)
     * @param string $documentUrl Full URL to the document
     * @param string|null $filename Optional filename for the document
     * @return array|null Returns message data on success, null on failure
     */
    public static function sendDocument(string $phone, string $documentUrl, ?string $filename = null): ?array
    {
        try {
            $waCloud = new WACloudService();
            
            if (!$waCloud->isConfigured()) {
                Log::warning('WACloud not configured. Cannot send document message.');
                return null;
            }

            // Format phone number
            $formattedPhone = $waCloud->formatPhoneNumber($phone);
            
            // Send document
            $result = $waCloud->sendDocumentMessage($formattedPhone, $documentUrl, $filename);
            
            if ($result) {
                Log::info('WhatsApp document message sent via helper', [
                    'phone' => $formattedPhone,
                    'document_url' => $documentUrl,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp document message via helper', [
                'phone' => $phone,
                'document_url' => $documentUrl,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send WhatsApp text message with auto-retry
     * 
     * @param string $phone Phone number
     * @param string $message Message text
     * @param int $maxRetries Maximum number of retry attempts
     * @return array|null
     */
    public static function sendTextWithRetry(string $phone, string $message, int $maxRetries = 3): ?array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $maxRetries) {
            $result = self::sendText($phone, $message);
            
            if ($result !== null) {
                return $result;
            }

            $attempt++;
            
            if ($attempt < $maxRetries) {
                // Wait before retry (exponential backoff)
                sleep(pow(2, $attempt - 1));
            }
        }

        Log::error('Failed to send WhatsApp text message after retries', [
            'phone' => $phone,
            'attempts' => $attempt,
        ]);

        return null;
    }

    /**
     * Send WhatsApp document message with auto-retry
     * 
     * @param string $phone Phone number
     * @param string $documentUrl Document URL
     * @param string|null $filename Optional filename
     * @param int $maxRetries Maximum number of retry attempts
     * @return array|null
     */
    public static function sendDocumentWithRetry(string $phone, string $documentUrl, ?string $filename = null, int $maxRetries = 3): ?array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $maxRetries) {
            $result = self::sendDocument($phone, $documentUrl, $filename);
            
            if ($result !== null) {
                return $result;
            }

            $attempt++;
            
            if ($attempt < $maxRetries) {
                // Wait before retry (exponential backoff)
                sleep(pow(2, $attempt - 1));
            }
        }

        Log::error('Failed to send WhatsApp document message after retries', [
            'phone' => $phone,
            'document_url' => $documentUrl,
            'attempts' => $attempt,
        ]);

        return null;
    }

    /**
     * Format phone number to WhatsApp format
     * 
     * @param string $phone Phone number in any format
     * @return string Formatted phone number (62xxxxxxxxxx)
     */
    public static function formatPhone(string $phone): string
    {
        $waCloud = new WACloudService();
        return $waCloud->formatPhoneNumber($phone);
    }

    /**
     * Check if WACloud is configured
     * 
     * @return bool
     */
    public static function isConfigured(): bool
    {
        $waCloud = new WACloudService();
        return $waCloud->isConfigured();
    }

    /**
     * Send payment notification after checkout
     * 
     * @param \App\Models\Order $order Order model
     * @return array|null
     */
    public static function sendPaymentNotification(\App\Models\Order $order): ?array
    {
        if (!self::isConfigured()) {
            Log::warning('WACloud not configured. Cannot send payment notification.');
            return null;
        }

        if (!$order->address || !$order->address->phone) {
            Log::warning('Order address or phone not available', [
                'order_id' => $order->id,
            ]);
            return null;
        }

        try {
            $phone = $order->address->phone;
            $message = self::buildPaymentMessage($order);
            
            Log::info('Sending payment notification via WhatsApp', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'phone' => $phone,
                'payment_method' => $order->payment_method,
                'has_xendit_url' => !empty($order->xendit_invoice_url),
            ]);
            
            $result = self::sendText($phone, $message);
            
            if ($result) {
                Log::info('Payment notification sent via WhatsApp', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'phone' => $phone,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send payment notification via WhatsApp', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send thank you notification after order is successfully created
     * 
     * @param \App\Models\Order $order Order model
     * @return array|null
     */
    public static function sendThankYouNotification(\App\Models\Order $order): ?array
    {
        if (!self::isConfigured()) {
            Log::warning('WACloud not configured. Cannot send thank you notification.');
            return null;
        }

        if (!$order->address || !$order->address->phone) {
            Log::warning('Order address or phone not available', [
                'order_id' => $order->id,
            ]);
            return null;
        }

        try {
            $phone = $order->address->phone;
            $message = self::buildThankYouMessage($order);
            
            Log::info('Sending thank you notification via WhatsApp', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'phone' => $phone,
            ]);
            
            $result = self::sendText($phone, $message);
            
            if ($result) {
                Log::info('Thank you notification sent via WhatsApp', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'phone' => $phone,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send thank you notification via WhatsApp', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Build thank you notification message
     * 
     * @param \App\Models\Order $order Order model
     * @return string
     */
    private static function buildThankYouMessage(\App\Models\Order $order): string
    {
        $message = "🎉 *Pembayaran Berhasil!*\n\n";
        
        $message .= "Terima kasih! Pembayaran untuk pesanan *#{$order->order_number}* telah berhasil kami terima.\n\n";
        
        $message .= "📦 *Informasi Pengiriman:*\n";
        $message .= "Pesanan Anda akan segera kami proses dan dikirim ke alamat:\n";
        
        if ($order->address) {
            $message .= "{$order->address->recipient_name}\n";
            $message .= "{$order->address->phone}\n";
            $message .= "{$order->address->address_detail}\n";
            
            $addressParts = [];
            if ($order->address->village) {
                $addressParts[] = $order->address->village->name;
            }
            if ($order->address->district) {
                $addressParts[] = 'Kec. ' . $order->address->district->name;
            }
            if ($order->address->regency) {
                $addressParts[] = $order->address->regency->name;
            }
            if ($order->address->province) {
                $addressParts[] = $order->address->province->name;
            }
            if ($order->address->postal_code) {
                $addressParts[] = $order->address->postal_code;
            }
            
            if (!empty($addressParts)) {
                $message .= implode(', ', $addressParts) . "\n";
            }
        }
        
        $message .= "\n";
        
        // Add expedition info if available
        if ($order->expedition) {
            $message .= "🚚 *Ekspedisi:* {$order->expedition->name}\n";
            
            // Get service name
            $serviceName = $order->expedition_service;
            if ($order->expedition->services && is_array($order->expedition->services)) {
                foreach ($order->expedition->services as $service) {
                    if (isset($service['code']) && $service['code'] === $order->expedition_service) {
                        $serviceName = $service['name'] ?? $order->expedition_service;
                        break;
                    }
                }
            }
            $message .= "Layanan: {$serviceName}\n";
            
            // Add estimated delivery days if available
            if ($order->expedition->est_days_min && $order->expedition->est_days_max) {
                $estDays = $order->expedition->est_days_min === $order->expedition->est_days_max
                    ? $order->expedition->est_days_min . ' hari'
                    : $order->expedition->est_days_min . '-' . $order->expedition->est_days_max . ' hari';
                $message .= "Estimasi Pengiriman: {$estDays}\n";
            }
            
            $message .= "\n";
        }
        
        $message .= "⏳ *Status Pesanan:*\n";
        $message .= "Pesanan Anda sedang dalam proses packing dan akan segera dikirim.\n";
        $message .= "Anda akan menerima notifikasi update melalui WhatsApp ketika pesanan sudah dikirim.\n\n";
        
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📞 *Butuh Bantuan?*\n";
        $message .= "Jika ada pertanyaan atau perubahan pesanan, silakan hubungi customer service kami.\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $message .= "Terima kasih telah mempercayakan Rasa Group untuk kebutuhan Anda! 🙏\n";
        $message .= "Kami berharap Anda puas dengan produk dan layanan kami.";
        
        return $message;
    }

    /**
     * Build payment notification message
     * 
     * @param \App\Models\Order $order Order model
     * @return string
     */
    private static function buildPaymentMessage(\App\Models\Order $order): string
    {
        $message = "📦 *Pesanan #{$order->order_number} Berhasil Dibuat*\n\n";
        
        $message .= "Terima kasih telah berbelanja di Rasa Group!\n\n";
        
        // Add order items detail
        if ($order->items && $order->items->count() > 0) {
            $message .= "🛍️ *Item Pesanan:*\n";
            $itemNumber = 1;
            foreach ($order->items as $item) {
                $productName = $item->product ? $item->product->name : 'Produk';
                $message .= "{$itemNumber}. {$productName}\n";
                $message .= "   Qty: {$item->quantity} x Rp " . number_format($item->price, 0, ',', '.') . "\n";
                $message .= "   Subtotal: Rp " . number_format($item->subtotal, 0, ',', '.') . "\n\n";
                $itemNumber++;
            }
        }
        
        $message .= "📋 *Rincian Pembayaran:*\n";
        $message .= "Subtotal: Rp " . number_format($order->subtotal, 0, ',', '.') . "\n";
        
        if ($order->shipping_cost > 0) {
            $message .= "Ongkir: Rp " . number_format($order->shipping_cost, 0, ',', '.') . "\n";
        }
        
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Total Pembayaran: *Rp " . number_format($order->total_amount, 0, ',', '.') . "*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Add expedition information
        if ($order->expedition) {
            $message .= "🚚 *Informasi Pengiriman:*\n";
            $message .= "Ekspedisi: *{$order->expedition->name}*\n";
            
            // Get service name from expedition_service code
            $serviceName = $order->expedition_service;
            if ($order->expedition->services && is_array($order->expedition->services)) {
                foreach ($order->expedition->services as $service) {
                    if (isset($service['code']) && $service['code'] === $order->expedition_service) {
                        $serviceName = $service['name'] ?? $order->expedition_service;
                        break;
                    }
                }
            }
            $message .= "Layanan: {$serviceName}\n";
            
            // Add estimated delivery days if available
            if ($order->expedition->est_days_min && $order->expedition->est_days_max) {
                $estDays = $order->expedition->est_days_min === $order->expedition->est_days_max
                    ? $order->expedition->est_days_min . ' hari'
                    : $order->expedition->est_days_min . '-' . $order->expedition->est_days_max . ' hari';
                $message .= "Estimasi Pengiriman: {$estDays}\n";
            }
            
            $message .= "\n";
        }
        
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "💳 *CARA PEMBAYARAN*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        if ($order->payment_method === 'xendit') {
            $message .= "Metode: *Pembayaran Online (Xendit)*\n\n";
            
            // Always include payment link - if not in order, try to get from Xendit API
            $invoiceUrl = $order->xendit_invoice_url;
            
            if (empty($invoiceUrl) && $order->xendit_invoice_id) {
                // Try to fetch invoice URL from Xendit API
                try {
                    $xenditService = new \App\Services\XenditService();
                    $invoiceDetails = $xenditService->getInvoice($order->xendit_invoice_id);
                    if ($invoiceDetails && isset($invoiceDetails['invoice_url'])) {
                        $invoiceUrl = $invoiceDetails['invoice_url'];
                        // Update order with the URL
                        $order->xendit_invoice_url = $invoiceUrl;
                        $order->save();
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch Xendit invoice URL', [
                        'order_id' => $order->id,
                        'invoice_id' => $order->xendit_invoice_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            if ($invoiceUrl) {
                $message .= "🔗 *Link Pembayaran:*\n";
                $message .= $invoiceUrl . "\n\n";
                $message .= "📝 *Langkah Pembayaran:*\n";
                $message .= "1. Klik link pembayaran di atas untuk melakukan pembayaran\n";
                $message .= "2. Pilih metode pembayaran yang tersedia (Virtual Account, E-Wallet, dll)\n";
                $message .= "3. Selesaikan pembayaran sesuai instruksi\n";
                $message .= "4. Pesanan akan otomatis diproses setelah pembayaran berhasil\n\n";
                $message .= "⏰ *Catatan:* Link pembayaran berlaku selama 24 jam.\n";
                $message .= "Jika link tidak bisa diklik, copy dan paste ke browser Anda.\n";
            } else {
                // If link still not available, log warning but still send notification
                Log::warning('Xendit invoice URL not available for payment notification', [
                    'order_id' => $order->id,
                    'invoice_id' => $order->xendit_invoice_id,
                ]);
                $message .= "⚠️ *Link Pembayaran:*\n";
                $message .= "Link pembayaran sedang diproses. Silakan cek email atau WhatsApp Anda untuk link pembayaran.\n";
                $message .= "Atau hubungi customer service kami untuk bantuan.\n";
            }
        } elseif ($order->payment_method === 'manual_transfer') {
            $message .= "Metode: *Transfer Bank Manual*\n\n";
            
            // Get bank accounts from settings or use default
            $bankAccounts = self::getBankAccounts();
            
            $message .= "💰 *Rekening Tujuan:*\n";
            foreach ($bankAccounts as $bank => $account) {
                $message .= "• *{$bank}:*\n";
                $message .= "  No. Rek: *{$account['number']}*\n";
                $message .= "  a.n: {$account['name']}\n\n";
            }
            
            $message .= "📝 *Langkah Pembayaran:*\n";
            $message .= "1. Transfer sebesar *Rp " . number_format($order->total_amount, 0, ',', '.') . "*\n";
            $message .= "   ke salah satu rekening di atas\n\n";
            $message .= "2. Setelah transfer, kirim bukti transfer ke WhatsApp ini dengan format:\n";
            $message .= "   *BUKTI TRANSFER*\n";
            $message .= "   Order: #{$order->order_number}\n";
            $message .= "   Bank: [Nama Bank]\n";
            $message .= "   Jumlah: Rp " . number_format($order->total_amount, 0, ',', '.') . "\n";
            $message .= "   [Lampirkan foto bukti transfer]\n\n";
            $message .= "3. Tim kami akan memverifikasi pembayaran Anda\n";
            $message .= "4. Pesanan akan diproses setelah pembayaran dikonfirmasi\n\n";
            $message .= "⏰ *Catatan:* Konfirmasi pembayaran maksimal 1x24 jam setelah transfer.\n";
        } else {
            $message .= "Metode: " . ucfirst(str_replace('_', ' ', $order->payment_method)) . "\n";
            $message .= "Silakan ikuti instruksi pembayaran yang diberikan.\n";
        }
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📞 *Butuh Bantuan?*\n";
        $message .= "Hubungi customer service kami untuk pertanyaan lebih lanjut.\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $message .= "Terima kasih atas kepercayaan Anda! 🙏";
        
        return $message;
    }

    /**
     * Get bank accounts for manual transfer
     * 
     * @return array
     */
    private static function getBankAccounts(): array
    {
        // Try to get from settings first
        $bcaNumber = \App\Models\Setting::get('bank_bca_number', '1234567890');
        $bcaName = \App\Models\Setting::get('bank_bca_name', 'PT Rasa Group Indonesia');
        $mandiriNumber = \App\Models\Setting::get('bank_mandiri_number', '0987654321');
        $mandiriName = \App\Models\Setting::get('bank_mandiri_name', 'PT Rasa Group Indonesia');
        $bniNumber = \App\Models\Setting::get('bank_bni_number', '5678901234');
        $bniName = \App\Models\Setting::get('bank_bni_name', 'PT Rasa Group Indonesia');
        
        return [
            'BCA' => [
                'number' => $bcaNumber,
                'name' => $bcaName,
            ],
            'Mandiri' => [
                'number' => $mandiriNumber,
                'name' => $mandiriName,
            ],
            'BNI' => [
                'number' => $bniNumber,
                'name' => $bniName,
            ],
        ];
    }
}

