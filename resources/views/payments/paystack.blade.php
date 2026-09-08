@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto py-20 text-center">

    <h2 class="text-xl font-semibold mb-6">
        Redirecting to Payment...
    </h2>

</div>

<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let handler = PaystackPop.setup({
        key: "{{ $publicKey }}",
        email: "{{ $email }}",
        amount: {{ $amount * 100 }},
        ref: "{{ $reference }}",

        callback: function(response) {
            // 🔥 THIS IS CRITICAL
            window.location.href = "/payment/verify/" + response.reference;
        },

        onClose: function() {
            window.location.href = "/cart";
        }
    });

    handler.openIframe();
});
</script>
@endsection
