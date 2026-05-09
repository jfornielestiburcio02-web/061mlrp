
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Empleados - 061 Málaga</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            /* Fondo con degradado profesional */
            background: linear-gradient(135deg, #d32f2f 0%, #222 100%);
            overflow: hidden;
        }

        /* Contenedor del Login */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card img {
            height: 80px;
            margin-bottom: 20px;
        }

        .login-card h2 {
            color: #333;
            margin-bottom: 5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-card p {
            color: #777;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        /* Inputs */
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #d32f2f;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: #d32f2f;
        }

        /* Botón */
        .btn-login {
            width: 100%;
            padding: 15px;
            background-color: #d32f2f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .btn-login:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
        }

        .footer-link {
            margin-top: 20px;
            font-size: 0.8rem;
        }

        .footer-link a {
            color: #d32f2f;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Logo -->
        <img src="/imagenes/061.png" alt="061 Málaga">
        
        <h2>061 MÁLAGA</h2>
        <p>Acceso exclusivo para Empleados</p>

        <!-- Formulario -->
        <form id="loginForm" method="POST">
            <div class="input-group">
                <label>IDENTIFICACIÓN (DNI/ID)</label>
                <input type="text" name="usuario" placeholder="Ej: 12345678X" required>
            </div>

            <div class="input-group">
                <label>CONTRASEÑA</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login">ENTRAR AL SISTEMA</button>
        </form>

        <div class="footer-link">
            <a href="#">¿Problemas para acceder? Contacte con Soporte</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').onsubmit = function(e) {
            e.preventDefault(); // Evita el envío normal para procesar la URL

            // Función para generar letras mayúsculas aleatorias (ej: 8 caracteres)
            function generarAleatorio(longitud) {
                const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                let resultado = '';
                for (let i = 0; i < longitud; i++) {
                    resultado += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
                }
                return resultado;
            }

            const valorAleatorio = generarAleatorio(10); // Genera 10 letras
            
            // Construimos la URL dinámica
            const urlDestino = "/ComprobarUsuario.php?ALEATORIO=" + valorAleatorio;
            
            // Asignamos la URL al action del formulario y lo enviamos
            this.action = urlDestino;
            this.submit();
        };
    </script>

</body>
</html>
