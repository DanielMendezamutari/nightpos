<?php

declare(strict_types=1);

namespace App\Services\Printing;

class TicketFormatterService
{
    private const LINE_WIDTH = 40;

    /**
     * Formatea una factura o recibo en texto plano para impresora térmica de 80mm (40 columnas)
     *
     * @param array $factura Datos de la factura o recibo
     * @return string Texto listo para imprimir
     */
    public function formatTicket(array $factura): string
    {
        $lines = [];

        // Encabezado
        $lines[] = $this->center("RIBERRESTO POS");
        $lines[] = $this->center("RIBERSOFT BOLIVIA");
        $lines[] = $this->center("NIT: 1028456023 | Telf: 67369293");
        $lines[] = $this->center("Santa Cruz - Bolivia");
        $lines[] = $this->divider('=');

        // Tipo de Comprobante
        $tipoComp = strtoupper((string)($factura['tipo_comprobante'] ?? 'FACTURA'));
        if ($tipoComp === 'RECIBO') {
            $lines[] = $this->center("RECIBO DE CAJA / NOTA DE VENTA");
            $lines[] = $this->center("Nro: " . ($factura['nro_comprobante'] ?? 'REC-' . ($factura['nro_factura'] ?? '1')));
            $lines[] = $this->center("*** COMPROBANTE CONSUMO INTERNO ***");
        } else {
            $lines[] = $this->center("FACTURA Nro: " . ($factura['nro_factura'] ?? '1'));
            if (!empty($factura['cuf'])) {
                $lines[] = $this->center("CUF: " . substr((string)$factura['cuf'], 0, 36) . "...");
            }
        }
        $lines[] = $this->divider('-');

        // Datos del Cliente y Mesa
        $fecha = $factura['fecha_emision'] ?? date('Y-m-d H:i');
        $cliente = $factura['razon_social'] ?? 'SIN NOMBRE';
        $doc = $factura['numero_documento'] ?? '0';
        $mesa = $factura['mesa_numero'] ?? 'BARRA';
        $cajero = $factura['cajero'] ?? 'Cajero';
        $metodo = $factura['metodo_pago'] ?? 'EFECTIVO';

        $lines[] = "Fecha: " . $fecha;
        $lines[] = "Senor(es): " . substr($cliente, 0, 29);
        $lines[] = "NIT/CI: " . $doc;
        $lines[] = "Mesa: " . $mesa . " | Cajero: " . $cajero;
        $lines[] = "Metodo de Pago: " . $metodo;
        $lines[] = $this->divider('-');

        // Columnas
        $lines[] = $this->twoColumns("CANT. DESCRIPCION", "SUBTOTAL");
        $lines[] = $this->divider('-');

        // Ítems / Detalles
        $detalles = $factura['detalles'] ?? [];
        if (empty($detalles) && !empty($factura['items'])) {
            $detalles = $factura['items'];
        }

        foreach ($detalles as $d) {
            $cant = $d['cantidad'] ?? 1;
            $nombre = $d['producto_nombre'] ?? ($d['producto']['nombre'] ?? 'Producto');
            $subtotal = number_format((float)($d['subtotal'] ?? 0), 2, '.', '');

            $cantCol = sprintf("%2dx ", $cant);
            $maxNameLen = self::LINE_WIDTH - strlen($cantCol) - strlen($subtotal) - 2;
            if ($maxNameLen > 0 && strlen($nombre) > $maxNameLen) {
                $nombre = substr($nombre, 0, $maxNameLen);
            }

            $left = $cantCol . $nombre;
            $lines[] = $this->twoColumns($left, $subtotal);
        }

        $lines[] = $this->divider('-');

        // Totales y Vuelto
        $total = number_format((float)($factura['monto_total'] ?? 0), 2, '.', '');
        $recibido = number_format((float)($factura['monto_recibido'] ?? $factura['monto_total'] ?? 0), 2, '.', '');
        $cambio = number_format((float)($factura['cambio'] ?? $factura['monto_cambio'] ?? 0), 2, '.', '');

        $lines[] = $this->twoColumns("TOTAL A PAGAR:", "Bs. " . $total);
        if ($metodo === 'EFECTIVO') {
            $lines[] = $this->twoColumns("Monto Entregado:", "Bs. " . $recibido);
            $lines[] = $this->twoColumns("Cambio / Vuelto:", "Bs. " . $cambio);
        } elseif ($metodo === 'MIXTO') {
            $ef = number_format((float)($factura['monto_efectivo'] ?? 0), 2, '.', '');
            $tar = number_format((float)($factura['monto_tarjeta'] ?? 0), 2, '.', '');
            $qr = number_format((float)($factura['monto_qr'] ?? 0), 2, '.', '');
            $lines[] = $this->twoColumns(" - Efectivo:", "Bs. " . $ef);
            if ((float)$tar > 0) $lines[] = $this->twoColumns(" - Tarjeta:", "Bs. " . $tar);
            if ((float)$qr > 0) $lines[] = $this->twoColumns(" - QR Digital:", "Bs. " . $qr);
        }

        $lines[] = $this->divider('-');

        // Mensaje Legal y Pie
        if ($tipoComp === 'FACTURA') {
            $lines[] = $this->center("ESTA FACTURA CONTRIBUYE AL");
            $lines[] = $this->center("DESARROLLO DEL PAIS");
            $lines[] = $this->center("Ley N 453: Exija su factura.");
        } else {
            $lines[] = $this->center("Gracias por su preferencia!");
        }

        $lines[] = $this->center("Desarrollado por Ribersoft: 67369293");
        $lines[] = "\n\n\n"; // Espacio para salida de papel

        return implode("\r\n", $lines);
    }

    private function center(string $text): string
    {
        $len = strlen($text);
        if ($len >= self::LINE_WIDTH) {
            return substr($text, 0, self::LINE_WIDTH);
        }
        $padLeft = (int)floor((self::LINE_WIDTH - $len) / 2);
        return str_repeat(' ', $padLeft) . $text;
    }

    private function divider(string $char): string
    {
        return str_repeat($char, self::LINE_WIDTH);
    }

    private function twoColumns(string $left, string $right): string
    {
        $spaceNeeded = self::LINE_WIDTH - strlen($left) - strlen($right);
        if ($spaceNeeded < 1) {
            return substr($left, 0, self::LINE_WIDTH - strlen($right) - 1) . ' ' . $right;
        }
        return $left . str_repeat(' ', $spaceNeeded) . $right;
    }
}
