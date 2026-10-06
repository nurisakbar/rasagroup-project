<?php
$faspayActive = [
    'active_faspay_qris' => false,
    'active_faspay_mandiri_va' => false
];
if ($faspayActive['active_faspay_qris'] ?? true) {
    echo "QRIS is shown\n";
} else {
    echo "QRIS is hidden\n";
}
