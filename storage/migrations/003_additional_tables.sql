-- Agents table for C2
CREATE TABLE IF NOT EXISTS "agents" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "agent_id" TEXT UNIQUE NOT NULL,
    "hostname" TEXT NOT NULL,
    "os" TEXT,
    "ip_address" TEXT,
    "last_seen" DATETIME,
    "status" TEXT DEFAULT 'active'
);

-- Tasks table for commands
CREATE TABLE IF NOT EXISTS "tasks" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "agent_id" INTEGER NOT NULL,
    "command" TEXT NOT NULL,
    "status" TEXT DEFAULT 'pending',
    "output" TEXT,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "executed_at" DATETIME,
    FOREIGN KEY ("agent_id") REFERENCES "agents"("id") ON DELETE CASCADE
);

-- Phishing campaigns
CREATE TABLE IF NOT EXISTS "phishing_campaigns" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT NOT NULL,
    "status" TEXT DEFAULT 'active',
    "clicks" INTEGER DEFAULT 0,
    "emails_sent" INTEGER DEFAULT 0,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert demo data (optional)
INSERT OR IGNORE INTO "agents" ("agent_id", "hostname", "os", "ip_address", "last_seen", "status") 
VALUES ('demo-win10', 'DESKTOP-DEMO', 'Windows 10', '192.168.1.100', datetime('now'), 'active');
