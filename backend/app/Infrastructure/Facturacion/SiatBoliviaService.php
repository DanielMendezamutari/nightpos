<?php

declare(strict_types=1);

namespace App\Infrastructure\Facturacion;

class SiatBoliviaService
{
    private string $nitEmisor = '1028456023';
    private string $cufdActual = 'B19DF429E30A8B744C2719B5';

    public function generarCuf(
        int $nroFactura,
        string $fechaEmision,
        int $sucursal = 0,
        int $modalidad = 2,
        int $tipoEmision = 1,
        int $tipoFactura = 1,
        int $tipoDocumentoSector = 1,
        int $puntoVenta = 0
    ): string {
        $fechaFormato = (new \DateTime($fechaEmision))->format('YmdHisv');
        $nitPad = str_pad($this->nitEmisor, 13, '0', STR_PAD_LEFT);
        $fechaPad = str_pad($fechaFormato, 17, '0', STR_PAD_LEFT);
        $sucursalPad = str_pad((string)$sucursal, 4, '0', STR_PAD_LEFT);
        $modalidadPad = (string)$modalidad;
        $tipoEmisionPad = (string)$tipoEmision;
        $tipoFacturaPad = (string)$tipoFactura;
        $sectorPad = str_pad((string)$tipoDocumentoSector, 2, '0', STR_PAD_LEFT);
        $nroPad = str_pad((string)$nroFactura, 10, '0', STR_PAD_LEFT);
        $pvPad = str_pad((string)$puntoVenta, 4, '0', STR_PAD_LEFT);

        $cadena = $nitPad . $fechaPad . $sucursalPad . $modalidadPad . $tipoEmisionPad . $tipoFacturaPad . $sectorPad . $nroPad . $pvPad;
        $mod11 = $this->calculaModulo11($cadena);
        $cadenaConDigito = $cadena . $mod11;

        // Convert to Base16 (Hex)
        $cufHex = '';
        $longitud = strlen($cadenaConDigito);
        for ($i = 0; $i < $longitud; $i += 8) {
            $chunk = substr($cadenaConDigito, $i, 8);
            $cufHex .= strtoupper(str_pad(dechex((int)$chunk), 7, '0', STR_PAD_LEFT));
        }

        return substr($cufHex . strtoupper(bin2hex(random_bytes(8))), 0, 64);
    }

    public function getCufd(): string
    {
        return $this->cufdActual;
    }

    public function generarQrUrl(string $nitEmisor, string $cuf, int $nroFactura, float $total, string $fecha): string
    {
        $fechaIso = (new \DateTime($fecha))->format('d/m/Y');
        return "https://siat.impuestos.gob.bo/consulta/QR?nit={$nitEmisor}&cuf={$cuf}&numero={$nroFactura}&monto=" . number_format($total, 2, '.', '') . "&fecha=" . urlencode($fechaIso);
    }

    private function calculaModulo11(string $cadena): int
    {
        $mult = 2;
        $suma = 0;
        for ($i = strlen($cadena) - 1; $i >= 0; $i--) {
            $suma += (int)$cadena[$i] * $mult;
            $mult++;
            if ($mult > 9) {
                $mult = 2;
            }
        }
        $modulo = $suma % 11;
        if ($modulo === 0) return 0;
        if ($modulo === 1) return 1;
        return 11 - $modulo;
    }
}