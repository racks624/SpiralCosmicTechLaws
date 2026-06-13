-- Users table (required for authentication)
CREATE TABLE IF NOT EXISTS "users" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "username" TEXT UNIQUE NOT NULL,
    "password_hash" TEXT NOT NULL,
    "role" TEXT DEFAULT 'operator'
);

-- RedTeam Ops tables
CREATE TABLE IF NOT EXISTS "redteam_targets" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT NOT NULL,
    "target_type" TEXT CHECK(target_type IN ('ip','domain','cidr','url')) NOT NULL,
    "target_value" TEXT NOT NULL,
    "description" TEXT,
    "status" TEXT CHECK(status IN ('pending','scanning','completed','failed')) DEFAULT 'pending',
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "redteam_scans" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "target_id" INTEGER NOT NULL,
    "scan_type" TEXT CHECK(scan_type IN ('port','vulnerability','service','web','full')) NOT NULL,
    "parameters" TEXT,
    "started_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "completed_at" DATETIME,
    "status" TEXT CHECK(status IN ('queued','running','completed','failed')) DEFAULT 'queued',
    "result_summary" TEXT,
    FOREIGN KEY ("target_id") REFERENCES "redteam_targets"("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "redteam_findings" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "scan_id" INTEGER NOT NULL,
    "severity" TEXT CHECK(severity IN ('info','low','medium','high','critical')) NOT NULL,
    "title" TEXT NOT NULL,
    "description" TEXT,
    "cve_id" TEXT,
    "recommendation" TEXT,
    "proof" TEXT,
    "discovered_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY ("scan_id") REFERENCES "redteam_scans"("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "redteam_reports" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "campaign_name" TEXT,
    "generated_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "report_data" TEXT,
    "format" TEXT CHECK(format IN ('pdf','html','json')) DEFAULT 'html'
);

-- Insert default operator (password: redteam123)
INSERT OR IGNORE INTO "users" ("username", "password_hash", "role") VALUES 
('redteam_operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'redteam');
