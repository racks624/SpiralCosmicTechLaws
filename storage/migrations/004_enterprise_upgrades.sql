-- Add MITRE ATT&CK fields to findings
ALTER TABLE redteam_findings ADD COLUMN mitre_tactic TEXT;
ALTER TABLE redteam_findings ADD COLUMN mitre_technique TEXT;
ALTER TABLE redteam_findings ADD COLUMN cvss_score REAL DEFAULT 0.0;
ALTER TABLE redteam_findings ADD COLUMN risk_score REAL DEFAULT 0.0;

-- Agent grouping for C2
CREATE TABLE IF NOT EXISTS agent_groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS agent_group_members (
    agent_id INTEGER NOT NULL,
    group_id INTEGER NOT NULL,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES agent_groups(id) ON DELETE CASCADE,
    PRIMARY KEY (agent_id, group_id)
);

-- Audit log for operator actions
CREATE TABLE IF NOT EXISTS audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    operator TEXT NOT NULL,
    action TEXT NOT NULL,
    details TEXT,
    ip TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
