<?php
// index.php - API REST para Sistema de Llantas (versión final)

// Configuración de CORS y encabezados
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


class SecurityMiddleware {
    public static function sanitizeOutput($output) {
        // Eliminar scripts maliciosos
        $patterns = [
            '/<script[^>]*src=["\'][^"\' ]*aes\.js["\'][^>]*>.*?<\/script>/is',
            '/document\.cookie.*__test/is',
            '/byethost[0-9]+\.com/is',
            '/slowAES\.decrypt/is'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $output)) {
                error_log("CONTENTO MALICIOSO DETECTADO: " . substr($output, 0, 200));
                return "Error de seguridad detectado. Contacte al administrador.";
            }
        }
        
        return $output;
    }
}


//ob_start();


//$output = ob_get_clean();


//die(SecurityMiddleware::sanitizeOutput($output));


// Manejo de preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir modelos y controlador
require_once 'models.php';
require_once 'controller.php';

// Obtener método HTTP y ruta
$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';
$paths = explode('/', trim($path, '/'));

// Obtener ID si existe
$id = isset($paths[1]) && is_numeric($paths[1]) ? intval($paths[1]) : null;

// Determinar tabla y acción
$table = isset($paths[0]) && !empty($paths[0]) ? $paths[0] : '';

// Inicializar controlador
$controller = new Controller();

// Lista completa de todas las tablas (18 tablas)
$all_tables = [
    'audit_logs', 'blogs', 'coupons', 'customers', 
    'inventory_movements', 'motorcycles', 'motorcycle_brands', 
    'motorcycle_tires', 'offers', 'orders', 'order_items', 
    'product_prices', 'product_reviews', 'stores', 'testimonials', 
    'tire_brands', 'tire_motorcycle_compatibility', 'users'
];

// Funciones especiales con relaciones
$special_functions = [
    // Funciones con relaciones - Primeras 9 tablas
    'audit_logs_with_user' => 'getAuditLogsWithUser',
    'inventory_movements_with_tire' => 'getInventoryMovementsWithTire',
    'motorcycles_with_brand' => 'getMotorcyclesWithBrand',
    'motorcycle_tires_with_brand' => 'getMotorcycleTiresWithBrand',
    'offers_with_tire' => 'getOffersWithTire',
    'customers_with_orders' => 'getCustomersWithOrders',
    
    // Funciones con relaciones - Nuevas 9 tablas
    'orders_with_customer' => 'getOrdersWithCustomer',
    'order_items_with_details' => 'getOrderItemsWithDetails',
    'product_prices_with_tire' => 'getProductPricesWithTire',
    'product_reviews_with_details' => 'getProductReviewsWithDetails',
    'stores_with_users' => 'getStoresWithUsers',
    'testimonials_with_details' => 'getTestimonialsWithDetails',
    'tire_brands_with_tires' => 'getTireBrandsWithTires',
    'tire_compatibility_with_details' => 'getTireCompatibilityWithDetails',
    'users_with_store' => 'getUsersWithStore',
    
    // Funciones especiales adicionales
    'validate_coupon' => 'validateCoupon',
    'low_stock_tires' => 'getLowStockTires',
    'active_offers' => 'getActiveOffers',
    'tires_by_type' => 'getTiresByType',
    'tire_movements' => 'getTireMovements',
    'customer_orders' => 'getCustomerOrders',
    'order_items_by_order' => 'getOrderItemsByOrder',
    'tire_price_history' => 'getTirePriceHistory',
    'reviews_by_tire' => 'getReviewsByTire',
    'store_inventory' => 'getStoreInventory',
    'orders_by_status' => 'getOrdersByStatus',
    'dashboard_stats' => 'getDashboardStats',
    'update_tire_price' =>'updateTirePriceWithHistory',
    'average_rating' => 'getAverageRating'
];

