<?php
$tituloPagina = 'Catálogo';
require_once __DIR__ . '/includes/header.php';
?>

<h1 style="font-size:22px;">Nuestros productos</h1>
<div id="catalogo" class="grid"><p style="color:var(--muted)">Cargando productos…</p></div>

<script>
function money(n){ return '$' + Number(n).toFixed(2); }

function renderProducto(p) {
  const primerMedia = p.media[0];
  let mediaHtml = '<div style="width:100%;height:200px;background:#0f0f1a;"></div>';
  if (primerMedia) {
    mediaHtml = primerMedia.type === 'video'
      ? `<video src="${primerMedia.file_path}" muted loop autoplay playsinline></video>`
      : `<img src="${primerMedia.file_path}" alt="${p.name}">`;
  }
  return `
    <a class="card" style="text-decoration:none;color:inherit;" href="/product.php?id=${p.id}">
      ${mediaHtml}
      <div class="info">
        <h3>${p.name}</h3>
        <div class="precio">${money(p.price)}</div>
      </div>
    </a>`;
}

async function cargarCatalogo() {
  try {
    const res = await fetch('/api/products.php', { cache: 'no-store' });
    const data = await res.json();
    const cont = document.getElementById('catalogo');
    if (!data.products.length) {
      cont.innerHTML = '<p style="color:var(--muted)">Todavía no hay productos publicados.</p>';
      return;
    }
    cont.innerHTML = data.products.map(renderProducto).join('');
  } catch (e) {
    console.error('Error cargando catálogo', e);
  }
}

cargarCatalogo();
// Refresca el catálogo cada 5s para reflejar cambios del admin casi en tiempo real
setInterval(cargarCatalogo, 5000);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
