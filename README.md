# Mi Tienda — Guía de instalación

## 1. Consigue un hosting con PHP + MySQL (para probar ya, gratis)

Yo no puedo crear el hosting por ti, pero puedes tener uno funcionando en minutos:
- **InfinityFree** (infinityfree.net) — gratis, PHP + MySQL, sin tarjeta.
- **000webhost** — gratis, similar.
- Cuando el negocio crezca, pásate a un hosting de pago (Hostinger, SiteGround, etc.) porque los gratuitos limitan el tamaño de subida de video.

## 2. Sube los archivos

Sube TODA la carpeta `tienda/` a la raíz pública de tu hosting (normalmente `public_html/` o `htdocs/`).

## 3. Crea la base de datos

1. En el panel de tu hosting, entra a **phpMyAdmin**.
2. Crea una base de datos (o usa la que te dieron).
3. Importa el archivo `schema.sql` (botón "Importar").
4. Abre `config/db.php` y pon ahí el host, nombre de base de datos, usuario y contraseña que te dio tu hosting.

## 4. Crea tu cuenta de administrador (una sola vez)

1. Entra a `https://tudominio.com/install/setup.php`
2. Elige tu propio usuario y contraseña (mínimo 10 caracteres). Se guardan cifrados, nunca en texto plano.
3. **Muy importante:** después de crear la cuenta, borra o renombra la carpeta `/install` de tu servidor. Si la dejas, cualquiera podría intentar usarla (aunque ya está bloqueada tras el primer uso, es mejor quitarla).
4. Entra al panel en `https://tudominio.com/admin/login.php`

## 5. Sube tus productos

Desde el panel de administrador puedes:
- Crear productos (ropa, dispositivos, accesorios...)
- Subir imágenes y videos por producto — los videos se guardan sin comprimir para conservar calidad HD/4K, así que los archivos grandes pueden tardar en subir. Esto es normal.
- Borrar productos o archivos.

Los cambios se reflejan en la tienda automáticamente: la página principal (`index.php`) actualiza el catálogo cada 5 segundos sin que nadie tenga que recargar la página.

## 6. Activa los pagos reales

Edita `config/payments.php`:

- **Stripe**: crea tu cuenta en stripe.com → Dashboard → Developers → API keys. Pega tu clave secreta en `STRIPE_SECRET_KEY`.
- **PayPal**: crea una app en developer.paypal.com → pega `PAYPAL_CLIENT_ID` y `PAYPAL_SECRET`. Cambia `PAYPAL_MODE` a `'live'` cuando quieras cobrar de verdad (empieza en `'sandbox'` para hacer pruebas sin dinero real).
- **Pago manual**: edita `MANUAL_PAYMENT_INFO` con tus datos bancarios o de WhatsApp.

También reemplaza `https://TU-DOMINIO.com` por tu dominio real dentro de `checkout.php` (aparece en `success_url`/`cancel_url`/`return_url`).

## 7. Seguridad ya incluida

- Contraseñas guardadas con `bcrypt` (nunca en texto plano).
- Consultas a la base de datos con sentencias preparadas (protección contra inyección SQL).
- Protección CSRF en todos los formularios.
- Límite de intentos de login (defensa básica contra fuerza bruta).
- Sesiones con cookies `httpOnly` (JavaScript no puede robarlas).
- La carpeta `config/` está bloqueada por `.htaccess` para que nadie pueda abrirla desde el navegador.

## Estructura del proyecto

```
tienda/
├── admin/           → panel de administrador (productos, imágenes, videos)
├── api/             → endpoint JSON para el catálogo en tiempo real
├── assets/uploads/  → aquí se guardan las imágenes y videos subidos
├── config/          → conexión a base de datos y claves de pago
├── includes/        → funciones de seguridad, header/footer
├── install/         → asistente de un solo uso para crear el admin
├── index.php        → catálogo público
├── product.php      → detalle de producto
├── checkout.php     → pago (Stripe / PayPal / manual)
├── register.php / login.php / logout.php → cuentas de clientes
└── schema.sql       → estructura de la base de datos
```
