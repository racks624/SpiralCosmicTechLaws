<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['username', 'password_hash', 'role'];
    protected static array $guarded = ['id'];

    public static function findByUsername($username)
    {
        $stmt = self::query("SELECT * FROM users WHERE username = ? LIMIT 1", [$username]);
        return $stmt->fetch();
    }

    public static function createUser($username, $password, $role = 'operator')
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        return self::create(['username' => $username, 'password_hash' => $hash, 'role' => $role]);
    }

    public static function verifyPassword($user, $password)
    {
        return password_verify($password, $user['password_hash']);
    }
}
