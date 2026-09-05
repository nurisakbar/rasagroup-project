<?php
$file = '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/warehouse/orders/show.blade.php';
$content = file_get_contents($file);

$needle = <<<HTML
                                </span>
                            </td>
                        </tr>
                        @if(\$order->notes)
HTML;

$replacement = <<<HTML
                                </span>
                            </td>
                        </tr>
                        @if(\$order->received_at)
                        <tr>
                            <th>Waktu Pesanan Diterima</th>
                            <td>
                                <span class="text-success"><i class="fa fa-check-circle"></i> {{ \$order->received_at->format('d M Y, H:i') }} WIB</span>
                            </td>
                        </tr>
                        @endif
                        @if(\$order->notes)
HTML;

$content = str_replace($needle, $replacement, $content);
file_put_contents($file, $content);

echo "Added received_at to warehouse order view.\n";
