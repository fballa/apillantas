<?php
// models.php - Modelos para la API REST (con relaciones)
/*
class Database {
    
 /*   private $host = "sql202.ezyro.com";
    private $db_name = "ezyro_40898176_dbllantas";
    private $username = "ezyro_40898176";
    private $password = "025f0130855e";
    private $conn;
    
    
    
    private $host = "sql109.infinityfree.com";
    private $db_name = "if0_40944762_dbllantas";
    private $username = "if0_40944762";
    private $password = "Tempo2026";
    private $conn;
    
    
    
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
*/

class Database {
    private static $conn = null;
    
    public static function getConnection() {
        
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=mysql-3425c3b8-franklinl-48c4.b.aivencloud.com;port=15806;dbname=defaultdb",
                    "avnadmin",
                    "AVNS_36gkMok-WoDkZ2lUyMX",
                    [
                        PDO::ATTR_PERSISTENT => false, // <-- CRÍTICO
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                    ]
                );
            } catch(PDOException $e) {
                error_log("DB Error: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }
        return self::$conn;
    }
}





class BaseModel {
    protected $table;
    protected $conn;
    protected $primaryKey = 'id';
    
    
   /* public function __construct($table) {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->table = $table;
    }
    */
    
    
    public function __construct($table) {
    $this->conn = Database::getConnection(); // Usar conexión única
    $this->table = $table;
}
    
