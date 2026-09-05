<?php
$file = '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/buyer/orders/show.blade.php';
$content = file_get_contents($file);

$old = <<<HTML
                                                @endif
                                            </div>

                                            @if(\$order->payment_status === 'pending' && in_array(\$order->payment_method, ['manual_transfer', 'transfer']))
HTML;

$new = <<<HTML
                                                @endif
                                            </div>

                                            @if(\$order->payment_status === 'pending')
                                                <div class="mt-4 pt-3 border-top text-center">
                                                    <a href="{{ route('checkout.success', \$order) }}" class="btn btn-outline-brand rounded-pill w-100 mb-10">
                                                        <i class="fi-rs-info mr-5"></i> Lihat Instruksi & Cara Bayar
                                                    </a>
                                                </div>
                                            @endif

                                            @if(\$order->payment_status === 'pending' && in_array(\$order->payment_method, ['manual_transfer', 'transfer']))
HTML;

$content = str_replace($old, $new, $content);
file_put_contents($file, $content);
echo "Added instruction button to buyer.\n";
