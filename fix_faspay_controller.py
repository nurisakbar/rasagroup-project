import re

with open('/Applications/MAMP/htdocs/rasagroup/rasagroup-project/app/Http/Controllers/Api/FaspaySnapController.php', 'r') as f:
    content = f.read()

# Replace inquiry response
inquiry_response_replacement = """        return response()->json([
            'responseCode' => '2002400',
            'responseMessage' => 'Success',
            'virtualAccountData' => [
                'partnerServiceId' => $partnerServiceId ?? substr($vaNumber, 0, 8),
                'customerNo' => $customerNo ?? substr($vaNumber, 8),
                'virtualAccountNo' => $vaNumber,
                'virtualAccountName' => $order->user->name ?? 'Customer Rasa Group',
                'virtualAccountEmail' => $order->user->email ?? 'customer@rasagroup.co.id',
                'virtualAccountPhone' => $order->user->phone ?? '6281234567890',
                'inquiryRequestId' => $inquiryRequestId,
                'totalAmount' => [
                    'value' => number_format($order->total_amount, 2, '.', ''),
                    'currency' => 'IDR'
                ]
            ]
        ]);"""
content = re.sub(r'        return response\(\)->json\(\[\s*\'responseCode\' => \'2002400\',\s*\'responseMessage\' => \'Success\',\s*\'virtualAccountData\' => \[\s*(.*?)\]\s*\]\);', inquiry_response_replacement, content, flags=re.DOTALL)

# Replace payment response
payment_response_replacement = """        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
            'virtualAccountData' => [
                'partnerServiceId' => $partnerServiceId ?? substr((string) $orderNumber, 0, 8),
                'customerNo' => $customerNo ?? substr((string) $orderNumber, 8),
                'virtualAccountNo' => (string) $orderNumber,
                'virtualAccountName' => $order->user->name ?? 'Customer Rasa Group',
                'paymentRequestId' => $request->input('paymentRequestId', ''),
                'paidAmount' => [
                    'value' => number_format($paidAmountValue, 2, '.', ''),
                    'currency' => 'IDR'
                ]
            ]
        ]);"""
content = re.sub(r'        return response\(\)->json\(\[\s*\'responseCode\' => \'2002500\',\s*\'responseMessage\' => \'Success\',\s*\'virtualAccountData\' => \[\s*(.*?)\]\s*\]\);', payment_response_replacement, content, flags=re.DOTALL)

# Fix VA numbers in UAT mock for inquiry
content = content.replace("['3685000212345679', '0212345679']", "['3702010212345679', '0212345679']")
content = content.replace("['3685000212345678', '0212345678']", "['3702010212345678', '0212345678']")
content = content.replace("['3685000212345677', '0212345677']", "['3702010212345677', '0212345677']")
content = content.replace("3685000212345677", "3702010212345677")

# Add CHANNEL-ID validation
channel_val = """        // Pengecekan Channel ID
        $channelId = $request->header('CHANNEL-ID', '');
        if ($channelId !== '77001') {
            return response()->json([
                'responseCode' => '401' . $serviceCode . '00',
                'responseMessage' => 'Unauthorized. Invalid Channel ID'
            ], 401);
        }

        // 1. Pengecekan Token UAT"""
content = content.replace("// 1. Pengecekan Token UAT", channel_val)

with open('/Applications/MAMP/htdocs/rasagroup/rasagroup-project/app/Http/Controllers/Api/FaspaySnapController.php', 'w') as f:
    f.write(content)
