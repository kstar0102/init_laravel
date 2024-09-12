<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .form {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        #card-element {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #card-errors {
            color: red;
            margin-top: 10px;
        }

        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="form">
    <form id="payment-form">
        <div class="form-group">
            <label for="card-element">Credit or debit card</label>
            <div id="card-element">
                <!-- A Stripe Element will be inserted here. -->
            </div>
            <div id="card-errors" role="alert"></div>
        </div>

        <div class="form-group">
            <label for="zip-code">ZIP Code (optional)</label>
            <input type="text" id="zip-code" placeholder="12345" />
        </div>

        <button id="submit-button" type="submit">Submit Payment</button>
    </form>
</div>

<script>
    // Initialize Stripe with your publishable key
    const stripe = Stripe('pk_test_51PwmEYGOr74zlXKw8pAGX1lxSzkKLzQW2r9AgYCPir8BWCSzOwE8CSfiKr84RLL6JAqueyBtKcKwGXpblCRbEf3a00gCaPSz3W');  // Replace with your own Stripe publishable key
    const elements = stripe.elements();

    // Create an instance of the card Element
    const card = elements.create('card');
    card.mount('#card-element');

    // Handle real-time validation errors from the card Element
    card.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Handle form submission
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        // Get the ZIP code entered by the user
        const zipCode = document.getElementById('zip-code').value;

        // Create a token using card details, with optional ZIP code
        stripe.createToken(card, {
            address_zip: zipCode
        }).then(function(result) {
            if (result.error) {
                // Inform the user if there was an error
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Display the token (for testing purposes)
                console.log('Token received:', result.token);
                alert('Token received: ' + result.token.id);

                // Optionally, you can also send the token to your server
                stripeTokenHandler(result.token);
            }
        });
    });

    // Send the token to your server
    function stripeTokenHandler(token) {
        fetch('http://127.0.0.1:8000/api/charge-card', {  // Replace with your backend URL
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'  // If using Laravel, pass the CSRF token
            },
            body: JSON.stringify({token: token.id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Payment failed: ' + data.error);
            } else {
                alert('Payment successful!');
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>

</body>
</html>
