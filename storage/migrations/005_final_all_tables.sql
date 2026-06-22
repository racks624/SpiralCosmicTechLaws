-- Combined Migration: All tables for SpiralCosmicTechLaws
-- This file is idempotent (CREATE IF NOT EXISTS)

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT DEFAULT 'operator'
);

-- RedTeam Targets
CREATE TABLE IF NOT EXISTS redteam_targets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    target_type TEXT CHECK(target_type IN ('ip','domain','cidr','url')) NOT NULL,
    target_value TEXT NOT NULL,
    description TEXT,
    status TEXT CHECK(status IN ('pending','scanning','completed','failed')) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- RedTeam Scans
CREATE TABLE IF NOT EXISTS redteam_scans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    target_id INTEGER NOT NULL,
    scan_type TEXT CHECK(scan_type IN ('port','vulnerability','service','web','full')) NOT NULL,
    parameters TEXT,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    status TEXT CHECK(status IN ('queued','running','completed','failed')) DEFAULT 'queued',
    result_summary TEXT,
    FOREIGN KEY (target_id) REFERENCES redteam_targets(id) ON DELETE CASCADE
);

-- RedTeam Findings (with MITRE fields)
CREATE TABLE IF NOT EXISTS redteam_findings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    scan_id INTEGER NOT NULL,
    severity TEXT CHECK(severity IN ('info','low','medium','high','critical')) NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    cve_id TEXT,
    recommendation TEXT,
    proof TEXT,
    discovered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    mitre_tactic TEXT,
    mitre_technique TEXT,
    cvss_score REAL DEFAULT 0,
    risk_score REAL DEFAULT 0,
    FOREIGN KEY (scan_id) REFERENCES redteam_scans(id) ON DELETE CASCADE
);

-- RedTeam Reports
CREATE TABLE IF NOT EXISTS redteam_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_name TEXT,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    report_data TEXT,
    format TEXT CHECK(format IN ('pdf','html','json')) DEFAULT 'html'
);

-- Agents (C2)
CREATE TABLE IF NOT EXISTS agents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    agent_id TEXT UNIQUE NOT NULL,
    hostname TEXT NOT NULL,
    os TEXT,
    ip_address TEXT,
    last_seen DATETIME,
    status TEXT DEFAULT 'active'
);

-- Tasks (C2 commands)
CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    agent_id INTEGER NOT NULL,
    command TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    output TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    executed_at DATETIME,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE
);

-- Phishing Campaigns
CREATE TABLE IF NOT EXISTS phishing_campaigns (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    status TEXT DEFAULT 'active',
    clicks INTEGER DEFAULT 0,
    emails_sent INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Masked URLs
CREATE TABLE IF NOT EXISTS masked_urls (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    original_url TEXT NOT NULL,
    masked_url TEXT NOT NULL,
    token TEXT UNIQUE NOT NULL,
    clicks INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Agent Groups
CREATE TABLE IF NOT EXISTS agent_groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    description TEXT
);

-- Agent Group Members
CREATE TABLE IF NOT EXISTS agent_group_members (
    agent_id INTEGER NOT NULL,
    group_id INTEGER NOT NULL,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES agent_groups(id) ON DELETE CASCADE,
    PRIMARY KEY (agent_id, group_id)
);

-- Audit Log
CREATE TABLE IF NOT EXISTS audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    operator TEXT NOT NULL,
    action TEXT NOT NULL,
    details TEXT,
    ip TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default operator (password: redteam123) if not exists
INSERT OR IGNORE INTO users (username, password_hash, role) VALUES 
('redteam_operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'redteam');

-- Insert demo agent (optional)
INSERT OR IGNORE INTO agents (agent_id, hostname, os, ip_address, last_seen, status) 
VALUES ('demo-win10', 'DESKTOP-DEMO', 'Windows 10', '192.168.1.100', datetime('now'), 'active');
