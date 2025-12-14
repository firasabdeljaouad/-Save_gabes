<?php

namespace App\Service;

use App\Entity\Donation;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripePaymentService
{
    private string $stripeSecret;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(string $stripeSecret, UrlGeneratorInterface $urlGenerator)
    {
        $this->stripeSecret = $stripeSecret;
        $this->urlGenerator = $urlGenerator;
        Stripe::setApiKey($this->stripeSecret);
    }

    public function createCheckoutSession(Donation $donation): ?string
    {
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd', // Modify currency as needed
                        'product_data' => [
                            'name' => 'Donation for ' . ($donation->getProject() ? $donation->getProject()->getName() : 'Charity'),
                        ],
                        'unit_amount' => (int) ($donation->getAmount() * 100), // Amount in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->urlGenerator->generate(
                    'app_payment_success',
                    ['session_id' => '{CHECKOUT_SESSION_ID}'],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'cancel_url' => $this->urlGenerator->generate(
                    'app_payment_cancel',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]);

            return $session->url;
        } catch (\Exception $e) {
            // Log error appropriately in a real app
            return null;
        }
    }
}
