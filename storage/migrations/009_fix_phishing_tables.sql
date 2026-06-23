-- Ensure sms_logs table exists and has correct columns
CREATE TABLE IF NOT EXISTS sms_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    phone TEXT,
    message TEXT,
    status TEXT DEFAULT 'queued',
    sent_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Ensure email_templates table exists and has correct columns
CREATE TABLE IF NOT EXISTS email_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    name TEXT,
    subject TEXT,
    body TEXT,
    attachments TEXT,
    ab_group TEXT DEFAULT 'A',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Ensure social_posts table exists and has correct columns
CREATE TABLE IF NOT EXISTS social_posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    platform TEXT,
    content TEXT,
    image_url TEXT,
    scheduled_at DATETIME,
    status TEXT DEFAULT 'queued',
    posted_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Ensure campaign_metrics table exists
CREATE TABLE IF NOT EXISTS campaign_metrics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    metric_type TEXT,
    value INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Ensure campaign_tracks table exists
CREATE TABLE IF NOT EXISTS campaign_tracks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    track_type TEXT,
    ip TEXT,
    user_agent TEXT,
    device_type TEXT,
    location TEXT,
    referrer TEXT,
    conversion_value INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
