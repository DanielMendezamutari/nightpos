<?php

declare(strict_types=1);

namespace App\Services\Printing;

class TicketFormatterService
{
    // Estándar ESC/POS para impresoras térmicas de 80mm con Font A (12x24)
    public const LINE_WIDTH = 42;

    // Comandos ESC/POS nativos
    private const ESC_INIT        = "\x1B\x40";
    private const ESC_ALIGN_LEFT  = "\x1B\x61\x00";
    private const ESC_ALIGN_CENTER= "\x1B\x61\x01";
    private const ESC_ALIGN_RIGHT = "\x1B\x61\x02";
    private const ESC_BOLD_ON     = "\x1B\x45\x01";
    private const ESC_BOLD_OFF    = "\x1B\x45\x00";
    private const ESC_DOUBLE_SIZE = "\x1D\x21\x11";
    private const ESC_NORMAL_SIZE = "\x1D\x21\x00";
    private const ESC_CUT_PAPER   = "\r\n\r\n\r\n\r\n\x1D\x56\x42\x00";
    private const ESC_OPEN_DRAWER = "\x1B\x70\x00\x19\xFA";

    /**
     * Formatea un recibo de cobro o factura fiscal idéntico a RestoTech.
     * Retorna el stream binario con comandos ESC/POS para impresión RAW de ancho completo (80mm).
     */
    public function formatTicket(array $factura, bool $abrirCajon = false): string
    {
        $tipoComp = strtoupper((string)($factura['tipo_comprobante'] ?? 'FACTURA'));
        $nroDoc = $factura['nro_comprobante'] ?? ('REC-' . str_pad((string)($factura['nro_factura'] ?? '1'), 6, '0', STR_PAD_LEFT));
        $fecha = $factura['fecha_emision'] ?? date('Y-m-d H:i:s');
        $cliente = $this->limpiarTexto($factura['razon_social'] ?? 'Sin Nombre');
        $doc = $factura['numero_documento'] ?? '0';
        $mesa = $factura['mesa_numero'] ?? 'BARRA';
        $cajero = $this->limpiarTexto($factura['cajero'] ?? 'Cajero');
        $metodo = strtoupper((string)($factura['metodo_pago'] ?? 'EFECTIVO'));

        $out = self::ESC_INIT;

        if ($abrirCajon) {
            $out .= self::ESC_OPEN_DRAWER;
        }

        // Encabezado
        $out .= self::ESC_ALIGN_CENTER;
        $out .= self::ESC_BOLD_ON . self::ESC_DOUBLE_SIZE . "RIBERESTO POS\r\n" . self::ESC_NORMAL_SIZE;
        $out .= "RIBERSOFT BOLIVIA\r\n";
        $out .= "NIT: 1028456023 | Telf: 67369293\r\n";
        $out .= "Santa Cruz - Bolivia\r\n";
        $out .= self::ESC_BOLD_OFF;
        $out .= $this->divider('=') . "\r\n";

        // Tipo de Comprobante
        $out .= self::ESC_BOLD_ON;
        if ($tipoComp === 'RECIBO') {
            $out .= "RECIBO DE CAJA / NOTA DE VENTA\r\n";
            $out .= "Nro: " . $nroDoc . "\r\n";
            $out .= "*** COMPROBANTE CONSUMO INTERNO ***\r\n";
        } else {
            $out .= "FACTURA Nro: " . ($factura['nro_factura'] ?? '1') . "\r\n";
            if (!empty($factura['cuf'])) {
                $out .= "CUF: " . substr((string)$factura['cuf'], 0, 36) . "...\r\n";
            }
        }
        $out .= self::ESC_BOLD_OFF;
        $out .= $this->divider('-') . "\r\n";

        // Datos del Cliente y Mesa
        $out .= self::ESC_ALIGN_LEFT;
        $out .= "Fecha: " . $fecha . "\r\n";
        $out .= "Senor(es): " . substr($cliente, 0, 31) . "\r\n";
        $out .= "NIT/CI: " . $doc . "\r\n";
        $out .= "Mesa: " . $mesa . " | Cajero: " . $cajero . "\r\n";
        $out .= "Metodo de Pago: " . $metodo . "\r\n";
        $out .= $this->divider('-') . "\r\n";

        // Columnas
        $out .= self::ESC_BOLD_ON;
        $out .= $this->twoColumns("CANT.  DESCRIPCION", "SUBTOTAL") . "\r\n";
        $out .= self::ESC_BOLD_OFF;
        $out .= $this->divider('-') . "\r\n";

        // Ítems / Detalles
        $detalles = $factura['detalles'] ?? [];
        if (empty($detalles) && !empty($factura['items'])) {
            $detalles = $factura['items'];
        }

        foreach ($detalles as $d) {
            $cant = (int)($d['cantidad'] ?? 1);
            $nombre = $this->limpiarTexto($d['producto_nombre'] ?? ($d['producto']['nombre'] ?? 'Producto'));
            $subtotal = number_format((float)($d['subtotal'] ?? 0), 2, '.', '');

            $cantCol = sprintf("%2dx ", $cant);
            // Ancho reservado: cant (4) + subtotal (8) + espacio (2) = 14 => nombre max 28 chars
            $maxNameLen = self::LINE_WIDTH - strlen($cantCol) - strlen($subtotal) - 2;
            if (strlen($nombre) > $maxNameLen) {
                $nombre = substr($nombre, 0, $maxNameLen);
            }

            $left = $cantCol . $nombre;
            $out .= $this->twoColumns($left, $subtotal) . "\r\n";
        }

        $out .= $this->divider('-') . "\r\n";

        // Totales y Vuelto
        $total = number_format((float)($factura['monto_total'] ?? 0), 2, '.', '');
        $recibido = number_format((float)($factura['monto_recibido'] ?? $factura['monto_total'] ?? 0), 2, '.', '');
        $cambio = number_format((float)($factura['cambio'] ?? $factura['monto_cambio'] ?? 0), 2, '.', '');

        $out .= self::ESC_BOLD_ON;
        $out .= $this->twoColumns("TOTAL A PAGAR:", "Bs. " . $total) . "\r\n";
        $out .= self::ESC_BOLD_OFF;

        if ($metodo === 'EFECTIVO') {
            $out .= $this->twoColumns("Monto Entregado:", "Bs. " . $recibido) . "\r\n";
            $out .= $this->twoColumns("Cambio / Vuelto:", "Bs. " . $cambio) . "\r\n";
        } elseif ($metodo === 'MIXTO') {
            $ef = number_format((float)($factura['monto_efectivo'] ?? 0), 2, '.', '');
            $tar = number_format((float)($factura['monto_tarjeta'] ?? 0), 2, '.', '');
            $qr = number_format((float)($factura['monto_qr'] ?? 0), 2, '.', '');
            $out .= $this->twoColumns(" - Efectivo:", "Bs. " . $ef) . "\r\n";
            if ((float)$tar > 0) $out .= $this->twoColumns(" - Tarjeta:", "Bs. " . $tar) . "\r\n";
            if ((float)$qr > 0) $out .= $this->twoColumns(" - QR Digital:", "Bs. " . $qr) . "\r\n";
        }

        $out .= $this->divider('-') . "\r\n";

        // Pie de Ticket RestoTech
        $out .= self::ESC_ALIGN_CENTER;
        if ($tipoComp === 'FACTURA') {
            $out .= "ESTA FACTURA CONTRIBUYE AL\r\n";
            $out .= "DESARROLLO DEL PAIS\r\n";
            $out .= "Ley N 453: Exija su factura.\r\n";
        } else {
            $out .= "Gracias por su preferencia!\r\n";
        }

        $out .= "Desarrollado por Ribersoft: 67369293\r\n";
        $out .= self::ESC_CUT_PAPER;

        return $out;
    }

