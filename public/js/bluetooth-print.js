/**
 * Impresión Bluetooth para impresoras térmicas ESC/POS
 * Usa Web Bluetooth API (funciona en Chrome Android)
 * Compatible con impresoras térmicas Bluetooth (Xprinter, 58mm, 80mm, etc.)
 */

const BTPrint = (function() {
    // UUIDs comunes para impresoras térmicas ESC/POS Bluetooth
    const SERVICIOS_IMPRESORA = [
        '000018f0-0000-1000-8000-00805f9b34fb', // Servicio estándar de impresión ESC/POS
        '49535343-fe7d-4ae5-8fa9-9fafd205e455'  // BLE / ESC/POS (muchas impresoras chinas)
    ];
    // UUID del servicio de escritura de datos (común en impresoras ESC/POS)
    const SERVICIO_ESC_POS = '000018f0-0000-1000-8000-00805f9b34fb';
    const CARACTERISTICA_ESCRITURA = '00002af1-0000-1000-8000-00805f9b34fb';

    // Comandos ESC/POS
    const ESC = 0x1B;
    const GS = 0x1D;
    const LF = 0x0A;

    // Estado de conexión
    let device = null;
    let server = null;
    let service = null;
    let characteristic = null;

    /**
     * Conecta a la impresora Bluetooth
     */
    async function conectar() {
        if (!navigator.bluetooth) {
            throw new Error('Este navegador no soporta Web Bluetooth. Usa Chrome en Android.');
        }

        try {
            device = await navigator.bluetooth.requestDevice({
                filters: [
                    { services: [SERVICIO_ESC_POS] }
                ],
                // Fallback: permitir seleccionar cualquier dispositivo
                acceptAllDevices: false,
                optionalServices: [SERVICIO_ESC_POS]
            });

            device.addEventListener('gattserverdisconnected', function() {
                server = null;
                service = null;
                characteristic = null;
            });

            server = await device.gatt.connect();
            service = await server.getPrimaryService(SERVICIO_ESC_POS);
            characteristic = await service.getCharacteristic(CARACTERISTICA_ESCRITURA);

            return true;
        } catch (err) {
            // Si falla con el servicio específico, intentar con el otro UUID común
            if (err && err.name !== 'NotFoundError') {
                throw err;
            }
        }

        // Intento alternativo con el otro UUID
        try {
            device = await navigator.bluetooth.requestDevice({
                filters: [{ services: ['49535343-fe7d-4ae5-8fa9-9fafd205e455'] }],
                optionalServices: ['49535343-fe7d-4ae5-8fa9-9fafd205e455']
            });

            device.addEventListener('gattserverdisconnected', function() {
                server = null;
                service = null;
                characteristic = null;
            });

            server = await device.gatt.connect();
            service = await server.getPrimaryService('49535343-fe7d-4ae5-8fa9-9fafd205e455');
            // Buscar la característica de escritura
            const characteristics = await service.getCharacteristics();
            if (characteristics.length > 0) {
                characteristic = characteristics[0];
            } else {
                throw new Error('No se encontró la característica de impresión en la impresora.');
            }

            return true;
        } catch (err) {
            throw new Error('No se pudo conectar a la impresora Bluetooth. Asegúrate de que está encendida y emparejada.');
        }
    }

    /**
     * Genera los datos ESC/POS de un ticket de venta
     */
    function generarTicketVenta(venta) {
        const txt = new TextEncoder();
        let data = [];

        // Configurar impresora
        data.push(ESC, 0x40); // Inicializar impresora
        data.push(ESC, 0x61, 0x01); // Alineación centrada
        data.push(ESC, 0x21, 0x08); // Doble tamaño
        data.push(0x1D, 0x21, 0x11); // Doble ancho y alto

        const convert = (str) => Array.from(txt.encode(str || ''));

        // Encabezado
        data = data.concat(convert(venta.tienda || ''));
        data.push(LF);
        data.push(ESC, 0x21, 0x00); // Tamaño normal
        data.push(ESC, 0x61, 0x01);
        data = data.concat(convert(venta.direccion || ''));
        data.push(LF);
        data = data.concat(convert(venta.telefono || ''));
        data.push(LF);

        // Número de venta
        data.push(ESC, 0x61, 0x01);
        data.push(ESC, 0x21, 0x08);
        data = data.concat(convert(venta.numero_venta || ''));
        data.push(LF);

        // Separador
        data.push(ESC, 0x21, 0x00);
        data = data.concat(convert('='.repeat(32)));
        data.push(LF);

        // Cliente y fecha
        data.push(ESC, 0x61, 0x00);
        data = data.concat(convert('CLIENTE: ' + (venta.cliente || 'VENTA GENERAL')));
        data.push(LF);
        data = data.concat(convert('FECHA: ' + venta.fecha));
        data.push(LF);
        data = data.concat(convert('PAGO: ' + venta.metodo_pago));
        data.push(LF);
        data = data.concat(convert('VENDEDOR: ' + venta.vendedor));
        data.push(LF);

        // Separador
        data = data.concat(convert('='.repeat(32)));
        data.push(LF);

        // Productos
        (venta.productos || []).forEach(function(prod) {
            data = data.concat(convert(prod.nombre || ''));
            data.push(LF);
            const linea = prod.cantidad + ' x ' + prod.precio_unitario + ' = ' + prod.subtotal;
            data = data.concat(convert(linea));
            data.push(LF);
        });

        // Separador
        data = data.concat(convert('='.repeat(32)));
        data.push(LF);

        // Totales
        if (venta.subtotal > 0) {
            data = data.concat(convert('Subtotal: ' + formatMoneda(venta.subtotal)));
            data.push(LF);
        }
        if (venta.descuento > 0) {
            data = data.concat(convert('Descuento: -' + formatMoneda(venta.descuento)));
            data.push(LF);
        }
        data = data.concat(convert('IGV/IVA: ' + formatMoneda(venta.impuesto)));
        data.push(LF);

        // Total
        data.push(ESC, 0x21, 0x08); // Doble tamaño
        data = data.concat(convert('TOTAL: ' + formatMoneda(venta.total)));
        data.push(LF);
        data.push(ESC, 0x21, 0x00);

        // Pie
        data.push(LF);
        data.push(ESC, 0x61, 0x01);
        data = data.concat(convert('¡Gracias por su preferencia!'));
        data.push(LF, LF, LF);

        // Cortar papel
        data.push(GS, 0x56, 0x41, 0x10); // Cortar papel

        return new Uint8Array(data);
    }

    /**
     * Genera los datos ESC/POS de un ticket de reparación
     */
    function generarTicketReparacion(reparacion) {
        const txt = new TextEncoder();
        let data = [];

        const convert = (str) => Array.from(txt.encode(str || ''));

        // Configurar impresora
        data.push(ESC, 0x40);
        data.push(ESC, 0x61, 0x01);
        data.push(ESC, 0x21, 0x08);

        // Encabezado
        data = data.concat(convert(reparacion.tienda || ''));
        data.push(LF);
        data.push(ESC, 0x21, 0x00);
        data = data.concat(convert(reparacion.direccion || ''));
        data.push(LF);

        // Número de orden
        data.push(ESC, 0x21, 0x08);
        data = data.concat(convert(reparacion.numero_orden || ''));
        data.push(LF);
        data.push(ESC, 0x21, 0x00);

        // Separador
        data = data.concat(convert('='.repeat(32)));
        data.push(LF);

        // Cliente
        data.push(ESC, 0x61, 0x00);
        data = data.concat(convert('CLIENTE: ' + (reparacion.cliente || '')));
        data.push(LF);
        data = data.concat(convert('TECNICO: ' + (reparacion.tecnico || '')));
        data.push(LF);
        data = data.concat(convert('RECIBIDO: ' + reparacion.fecha_recepcion));
        data.push(LF);

        // Equipo
        data = data.concat(convert('TIPO: ' + (reparacion.tipo_dispositivo || '')));
        data.push(LF);
        if (reparacion.marca) {
            data = data.concat(convert('MARCA: ' + reparacion.marca));
            data.push(LF);
        }
        if (reparacion.modelo) {
            data = data.concat(convert('MODELO: ' + reparacion.modelo));
            data.push(LF);
        }
        if (reparacion.imei) {
            data = data.concat(convert('IMEI: ' + reparacion.imei));
            data.push(LF);
        }
        if (reparacion.color) {
            data = data.concat(convert('COLOR: ' + reparacion.color));
            data.push(LF);
        }

        // Separador
        data = data.concat(convert('='.repeat(32)));
        data.push(LF);

        // Falla
        if (reparacion.falla_reportada) {
            data = data.concat(convert('FALLA: ' + reparacion.falla_reportada));
            data.push(LF);
        }
        if (reparacion.diagnostico) {
            data = data.concat(convert('DIAG: ' + reparacion.diagnostico));
            data.push(LF);
        }

        // Precios
        data = data.concat(convert('='.repeat(32)));
        data.push(LF);
        if (reparacion.presupuesto > 0) {
            data = data.concat(convert('PRESUPUESTO: ' + formatMoneda(reparacion.presupuesto)));
            data.push(LF);
        }
        if (reparacion.abono > 0) {
            data = data.concat(convert('ABONO: ' + formatMoneda(reparacion.abono)));
            data.push(LF);
        }
        if (reparacion.impuesto > 0) {
            data = data.concat(convert('IGV/IVA: ' + formatMoneda(reparacion.impuesto)));
            data.push(LF);
        }
        if (reparacion.total > 0) {
            data.push(ESC, 0x21, 0x08);
            data = data.concat(convert('TOTAL: ' + formatMoneda(reparacion.total)));
            data.push(LF);
            data.push(ESC, 0x21, 0x00);
        }

        // Pie
        data.push(LF);
        data.push(ESC, 0x61, 0x01);
        data = data.concat(convert('¡Gracias por su preferencia!'));
        data.push(LF, LF, LF);

        data.push(GS, 0x56, 0x41, 0x10);

        return new Uint8Array(data);
    }

    function formatMoneda(valor) {
        return (typeof valor === 'number' ? valor.toFixed(2) : '0.00');
    }

    /**
     * Envía los datos a la impresora.
     * Si no hay chunks definidos, se envía todo de una vez.
     */
    async function enviarDatos(data) {
        if (!characteristic) {
            throw new Error('No hay conexión con la impresora. Conecta primero.');
        }

        try {
            // Enviar en chunks para evitar problemas de buffer (imprime por partes)
            const chunkSize = 100;
            for (let i = 0; i < data.length; i += chunkSize) {
                const chunk = data.slice(i, i + chunkSize);
                await characteristic.writeValue(chunk);
            }
            return true;
        } catch (err) {
            throw new Error('Error al enviar los datos a la impresora: ' + err.message);
        }
    }

    /**
     * API pública
     */
    return {
        conectar: conectar,
        generarTicketVenta: generarTicketVenta,
        generarTicketReparacion: generarTicketReparacion,
        enviarDatos: enviarDatos
    };
})();