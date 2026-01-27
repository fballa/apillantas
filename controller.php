<?php
// controller.php - Controlador principal de la API (con todas las funciones)

class Controller {
    private $models = [];
    
    public function __construct() {
        // Inicializar todos los modelos (18 tablas)
        $this->models = [
            // Primeras 9 tablas
            'audit_logs' => new AuditLogs(),
            'blogs' => new Blogs(),
            'coupons' => new Coupons(),
            'customers' => new Customers(),
            'inventory_movements' => new InventoryMovements(),
            'motorcycles' => new Motorcycles(),
            'motorcycle_brands' => new MotorcycleBrands(),
            'motorcycle_tires' => new MotorcycleTires(),
            'offers' => new Offers(),
            
            // Nuevas 9 tablas
            'orders' => new Orders(),
            'order_items' => new OrderItems(),
            'product_prices' => new ProductPrices(),
            'product_reviews' => new ProductReviews(),
            'stores' => new Stores(),
            'testimonials' => new Testimonials(),
            'tire_brands' => new TireBrands(),
            'tire_motorcycle_compatibility' => new TireMotorcycleCompatibility(),
            'users' => new Users()
        ];
    }
    
    // ============================================
    // MÉTODOS CRUD BÁSICOS PARA TODAS LAS TABLAS
    // ============================================
    
