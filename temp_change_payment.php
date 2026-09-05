    public function changePaymentMethod(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->payment_status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pesanan pending yang bisa diganti metode pembayarannya.');
        }

        $request->validate([
            'payment_method' => 'required|string'
        ]);

        $activeGateway = config('services.active_payment_gateway');
        $user = $order->user;
        $total = $order->total_amount;

        try {
            DB::beginTransaction();

            // Clear old payment URLs if any
            $order->xendit_invoice_url = null;
            $order->xendit_invoice_id = null;
            $order->faspay_redirect_url = null;
            $order->faspay_bill_no = null;
            $order->virtual_account_no = null;

            if (str_starts_with($request->payment_method, 'faspay_')) {
                if ($request->payment_method === 'faspay_qris') {
                    $snapService = new \App\Services\FaspaySnapService();
                    $qrisData = $snapService->generateQris($order, $total);
                    if ($qrisData && (isset($qrisData['qrUrl']) || isset($qrisData['additionalInfo']['qrImageUrl']))) {
                        $order->virtual_account_no = $qrisData['qrUrl'] ?? $qrisData['additionalInfo']['qrImageUrl'];
                        $order->payment_method = 'faspay_qris';
                    } else {
                        throw new \Exception('Failed to generate Faspay QRIS');
                    }
                } elseif ($request->payment_method === 'faspay_bca_va') {
                    $faspayService = new \App\Services\FaspayService();
                    $invoice = $faspayService->createBill($order, $user, '702');
                    if ($invoice && isset($invoice['bill_no'])) {
                        $order->faspay_bill_no = $invoice['bill_no'] ?? $order->order_number;
                        $order->faspay_redirect_url = $invoice['redirect_url'];
                        $order->payment_method = 'faspay_bca_va';
                    } else {
                        throw new \Exception('Failed to generate Faspay BCA VA');
                    }
                } else {
                    $envType = env('FASPAY_ENV', 'development');
                    if ($envType === 'production') {
                        $prefixes = [
                            'faspay_permata_va'  => '864003',
                            'faspay_mandiri_va'  => '881682',
                            'faspay_bri_va'      => '121568',
                            'faspay_bni_va'      => '8583',
                            'faspay_sinarmas_va' => '979803',
                            'faspay_maybank_va'  => '270425',
                            'faspay_danamon_va'  => '797039',
                            'faspay_bsi_va'      => '12601021',
                            'faspay_cimb_va'     => '222550',
                        ];
                    } else {
                        $prefixes = [
                            'faspay_permata_va'  => '370201',
                            'faspay_mandiri_va'  => '37020002',
                            'faspay_bri_va'      => '370202',
                            'faspay_cimb_va'     => '370204',
                            'faspay_bni_va'      => '9881236387',
                        ];
                    }
                    $prefix = $prefixes[$request->payment_method] ?? '370200';
                    $targetLength = 16;
                    $prefixLength = strlen($prefix);
                    $freeDigitsLength = $targetLength - $prefixLength;
                    
                    $numericOrder = preg_replace('/[^0-9]/', '', $order->order_number);
                    if (strlen($numericOrder) > $freeDigitsLength) {
                        $freeDigits = substr($numericOrder, -$freeDigitsLength);
                    } else {
                        $freeDigits = str_pad($numericOrder, $freeDigitsLength, '0', STR_PAD_LEFT);
                    }
                    
                    $order->virtual_account_no = $prefix . $freeDigits;
                    $order->payment_method = $request->payment_method;
                }
            } elseif ($activeGateway === 'xendit' && in_array($request->payment_method, ['xendit'])) {
                $xenditService = new \App\Services\XenditService();
                $customer = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $order->address->phone ?? $user->phone,
                ];

                $xenditItems = $order->items->map(function ($item) {
                    return [
                        'name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'category' => 'Product'
                    ];
                })->toArray();

                $invoice = $xenditService->createInvoice($order, $customer, $xenditItems);

                if ($invoice && isset($invoice['id'])) {
                    $order->xendit_invoice_id = $invoice['id'];
                    $order->xendit_invoice_url = $invoice['invoice_url'] ?? null;
                    $order->payment_method = 'xendit';
                } else {
                    throw new \Exception('Failed to create Xendit invoice');
                }
            } else {
                $order->payment_method = $request->payment_method;
            }

            $order->save();
            DB::commit();

            return redirect()->route('checkout.success', $order)->with('success', 'Metode pembayaran berhasil diubah.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Change Payment Method Error', ['error' => $e->getMessage(), 'order_id' => $order->id]);
            return redirect()->back()->with('error', 'Gagal mengubah metode pembayaran: ' . $e->getMessage());
        }
    }
