<?php
// tests/UserTest.php
namespace Tests;

use App\Models\User;
use App\Models\Role;

class UserTest extends TestCase {
    
    public function testCreateAndAuthenticate() {
        $email = 'test_unit_' . uniqid() . '@example.com';
        $password = 'secret123';
        
        // 1. Create a user (as IT Manager)
        $created = User::create(Role::IT_MANAGER, 'Test', 'User', $email, $password);
        $this->assertTrue($created, "User creation failed");
        
        // 2. Try to authenticate
        $user = User::authenticate($email, $password);
        $this->assertTrue($user !== false, "Authentication failed for legitimate user");
        $this->assertEquals($email, $user['email']);
        $this->assertEquals('IT-Manager', $user['role_name']);
    }
    
    public function testEmployeeCannotLogin() {
        $email = 'emp_unit_' . uniqid() . '@example.com';
        $password = 'secret123';
        
        // 1. Create an employee
        User::create(Role::EMPLOYEE, 'Emp', 'User', $email, $password);
        
        // 2. Authentication must fail (as per business logic in User::authenticate)
        $user = User::authenticate($email, $password);
        $this->assertFalse($user, "Employee should not be able to login to the system");
    }

    public function testDeleteUser() {
        $email = 'del_unit_' . uniqid() . '@example.com';
        User::create(Role::IT_MANAGER, 'Del', 'User', $email, 'pass');
        $user = User::authenticate($email, 'pass');
        $id = $user['id'];
        
        $deleted = User::delete($id);
        $this->assertTrue($deleted);
        
        // Should not be able to authenticate anymore
        $auth = User::authenticate($email, 'pass');
        $this->assertFalse($auth);
    }
}
