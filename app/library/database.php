<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

class Database
{
    private static $instance = null;
    private $db;
    private $dbhost = DBHOST;
    private $dbuser = DBUSER;
    private $dbpass = DBPASS;
    private $dbname = DBNAME;

    private function __construct()
    {
        try {
            $this->db = new PDO('mysql:host=' . $this->dbhost . ';dbname=' . $this->dbname . ';charset=utf8', $this->dbuser, $this->dbpass);

            // echo "Bağlantı Başarılı";

        } catch (Exception $e) {

            die("Bağlantı Başarısız:" . $e->getMessage());
        }
    }

    // Dışarıdan sınıfımızı çağıracağımız metodumuz.
    public static function getInstance()
    {
        // eğer daha önce örnek oluşturulmamış ise sınıfın tek örneğini oluştur
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    // dışarıdan kopyalanmasını engelledik
    // Bağlantının kopyalanabilmesini önlemek için boş döndürüyoruz
    private function __clone()
    {
    }

    // tekrardan oluşturulmasını engelledik
    private function __wakeup()
    {
    }

    //Değerleri birleştirme fonksiyonu
    public function addValue($argse)
    {

        $values = implode(',', array_map(function ($item) {
            return $item . '=?';
        }, array_keys($argse)));

        return $values;
    }


    //Veritabanına kayıt ekleme fonksiyonu
    public function insert($table, $values, $options = [])
    {

        try {

            $stmt = $this->db->prepare("INSERT INTO $table SET {$this->addValue($values)}");
            $stmt->execute(array_values($values));

            if ($stmt->rowCount()) {
                return ['status' => TRUE];
            } else {
                return ['status' => false, 'error' => "Kayıt Başarısız"];
                exit;
            }
        } catch (Exception $e) {

            return ['status' => FALSE, 'error' => $e->getMessage()];
        }
    }

    //Güncelleme fonksiyonu
    public function update($table, $values, $options = [])
    {

        try {

            $columns_id = $values[$options['columns']];
            unset($values[$options['columns']]);
            $valuesExecute = $values;
            $valuesExecute += [$options['columns'] => $columns_id];

            $stmt = $this->db->prepare("UPDATE $table SET {$this->addValue($values)} WHERE {$options['columns']}=?");
            $stmt->execute(array_values($valuesExecute));

            if ($stmt->rowCount() > 0) {
                return ['status' => TRUE];
            } else {
                throw new Exception('İşlem Başarısız');
            }
        } catch (Exception $e) {

            return ['status' => FALSE, 'error' => $e->getMessage()];
        }
    }

    //Silme fonksiyonu
    public function delete($table, $columns, $values, $fileName = null)
    {
        try {

            $stmt = $this->db->prepare("DELETE FROM $table WHERE $columns=?");
            $stmt->execute([htmlspecialchars($values)]);

            if ($stmt->rowCount() > 0) {
                return ['status' => TRUE];
            } else {
                throw new Exception('İşlem Başarısız');
            }
        } catch (Exception $e) {

            return ['status' => FALSE, 'error' => $e->getMessage()];
        }
    }

    //Veri okuma fonksiyonu
    public function read($table, $options = [])
    {
        try {

            if (isset($options['columns_name']) && empty($options['limit'])) {

                $stmt = $this->db->prepare("SELECT * FROM $table order by {$options['columns_name']} {$options['columns_sort']}");
            } else if (isset($options['columns_name']) && isset($options['limit'])) {


                $stmt = $this->db->prepare("SELECT * FROM $table order by {$options['columns_name']} {$options['columns_sort']} limit {$options['limit']}");
            } else {

                $stmt = $this->db->prepare("SELECT * FROM $table");
            }


            $stmt->execute();

            return $stmt;
        } catch (Exception $e) {

            echo $e->getMessage();
            return false;
        }
    }

    //koşullu veri okuma
    public function wread($table, $columns, $values, $options = [])
    {

        try {

            $stmt = $this->db->prepare("SELECT * FROM $table WHERE $columns=?");
            $stmt->execute([htmlspecialchars($values)]);

            return $stmt;
        } catch (Exception $e) {

            return ['status' => FALSE, 'error' => $e->getMessage()];
        }
    }

    //sql sorgusu fonksiyonu
    public function qSql($sql, $options = [])
    {

        try {

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {

            return ['status' => FALSE, 'error' => $e->getMessage()];
        }
    }

    //Kullanıcı girişi yapma
    public function userLogin($users_email, $users_password)
    {

        try {

            $stmt = $this->db->prepare("SELECT * FROM customer WHERE email=? AND passwords=?");
            $stmt->execute([$users_email, md5($users_password)]);

            if ($stmt->rowCount() == 1) {

                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $_SESSION["users"] = [
                    "users_email" => $users_email,
                    "users_name" => $row['name'],
                    "users_surname" => $row['surname'],
                    "users_id" => $row['id']
                ];

                return ['status' => TRUE];
            } else {

                return ['status' => FALSE, 'error' => "Giriş Başarısız"];
            }
        } catch (Exception $e) {

            return ['status' => FALSE, 'error' => $e->getMessage()];
        }
    }

    //Kullanıcı çıkış fonksiyonu
    public function userLogout()
    {
        session_start();
        unset($_SESSION['users']);
        Header("Location:/login");
        exit;
    }
}
