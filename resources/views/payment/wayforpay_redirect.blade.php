<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Перенаправлення на сторінку оплати...</title>
</head>
<body>
    <div style="text-align: center; margin-top: 100px; font-family: sans-serif;">
        <h2>Зачекайте, будь ласка...</h2>
        <p>Перенаправляємо вас на захищену сторінку оплати WayForPay.</p>
    </div>

    <form id="wayforpay_form" action="{{ $gatewayUrl }}" method="POST" accept-charset="utf-8">
        @foreach($fields as $name => $value)
            @if(is_array($value))
                @foreach($value as $item)
                    <input type="hidden" name="{{ $name }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach
    </form>

    <script>
        document.getElementById('wayforpay_form').submit();
    </script>
</body>
</html>