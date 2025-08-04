<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SERAMER-SYS</title>
    <!-- Remix Icon CSS -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Google Fonts - Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-purple': '#837aff',
                        'primary-dark': '#6f62ff',
                        'background-light': '#f4f6f8',
                    },
                    fontFamily: {
                        'montserrat': ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        .scroll-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background-color: theme('colors.primary-purple');
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 9999px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
            cursor: pointer;
            display: none; /* Initially hidden */
        }
        .scroll-to-top:hover {
            background-color: theme('colors.primary-dark');
            transform: translateY(-3px);
        }
        
        /* Estilos para el bloque de código PHP */
        .php-code-container {
            background-color: #2d2d2d;
            color: #f8f8f2;
            border-radius: 0.5rem;
            padding: 1.5rem;
            overflow-x: auto;
            position: relative;
        }
        .php-code-container::before {
            content: 'PHP';
            position: absolute;
            top: 0.5rem;
            right: 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #f8f8f2;
            opacity: 0.5;
        }
        .php-code-container .alert {
            font-family: 'Montserrat', sans-serif;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
        }
        .php-code-container .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
        }
        .php-code-container .alert .ri-alert-line {
            font-size: 1.5rem;
            color: #837aff;
            background-color: transparent;
            padding: 0;
            border-radius: 0;
            margin-right: 0.5rem;
        }
        .php-code-container pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="bg-background-light font-montserrat text-gray-800">

    <!-- Navegación -->
    <header class="sticky top-0 z-50 shadow-md">
        <nav class="bg-primary-purple p-4">
            <div class="container mx-auto flex justify-between items-center">
                <a href="#" class="text-2xl font-bold text-white">Documentación Técnica</a>
                <div class="hidden md:flex space-x-6">
                    <a href="#php" class="text-white hover:text-gray-200 transition-colors">PHP</a>
                    <a href="#mysql" class="text-white hover:text-gray-200 transition-colors">MySQL</a>
                    <a href="#python" class="text-white hover:text-gray-200 transition-colors">Python</a>
                    <a href="#javascript" class="text-white hover:text-gray-200 transition-colors">JavaScript</a>
                    <a href="#code" class="text-white hover:text-gray-200 transition-colors">Código PHP</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mx-auto px-4 py-8">
        <!-- Hero Section -->
        <section class="bg-primary-purple text-white p-12 rounded-xl shadow-lg text-center mb-8">
            <h1 class="text-5xl font-bold mb-2">S E R A M E R - S Y S</h1>
            <h5 class="text-xl font-light opacity-80 mb-4">Documentación Técnica y Arquitectura del Proyecto</h5>
            <p class="max-w-3xl mx-auto text-lg">
                Un proyecto integral para la digitalización y optimización de la gestión del Mercado Municipal de Carúpano, Estado Sucre, desarrollado por la **UPTP Luis Mariano Rivera**.
            </p>
        </section>

        <!-- Secciones de Documentación -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Sección PHP -->
            <section id="php" class="bg-white p-8 rounded-xl shadow-lg transition-transform duration-300 hover:scale-[1.02] flex items-start">
                <div class="p-4 bg-primary-purple text-white rounded-full flex-shrink-0 mr-6">
                    <i class="ri-code-s-slash-line text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-semibold text-primary-purple mb-4">Backend con PHP</h3>
                    <p class="mb-4 text-gray-700">El núcleo del sistema está construido sobre una arquitectura **ligera pero robusta en PHP 8.x**, siguiendo el patrón de diseño **Modelo-Vista-Controlador (MVC)**. Esto asegura una clara separación de responsabilidades, facilitando el mantenimiento y la escalabilidad del proyecto.</p>
                    
                    <h4 class="text-xl font-medium mt-6 mb-2">Manejo de Sesiones y Autenticación</h4>
                    <p class="text-gray-600">La seguridad es primordial. El sistema de autenticación utiliza sesiones de PHP para controlar el acceso.</p>
                    
                    <h4 class="text-xl font-medium mt-6 mb-2">Estructura de Directorios</h4>
                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                        <li><strong>`app/`</strong>: Lógica de negocio (Modelos), controladores y vistas.</li>
                        <li><strong>`public/`</strong>: Punto de entrada (index.php), assets estáticos (CSS, JS).</li>
                        <li><strong>`config/`</strong>: Archivos de configuración de la aplicación.</li>
                    </ul>
                </div>
            </section>

            <!-- Sección MySQL -->
            <section id="mysql" class="bg-white p-8 rounded-xl shadow-lg transition-transform duration-300 hover:scale-[1.02] flex items-start">
                <div class="p-4 bg-primary-purple text-white rounded-full flex-shrink-0 mr-6">
                    <i class="ri-database-2-line text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-semibold text-primary-purple mb-4">Base de Datos MySQL</h3>
                    <p class="mb-4 text-gray-700">La información del mercado se almacena en una base de datos **MySQL**, diseñada para ser eficiente y relacional. La estructura de las tablas refleja la jerarquía y las relaciones entre entidades como empleados, adjudicatarios y las infracciones que se cometen.</p>
                    
                    <h4 class="text-xl font-medium mt-6 mb-2">Esquema de Tablas Clave</h4>
                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                        <li><strong>`empleados`</strong>: Almacena la información de los usuarios del sistema, incluyendo sus roles.</li>
                        <li><strong>`adjudicatarios`</strong>: Contiene los datos de los dueños de los puestos de mercado.</li>
                        <li><strong>`infracciones`</strong>: Registra cada infracción, su tipo, fecha, descripción y el adjudicatario asociado.</li>
                    </ul>
                </div>
            </section>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
            <!-- Sección Python -->
            <section id="python" class="bg-white p-8 rounded-xl shadow-lg transition-transform duration-300 hover:scale-[1.02] flex items-start">
                <div class="p-4 bg-primary-purple text-white rounded-full flex-shrink-0 mr-6">
                    <i class="ri-leaf-line text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-semibold text-primary-purple mb-4">Scripts Administrativos con Python</h3>
                    <p class="mb-4 text-gray-700">Se utilizan scripts en **Python** para tareas de automatización y análisis de datos. Estos scripts complementan la funcionalidad del backend de PHP, permitiendo manejar procesos complejos fuera del ciclo de la solicitud web.</p>
                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                        <li>Generación de reportes semanales o mensuales en formato CSV o PDF.</li>
                        <li>Análisis predictivo de tendencias de infracciones.</li>
                        <li>Automatización de copias de seguridad de la base de datos.</li>
                    </ul>
                </div>
            </section>
            
            <!-- Sección JavaScript -->
            <section id="javascript" class="bg-white p-8 rounded-xl shadow-lg transition-transform duration-300 hover:scale-[1.02] flex items-start">
                <div class="p-4 bg-primary-purple text-white rounded-full flex-shrink-0 mr-6">
                    <i class="ri-bar-chart-line text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-semibold text-primary-purple mb-4">Frontend con JavaScript Vanilla</h3>
                    <p class="mb-4 text-gray-700">El frontend se gestiona con **JavaScript Vanilla** para garantizar un rendimiento óptimo sin dependencias pesadas. Se encarga de la interactividad del usuario, la validación de formularios en el cliente y la visualización dinámica de datos.</p>
                    
                    <h4 class="text-xl font-medium mt-6 mb-2">Visualización de Datos con Chart.js</h4>
                    <p class="text-gray-600">Para la visualización del dashboard, se integra la librería **Chart.js**. El código JS se encarga de obtener datos de la API de PHP y de renderizar los gráficos de barras, dona y línea.</p>
                </div>
            </section>
        </div>

        <!-- Ejemplo de Código PHP -->
        <section id="code" class="mt-12">
            <h2 class="text-4xl font-bold text-gray-800 mb-6 text-center">Ejemplo de Código PHP</h2>
            <div class="php-code-container shadow-lg">
                <pre>
                    <code>
