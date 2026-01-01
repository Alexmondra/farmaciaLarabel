<!DOCTYPE html>
<html>

<head>
    <title>Comprobante</title>
</head>

<body>
    <h2>Hola, {{ $venta->cliente->nombre_completo }}</h2>
    <p>Gracias por tu compra en nuestra farmacia.</p>
    <p>Adjunto encontrarás tu comprobante electrónico.</p>
    <br>
    <p>Atentamente,<br>mundofarma</p>
</body>

</html>