    public function getAll($table, $page = 1, $limit = 10, $filters = []) {
        try {
            if (!isset($this->models[$table])) {
                throw new Exception("Tabla no encontrada");
            }
            
            $result = $this->models[$table]->getAll($page, $limit, $filters);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result['data'],
                "pagination" => $result['pagination']
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al obtener registros: " . $e->getMessage()
            ]);
        }
    }
    
    public function getById($table, $id) {
        try {
            if (!isset($this->models[$table])) {
                throw new Exception("Tabla no encontrada");
            }
            
            $result = $this->models[$table]->getById($id);
            
            if ($result) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "message" => "Registro no encontrado"
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al obtener registro: " . $e->getMessage()
            ]);
        }
    }
    
    public function create($table, $data) {
        try {
            if (!isset($this->models[$table])) {
                throw new Exception("Tabla no encontrada");
            }
            
            // Registrar acción de auditoría si es necesario
            /*if ($table !== 'audit_logs') {
                $this->logAudit('CREAR', $table, null);
            }
            */
            
            $id = $this->models[$table]->create($data);
            
            if ($id) {
                http_response_code(201);
                echo json_encode([
                    "success" => true,
                    "message" => "Registro creado exitosamente",
                    "id" => $id
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Error al crear registro"
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al crear registro: " . $e->getMessage()
            ]);
        }
    }
    
    public function update($table, $id, $data) {
        try {
            if (!isset($this->models[$table])) {
                throw new Exception("Tabla no encontrada");
            }
            
            // Registrar acción de auditoría si es necesario
           /* if ($table !== 'audit_logs') {
                $this->logAudit('ACTUALIZAR', $table, $id);
            }
            */
            
            $result = $this->models[$table]->update($id, $data);
            
            if ($result) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Registro actualizado exitosamente"
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "message" => "Registro no encontrado o sin cambios"
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al actualizar registro: " . $e->getMessage()
            ]);
        }
    }
    
    public function delete($table, $id) {
        try {
            if (!isset($this->models[$table])) {
                throw new Exception("Tabla no encontrada");
            }
            
            // Registrar acción de auditoría si es necesario
           /* if ($table !== 'audit_logs') {
                $this->logAudit('ELIMINAR', $table, $id);
            }
            */
            
            $result = $this->models[$table]->delete($id);
            
            if ($result) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Registro eliminado exitosamente"
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "message" => "Registro no encontrado"
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al eliminar registro: " . $e->getMessage()
            ]);
        }
    }
    
    // ============================================
    // FUNCIONES CON RELACIONES - PRIMERAS 9 TABLAS
    // ============================================
    
    public function getAuditLogsWithUser($id = null) {
        try {
            $result = $this->models['audit_logs']->getWithUser($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getInventoryMovementsWithTire($id = null) {
        try {
            $result = $this->models['inventory_movements']->getWithTire($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getMotorcyclesWithBrand($id = null) {
        try {
            $result = $this->models['motorcycles']->getWithBrand($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getMotorcycleTiresWithBrand($id = null) {
        try {
            $result = $this->models['motorcycle_tires']->getWithBrand($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getOffersWithTire($id = null) {
        try {
            $result = $this->models['offers']->getWithTire($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getCustomersWithOrders($id = null) {
        try {
            $result = $this->models['customers']->getWithOrders($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    // ============================================
    // FUNCIONES CON RELACIONES - NUEVAS 9 TABLAS
    // ============================================
    
    public function getOrdersWithCustomer($id = null) {
        try {
            $result = $this->models['orders']->getWithCustomer($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getOrderItemsWithDetails($id = null) {
        try {
            $result = $this->models['order_items']->getWithDetails($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getProductPricesWithTire($id = null) {
        try {
            $result = $this->models['product_prices']->getWithTire($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getProductReviewsWithDetails($id = null) {
        try {
            $result = $this->models['product_reviews']->getWithDetails($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getStoresWithUsers($id = null) {
        try {
            $result = $this->models['stores']->getWithUsers($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }




public function getTestimonialsWithDetails($id = null) {
        try {
            $result = $this->models['testimonials']->getWithDetails($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getTireBrandsWithTires($id = null) {
        try {
            $result = $this->models['tire_brands']->getWithTires($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getTireCompatibilityWithDetails($id = null) {
        try {
            $result = $this->models['tire_motorcycle_compatibility']->getWithDetails($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getUsersWithStore($id = null) {
        try {
            $result = $this->models['users']->getWithStore($id);
            
            if ($id) {
                if ($result) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Registro no encontrado"
                    ]);
                }
            } else {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    // ============================================
    // FUNCIONES ESPECIALES ADICIONALES
    // ============================================
    
    public function validateCoupon($code) {
        try {
            $result = $this->models['coupons']->validateCoupon($code);
            
            if ($result) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $result,
                    "message" => "Cupón válido"
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "message" => "Cupón no válido o expirado"
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al validar cupón: " . $e->getMessage()
            ]);
        }
    }
    
    
    
    public function getLowStockTires() {
    try {
        // Usar conexión directa de Database
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT 
                     mt.id, 
                     mt.model, 
                     mt.full_size, 
                     mt.price,
                     mt.stock,
                     mt.stock_min,
                     tb.name as brand_name,
                     (mt.stock_min - mt.stock) as faltante,
                     CASE 
                         WHEN mt.stock <= 3 THEN 'CRÍTICO'
                         WHEN mt.stock <= 5 THEN 'ALTO'
                         WHEN mt.stock < 10 THEN 'MEDIO'
                         ELSE 'NORMAL'
                     END as nivel_alerta
                  FROM motorcycle_tires mt 
                  LEFT JOIN tire_brands tb ON mt.brand_id = tb.id 
                  WHERE mt.stock < 10 AND mt.status = 1 
                  ORDER BY mt.stock ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "count" => count($result),
            "data" => $result,
            "summary" => [
                "umbral_stock" => 10,
                "productos_bajo_stock" => count($result),
                "recomendacion" => count($result) > 0 ? "Reabastecer inventario" : "Stock en niveles normales"
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(200); // 200 para que el frontend pueda leer el error
        echo json_encode([
            "success" => false,
            "message" => "Error obteniendo stock bajo",
            "data" => [],
            "count" => 0
        ]);
    }
}
    
    
    
    
    
    public function getActiveOffers() {
        try {
            $result = $this->models['offers']->getActiveOffers();
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result,
                "count" => count($result)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al obtener ofertas activas: " . $e->getMessage()
            ]);
        }
    }
    
    public function getCustomerOrders($customer_id) {
        try {
            $result = $this->models['customers']->getOrders($customer_id);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result,
                "count" => count($result)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al obtener órdenes del cliente: " . $e->getMessage()
            ]);
        }
    }
    
    public function getTiresByType($type) {
        try {
            $result = $this->models['motorcycle_tires']->getTiresByType($type);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result,
                "count" => count($result)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    
    
    public function getOrderItemsByOrder($order_id) {
        try {
            $result = $this->models['orders']->getOrderItems($order_id);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result,
                "count" => count($result)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getTirePriceHistory($tire_id) {
        try {
            $result = $this->models['product_prices']->getTirePriceHistory($tire_id);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result,
                "count" => count($result)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getReviewsByTire($tire_id) {
        try {
            $result = $this->models['product_reviews']->getReviewsByTire($tire_id);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result,
                "count" => count($result)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getStoreInventory($store_id) {
        try {
            $result = $this->models['stores']->getStoreInventory($store_id);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result,
                "count" => count($result)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getOrdersByStatus($status) {
        try {
            $result = $this->models['orders']->getByStatus($status);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result,
                "count" => count($result)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    public function getAverageRating($tire_id) {
        try {
            $result = $this->models['product_reviews']->getAverageRating($tire_id);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $result
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
    // ============================================
    // MÉTODO PRIVADO PARA AUDITORÍA
    // ============================================
    
    private function logAudit($action, $entity, $entity_id) {
        try {
            $user_id = isset($_SERVER['PHP_AUTH_USER']) ? 1 : 0; // Simulado
            $this->models['audit_logs']->logAction($user_id, $action, $entity, $entity_id);
        } catch (Exception $e) {
            // Silenciar errores de auditoría para no afectar la operación principal
        }
    }
    
    
    
    
    public function getDashboardStats() {
    try {
        $today = date('Y-m-d');
        
        // 1. SUMA TOTAL DE VENTAS HOY - usando el campo 'total' de la tabla 'orders'
        $queryVentas = "SELECT COALESCE(SUM(total), 0) as total_ventas_hoy 
                       FROM orders 
                       WHERE DATE(created_at) = :today 
                       AND status IN ('PAGADO', 'DESPACHADO', 'ENTREGADO')";
        
        // 2. TOTAL DE PEDIDOS HOY
        $queryPedidos = "SELECT COALESCE(COUNT(*), 0) as total_pedidos_hoy 
                        FROM orders 
                        WHERE DATE(created_at) = :today";
        
        // 3. PRODUCTOS CON STOCK CRÍTICO (menos de 10 unidades)
        $queryStockCritico = "SELECT COALESCE(COUNT(*), 0) as productos_stock_critico 
                             FROM motorcycle_tires 
                             WHERE stock < 10 AND status = 1";
        
        // 4. TICKET PROMEDIO DE COMPRA (general, no solo hoy)
        $queryTicketPromedio = "SELECT COALESCE(AVG(total), 0) as ticket_promedio 
                               FROM orders 
                               WHERE status IN ('PAGADO', 'DESPACHADO', 'ENTREGADO')";
        
        // Preparar todas las consultas
       // $conn = $this->models['orders']->getConnection();
        $stmtVentas = $this->models['orders']->getConnection()->prepare($queryVentas);
        $stmtPedidos = $this->models['orders']->getConnection()->prepare($queryPedidos);
        $stmtStock = $this->models['motorcycle_tires']->getConnection()->prepare($queryStockCritico);
        $stmtTicket = $this->models['orders']->getConnection()->prepare($queryTicketPromedio);
        
        // Ejecutar consultas
        $stmtVentas->execute([':today' => $today]);
        $stmtPedidos->execute([':today' => $today]);
        $stmtStock->execute();
        $stmtTicket->execute();
        
        // Obtener resultados
        $ventas = $stmtVentas->fetch(PDO::FETCH_ASSOC);
        $pedidos = $stmtPedidos->fetch(PDO::FETCH_ASSOC);
        $stock = $stmtStock->fetch(PDO::FETCH_ASSOC);
        $ticket = $stmtTicket->fetch(PDO::FETCH_ASSOC);
        
        // Verificar resultados y convertir a valores numéricos seguros
        $totalVentas = floatval($ventas['total_ventas_hoy'] ?? 0);
        $totalPedidos = intval($pedidos['total_pedidos_hoy'] ?? 0);
        $stockCritico = intval($stock['productos_stock_critico'] ?? 0);
        $ticketPromedio = floatval($ticket['ticket_promedio'] ?? 0);
        
        // Calcular venta promedio por pedido (solo para hoy)
        $ventaPromedioPorPedido = 0;
        if ($totalPedidos > 0) {
            $ventaPromedioPorPedido = $totalVentas / $totalPedidos;
        }
        
        // Respuesta JSON
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "timestamp" => date('Y-m-d H:i:s'),
            "data" => [
                "estadisticas_del_dia" => [
                    "fecha" => $today,
                    "total_ventas_hoy" => $totalVentas,
                    "total_ordenes_hoy" => $totalPedidos,
                    "ticket_promedio_compra" => $ticketPromedio
                ],
                "inventario" => [
                    "productos_stock_critico" => $stockCritico,
                    "umbral_stock_critico" => 10,
                    "descripcion" => "Productos con menos de 10 unidades en inventario"
                ],
                "resumen" => [
                    "venta_promedio_por_pedido_hoy" => $ventaPromedioPorPedido,
                    "productos_en_riesgo" => $stockCritico > 0 ? "SI" : "NO"
                ]
            ],
            "currency" => "C$",
            "notas" => [
                "Las ventas incluyen solo órdenes con estado PAGADO, DESPACHADO o ENTREGADO",
                "Stock crítico: productos con menos de 10 unidades en inventario",
                "Ticket promedio calculado sobre todas las órdenes pagadas"
            ]
        ]);
        
    } catch (Exception $e) {
        // En caso de error, retornar valores en 0
        http_response_code(200);
        echo json_encode([
            "success" => false,
            "message" => "Error al obtener estadísticas",
            "timestamp" => date('Y-m-d H:i:s'),
            "data" => [
                "estadisticas_del_dia" => [
                    "fecha" => date('Y-m-d'),
                    "total_ventas_hoy" => 0,
                    "total_ordenes_hoy" => 0,
                    "ticket_promedio_compra" => 0
                ],
                "inventario" => [
                    "productos_stock_critico" => 0,
                    "umbral_stock_critico" => 10,
                    "descripcion" => "Productos con menos de 10 unidades en inventario"
                ],
                "resumen" => [
                    "venta_promedio_por_pedido_hoy" => 0,
                    "productos_en_riesgo" => "NO"
                ]
            ]
        ]);
    }
}
    
    
    
    
    public function updateTirePriceWithHistory($id) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar campos requeridos
        if (!isset($data['new_price']) || !isset($data['reason'])) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Campos requeridos: new_price, reason"
            ]);
            return;
        }
        
        $tireModel = $this->models['motorcycle_tires'];
        $priceModel = $this->models['product_prices'];
        
        // Obtener llanta actual
        $currentTire = $tireModel->getById($id);
        if (!$currentTire) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Llanta no encontrada"]);
            return;
        }
        
        // 1. Registrar en histórico
        $historyData = [
            'tire_id' => $id,
            'old_price' => $currentTire['price'],
            'new_price' => $data['new_price'],
            'changed_by' => $data['changed_by'] ?? 1,
            'reason' => $data['reason'],
            'effective_from' => date('Y-m-d H:i:s')
        ];
        
        $historyId = $priceModel->create($historyData);
        
        // 2. Actualizar precio en llanta
        $updateResult = $tireModel->update($id, ['price' => $data['new_price']]);
        
        if ($updateResult && $historyId) {
            echo json_encode([
                "success" => true,
                "message" => "Precio actualizado e histórico registrado",
                "price_update" => [
                    "tire_id" => $id,
                    "old_price" => (float)$currentTire['price'],
                    "new_price" => (float)$data['new_price'],
                    "difference" => (float)$data['new_price'] - (float)$currentTire['price']
                ],
                "history_record" => [
                    "id" => $historyId,
                    "reason" => $data['reason'],
                    "effective_from" => $historyData['effective_from']
                ]
            ]);
        } else {
            throw new Exception("Error en la actualización");
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}
    
    
    
    public function getTireMovements($tire_id) {
    error_log("=== DEBUG getTireMovements START ===");
    error_log("tire_id: " . $tire_id);
    
    try {
        if (!is_numeric($tire_id) || $tire_id <= 0) {
            error_log("Validation failed for tire_id");
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "ID de llanta inválido"
            ]);
            return;
        }
        
        error_log("Validation passed");
        
        // Conexión con manejo explícito
        try {
            $database = new Database();
            $conn = $database->getConnection();
            error_log("Database instance created");
        } catch (Exception $connEx) {
            error_log("Database connection error: " . $connEx->getMessage());
            throw new Exception("Database error: " . $connEx->getMessage());
        }
        
        if (!$conn) {
            error_log("Connection is NULL");
            throw new Exception("Connection object is null");
        }
        
        error_log("Connection obtained successfully");
        
        // Consulta simplificada paso a paso
        $query = "SELECT * FROM inventory_movements WHERE tire_id = :tire_id LIMIT 5";
        error_log("Query: " . $query);
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':tire_id', $tire_id, PDO::PARAM_INT);
        
        if (!$stmt->execute()) {
            $error = $stmt->errorInfo();
            error_log("Execute failed: " . print_r($error, true));
            throw new Exception("Query execution failed: " . $error[2]);
        }
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Results found: " . count($result));
        
        // Respuesta de éxito incluso si no hay datos
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "data" => $result,
            "count" => count($result),
            "tire_id" => (int)$tire_id,
            "debug" => [
                "query" => $query,
                "execution_time" => date('Y-m-d H:i:s')
            ]
        ]);
        
        error_log("=== DEBUG getTireMovements SUCCESS ===");
        
    } catch (Exception $e) {
        error_log("EXCEPTION in getTireMovements: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(500);
        echo json_encode([
            "success" => false, 
            "message" => "Error interno",
            "debug" => [
                "exception" => $e->getMessage(),
                "tire_id" => $tire_id,
                "timestamp" => date('Y-m-d H:i:s')
            ]
        ]);
        
        error_log("=== DEBUG getTireMovements ERROR ===");
    }
}
    
    
    
    
    
    
    public function getCustomerByEmail() {
    try {
        // Obtener email del parámetro GET
        $email = isset($_GET['email']) ? trim($_GET['email']) : null;
        
        if (!$email) {
            throw new Exception("Correo electrónico requerido");
        }
        
        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de correo electrónico inválido");
        }
        
        // Buscar cliente por email
        $result = $this->models['customers']->findByEmail($email);
        
        if ($result) {
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "found" => true,
                "data" => $result,
                "message" => "Cliente encontrado"
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "success" => true,
                "found" => false,
                "data" => null,
                "message" => "Cliente no encontrado"
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ]);
    }
}





public function createOrder() {
    try {
        // Obtener datos JSON
        $input = json_decode(file_get_contents("php://input"), true);
        
        // Validar datos mínimos
        if (empty($input['order_items']) || count($input['order_items']) == 0) {
            throw new Exception("La orden debe contener al menos un item");
        }
        
        if (empty($input['customer']) || !is_array($input['customer'])) {
            throw new Exception("Datos del cliente requeridos");
        }
        
        // Iniciar transacción
        $conn = $this->models['orders']->getConnection();
        $conn->beginTransaction();
        
        // ============================================
        // 1. MANEJAR CLIENTE - LÓGICA SIMPLIFICADA
        // ============================================
        $customer_id = null;
        
        // Validar datos del cliente
        $this->validateCustomerData($input['customer']);
        
        // LÓGICA: Si customer_id es null/0, CREAR. Si es mayor a 0, ACTUALIZAR
        if (isset($input['customer_id']) && $input['customer_id'] > 0) {
            // ACTUALIZAR cliente existente
            $customer_id = (int) $input['customer_id'];
            
            // Verificar si el cliente existe
            $existing_customer = $this->models['customers']->getById($customer_id);
            if (!$existing_customer) {
                throw new Exception("Cliente con ID $customer_id no encontrado");
            }
            
            // Actualizar cliente existente
            $result = $this->models['customers']->update($customer_id, $input['customer']);
            if (!$result) {
                throw new Exception("Error al actualizar cliente");
            }
            
            $customer_action = "updated";
        } else {
            // CREAR nuevo cliente (customer_id es 0, null o no existe)
            $customer_id = $this->models['customers']->create($input['customer']);
            if (!$customer_id) {
                throw new Exception("Error al crear cliente");
            }
            
            $customer_action = "created";
        }
        
        // ============================================
        // 2. VALIDAR ITEMS Y CALCULAR TOTALES
        // ============================================
        $order_total = 0;
        $items_validated = [];
        
        foreach ($input['order_items'] as $item) {
            // Validar item básico
            if (empty($item['tire_id']) || empty($item['quantity'])) {
                throw new Exception("Cada item debe tener tire_id y quantity");
            }
            
            // Obtener información de la llanta
            $tire = $this->models['motorcycle_tires']->getById($item['tire_id']);
            if (!$tire) {
                throw new Exception("Llanta con ID {$item['tire_id']} no encontrada");
            }
            
            // Validar stock
            if ($tire['stock'] < $item['quantity']) {
                throw new Exception("Stock insuficiente para {$tire['model']}. Disponible: {$tire['stock']}, Solicitado: {$item['quantity']}");
            }
            
            // Calcular precio
            $price = isset($item['price']) ? $item['price'] : $tire['price'];
            $subtotal = $price * $item['quantity'];
            $order_total += $subtotal;
            
            // Guardar item validado
            $items_validated[] = [
                'tire_id' => $item['tire_id'],
                'quantity' => $item['quantity'],
                'price' => $price,
                'subtotal' => $subtotal,
                'tire_data' => $tire
            ];
        }
        
        // ============================================
        // 3. CREAR LA ORDEN
        // ============================================
        $order_data = [
            'customer_id' => $customer_id,
            'status' => isset($input['order']['status']) ? $input['order']['status'] : 'NUEVO',
            'payment_method' => isset($input['order']['payment_method']) ? $input['order']['payment_method'] : 'EFECTIVO',
            'subtotal' => $order_total,
            'discount' => isset($input['order']['discount']) ? $input['order']['discount'] : 0,
            'tax' => isset($input['order']['tax']) ? $input['order']['tax'] : 0,
            'total' => $order_total,
            'notes' => isset($input['order']['notes']) ? $input['order']['notes'] : ''
        ];
        
        $order_id = $this->models['orders']->create($order_data);
        if (!$order_id) {
            throw new Exception("Error al crear la orden");
        }
        
        // ============================================
        // 4. CREAR ITEMS Y ACTUALIZAR STOCK
        // ============================================
        foreach ($items_validated as $item) {
            // Crear item de orden
            $order_item_data = [
                'order_id' => $order_id,
                'tire_id' => $item['tire_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['subtotal']
            ];
            
            $this->models['order_items']->create($order_item_data);
            
            // Actualizar stock de llanta
            $new_stock = $item['tire_data']['stock'] - $item['quantity'];
            $this->models['motorcycle_tires']->update($item['tire_id'], [
                'stock' => $new_stock
            ]);
            
            // Registrar movimiento de inventario
            $movement_data = [
                'tire_id' => $item['tire_id'],
                'type' => 'SALIDA',
                'quantity' => $item['quantity'],
                'note' => "Venta - Orden #$order_id"
            ];
            
            $this->models['inventory_movements']->create($movement_data);
        }
        
        // Confirmar transacción
        $conn->commit();
        
        // Respuesta exitosa
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Orden creada exitosamente",
            "order_id" => $order_id,
            "customer_id" => $customer_id,
            "total" => $order_total,
            "items_count" => count($items_validated),
            "customer_action" => $customer_action
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    }
}

// ============================================
// MÉTODO AUXILIAR PARA VALIDAR DATOS DE CLIENTE
// ============================================
private function validateCustomerData($data) {
    // Campos obligatorios mínimos
    $required_fields = ['name', 'phone'];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Campo de cliente requerido: $field");
        }
    }
    
    // Validar email si está presente
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Formato de email inválido");
    }
    
    return true;
}



/*public function sendEmail() {
    try {
        error_log("=== DEBUG sendEmail START ===");
        
        $data = json_decode(file_get_contents("php://input"), true);
        error_log("Datos recibidos: " . print_r($data, true));
        
        if (empty($data['to']) || empty($data['subject']) || empty($data['htmlContent'])) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Datos requeridos: to, subject, htmlContent"
            ]);
            return;
        }
        
        require_once 'PHPMailer/src/PHPMailer.php';
        require_once 'PHPMailer/src/SMTP.php';
        require_once 'PHPMailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // ✅ CONFIGURACIÓN SENDGRID PARA PRUEBAS
        $mail->isSMTP();
        $mail->Host = 'smtp.sendgrid.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'apikey';  // ← Esto es literal
        $mail->Password = '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'; 
        // Tu API Key real
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // ✅ USAR EMAIL VERIFICADO COMO SINGLE SENDER
        $mail->setFrom('ventas@mitiendadellantas.netlify.app', 'Mi Tienda de Llantas');
        $mail->addAddress($data['to'], $data['name'] ?? '');
        
        $mail->isHTML(true);
        $mail->Subject = $data['subject'];
        $mail->Body = $data['htmlContent'];
        $mail->AltBody = strip_tags($data['htmlContent']);
        
        if ($mail->send()) {
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Correo enviado exitosamente"
            ]);
        } else {
            throw new Exception("Error PHPMailer: " . $mail->ErrorInfo);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ]);
    }
}

*/





public function sendEmail() {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['to']) || empty($data['subject']) || empty($data['htmlContent'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Datos requeridos"]);
            return;
        }
        
        // Cargar PHPMailer
        require_once 'PHPMailer/src/PHPMailer.php';
        require_once 'PHPMailer/src/SMTP.php';
        require_once 'PHPMailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // CONFIGURACIÓN MAILTRAP (USA ESTOS DATOS EXACTOS):
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = 'ebc9a7ff2e36b2';  // ← Funciona para pruebas
        $mail->Password = '9d7e1f5c5d8b3a';   // ← Funciona para pruebas
        $mail->Port = 2525;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        
        // Remitente (puede ser cualquier email para pruebas)
        $mail->setFrom('ventas@mitiendadellantas.com', 'Mi Tienda de Llantas');
        $mail->addAddress($data['to'], $data['name'] ?? '');
        
        $mail->isHTML(true);
        $mail->Subject = $data['subject'];
        $mail->Body = $data['htmlContent'];
        $mail->AltBody = strip_tags($data['htmlContent']);
        
        if ($mail->send()) {
            echo json_encode([
                "success" => true,
                "message" => "Correo enviado (atrapado en Mailtrap)"
            ]);
        } else {
            throw new Exception("Error: " . $mail->ErrorInfo);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage(),
            "debug" => "Usa las credenciales de arriba"
        ]);
    }
}



}



?>




    