&lt;?php if (!$has_staff_data): ?&gt;
    &lt;div class="alert alert-warning" role="alert"&gt;
        &lt;i class="ri-alert-line me-2"&gt;&lt;/i&gt;
        &lt;strong&gt;Información Incompleta:&lt;/strong&gt; Este usuario no tiene datos de personal completos asociados. 
        Se muestra la información disponible del sistema de usuarios.
    &lt;/div&gt;
&lt;?php endif; ?&gt;
&lt;div class="grid grid-cols-1 md:grid-cols-2 gap-8"&gt;
    &lt;!-- Información Principal --&gt;
    &lt;div class="bg-white p-6 rounded-xl shadow-lg"&gt;
        &lt;div class="flex flex-col items-center mb-6"&gt;
            &lt;img class="w-24 h-24 rounded-full mb-3" 
                 src="&lt;?php echo '../../public/assets/img/avatars/1.png'; ?&gt;" 
                 alt="User avatar"&gt;
            &lt;div class="text-center"&gt;
                &lt;h4 class="text-xl font-semibold mb-1"&gt;
                    &lt;?php 
                    if (!empty($user['first_name']) && !empty($user['last_name'])) {
                        echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                    } else {
                        echo htmlspecialchars($user['username']);
                    }
                    ?&gt;
                &lt;/h4&gt;
                &lt;span class="bg-&lt;?php echo ($user['status'] == 'active') ? 'green-500' : 'red-500'; ?&gt; text-white text-xs font-bold px-2 py-1 rounded-full"&gt;
                    &lt;?php echo ($user['status'] == 'active') ? 'Activo' : 'Inactivo'; ?&gt;
                &lt;/span&gt;
            &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;div class="border-b border-gray-200 pb-4 mb-4"&gt;
            &lt;h5 class="text-lg font-semibold"&gt;Datos de Contacto&lt;/h5&gt;
            &lt;ul class="mt-2 space-y-2 text-sm text-gray-600"&gt;
                &lt;li&gt;&lt;span class="font-medium"&gt;Email:&lt;/span&gt; &lt;span&gt;&lt;?php echo htmlspecialchars($user['email']); ?&gt;&lt;/span&gt;&lt;/li&gt;
                &lt;li&gt;&lt;span class="font-medium"&gt;Último Login:&lt;/span&gt; &lt;span&gt;&lt;?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca'; ?&gt;&lt;/span&gt;&lt;/li&gt;
                &lt;li&gt;&lt;span class="font-medium"&gt;Fecha de Creación:&lt;/span&gt; &lt;span&gt;&lt;?php echo date('d/m/Y', strtotime($user['created_at'])); ?&gt;&lt;/span&gt;&lt;/li&gt;
            &lt;/ul&gt;
        &lt;/div&gt;
        
        &lt;div class="flex justify-center space-x-4 mt-4"&gt;
            &lt;a href="edit.php?id=&lt;?php echo $user['id']; ?&gt;" class="bg-primary-purple text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors"&gt;
                &lt;i class="ri-edit-line mr-1"&gt;&lt;/i&gt;Editar
            &lt;/a&gt;
            &lt;a href="index.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"&gt;
                &lt;i class="ri-arrow-left-line mr-1"&gt;&lt;/i&gt;Volver
            &lt;/a&gt;
        &lt;/div&gt;
    &lt;/div&gt;

    &lt;!-- Información Detallada --&gt;
    &lt;div class="bg-white p-6 rounded-xl shadow-lg"&gt;
        &lt;h5 class="text-xl font-semibold mb-4"&gt;Información Personal&lt;/h5&gt;
        &lt;div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-sm text-gray-600"&gt;
            &lt;dl&gt;
                &lt;div class="flex mb-2"&gt;
                    &lt;dt class="font-medium w-36"&gt;Nombre:&lt;/dt&gt;
                    &lt;dd class="flex-1"&gt;
                        &lt;?php 
                        if (!empty($user['first_name']) && !empty($user['last_name'])) {
                            echo htmlspecialchars($user['first_name'] . ' ' . 
                                (($user['middle_name'] ?? '') ? $user['middle_name'] . ' ' : '') . 
                                $user['last_name'] . 
                                (($user['second_last_name'] ?? '') ? ' ' . $user['second_last_name'] : ''));
                        } else {
                            echo '&lt;em class="text-muted"&gt;Datos de personal no disponibles&lt;/em&gt;';
                        }
                        ?&gt;
                    &lt;/dd&gt;
                &lt;/div&gt;
                &lt;div class="flex mb-2"&gt;
                    &lt;dt class="font-medium w-36"&gt;Cédula:&lt;/dt&gt;
                    &lt;dd class="flex-1"&gt;
                        &lt;?php echo !empty($user['id_number']) ? htmlspecialchars($user['id_number']) : '&lt;em class="text-muted"&gt;No registrada&lt;/em&gt;'; ?&gt;
                    &lt;/dd&gt;
                &lt;/div&gt;
                &lt;div class="flex mb-2"&gt;
                    &lt;dt class="font-medium w-36"&gt;Fecha de Nac.:&lt;/dt&gt;
                    &lt;dd class="flex-1"&gt;
                        &lt;?php echo !empty($user['birth_date']) ? date('d/m/Y', strtotime($user['birth_date'])) : '&lt;em class="text-muted"&gt;No registrada&lt;/em&gt;'; ?&gt;
                    &lt;/dd&gt;
                &lt;/div&gt;
                &lt;div class="flex mb-2"&gt;
                    &lt;dt class="font-medium w-36"&gt;Género:&lt;/dt&gt;
                    &lt;dd class="flex-1"&gt;
                        &lt;?php 
                        $gender = $user['gender'] ?? null;
                        if ($gender === null || $gender === '') {
                            echo '&lt;em class="text-muted"&gt;No especificado&lt;/em&gt;';
                        } elseif ($gender == 1) {
                            echo 'Femenino';
                        } else {
                            echo 'Masculino';
                        }
                        ?&gt;
                    &lt;/dd&gt;
                &lt;/div&gt;
            &lt;/dl&gt;
            &lt;dl&gt;
                &lt;div class="flex mb-2"&gt;
                    &lt;dt class="font-medium w-36"&gt;Fecha de Ingreso:&lt;/dt&gt;
                    &lt;dd class="flex-1"&gt;
                        &lt;?php echo !empty($user['hire_date']) ? date('d/m/Y', strtotime($user['hire_date'])) : '&lt;em class="text-muted"&gt;No registrada&lt;/em&gt;'; ?&gt;
                    &lt;/dd&gt;
                &lt;/div&gt;
                &lt;div class="flex mb-2"&gt;
                    &lt;dt class="font-medium w-36"&gt;Estado del Personal:&lt;/dt&gt;
                    &lt;dd class="flex-1"&gt;
                        &lt;span class="bg-&lt;?php echo ($user['status'] == 'active') ? 'green-500' : 'yellow-500'; ?&gt; text-white text-xs font-bold px-2 py-1 rounded-full"&gt;
                            &lt;?php echo ucfirst($user['status'] ?? 'desconocido'); ?&gt;
                        &lt;/span&gt;
                    &lt;/dd&gt;
                &lt;/div&gt;
                &lt;div class="flex mb-2"&gt;
                    &lt;dt class="font-medium w-36"&gt;Grado Académico:&lt;/dt&gt;
                    &lt;dd class="flex-1"&gt;
                        &lt;?php echo !empty($user['academic_degree_name']) ? htmlspecialchars($user['academic_degree_name']) : '&lt;em class="text-muted"&gt;No registrado&lt;/em&gt;'; ?&gt;
                    &lt;/dd&gt;
                &lt;/div&gt;
                &lt;div class="flex mb-2"&gt;
                    &lt;dt class="font-medium w-36"&gt;Especialización:&lt;/dt&gt;
                    &lt;dd class="flex-1"&gt;
                        &lt;?php echo !empty($user['academic_specialization_name']) ? htmlspecialchars($user['academic_specialization_name']) : '&lt;em class="text-muted"&gt;No registrada&lt;/em&gt;'; ?&gt;
                    &lt;/dd&gt;
                &lt;/div&gt;
            &lt;/dl&gt;
        &lt;/div&gt;
    &lt;/div&gt;
