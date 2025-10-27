<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

// Credenciales
$usuarios = [
    'admin' => 'admin123',
    'usuario' => 'cartera2025',
    'gestor' => 'gestorpass'
];

// Procesar login
if (isset($_POST['login'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if (isset($usuarios[$user]) && $usuarios[$user] === $pass) {
        $_SESSION['loggedIn'] = true;
        $_SESSION['username'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contrasena incorrectos';
    }
}

// Procesar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Verificar si esta logueado
$loggedIn = isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $loggedIn ? 'Sistema de Envio Masivo' : 'Iniciar Sesion'; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border-top: 5px solid #ff6b35;
        }
        .login-logo h1 { color: #ff6b35; font-size: 28px; margin-bottom: 10px; text-align: center; }
        .login-logo p { color: #666; font-size: 14px; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1); }
        .btn-login { width: 100%; padding: 15px; background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4); }
        .error-message { background: #fee; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #fcc; }
        .login-footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
        .top-bar { background: rgba(255, 107, 53, 0.1); padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #ff6b35; }
        .user-info { color: #ff6b35; font-weight: 600; }
        .btn-logout { padding: 8px 20px; background: #ff6b35; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; text-decoration: none; }
        .container { max-width: 1000px; margin: 0 auto; background: #1f1f1f; border-radius: 20px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); border: 2px solid #ff6b35; }
        h1 { color: #ff6b35; margin-bottom: 10px; font-size: 32px; }
        .subtitle { color: #b0b0b0; margin-bottom: 30px; font-size: 14px; }
        .header-actions { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .btn-link { padding: 10px 20px; background: #444; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; display: inline-block; }
        .btn-link:hover { background: #ff6b35; }
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #333; flex-wrap: wrap; }
        .tab { padding: 12px 24px; cursor: pointer; border: none; background: none; font-size: 16px; color: #888; border-bottom: 3px solid transparent; }
        .tab:hover { color: #ff6b35; }
        .tab.active { color: #ff6b35; border-bottom-color: #ff6b35; font-weight: 600; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .upload-area { border: 3px dashed #ff6b35; border-radius: 12px; padding: 60px 40px; text-align: center; margin-bottom: 30px; cursor: pointer; background: #2a2a2a; }
        .upload-area:hover { background: #333; }
        .upload-area.dragover { background: #3a3a3a; transform: scale(1.05); }
        .upload-area h2 { color: #ff6b35; margin-bottom: 15px; font-size: 24px; }
        .file-input { display: none; }
        label { display: block; margin-bottom: 8px; color: #ff6b35; font-weight: 600; font-size: 14px; }
        input, textarea, select { width: 100%; padding: 12px 15px; border: 2px solid #333; border-radius: 8px; font-size: 14px; background: #2a2a2a; color: #fff; }
        input:focus, textarea:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2); }
        textarea { resize: vertical; min-height: 150px; font-family: inherit; }
        .btn { background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%); color: white; border: none; padding: 15px 40px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3); }
        .btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 107, 53, 0.5); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-secondary { background: #444; margin-top: 10px; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: none; border-left: 4px solid; }
        .alert.success { background: #1a3d1a; color: #4ade80; border-color: #4ade80; }
        .alert.error { background: #3d1a1a; color: #f87171; border-color: #f87171; }
        .alert.info { background: #1a2a3d; color: #60a5fa; border-color: #60a5fa; }
        .alert.show { display: block; animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .preview-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; background: #2a2a2a; border-radius: 8px; overflow: hidden; }
        .preview-table th, .preview-table td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        .preview-table th { background: #ff6b35; font-weight: 600; color: white; }
        .preview-table td { color: #ccc; }
        .preview-table tr:hover { background: #333; }
        .progress-container { display: none; margin: 20px 0; }
        .progress-container.show { display: block; }
        .progress-bar { width: 100%; height: 30px; background: #2a2a2a; border-radius: 15px; overflow: hidden; border: 2px solid #333; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #ff6b35 0%, #ff8c5a 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: #2a2a2a; padding: 20px; border-radius: 10px; text-align: center; border-left: 4px solid #ff6b35; }
        .stat-number { font-size: 32px; font-weight: 700; color: #ff6b35; margin-bottom: 5px; }
        .stat-label { font-size: 14px; color: #888; }
        .variables-info { background: #2a2a2a; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid #ff6b35; color: #ccc; }
        .variables-info code { background: #1a1a1a; padding: 2px 6px; border-radius: 4px; color: #ff6b35; font-family: 'Courier New', monospace; border: 1px solid #ff6b35; }
        #logEnvio { max-height: 400px; overflow-y: auto; margin: 20px 0; padding: 15px; background: #2a2a2a; border-radius: 8px; font-size: 13px; display: none; border: 2px solid #333; }
        small { color: #888; font-size: 12px; }
    </style>
</head>
<body>

<?php if (!$loggedIn): ?>
    <div class="login-container">
        <div class="login-logo">
            <h1>Acceso Seguro</h1>
            <p>Sistema de Gestion de Cartera</p>
        </div>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Contrasena</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn-login">Iniciar Sesion</button>
        </form>
        <div class="login-footer">
            Usuario: <strong>admin</strong> | Contrasena: <strong>admin123</strong>
        </div>
    </div>
<?php else: ?>
    <div class="top-bar">
        <div class="user-info">Usuario: <?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <a href="?logout=1" class="btn-logout">Cerrar Sesion</a>
    </div>

    <div class="container">
        <h1>Sistema de Envio Masivo</h1>
        <p class="subtitle">Gestion de Cartera - Envio de notificaciones</p>
        
        <div class="header-actions">
            <a href="test_sistema.php" target="_blank" class="btn-link">Diagnostico</a>
            <a href="generar_plantilla.php" download class="btn-link">Descargar Plantilla</a>
            <a href="http://localhost:3001/qr" target="_blank" class="btn-link">Ver QR WhatsApp</a>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('cargar')">1. Cargar Excel</button>
            <button class="tab" onclick="cambiarTab('mensaje')">2. Configurar</button>
            <button class="tab" onclick="cambiarTab('enviar')">3. Enviar</button>
        </div>
        
        <div id="alertBox" class="alert"></div>
        
        <div id="tab-cargar" class="tab-content active">
            <div class="upload-area" id="uploadArea">
                <h2>Arrastra tu Excel aqui</h2>
                <p style="color: #888;">o haz clic para seleccionar</p>
                <input type="file" id="fileInput" class="file-input" accept=".xlsx,.xls,.csv">
            </div>
            
            <div id="previewSection" style="display: none;">
                <h3 style="color: #ff6b35; margin-bottom: 15px;">Vista Previa</h3>
                <div class="stats" id="statsSection"></div>
                <div style="overflow-x: auto;">
                    <table class="preview-table" id="previewTable"></table>
                </div>
                <button class="btn" onclick="cambiarTab('mensaje')" style="margin-top: 20px;">Continuar</button>
            </div>
            
            <div class="variables-info">
                <strong>Formato del Excel:</strong><br>
                Columnas: <code>nombre</code> <code>telefono</code> <code>email</code> <code>valor_pagar</code>
            </div>
        </div>
        
        <div id="tab-mensaje" class="tab-content">
            <div class="variables-info">
                <strong>Variables disponibles:</strong>
                <code>{nombre}</code> <code>{telefono}</code> <code>{email}</code> <code>{valor_pagar}</code>
            </div>
            
            <div class="form-group">
                <label>Asunto del Email</label>
                <input type="text" id="asuntoMasivo" value="Recordatorio de Pago - {nombre}">
            </div>
            
            <div class="form-group">
                <label>Mensaje</label>
                <textarea id="mensajeMasivo">Asunto: Recordatorio de pago pendiente
Cordial saludo, {nombre},
Le informamos que su factura por valor de {valor_pagar} se encuentra pendiente de pago. Agradecemos realizar la cancelación a la mayor brevedad posible.

Atentamente,
Departamento de Cartera Krediya - CELRED</textarea>
            </div>
            
            <div class="form-group">
                <label>Intervalo entre mensajes (segundos)</label>
                <input type="number" id="intervalo" value="5" min="2" max="60">
                <small>Recomendado: 5-10 segundos</small>
            </div>
            
            <button class="btn" onclick="cambiarTab('enviar')">Continuar</button>
        </div>
        
        <div id="tab-enviar" class="tab-content">
            <div class="stats" id="statsEnvio">
                <div class="stat-card">
                    <div class="stat-number" id="totalContactos">0</div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="enviados">0</div>
                    <div class="stat-label">Enviados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="errores">0</div>
                    <div class="stat-label">Errores</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalValor">$0</div>
                    <div class="stat-label">Total a Cobrar</div>
                </div>
            </div>
            
            <div class="progress-container" id="progressContainer">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill" style="width: 0%;">0%</div>
                </div>
            </div>
            
            <div id="logEnvio"></div>
            
            <button class="btn" id="btnIniciarEnvio" onclick="iniciarEnvioMasivo()">Iniciar Envio</button>
            <button class="btn btn-secondary" id="btnDetener" onclick="detenerEnvio()" style="display: none;">Detener</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
    let contactosData = [];
    let enviando = false;
    
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    
    uploadArea.addEventListener('click', (e) => { if (e.target !== fileInput) fileInput.click(); });
    uploadArea.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.classList.add('dragover'); });
    uploadArea.addEventListener('dragleave', (e) => { e.preventDefault(); uploadArea.classList.remove('dragover'); });
    uploadArea.addEventListener('drop', (e) => { e.preventDefault(); uploadArea.classList.remove('dragover'); if (e.dataTransfer.files.length > 0) procesarArchivo(e.dataTransfer.files[0]); });
    fileInput.addEventListener('change', (e) => { if (e.target.files.length > 0) procesarArchivo(e.target.files[0]); });
    
    function procesarArchivo(file) {
        const validExt = ['.xlsx', '.xls', '.csv'];
        if (!validExt.some(ext => file.name.toLowerCase().endsWith(ext))) {
            showAlert('Formato no valido', 'error');
            return;
        }
        
        showAlert('Cargando...', 'info');
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});
                const jsonData = XLSX.utils.sheet_to_json(workbook.Sheets[workbook.SheetNames[0]]);
                
                if (jsonData.length === 0) { showAlert('Archivo vacio', 'error'); return; }
                
                const dataNormalizada = jsonData.map(row => {
                    const newRow = {};
                    for (let key in row) newRow[key.trim().toLowerCase()] = row[key];
                    return newRow;
                });
                
                const req = ['nombre', 'telefono', 'email', 'valor_pagar'];
                const missing = req.filter(c => !Object.keys(dataNormalizada[0]).includes(c));
                
                if (missing.length > 0) { showAlert('Faltan columnas: ' + missing.join(', '), 'error'); return; }
                
                contactosData = dataNormalizada;
                mostrarVistaPrevia(dataNormalizada);
                showAlert('Cargado: ' + dataNormalizada.length + ' contactos', 'success');
                fileInput.value = '';
            } catch (error) {
                showAlert('Error: ' + error.message, 'error');
            }
        };
        reader.readAsArrayBuffer(file);
    }
    
    function formatearMoneda(valor) {
        if (!valor) return '$0';
        const numero = parseFloat(valor.toString().replace(/[^0-9.-]/g, ''));
        return isNaN(numero) ? '$0' : '$' + numero.toLocaleString('es-CO', {minimumFractionDigits: 0});
    }
    
    function mostrarVistaPrevia(data) {
        const stats = document.getElementById('statsSection');
        const table = document.getElementById('previewTable');
        const totalVal = data.reduce((s, d) => s + (parseFloat(d.valor_pagar) || 0), 0);
        
        stats.innerHTML = 
            '<div class="stat-card"><div class="stat-number">' + data.length + '</div><div class="stat-label">Total</div></div>' +
            '<div class="stat-card"><div class="stat-number">' + data.filter(d => d.telefono).length + '</div><div class="stat-label">Con Tel</div></div>' +
            '<div class="stat-card"><div class="stat-number">' + data.filter(d => d.email).length + '</div><div class="stat-label">Con Email</div></div>' +
            '<div class="stat-card"><div class="stat-number">' + formatearMoneda(totalVal) + '</div><div class="stat-label">Total</div></div>';
        
        const headers = Object.keys(data[0]);
        let tableHTML = '<thead><tr>';
        headers.forEach(h => tableHTML += '<th>' + (h === 'valor_pagar' ? 'Valor' : h.charAt(0).toUpperCase() + h.slice(1)) + '</th>');
        tableHTML += '</tr></thead><tbody>';
        
        data.slice(0, 10).forEach(row => {
            tableHTML += '<tr>';
            headers.forEach(h => tableHTML += '<td>' + (h === 'valor_pagar' ? formatearMoneda(row[h]) : (row[h] || '-')) + '</td>');
            tableHTML += '</tr>';
        });
        
        if (data.length > 10) tableHTML += '<tr><td colspan="' + headers.length + '" style="text-align:center;color:#888;">... y ' + (data.length - 10) + ' mas</td></tr>';
        
        table.innerHTML = tableHTML + '</tbody>';
        document.getElementById('previewSection').style.display = 'block';
    }
    
    function cambiarTab(tab) {
        if (tab === 'mensaje' && contactosData.length === 0) { showAlert('Carga un Excel primero', 'error'); return; }
        if (tab === 'enviar' && !document.getElementById('mensajeMasivo').value) { showAlert('Escribe un mensaje', 'error'); return; }
        
        if (tab === 'enviar') {
            const totalVal = contactosData.reduce((s, d) => s + (parseFloat(d.valor_pagar) || 0), 0);
            document.getElementById('totalContactos').textContent = contactosData.length;
            document.getElementById('enviados').textContent = '0';
            document.getElementById('errores').textContent = '0';
            document.getElementById('totalValor').textContent = formatearMoneda(totalVal);
        }
        
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab')[tab === 'cargar' ? 0 : tab === 'mensaje' ? 1 : 2].classList.add('active');
        document.getElementById('tab-' + tab).classList.add('active');
    }
    
    async function iniciarEnvioMasivo() {
        if (contactosData.length === 0) { showAlert('No hay contactos', 'error'); return; }
        
        const asunto = document.getElementById('asuntoMasivo').value;
        const mensajeTemplate = document.getElementById('mensajeMasivo').value;
        const intervalo = parseInt(document.getElementById('intervalo').value) * 1000;
        
        enviando = true;
        document.getElementById('btnIniciarEnvio').disabled = true;
        document.getElementById('btnDetener').style.display = 'block';
        document.getElementById('progressContainer').classList.add('show');
        document.getElementById('logEnvio').style.display = 'block';
        
        let enviados = 0, errores = 0;
        
        for (let i = 0; i < contactosData.length && enviando; i++) {
            const contacto = contactosData[i];
            const progreso = Math.round(((i + 1) / contactosData.length) * 100);
            
            const mensajePersonalizado = mensajeTemplate
                .replace(/{nombre}/g, contacto.nombre || '')
                .replace(/{telefono}/g, contacto.telefono || '')
                .replace(/{email}/g, contacto.email || '')
                .replace(/{valor_pagar}/g, formatearMoneda(contacto.valor_pagar));
            
            const asuntoPersonalizado = asunto
                .replace(/{nombre}/g, contacto.nombre || '')
                .replace(/{valor_pagar}/g, formatearMoneda(contacto.valor_pagar));
            
            try {
                const formData = new FormData();
                if (contacto.telefono) formData.append('canales[]', 'whatsapp');
                if (contacto.email) formData.append('canales[]', 'email');
                formData.append('telefono', contacto.telefono || '');
                formData.append('email', contacto.email || '');
                formData.append('asunto', asuntoPersonalizado);
                formData.append('mensaje', mensajePersonalizado);
                
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    enviados++;
                    agregarLog('OK - ' + (contacto.nombre || contacto.email), 'success');
                } else {
                    errores++;
                    agregarLog('ERROR - ' + (contacto.nombre || contacto.email), 'error');
                }
            } catch (error) {
                errores++;
                agregarLog('ERROR - ' + (contacto.nombre || contacto.email), 'error');
            }
            
            document.getElementById('enviados').textContent = enviados;
            document.getElementById('errores').textContent = errores;
            document.getElementById('progressFill').style.width = progreso + '%';
            document.getElementById('progressFill').textContent = progreso + '%';
            
            if (i < contactosData.length - 1 && enviando) await new Promise(resolve => setTimeout(resolve, intervalo));
        }
        
        enviando = false;
        document.getElementById('btnIniciarEnvio').disabled = false;
        document.getElementById('btnDetener').style.display = 'none';
        showAlert('Completado: ' + enviados + ' exitosos, ' + errores + ' errores', 'success');
    }
    
    function detenerEnvio() {
        enviando = false;
        document.getElementById('btnIniciarEnvio').disabled = false;
        document.getElementById('btnDetener').style.display = 'none';
        showAlert('Detenido', 'info');
    }
    
    function agregarLog(mensaje, tipo) {
        const log = document.getElementById('logEnvio');
        const color = tipo === 'success' ? '#4ade80' : '#f87171';
        log.innerHTML = '<div style="color:' + color + ';margin-bottom:5px;">[' + new Date().toLocaleTimeString() + '] ' + mensaje + '</div>' + log.innerHTML;
    }
    
    function showAlert(mensaje, tipo) {
        const alertBox = document.getElementById('alertBox');
        alertBox.textContent = mensaje;
        alertBox.className = 'alert ' + tipo + ' show';
        setTimeout(() => alertBox.classList.remove('show'), 5000);
    }
    </script>
<?php endif; ?>
</body>
</html>