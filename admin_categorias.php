<?php

//CloudMR 2025

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = conectarDB();

$mensaje = "";
$categorias = [];


$sql_categorias = "SELECT id, nombre_categoria, descripcion FROM categorias ORDER BY nombre_categoria ASC";
$result_categorias = $conn->query($sql_categorias);
if ($result_categorias && $result_categorias->num_rows > 0) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_categoria'])) {
    $nombre_categoria = $_POST['nombre_categoria'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    if (!empty($nombre_categoria)) {
        $stmt_check = $conn->prepare("SELECT id FROM categorias WHERE nombre_categoria = ?");
        $stmt_check->bind_param("s", $nombre_categoria);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $mensaje = "<div class='alert alert-danger'>La categoría ya existe.</div>";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)");
            $stmt_insert->bind_param("ss", $nombre_categoria, $descripcion);

            if ($stmt_insert->execute()) {
                $mensaje = "<div class='alert alert-success'>Categoría creada exitosamente.</div>";
         
                $categorias = [];
                $result_categorias = $conn->query($sql_categorias);
                if ($result_categorias && $result_categorias->num_rows > 0) {
                    while ($row = $result_categorias->fetch_assoc()) {
                        $categorias[] = $row;
                    }
                }
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al crear la categoría. Por favor, inténtelo de nuevo.</div>";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>El nombre de la categoría es obligatorio.</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_categoria'])) {
    $id_categoria_editar = $_POST['id_categoria_editar'] ?? 0;
    $nombre_categoria_editar = $_POST['nombre_categoria_editar'] ?? '';
    $descripcion_editar = $_POST['descripcion_editar'] ?? '';

    if ($id_categoria_editar > 0 && !empty($nombre_categoria_editar)) {
        $stmt_update = $conn->prepare("UPDATE categorias SET nombre_categoria = ?, descripcion = ? WHERE id = ?");
        $stmt_update->bind_param("ssi", $nombre_categoria_editar, $descripcion_editar, $id_categoria_editar);

        if ($stmt_update->execute()) {
            $mensaje = "<div class='alert alert-success'>Categoría actualizada exitosamente.</div>";
           
            $categorias = [];
            $result_categorias = $conn->query($sql_categorias);
            if ($result_categorias && $result_categorias->num_rows > 0) {
                while ($row = $result_categorias->fetch_assoc()) {
                    $categorias[] = $row;
                }
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al actualizar la categoría. Por favor, inténtelo de nuevo.</div>";
        }
        $stmt_update->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>Por favor, complete todos los campos para actualizar la categoría.</div>";
    }
}

if (isset($_GET['eliminar'])) {
    $id_categoria_eliminar = $_GET['eliminar'];
    if (is_numeric($id_categoria_eliminar) && $id_categoria_eliminar > 0) {
      
        $stmt_check_productos = $conn->prepare("SELECT id FROM productos WHERE id_categoria = ?");
        $stmt_check_productos->bind_param("i", $id_categoria_eliminar);
        $stmt_check_productos->execute();
        $result_check_productos = $stmt_check_productos->get_result();

        if ($result_check_productos->num_rows > 0) {
            $mensaje = "<div class='alert alert-danger'>No se puede eliminar la categoría porque está asociada a uno o más productos.</div>";
        } else {
            $stmt_delete = $conn->prepare("DELETE FROM categorias WHERE id = ?");
            $stmt_delete->bind_param("i", $id_categoria_eliminar);

            if ($stmt_delete->execute()) {
                $mensaje = "<div class='alert alert-success'>Categoría eliminada exitosamente.</div>";
                
                $categorias = [];
                $result_categorias = $conn->query($sql_categorias);
                if ($result_categorias && $result_categorias->num_rows > 0) {
                    while ($row = $result_categorias->fetch_assoc()) {
                        $categorias[] = $row;
                    }
                }
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al eliminar la categoría. Por favor, inténtelo de nuevo.</div>";
            }
            $stmt_delete->close();
        }
        $stmt_check_productos->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>ID de categoría inválido para eliminar.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Categorías</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .content-wrapper {
            padding: 20px;
        }
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .form-container h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 25px;
            color: #343a40;
        }
        .form-group label {
            font-weight: bold;
            color: #495057;
        }
        .alert {
            margin-top: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-sm {
            margin-right: 5px;
        }
        .modal-title {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <div class="text-center mt-3">
                        <img src="imagenes/logo_abarrotes.png" alt="Logo" class="img-fluid mb-3" style="max-width: 150px;">
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admon_panel_central.php">
                                <i class="fas fa-tachometer-alt"></i> Panel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_modif_user.php">
                                <i class="fas fa-users"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_categorias.php">
                                <i class="fas fa-tags"></i> Categorías
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_productos.php">
                                <i class="fas fa-box-open"></i> Productos
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link btn btn-danger btn-sm" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-tags mr-2"></i> Administrar Categorías</h1>
                </div>

                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-plus-circle mr-2"></i> Crear Nueva Categoría</h2>
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="nombre_categoria"><i class="fas fa-tag mr-2"></i> Nombre de Categoría:</label>
                                        <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="descripcion"><i class="fas fa-file-alt mr-2"></i> Descripción (opcional):</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block" name="crear_categoria"><i class="fas fa-save mr-2"></i> Crear Categoría</button>
                                </form>
                                <?php echo $mensaje; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-container">
                                <h2 class="text-center"><i class="fas fa-list-alt mr-2"></i> Categorías Existentes</h2>
                                <?php if (empty($categorias)): ?>
                                    <p>No hay categorías creadas aún.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Descripción</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($categorias as $categoria): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></td>
                                                        <td><?php echo htmlspecialchars($categoria['descripcion']); ?></td>
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-success btn-editar-categoria"
                                                                    data-toggle="modal"
                                                                    data-target="#editarCategoriaModal"
                                                                    data-id="<?php echo htmlspecialchars($categoria['id']); ?>"
                                                                    data-nombre="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>"
                                                                    data-descripcion="<?php echo htmlspecialchars($categoria['descripcion']); ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <a href="?eliminar=<?php echo htmlspecialchars($categoria['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoría?')">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="editarCategoriaModal" tabindex="-1" aria-labelledby="editarCategoriaModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editarCategoriaModalLabel"><i class="fas fa-edit mr-2"></i> Editar Categoría</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <input type="hidden" id="id_categoria_editar" name="id_categoria_editar">
                                    <div class="form-group">
                                        <label for="nombre_categoria_editar"><i class="fas fa-tag mr-2"></i> Nombre de Categoría:</label>
                                        <input type="text" class="form-control" id="nombre_categoria_editar" name="nombre_categoria_editar" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="descripcion_editar"><i class="fas fa-file-alt mr-2"></i> Descripción (opcional):</label>
                                        <textarea class="form-control" id="descripcion_editar" name="descripcion_editar" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-2"></i> Cancelar</button>
                                    <button type="submit" class="btn btn-success" name="actualizar_categoria"><i class="fas fa-save mr-2"></i> Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#editarCategoriaModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); 
                var id = button.data('id');
                var nombre = button.data('nombre');
                var descripcion = button.data('descripcion');

                $('#id_categoria_editar').val(id);
                $('#nombre_categoria_editar').val(nombre);
                $('#descripcion_editar').val(descripcion);
            });
        });
    </script>
</body>
</html>