    /**
     * Formatea una PRECUENTA de mesa activa idéntica a RestoTech (printCuentaTotalFactura).
     */
    public function formatPrecuenta(array $cuenta): string
    {
        $mesa = $cuenta['mesa_numero'] ?? 'Mesa';
        $mesero = $this->limpiarTexto($cuenta['mesero'] ?? 'Garzon');
        $cliente = $this->limpiarTexto($cuenta['cliente'] ?? '');
        $fecha = $cuenta['fecha'] ?? date('Y-m-d H:i:s');
        $detalles = $cuenta['detalles'] ?? $cuenta['items'] ?? [];
        $totalMonto = (float)($cuenta['monto_total'] ?? 0);

        $out = self::ESC_INIT;
        $out .= self::ESC_ALIGN_CENTER;
        $out .= self::ESC_BOLD_ON . self::ESC_DOUBLE_SIZE . "RIBERESTO POS\r\n" . self::ESC_NORMAL_SIZE;
        $out .= self::ESC_BOLD_ON . "CUENTA\r\n" . self::ESC_BOLD_OFF;
        $out .= "En Mesa\r\n";
        $out .= $this->divider('=') . "\r\n";

        $out .= self::ESC_ALIGN_LEFT;
        $out .= "Fecha: " . $fecha . "\r\n";
        if (!empty($cliente)) {
            $out .= "Cliente: " . substr($cliente, 0, 32) . "\r\n";
        }
        $out .= "Mesa: " . $mesa . " | Mesero: " . $mesero . "\r\n";
        $out .= $this->divider('-') . "\r\n";

        // Columnas estilo RestoTech: DESCRIPCION / CANT. / TOTAL
        $out .= self::ESC_BOLD_ON;
        $out .= $this->formatThreeColsHeader("DESCRIPCION", "CANT.", "TOTAL") . "\r\n";
        $out .= self::ESC_BOLD_OFF;
        $out .= $this->divider('-') . "\r\n";

        $calculoTotal = 0.0;
        foreach ($detalles as $d) {
            $cant = (float)($d['cantidad'] ?? 1);
            $nombre = $this->limpiarTexto($d['producto_nombre'] ?? ($d['producto']['nombre'] ?? 'Producto'));
            $subtotal = (float)($d['subtotal'] ?? ($cant * (float)($d['precio_unitario'] ?? 0)));
            $calculoTotal += $subtotal;

            $out .= $this->formatThreeColsRow($nombre, $cant, $subtotal) . "\r\n";
        }

        if ($totalMonto <= 0) {
            $totalMonto = $calculoTotal;
        }

        $out .= $this->divider('-') . "\r\n";
        $out .= self::ESC_BOLD_ON;
        $out .= $this->twoColumns("TOTAL A PAGAR:", "Bs. " . number_format($totalMonto, 2, '.', '')) . "\r\n";
        $out .= self::ESC_BOLD_OFF;
        $out .= $this->divider('-') . "\r\n";

        $out .= self::ESC_ALIGN_CENTER;
        $out .= "Gracias por su preferencia!\r\n";
        $out .= "Desarrollado por Ribersoft: 67369293\r\n";
        $out .= self::ESC_CUT_PAPER;

        return $out;
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

    private function formatThreeColsHeader(string $c1, string $c2, string $c3): string
    {
        // 42 columnas: Descripcion (24) | Cant (8) | Total (10)
        return sprintf("%-24s %7s %9s", $c1, $c2, $c3);
    }

    private function formatThreeColsRow(string $nombre, float $cant, float $subtotal): string
    {
        // Descripcion max 24 caracteres
        $nombreCorto = (strlen($nombre) > 24) ? substr($nombre, 0, 24) : $nombre;
        $cantStr = (floor($cant) == $cant) ? (string)((int)$cant) : number_format($cant, 2, '.', '');
        $totalStr = number_format($subtotal, 2, '.', '');

        return sprintf("%-24s %7s %9s", $nombreCorto, $cantStr, $totalStr);
    }

    private function limpiarTexto(string $texto): string
    {
        // Reemplazar tildes y caracteres especiales para compatibilidad térmico ESC/POS
        $unwanted = [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
            'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U',
            'ñ'=>'n', 'Ñ'=>'N', '°'=>' '
        ];
        return strtr($texto, $unwanted);
    }
}
