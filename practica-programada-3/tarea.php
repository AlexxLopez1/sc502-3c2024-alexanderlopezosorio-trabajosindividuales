<?php
$transacciones = [];

function registrarTransaccion($id, $descripcion, $monto) {
    global $transacciones;
    array_push($transacciones, [
        'id' => $id,
        'descripcion' => $descripcion,
        'monto' => $monto
    ]);
}

function generarEstadoDeCuenta() {
    global $transacciones;

    $montoTotalContado = 0;
    foreach ($transacciones as $transaccion) {
        $montoTotalContado += $transaccion['monto'];
    }

    $montoConInteres = $montoTotalContado * 1.026;
    $cashback = $montoTotalContado * 0.001;
    $montoFinal = $montoConInteres - $cashback;

    echo "Estado de Cuenta:\n";
    echo "----------------------\n";
    echo "Transacciones:\n";
    foreach ($transacciones as $transaccion) {
        echo "ID: " . $transaccion['id'] . " - Descripción: " . $transaccion['descripcion'] . " - Monto: $" . number_format($transaccion['monto'], 2) . "\n";
    }
    echo "----------------------\n";
    echo "Monto Total de Contado: $" . number_format($montoTotalContado, 2) . "\n";
    echo "Monto con Interés (2.6%): $" . number_format($montoConInteres, 2) . "\n";
    echo "Cashback (0.1%): $" . number_format($cashback, 2) . "\n";
    echo "Monto Final a Pagar: $" . number_format($montoFinal, 2) . "\n";

    $contenidoArchivo = "Estado de Cuenta:\n";
    $contenidoArchivo .= "----------------------\n";
    $contenidoArchivo .= "Transacciones:\n";
    foreach ($transacciones as $transaccion) {
        $contenidoArchivo .= "ID: " . $transaccion['id'] . " - Descripción: " . $transaccion['descripcion'] . " - Monto: $" . number_format($transaccion['monto'], 2) . "\n";
    }
    $contenidoArchivo .= "----------------------\n";
    $contenidoArchivo .= "Monto Total de Contado: $" . number_format($montoTotalContado, 2) . "\n";
    $contenidoArchivo .= "Monto con Interés (2.6%): $" . number_format($montoConInteres, 2) . "\n";
    $contenidoArchivo .= "Cashback (0.1%): $" . number_format($cashback, 2) . "\n";
    $contenidoArchivo .= "Monto Final a Pagar: $" . number_format($montoFinal, 2) . "\n";

    file_put_contents("estado_cuenta.txt", $contenidoArchivo);
}

registrarTransaccion(1, "Compra en tienda A", 100.00);
registrarTransaccion(2, "Pago de servicios", 200.00);
registrarTransaccion(3, "Cena en restaurante", 50.00);

generarEstadoDeCuenta();
?>