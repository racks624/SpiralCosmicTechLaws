-- Add fields to agents
ALTER TABLE agents ADD COLUMN jitter INTEGER DEFAULT 0;
ALTER TABLE agents ADD COLUMN group_id INTEGER;
ALTER TABLE agents ADD COLUMN last_heartbeat DATETIME;
ALTER TABLE agents ADD COLUMN uptime INTEGER DEFAULT 0;
ALTER TABLE agents ADD COLUMN protocol TEXT DEFAULT 'https';

-- Agent groups table
CREATE TABLE IF NOT EXISTS agent_groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Scheduled tasks
CREATE TABLE IF NOT EXISTS scheduled_tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    agent_id INTEGER NOT NULL,
    command TEXT NOT NULL,
    scheduled_at DATETIME,
    status TEXT DEFAULT 'pending',
    priority INTEGER DEFAULT 0,
    retry_count INTEGER DEFAULT 0,
    max_retries INTEGER DEFAULT 3,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE
);

-- Audit log (enhanced)
CREATE TABLE IF NOT EXISTS c2_audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    operator TEXT NOT NULL,
    action TEXT NOT NULL,
    target_type TEXT,
    target_id INTEGER,
    details TEXT,
    ip TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create default group
INSERT OR IGNORE INTO agent_groups (name, description) VALUES ('Default', 'Default agent group');
