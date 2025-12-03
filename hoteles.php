<?php 
    require_once 'layout_top.php';
    require_once 'funciones.php';
?>

<header>
    <h1>Hoteles disponibles</h1>
</header>

<div class="card">
    <div class="galeria">
        <img src="public/media/welcome-again-to-coche.jpg" class="img-hotel" alt="foto">
        <img src="public/media/puntaNlanca2.jpg" class="img-hotel" alt="foto">
        <img src="public/media/puntablanca3.jpg" class="img-hotel" alt="foto">
        <img src="public/media/morrocoy.webp" class="img-hotel" alt="foto">
        <img src="public/media/caballitos.jpg" class="img-hotel" alt="foto">
    </div>
    <h2 style="color: var(--dark);">Sunsol Punta Blanca</h2>
    <p>📍 Isla de coche</p>
    <ul>
        <li>
            Frente al mar
        </li>
        <li>
            Carreras de morrocoy
        </li>
        <li>
            Centro hípico de caballitos de mar
        </li>
    </ul>
</div>
<div class="card">
    <div class="galeria">
        <img src="public/media/ecoland1.jpg" class="img-hotel" alt="foto">
        <img src="public/media/ecoland2.jpg" class="img-hotel" alt="foto">
        <img src="public/media/ecoland3.jpg" class="img-hotel" alt="foto">
        <img src="public/media/ajedrez.jpg" class="img-hotel" alt="foto">
    </div>
    <h2 style="color: var(--dark);">Sunsol Ecoland</h2>
    <p📍 >Valle de Pedro Gonzales, Isla de Margarita</p>
    <ul>
        <li>
            Con paseo al faro
        </li>
        <li>
            cerca de la playa
        </li>
        <li>
            Ajedrez bajo el agua
        </li>
    </ul>
</div>

<div class="card">
    <div class="galeria">
        <img src="public/media/dorada1.jpg" class="img-hotel" alt="foto">
        <img src="public/media/dorada2.jpg" class="img-hotel" alt="foto">
        <img src="public/media/dorada3.jpg" class="img-hotel" alt="foto">
        <img src="public/media/pp.png" class="img-hotel" alt="foto">
    </div>
    <h2 style="color: var(--dark);">Lidotel Agua Dorada</h2>
    <p>📍 PLaya el agua, isla de Margarita</p>
    <ul>
        <li>
            Comodas habitaciones
        </li>
        <li>
            Vida nocturna en la playa
        </li>
        <li>
            Ping Pong con huevo
        </li>
    </ul>
</div>

<div class="card">
    <div class="galeria">
        <img src="public/media/hesperia1.jpg" class="img-hotel" alt="foto">
        <img src="public/media/hesperia2.png" class="img-hotel" alt="foto">
        <img src="public/media/hesperia3.webp" class="img-hotel" alt="foto">
        <img src="public/media/caida.png" class="img-hotel" alt="foto">
    </div>
    <h2 style="color: var(--dark);">Hesperia</h2>
    <p>📍 Valle de Pedro Gonzalez, isla de Margarita</p>
    <ul>
        <li>
            Maximo lujo
        </li>
        <li>
            Cerca de la playa
        </li>
        <li>
            Club de golf, lagunas y discoteca
        </li>
        <li>
            Caida presa
        </li>
    </ul>
</div>
<div id="fullpage" onclick="this.style.display='none';"></div>

<script>
    const imgs = document.querySelectorAll('.galeria img');
    const fullPage = document.querySelector('#fullpage');

    imgs.forEach(img => {
      img.addEventListener('click', function() {
        fullPage.style.backgroundImage = 'url(' + img.src + ')';
        fullPage.style.display = 'block';
      });
    });
</script>