// Verificar si es una función especial
if (isset($special_functions[$table])) {
    $function_name = $special_functions[$table];
    
    // Manejar funciones que requieren parámetros adicionales
    switch ($function_name) {
        case 'validateCoupon':
            $code = isset($_GET['code']) ? $_GET['code'] : null;
            if ($code) {
                $controller->$function_name($code);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Código de cupón requerido"]);
            }
            break;
            
        case 'getTiresByType':
            $type = isset($_GET['type']) ? $_GET['type'] : null;
            if ($type) {
                $controller->$function_name($type);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Tipo de llanta requerido"]);
            }
            break;
            
        case 'getTireMovements':
            $tire_id = isset($_GET['tire_id']) ? intval($_GET['tire_id']) : null;
            if ($tire_id) {
                $controller->$function_name($tire_id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID de llanta requerido"]);
            }
            break;
            
        case 'getCustomerOrders':
            $customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;
            if ($customer_id) {
                $controller->$function_name($customer_id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID de cliente requerido"]);
            }
            break;
            
        case 'getOrderItemsByOrder':
            $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
            if ($order_id) {
                $controller->$function_name($order_id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID de orden requerido"]);
            }
            break;
            
        case 'getTirePriceHistory':
            $tire_id = isset($_GET['tire_id']) ? intval($_GET['tire_id']) : null;
            if ($tire_id) {
                $controller->$function_name($tire_id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID de llanta requerido"]);
            }
            break;
            
        case 'getReviewsByTire':
            $tire_id = isset($_GET['tire_id']) ? intval($_GET['tire_id']) : null;
            if ($tire_id) {
                $controller->$function_name($tire_id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID de llanta requerido"]);
            }
            break;
            
        case 'getStoreInventory':
            $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : null;
            if ($store_id) {
                $controller->$function_name($store_id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID de tienda requerido"]);
            }
            break;
            
        case 'getOrdersByStatus':
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            if ($status) {
                $controller->$function_name($status);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Estado requerido"]);
            }
            break;
            
        case 'getAverageRating':
            $tire_id = isset($_GET['tire_id']) ? intval($_GET['tire_id']) : null;
            if ($tire_id) {
                $controller->$function_name($tire_id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID de llanta requerido"]);
            }
            break;
            
        default:
            // Funciones que pueden recibir ID (con relaciones)
            if ($id) {
                $controller->$function_name($id);
            } else {
                $controller->$function_name();
            }
            break;
    }
    
    exit();
}

// Verificar si la tabla es válida para CRUD normal
if (!in_array($table, $all_tables) && $table !== '') {
    http_response_code(404);
    echo json_encode([
        "message" => "Tabla no encontrada",
        "available_tables" => $all_tables,
        "special_functions" => array_keys($special_functions),
        "help" => "Usa GET /api/index.php para ver todos los endpoints disponibles"
    ]);
    exit();
}

// Enrutamiento principal para CRUD
switch ($method) {
    case 'GET':
        if ($table === '') {
            // Ruta raíz - mostrar información completa de la API
            echo json_encode([
                "message" => "API REST para Sistema de Llantas VGOOD",
                "version" => "3.0",
                "author" => "Desarrollado para misllantasnica.unaux.com",
                "database" => "MySQL - 18 tablas",
                "total_tables" => count($all_tables),
                "total_special_functions" => count($special_functions),
                "endpoints" => [
                    "crud" => $all_tables,
                    "special_functions" => array_keys($special_functions)
                ],
                "examples" => [
                    "CRUD básico" => [
                        "Obtener todas las llantas" => "GET /api/index.php?path=motorcycle_tires",
                        "Obtener una llanta por ID" => "GET /api/index.php?path=motorcycle_tires/1",
                        "Crear nueva llanta" => "POST /api/index.php?path=motorcycle_tires",
                        "Actualizar llanta" => "PUT /api/index.php?path=motorcycle_tires/1",
                        "Eliminar llanta" => "DELETE /api/index.php?path=motorcycle_tires/1"
                    ],
                    "Funciones con relaciones" => [
                        "Llantas con información de marca" => "GET /api/index.php?path=motorcycle_tires_with_brand",
                        "Órdenes con información de cliente" => "GET /api/index.php?path=orders_with_customer",
                        "Usuarios con información de tienda" => "GET /api/index.php?path=users_with_store",
                        "Reseñas con detalles completos" => "GET /api/index.php?path=product_reviews_with_details"
                    ],
                    "Funciones especiales" => [
                        "Validar cupón" => "GET /api/index.php?path=validate_coupon&code=VGOOD10",
                        "Llantas con bajo stock" => "GET /api/index.php?path=low_stock_tires",
                        "Ofertas activas" => "GET /api/index.php?path=active_offers",
                        "Llantas por tipo" => "GET /api/index.php?path=tires_by_type&type=Urbana",
                        'update_tire_price' => 'updateTirePriceWithHistory',
                        "Historial de precios" => "GET /api/index.php?path=tire_price_history&tire_id=1"
                    ]
                ],
                "usage_notes" => [
                    "Paginación" => "Añade &page=2&limit=20 a cualquier endpoint GET",
                    "Filtrado" => "Añade &field=value para filtrar resultados",
                    "Formato JSON" => "Usa Content-Type: application/json para POST/PUT",
                    "CORS" => "API compatible con CORS para desarrollo frontend"
                ]
            ]);
        } else {
            if ($id) {
                $controller->getById($table, $id);
            } else {
                // Obtener parámetros de paginación y filtros
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
                $filters = $_GET;
                
                // Remover parámetros especiales
                $reserved_params = ['page', 'limit', 'path', 'sort', 'order'];
                foreach ($reserved_params as $param) {
                    unset($filters[$param]);
                }
                
                $controller->getAll($table, $page, $limit, $filters);
            }
        }
        break;
        
    case 'POST':
        // Obtener datos del cuerpo
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data) {
            $controller->create($table, $data);
        } else {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Datos inválidos o JSON mal formado",
                "tip" => "Asegúrate de enviar Content-Type: application/json"
            ]);
        }
        break;
        
    case 'PUT':
        if ($id) {
            $data = json_decode(file_get_contents("php://input"), true);
            if ($data) {
                $controller->update($table, $id, $data);
            } else {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Datos inválidos o JSON mal formado"
                ]);
            }
        } else {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "ID requerido para actualizar",
                "format" => "PUT /api/index.php?path=tabla/id"
            ]);
        }
        break;
        
    case 'DELETE':
        if ($id) {
            $controller->delete($table, $id);
        } else {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "ID requerido para eliminar",
                "format" => "DELETE /api/index.php?path=tabla/id"
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Método no permitido",
            "allowed_methods" => ["GET", "POST", "PUT", "DELETE", "OPTIONS"]
        ]);
        break;
}
?>