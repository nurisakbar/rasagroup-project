<?php
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Notifications\Orders\NewTopOrderNotification;
use App\Notifications\Orders\OrderCompletedNotification;
use App\Notifications\Orders\OrderPickupReadyNotification;
use App\Notifications\Orders\OrderProcessingNotification;
use App\Notifications\Orders\OrderShippedNotification;
use App\Notifications\Orders\PaymentConfirmationSubmittedNotification;
use App\Notifications\VerifyEmailNotification;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'nuris.akbar@gmail.com';

echo "Sending test emails to $email...\n";

// 1. Send Order Notifications
$order = Order::with(['user', 'expedition'])->latest()->first();
if (!$order) {
    echo "No order found in the database. Cannot send order notifications.\n";
} else {
    echo "Sending order notifications for Order #" . $order->order_number . "\n";
    $route = Notification::route('mail', $email);
    
    Notification::sendNow($route, new NewTopOrderNotification($order));
    Notification::sendNow($route, new OrderCompletedNotification($order));
    Notification::sendNow($route, new OrderPickupReadyNotification($order));
    Notification::sendNow($route, new OrderProcessingNotification($order));
    Notification::sendNow($route, new OrderShippedNotification($order));
    Notification::sendNow($route, new PaymentConfirmationSubmittedNotification($order));
    echo "Order notifications sent.\n";
}

// 2. Send Verify Email Notification
$existingUser = User::first();
if ($existingUser) {
    $oldEmail = $existingUser->email;
    $existingUser->email = $email;
    Notification::sendNow($existingUser, new VerifyEmailNotification());
    $existingUser->email = $oldEmail; // Not saving to DB, just for memory
    echo "Verify email notification sent using existing user.\n";
} else {
    echo "No user found in the database. Cannot send verify email notification.\n";
}

echo "All test emails sent successfully.\n";
