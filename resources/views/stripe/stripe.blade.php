<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stripe</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" />
    <style>
        body{
            position: relative;
            height: 100vh;
        }
        .center{
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        #rzp-button{
            background: #0a8708;
            color: #ffffff;
            padding: 10px;
            font-size:16px;
            border: 1px solid #0a8708;
            border-radius: 10px;
        }
        img{
            margin: auto;
/*            width: 30px;*/
        }
    </style>
	<link rel="stylesheet" href="https://cdn.moyasar.com/mpf/1.14.0/moyasar.css" />

  <!-- Moyasar Scripts -->
  <script src="https://cdnjs.cloudflare.com/polyfill/v3/polyfill.min.js?version=4.8.0&features=fetch"></script>
  <script src="https://cdn.moyasar.com/mpf/1.14.0/moyasar.js"></script>

</head>
<body>
    <div class="center">
             
        
             <div class="card-body">
                    

                <form action="{{ route('checkout.process') }}" method="POST">

                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <input type='hidden' name="amount" value="{{ $amount }}" id="amount" >
                <input type='hidden' name="productname" value="taxi">
                <input type='hidden' name="payment_for" value="{{$payment_for}}">
                <input type='hidden' name="currency" value="{{$currency}}">
                <input type='hidden' name="user_id" value="{{$user_id}}">
                <input type='hidden' name="request_id" value="{{$request_id}}">

                </form>
            </div>
    </div>
        
        <div style="padding:1.5rem" >
        	<div class="mysr-form"></div>
        </div>
        
        
        
        <script>
        var amount = document.getElementById('amount').value;

        Moyasar.init({
            element: '.mysr-form',
        	language: 'ar',
            amount: amount,
            currency: 'SAR',
            description: 'Drb Sa Taxi',
          	metadata: {
                amount: "{{$amount}}",
                productname: "taxi",
                payment_for: "{{ $payment_for }}",
                currency: "{{ $currency }}",
                user_id: "{{ $user_id }}",
                request_id: "{{ $request_id }}"
            },
            publishable_api_key: 'pk_live_jkRUWiNkDr2aCx7kCbpxYs3YJKfViFbhXp771tRL',
            callback_url: "{{ route('checkout.process') }}",
            methods: ['creditcard', 'applepay'],
         	apple_pay: {
            	country: 'SA',
            	label: 'Drb Sa',
            	validate_merchant_url: 'https://api.moyasar.com/v1/applepay/initiate',
        	},
        	// on_completed: 'https://mystore.com/checkout/savepayment',
        })
    </script>
</body>
</html>
