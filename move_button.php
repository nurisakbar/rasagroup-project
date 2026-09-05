<?php
$files = [
    '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/buyer/distributor/orders/show.blade.php',
    '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/buyer/orders/show.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // 1. Remove button from Lacak Sekarang area
    $oldTrackLogic = <<<HTML
                                                            @elseif(\$isKurirToko && in_array(\$order->order_status, ['shipped', 'delivered']) && \$order->order_status !== 'completed')
                                                                <form action="{{ route('distributor.orders.confirm-receipt', \$order) }}" method="POST" class="d-inline mt-1" onsubmit="return confirm('Apakah Anda yakin barang sudah diterima dengan baik dan pesanan selesai?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-brand rounded-pill mt-2">
                                                                        <i class="fi-rs-check mr-5"></i> Pesanan Diterima
                                                                    </button>
                                                                </form>
HTML;
    $content = str_replace($oldTrackLogic, '', $content);
    
    // Also try the buyer version which might have route('buyer.orders.confirm-receipt' or just 'orders.confirm-receipt')
    $oldTrackLogicBuyer = str_replace('distributor.orders.confirm-receipt', 'orders.confirm-receipt', $oldTrackLogic);
    $content = str_replace($oldTrackLogicBuyer, '', $content);

    // 2. Add button below Waktu Diterima
    // It looks like:
    //                                                 </div>
    //                                                 @endif
    //                                             </div>
    
    $findEndList = "                                                @endif\n                                            </div>";
    
    $isDistributor = strpos($file, 'distributor') !== false;
    $routeName = $isDistributor ? 'distributor.orders.confirm-receipt' : 'orders.confirm-receipt';
    
    $addButton = <<<HTML
                                                @endif
                                                
                                                @if(isset(\$isKurirToko) && \$isKurirToko && in_array(\$order->order_status, ['shipped', 'delivered']) && \$order->order_status !== 'completed')
                                                <div class="mt-4 pt-3 border-top">
                                                    <form action="{{ route('{$routeName}', \$order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin pesanan sudah diterima dengan baik dan selesai?');">
                                                        @csrf
                                                        <button type="submit" class="btn w-100 shadow-sm" style="background-color: #3bb77e; border: none; color: white; padding: 12px; border-radius: 8px; font-weight: 600;">
                                                            <i class="fi-rs-check mr-5"></i> Konfirmasi Pesanan Diterima
                                                        </button>
                                                    </form>
                                                </div>
                                                @endif
                                            </div>
HTML;

    $content = str_replace($findEndList, $addButton, $content);
    
    file_put_contents($file, $content);
}

echo "Moved button.\n";
