CREATE TABLE IF NOT EXISTS virtual_machines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    machine_id TEXT UNIQUE NOT NULL,
    os TEXT NOT NULL,
    status TEXT DEFAULT 'running',
    ip TEXT,
    config TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
