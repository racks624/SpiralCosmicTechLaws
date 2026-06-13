<?php
namespace App\Core;

use PDO;
use PDOException;

abstract class Model
{
    protected static PDO $db;
    protected static string $table;
    protected static array $fillable = [];
    protected static array $guarded = ['id'];
    protected static string $primaryKey = 'id';
    public static bool $timestamps = true;

    private static function connect(): void
    {
        if (isset(self::$db)) return;

        $config = require ROOT_PATH . '/config/database.php';
        $connection = $config['connections'][$config['default']];
        try {
            if ($config['default'] === 'sqlite') {
                self::$db = new PDO("sqlite:" . $connection['database']);
            } else {
                self::$db = new PDO(
                    "mysql:host={$connection['host']};port={$connection['port']};dbname={$connection['database']};charset={$connection['charset']}",
                    $connection['username'],
                    $connection['password']
                );
            }
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        self::connect();
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function lastInsertId(): int
    {
        return (int) self::$db->lastInsertId();
    }

    public static function all(): array
    {
        $stmt = self::query("SELECT * FROM " . static::$table . " ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $stmt = self::query("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ? LIMIT 1", [$id]);
        return $stmt->fetch();
    }

    public static function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $stmt = self::query("SELECT * FROM " . static::$table . " WHERE {$column} {$operator} ?", [$value]);
        return $stmt->fetchAll();
    }

    public static function create(array $data)
    {
        // Apply fillable/guarded
        if (!empty(static::$fillable)) {
            $data = array_intersect_key($data, array_flip(static::$fillable));
        } else {
            foreach (static::$guarded as $guard) {
                unset($data[$guard]);
            }
        }
        if (static::$timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})";
        self::query($sql, $data);
        return self::lastInsertId();
    }

    public static function update($id, array $data)
    {
        if (static::$timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = :{$key}";
        }
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $sets) . " WHERE " . static::$primaryKey . " = :id";
        $data['id'] = $id;
        self::query($sql, $data);
        return self::find($id);
    }

    public static function delete($id)
    {
        self::query("DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?", [$id]);
    }
}