    // En la clase BaseModel, agrega:
public function getConnection() {
    return $this->conn;
}
    
    
    // Métodos base (CRUD)
    public function getAll($page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
        $where = '';
        $params = [];
        
        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $key => $value) {
                $conditions[] = "$key LIKE ?";
                $params[] = "%$value%";
            }
            $where = "WHERE " . implode(" AND ", $conditions);
        }
        
        $query = "SELECT * FROM " . $this->table . " $where LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        
        $countQuery = "SELECT COUNT(*) as total FROM " . $this->table . " $where";
        $countStmt = $this->conn->prepare($countQuery);
        
        if (!empty($params)) {
            foreach ($params as $index => $value) {
                $countStmt->bindValue($index + 1, $value);
            }
        }
        
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        if (!empty($params)) {
            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }
        }
        
        $stmt->execute();
        
        $result = [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
        
        return $result;
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE " . $this->primaryKey . " = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $row : null;
    }
    
   /*
   public function create($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        
        $query = "INSERT INTO " . $this->table . " (" . $columns . ") VALUES (" . $placeholders . ")";
        $stmt = $this->conn->prepare($query);
        
        $i = 1;
        foreach ($data as $value) {
            $stmt->bindValue($i++, $value);
        }
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    */
    
    
    public function create($data) {
    $columns = implode(", ", array_keys($data));
    $placeholders = implode(", ", array_fill(0, count($data), "?"));
    
    $query = "INSERT INTO " . $this->table . " (" . $columns . ") VALUES (" . $placeholders . ")";
    $stmt = $this->conn->prepare($query);
    
    $i = 1;
    foreach ($data as $value) {
        // Manejar valores nulos
        if ($value === null) {
            $stmt->bindValue($i++, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue($i++, $value);
        }
    }
    
    if ($stmt->execute()) {
        return $this->conn->lastInsertId();
    }
    
    
    // Para debugging, puedes loggear el error
    // error_log("Error en create(): " . print_r($stmt->errorInfo(), true));
   
    return false;
    
}
    
    
    
    public function update($id, $data) {
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "$key = ?";
        }
        
        $query = "UPDATE " . $this->table . " SET " . implode(", ", $setClause) . 
                " WHERE " . $this->primaryKey . " = ?";
        $stmt = $this->conn->prepare($query);
        
        $i = 1;
        foreach ($data as $value) {
            $stmt->bindValue($i++, $value);
        }
        $stmt->bindValue($i, $id);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        return $stmt->execute();
    }
    
    public function findBy($field, $value) {
        $query = "SELECT * FROM " . $this->table . " WHERE $field = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $value);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ============================================
// MODELOS PARA LAS PRIMERAS 9 TABLAS
// ============================================

class AuditLogs extends BaseModel {
    public function __construct() {
        parent::__construct('audit_logs');
    }
    
    public function getWithUser($id = null) {
        if ($id) {
            $query = "SELECT al.*, u.name as user_name, u.email as user_email, u.role as user_role 
                      FROM audit_logs al 
                      LEFT JOIN users u ON al.user_id = u.id 
                      WHERE al.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT al.*, u.name as user_name, u.email as user_email, u.role as user_role 
                      FROM audit_logs al 
                      LEFT JOIN users u ON al.user_id = u.id 
                      ORDER BY al.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    
    public function logAction($user_id, $action, $entity, $entity_id) {
        $data = [
            'user_id' => $user_id,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entity_id
        ];
        
        try {
            return $this->create($data);
        } catch (Exception $e) {
            error_log("Error en logAction: " . $e->getMessage());
            return false;
        }
    }
    
    
}

class Blogs extends BaseModel {
    public function __construct() {
        parent::__construct('blogs');
    }
}

class Coupons extends BaseModel {
    public function __construct() {
        parent::__construct('coupons');
    }
}

class Customers extends BaseModel {
    public function __construct() {
        parent::__construct('customers');
    }
    
    public function getWithOrders($id = null) {
        if ($id) {
            $query = "SELECT c.*, 
                      COUNT(o.id) as total_orders,
                      SUM(o.total) as total_spent
                      FROM customers c
                      LEFT JOIN orders o ON c.id = o.customer_id
                      WHERE c.id = ?
                      GROUP BY c.id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT c.*, 
                      COUNT(o.id) as total_orders,
                      SUM(o.total) as total_spent
                      FROM customers c
                      LEFT JOIN orders o ON c.id = o.customer_id
                      GROUP BY c.id
                      ORDER BY c.name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    
      // NUEVO MÉTODO: Buscar cliente por email
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    
}

class InventoryMovements extends BaseModel {
    public function __construct() {
        parent::__construct('inventory_movements');
    }
    
    public function getWithTire($id = null) {
        if ($id) {
            $query = "SELECT im.*, 
                      mt.model as tire_model, mt.full_size as tire_size, 
                      mt.price as tire_price, mt.stock as current_stock,
                      tb.name as tire_brand_name
                      FROM inventory_movements im 
                      LEFT JOIN motorcycle_tires mt ON im.tire_id = mt.id 
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id 
                      WHERE im.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT im.*, 
                      mt.model as tire_model, mt.full_size as tire_size, 
                      mt.price as tire_price, mt.stock as current_stock,
                      tb.name as tire_brand_name
                      FROM inventory_movements im 
                      LEFT JOIN motorcycle_tires mt ON im.tire_id = mt.id 
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id 
                      ORDER BY im.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

class Motorcycles extends BaseModel {
    public function __construct() {
        parent::__construct('motorcycles');
    }
    
    public function getWithBrand($id = null) {
        if ($id) {
            $query = "SELECT m.*, mb.name as brand_name 
                      FROM motorcycles m 
                      JOIN motorcycle_brands mb ON m.brand_id = mb.id 
                      WHERE m.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT m.*, mb.name as brand_name 
                      FROM motorcycles m 
                      JOIN motorcycle_brands mb ON m.brand_id = mb.id 
                      ORDER BY mb.name, m.model";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

class MotorcycleBrands extends BaseModel {
    public function __construct() {
        parent::__construct('motorcycle_brands');
    }
}

class MotorcycleTires extends BaseModel {
    public function __construct() {
        parent::__construct('motorcycle_tires');
    }
    
    public function getWithBrand($id = null) {
        if ($id) {
            $query = "SELECT mt.*, tb.name as brand_name, tb.country as brand_country 
                      FROM motorcycle_tires mt 
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id 
                      WHERE mt.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT mt.*, tb.name as brand_name, tb.country as brand_country 
                      FROM motorcycle_tires mt 
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id 
                      WHERE mt.status = 1 
                      ORDER BY tb.name, mt.model";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

class Offers extends BaseModel {
    public function __construct() {
        parent::__construct('offers');
    }
    
    public function getWithTire($id = null) {
        if ($id) {
            $query = "SELECT o.*, 
                      mt.model as tire_model, mt.full_size as tire_size, 
                      mt.price as tire_price, mt.image_url as tire_image,
                      tb.name as tire_brand_name
                      FROM offers o 
                      LEFT JOIN motorcycle_tires mt ON o.tire_id = mt.id 
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id 
                      WHERE o.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT o.*, 
                      mt.model as tire_model, mt.full_size as tire_size, 
                      mt.price as tire_price, mt.image_url as tire_image,
                      tb.name as tire_brand_name
                      FROM offers o 
                      LEFT JOIN motorcycle_tires mt ON o.tire_id = mt.id 
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id 
                      WHERE o.is_active = 1 
                      ORDER BY o.start_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

// ============================================
// NUEVOS MODELOS PARA LAS 9 TABLAS RESTANTES
// ============================================

class Orders extends BaseModel {
    public function __construct() {
        parent::__construct('orders');
    }
    
    public function getWithCustomer($id = null) {
        if ($id) {
            $query = "SELECT o.*, c.name as customer_name, c.email as customer_email, 
                      c.phone as customer_phone, c.address as customer_address
                      FROM orders o 
                      LEFT JOIN customers c ON o.customer_id = c.id 
                      WHERE o.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT o.*, c.name as customer_name, c.email as customer_email, 
                      c.phone as customer_phone, c.address as customer_address
                      FROM orders o 
                      LEFT JOIN customers c ON o.customer_id = c.id 
                      ORDER BY o.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    public function getOrderItems($order_id) {
        $query = "SELECT oi.*, mt.model as tire_model, mt.full_size as tire_size,
                  mt.price as tire_price, tb.name as tire_brand_name
                  FROM order_items oi
                  LEFT JOIN motorcycle_tires mt ON oi.tire_id = mt.id
                  LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
                  WHERE oi.order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class OrderItems extends BaseModel {
    public function __construct() {
        parent::__construct('order_items');
    }
    
    public function getWithDetails($id = null) {
        if ($id) {
            $query = "SELECT oi.*, 
                      mt.model as tire_model, mt.full_size as tire_size,
                      tb.name as tire_brand_name,
                      o.status as order_status, o.customer_id
                      FROM order_items oi
                      LEFT JOIN motorcycle_tires mt ON oi.tire_id = mt.id
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
                      LEFT JOIN orders o ON oi.order_id = o.id
                      WHERE oi.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT oi.*, 
                      mt.model as tire_model, mt.full_size as tire_size,
                      tb.name as tire_brand_name,
                      o.status as order_status
                      FROM order_items oi
                      LEFT JOIN motorcycle_tires mt ON oi.tire_id = mt.id
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
                      LEFT JOIN orders o ON oi.order_id = o.id
                      ORDER BY oi.id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

class ProductPrices extends BaseModel {
    public function __construct() {
        parent::__construct('product_prices');
    }
    
    public function getWithTire($id = null) {
        if ($id) {
            $query = "SELECT pp.*, 
                      mt.model as tire_model, mt.full_size as tire_size,
                      tb.name as tire_brand_name,
                      u.name as changed_by_name
                      FROM product_prices pp
                      LEFT JOIN motorcycle_tires mt ON pp.tire_id = mt.id
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
                      LEFT JOIN users u ON pp.changed_by = u.id
                      WHERE pp.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT pp.*, 
                      mt.model as tire_model, mt.full_size as tire_size,
                      tb.name as tire_brand_name,
                      u.name as changed_by_name
                      FROM product_prices pp
                      LEFT JOIN motorcycle_tires mt ON pp.tire_id = mt.id
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
                      LEFT JOIN users u ON pp.changed_by = u.id
                      ORDER BY pp.effective_from DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

class ProductReviews extends BaseModel {
    public function __construct() {
        parent::__construct('product_reviews');
    }
    
    public function getWithDetails($id = null) {
        if ($id) {
            $query = "SELECT pr.*, 
                      mt.model as tire_model, mt.full_size as tire_size,
                      tb.name as tire_brand_name
                     
                      FROM product_reviews pr
                      LEFT JOIN motorcycle_tires mt ON pr.tire_id = mt.id
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
                    
                      WHERE pr.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT pr.*, 
                      mt.model as tire_model, mt.full_size as tire_size,
                      tb.name as tire_brand_name
                     
                      FROM product_reviews pr
                      LEFT JOIN motorcycle_tires mt ON pr.tire_id = mt.id
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
                     
                      WHERE pr.status = 'APPROVED'
                      ORDER BY pr.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    
// Dentro de la clase ProductReviews en models.php, agregar:

public function getReviewsByTire($tire_id) {
    $query = "SELECT pr.*, 
              mt.model as tire_model, mt.full_size as tire_size,
              tb.name as tire_brand_name
            
              FROM product_reviews pr
              LEFT JOIN motorcycle_tires mt ON pr.tire_id = mt.id
              LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
              
              WHERE pr.tire_id = ? AND pr.status = 'APPROVED'
              ORDER BY pr.created_at DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $tire_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    
}


class Stores extends BaseModel {
    public function __construct() {
        parent::__construct('stores');
    }
    
    public function getWithUsers($id = null) {
        if ($id) {
            $query = "SELECT s.*, 
                      COUNT(u.id) as total_users,
                      GROUP_CONCAT(u.name SEPARATOR ', ') as users_names
                      FROM stores s
                      LEFT JOIN users u ON s.id = u.store_id
                      WHERE s.id = ?
                      GROUP BY s.id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT s.*, 
                      COUNT(u.id) as total_users
                      FROM stores s
                      LEFT JOIN users u ON s.id = u.store_id
                      GROUP BY s.id
                      ORDER BY s.name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

class Testimonials extends BaseModel {
    public function __construct() {
        parent::__construct('testimonials');
    }
    
    public function getWithDetails($id = null) {
        if ($id) {
            $query = "SELECT t.* 
                      FROM testimonials t
                      WHERE t.id = ? AND t.approved = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT t.* 
                      FROM testimonials t
                      WHERE t.approved = 1 
                      ORDER BY t.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

class TireBrands extends BaseModel {
    public function __construct() {
        parent::__construct('tire_brands');
    }
    
    public function getWithTires($id = null) {
        if ($id) {
            $query = "SELECT tb.*, 
                      COUNT(mt.id) as total_tires,
                      GROUP_CONCAT(mt.model SEPARATOR ', ') as tire_models
                      FROM tire_brands tb
                      LEFT JOIN motorcycle_tires mt ON tb.id = mt.brand_id
                      WHERE tb.id = ?
                      GROUP BY tb.id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT tb.*, 
                      COUNT(mt.id) as total_tires
                      FROM tire_brands tb
                      LEFT JOIN motorcycle_tires mt ON tb.id = mt.brand_id
                      GROUP BY tb.id
                      ORDER BY tb.name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

class TireMotorcycleCompatibility extends BaseModel {
    public function __construct() {
        parent::__construct('tire_motorcycle_compatibility');
    }
    
    public function getWithDetails($id = null) {
        if ($id) {
            $query = "SELECT tmc.*, 
                      mt.model as tire_model, mt.full_size as tire_size,
                      tb.name as tire_brand_name,
                      m.model as motorcycle_model, mb.name as motorcycle_brand_name
                      FROM tire_motorcycle_compatibility tmc
                      LEFT JOIN motorcycle_tires mt ON tmc.tire_id = mt.id
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
                      LEFT JOIN motorcycles m ON tmc.motorcycle_id = m.id
                      LEFT JOIN motorcycle_brands mb ON m.brand_id = mb.id
                      WHERE tmc.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT tmc.*, 
                      mt.model as tire_model, mt.full_size as tire_size,
                      tb.name as tire_brand_name,
                      m.model as motorcycle_model, mb.name as motorcycle_brand_name
                      FROM tire_motorcycle_compatibility tmc
                      LEFT JOIN motorcycle_tires mt ON tmc.tire_id = mt.id
                      LEFT JOIN tire_brands tb ON mt.brand_id = tb.id
                      LEFT JOIN motorcycles m ON tmc.motorcycle_id = m.id
                      LEFT JOIN motorcycle_brands mb ON m.brand_id = mb.id
                      ORDER BY tb.name, mt.model";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

class Users extends BaseModel {
    public function __construct() {
        parent::__construct('users');
    }
    
    public function getWithStore($id = null) {
        if ($id) {
            $query = "SELECT u.*, 
                      s.name as store_name, s.address as store_address,
                      s.phone as store_phone, s.email as store_email
                      FROM users u
                      LEFT JOIN stores s ON u.store_id = s.id
                      WHERE u.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT u.*, 
                      s.name as store_name, s.address as store_address
                      FROM users u
                      LEFT JOIN stores s ON u.store_id = s.id
                      ORDER BY u.name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}