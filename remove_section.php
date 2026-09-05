<?php
$file = '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/warehouse/orders/show.blade.php';
$content = file_get_contents($file);

$sectionToRemove = <<<HTML
                        <!-- Payment Status -->
                        <div class="form-group">
                            <label>Status Pembayaran Saat Ini</label>
                            @php
                                \$paymentClass = [
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'failed' => 'danger',
                                    'refunded' => 'info',
                                ][\$order->payment_status] ?? 'default';
                            @endphp
                            <div class="text-center" style="margin-bottom: 10px;">
                                <span class="label label-{{\$paymentClass}}" style="font-size: 14px; padding: 8px 15px;">
                                    {{ strtoupper(\$order->payment_status) }}
                                </span>
                                <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">
                                    <i class="fa fa-{{ \$order->payment_method == 'transfer' ? 'bank' : 'money' }}"></i>
                                    {{ \$order->payment_method == 'transfer' ? 'Transfer Bank' : (\$order->payment_method == 'cod' ? 'COD (Bayar di Tempat)' : ucfirst(\$order->payment_method)) }}
                                </p>
                            </div>

                        </div>
HTML;

// Removing using regex just in case there's slight whitespace difference
$content = preg_replace('/<!-- Payment Status -->\s*<div class="form-group">\s*<label>Status Pembayaran Saat Ini<\/label>.*?<\/div>\s*<\/div>/s', '', $content);

file_put_contents($file, $content);

echo "Removed section.\n";
