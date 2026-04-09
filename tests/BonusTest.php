<?php
// tests/BonusTest.php
namespace Tests;

use App\Models\Bonus;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;

class BonusTest extends TestCase {
    
    public function testCreateManualBonus() {
        // Setup: We need a target user (employee) and a creator (manager)
        $empEmail = 'target_bonus_' . uniqid() . '@example.com';
        User::create(Role::EMPLOYEE, 'Target', 'Employee', $empEmail, '');
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$empEmail]);
        $targetId = $stmt->fetchColumn();
        
        $mgrEmail = 'mgr_bonus_' . uniqid() . '@example.com';
        User::create(Role::IT_MANAGER, 'Mgr', 'Creator', $mgrEmail, 'pass');
        $stmt->execute([$mgrEmail]);
        $creatorId = $stmt->fetchColumn();
        
        // 1. Create manual bonus (The fix test!)
        $result = Bonus::createManual($targetId, 150.75, "Test manual bonus", $creatorId);
        $this->assertTrue($result, "Manual bonus creation failed - this might indicate the DB constraint issue still exists or another error occurred");
        
        // 2. Refresh details to verify
        $bonuses = Bonus::getAllWithDetails();
        $found = false;
        foreach ($bonuses as $b) {
            if ($b['comment'] === "Test manual bonus" && (float)$b['amount'] === 150.75) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Created manual bonus was not found in details list");
    }

    public function testProcessApproval() {
        // Create a bonus first
        $empEmail = 'target_app_' . uniqid() . '@example.com';
        User::create(Role::EMPLOYEE, 'Target', 'App', $empEmail, '');
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$empEmail]);
        $targetId = $stmt->fetchColumn();
        
        $mgrEmail = 'mgr_app_' . uniqid() . '@example.com';
        User::create(Role::IT_MANAGER, 'Mgr', 'App', $mgrEmail, 'pass');
        $stmt->execute([$mgrEmail]);
        $creatorId = $stmt->fetchColumn();
        
        Bonus::createManual($targetId, 100, "Approval Test", $creatorId);
        
        $stmt = $this->db->prepare("SELECT id FROM bonuses WHERE comment = ?");
        $stmt->execute(["Approval Test"]);
        $bonusId = $stmt->fetchColumn();
        
        // Approve by another manager (e.g. IT Manager)
        $approverEmail = 'app_mgr_' . uniqid() . '@example.com';
        User::create(Role::IT_MANAGER, 'Approver', 'System', $approverEmail, 'pass');
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$approverEmail]);
        $approverId = $stmt->fetchColumn();
        
        $ok = Bonus::processApproval($bonusId, $approverId, true, "Approved in test");
        $this->assertTrue($ok);
        
        // Check if it's in fully approved list
        $approved = Bonus::getFullyApproved();
        $found = false;
        foreach ($approved as $b) {
            if ($b['id'] == $bonusId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Bonus was not found in fully approved list after approval");
    }
}