&lt;/div&gt;
                    </code>
                </pre>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-primary-dark text-white p-8 rounded-xl mx-4 mb-4 shadow-lg">
        <div class="container mx-auto text-center">
            <div class="md:flex justify-between items-center mb-4">
                <div class="text-center md:text-left">
                    <h5 class="text-xl font-bold">Sistema de Gestión del Mercado</h5>
                    <p class="text-gray-300 mt-2">Un proyecto de la UPTP Luis Mariano Rivera para el Mercado Municipal de Carúpano, Estado Sucre.</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <h5 class="text-lg font-semibold mb-2">Enlaces</h5>
                    <ul class="space-y-1">
                        <li><a class="text-gray-300 hover:text-white transition-colors" href="#">Inicio</a></li>
                        <li><a class="text-gray-300 hover:text-white transition-colors" href="#php">PHP</a></li>
                        <li><a class="text-gray-300 hover:text-white transition-colors" href="#mysql">MySQL</a></li>
                        <li><a class="text-gray-300 hover:text-white transition-colors" href="#javascript">JavaScript</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-4 mt-4 text-white font-bold">
                <p>© 2024 UPTP Luis Mariano Rivera. Desarrollado por: Josibel Farias, Daniel Alfonsi y Jesus Natera</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button id="scroll-to-top-btn" class="scroll-to-top">
        <i class="ri-arrow-up-line text-xl"></i>
    </button>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Show/hide scroll-to-top button
        const scrollToTopBtn = document.getElementById('scroll-to-top-btn');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 200) {
                scrollToTopBtn.style.display = 'block';
            } else {
                scrollToTopBtn.style.display = 'none';
            }
        });
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
