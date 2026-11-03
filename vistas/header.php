<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Empleados | SERVIR</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .nav-link {
            font-weight: 500;
            transition: all 0.2s ease;
            color: #cbd5e1 !important;
            padding: 0.5rem 1rem !important;
            border-radius: 6px;
        }
        .nav-link:hover, .nav-link.active {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .btn-primary-custom {
            background-color: #2563eb;
            border: none;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
        }
        .btn-primary-custom:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>

    <!-- Navbar Principal -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
                <span class="tracking-tight">SERVIR</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fa-solid fa-house me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="departamento.php">
                            <i class="fa-solid fa-building me-1"></i> Departamentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="empleado.php">
                            <i class="fa-solid fa-users me-1"></i> Empleados
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="flex-grow-1 py-5">