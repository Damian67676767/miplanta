<?php
session_start();
include 'conexion.php';

$info_resultado = "";
if (isset($_POST['consultar'])) {
    $nombre_planta = htmlspecialchars($_POST['nombre']);
    $etapa = htmlspecialchars($_POST['etapa']);
    $clima = htmlspecialchars($_POST['clima']);

    // --- LÓGICA PARA ASOCIAR LA IMAGEN AUTOMÁTICAMENTE ---
    $nombre_limpio = strtolower(trim($nombre_planta));
    $busqueda = array('á', 'é', 'í', 'ó', 'ú', 'ñ', ' ');
    $reemplazo = array('a', 'e', 'i', 'o', 'u', 'n', '_');
    $nombre_archivo = str_replace($busqueda, $reemplazo, $nombre_limpio);

    // Ruta donde buscará la imagen (ej. img/rabano.jpg)
    $ruta_imagen = "img/" . $nombre_archivo . ".jpg";

    // Si la imagen específica no existe, carga una imagen genérica por defecto
    if (!file_exists($ruta_imagen)) {
        $ruta_imagen = "img/default.jpg"; 
    }
    // ---------------------------------------------------

    if (isset($_SESSION['usuario_id'])) {
        $stmt = $pdo->prepare("INSERT INTO plantas (usuario_id, nombre, etapa, clima) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['usuario_id'], $nombre_planta, $etapa, $clima]);
    }

    // --- AGREGAR IMAGEN AL INICIO DEL PANEL DE RESULTADOS ---
    $info_resultado .= "<div style='text-align: center; margin-bottom: 15px;'>";
    $info_resultado .= "<img src='$ruta_imagen' alt='Cultivo de $nombre_planta' style='max-width: 100%; height: 180px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);'>";
    $info_resultado .= "</div>";

    $info_resultado .= "<h3>Información para: <strong>$nombre_planta</strong></h3>";
    $info_resultado .= "<p><strong>Etapa:</strong> " . ucfirst($etapa) . " | <strong>Clima seleccionado:</strong> " . ucfirst($clima) . "</p><hr>";
    
    $info_resultado .= "<h4>💧 Necesidad de Agua Específica:</h4>";
    if ($clima == "seco") {
        if ($etapa == "semilla") {
            $info_resultado .= "<p>En clima seco y etapa de semilla, el sustrato se evapora rápido. Riega con un atomizador <strong>3 veces al día</strong> manteniendo la humedad constante sin encharcar.</p>";
        } else {
            $info_resultado .= "<p>El clima seco exige riegos más frecuentes. Revisa la tierra diariamente a 3 cm de profundidad; si está seca, riega profundamente por la mañana temprano.</p>";
        }
    } elseif ($clima == "tropical") {
        if ($etapa == "semilla") {
            $info_resultado .= "<p>Alta humedad ambiental. Usa atomizador con moderación una vez al día y asegúrate de que haya buena ventilación para evitar hongos.</p>";
        } else {
            $info_resultado .= "<p>Clima húmedo: El agua se retiene más tiempo en la maceta. Riega solo cuando notes que los primeros centímetros de tierra estén secos.</p>";
        }
    } else { // Templado o Frío
        if ($etapa == "semilla") {
            $info_resultado .= "<p>Mantén el sustrato húmedo cubriendo levemente con plástico transparente para retener la temperatura templada/fresca.</p>";
        } else {
            $info_resultado .= "<p>Riego moderado cada 3 o 4 días. Monitorea los sensores o comprueba introduciendo un dedo en la tierra antes de aportar más agua.</p>";
        }
    }

    // Cuidados Generales de Luz y Temperatura
    $info_resultado .= "<h4 style='margin-top: 15px;'>☀️ Luz y Clima:</h4>";
    if ($clima == "seco") {
        $info_resultado .= "<p>Protégete de sol directo extremo en horas pico de la tarde, o asegúrate de que reciba luz indirecta muy brillante.</p>";
    } elseif ($clima == "frio") {
        $info_resultado .= "<p>Protege la planta de corrientes de aire helado y heladas nocturnas; ubícala cerca de una ventana muy iluminada.</p>";
    } else {
        $info_resultado .= "<p>Coloca la planta en un lugar donde reciba entre 5 y 6 horas de luz solar indirecta o filtrada al día.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPlant - Cuidados y Riego Inteligente</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <nav class="menu">
        <div class="logo">🌱 MyPlant</div>
        <ul class="enlaces">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="#mis-plantas">Mis Plantas</a></li>
            <li><a href="calificanos.php" target="_blank">Califícanos</a></li>
            <?php if(isset($_SESSION['username'])): ?>
                <li><a href="#">Hola, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                <li><a href="login.php?logout=true" style="color: #ffcccc;">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="login.php" target="_blank">Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="contenedor-principal">
        
        <!-- PANEL FORMULARIO -->
        <section class="panel-formulario">
            <h2>Registra tu Planta</h2>
            <p>Ingresa los datos para conocer sus necesidades de agua según su clima.</p>
            <?php if(!isset($_SESSION['usuario_id'])): ?>
                <p style="font-size: 0.85rem; color: #d35400; background: #fdebd0; padding: 8px; border-radius: 4px;">💡 <a href="login.php" target="_blank" style="color: #d35400; font-weight: bold;">Inicia sesión</a> para guardar tus plantas en tu cuenta personal.</p>
            <?php endif; ?>
            
            <form action="index.php" method="POST">
                <label for="nombre">Nombre de la planta:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej. Rábano, Tomate, Suculenta..." required>

                <label for="etapa">Etapa de crecimiento:</label>
                <select id="etapa" name="etapa" required>
                    <option value="" disabled selected>Selecciona una opción</option>
                    <option value="semilla">Semilla / Germinación</option>
                    <option value="brote">Brote en maceta</option>
                    <option value="crecimiento">Crecimiento vegetativo</option>
                    <option value="madura">Planta Madura / Cosecha</option>
                </select>

                <label for="clima">Tipo de clima de tu zona:</label>
                <select id="clima" name="clima" required>
                    <option value="" disabled selected>Selecciona tu clima</option>
                    <option value="seco">Seco / Desértico (Calor y baja humedad)</option>
                    <option value="templado">Templado (Clima moderado / Estacional)</option>
                    <option value="tropical">Tropical (Húmedo y cálido)</option>
                    <option value="frio">Frío de montaña / Invernal</option>
                </select>

                <button type="submit" name="consultar" class="btn-consultar">Consultar Cuidados</button>
            </form>
        </section>

        <!-- PANEL RESULTADOS -->
        <section class="panel-resultados">
            <h2>Resultados de Cuidados</h2>
            <div class="caja-informacion">
                <?php 
                if(!empty($info_resultado)) {
                    echo $info_resultado;
                } else {
                    echo "<p class='mensaje-vacio'>Llena el formulario con el clima y la etapa para ver la información detallada de riego aquí.</p>";
                }
                ?>
            </div>
        </section>
    </div>

    <div style="max-width: 1100px; margin: 0 auto; padding: 0 20px;" id="mis-plantas">
        <section class="seccion-mis-plantas">
            <h2>🌿 Mis Plantas Registradas</h2>
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <?php
                $stmt_p = $pdo->prepare("SELECT * FROM plantas WHERE usuario_id = ?");
                $stmt_p->execute([$_SESSION['usuario_id']]);
                $mis_plantas = $stmt_p->fetchAll(PDO::FETCH_ASSOC);
                if(count($mis_plantas) > 0):
                ?>
                    <ul style="list-style-type: none; margin-top: 15px;">
                        <?php foreach($mis_plantas as $p): ?>
                            <li style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                                <span>🌱 <strong><?php echo htmlspecialchars($p['nombre']); ?></strong></span>
                                <span style="color: #666;">Etapa: <?php echo ucfirst($p['etapa']); ?> | Clima: <?php echo ucfirst($p['clima']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="margin-top: 10px;">Aún no has registrado ninguna planta con tu cuenta.</p>
                <?php endif; ?>
            <?php else: ?>
                <p style="margin-top: 10px;">Debes <a href="login.php" target="_blank">iniciar sesión</a> para ver las plantas asociadas a tu cuenta.</p>
            <?php endif; ?>
        </section>
    </div>

  
    <footer class="footer-fuente">
        <p>Los parámetros de riego y cuidado de este portal están basados en las guías de agricultura urbana de la 
           <a href="https://www.fao.org/horticulture/es/" target="_blank">FAO (Organización de las Naciones Unidas para la Alimentación y la Agricultura)</a> 
           y manuales de horticultura general.
        </p>
    </footer>

</body>
</html>