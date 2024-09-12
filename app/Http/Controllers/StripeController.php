<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Exception\CardException;

class StripeController extends Controller
{
    public function verifyCard(Request $request)
    {
        // Set your Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Get the card details from the request
        $cardDetails = [
            'number' => $request->input('card_number'),
            'exp_month' => $request->input('exp_month'),
            'exp_year' => $request->input('exp_year'),
            'cvc' => $request->input('cvv'),
        ];

        try {
            // Create a token using the card details
            $token = Token::create([
                'card' => $cardDetails,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Card is valid',
                'token' => $token->id,
            ]);
        } catch (CardException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getError()->message,
            ], 400);
        }
    }
}
