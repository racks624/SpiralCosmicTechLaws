-- Add enterprise fields
ALTER TABLE phishing_campaigns ADD COLUMN platform TEXT DEFAULT 'email';
ALTER TABLE phishing_campaigns ADD COLUMN scheduled_at DATETIME;
ALTER TABLE phishing_campaigns ADD COLUMN sent_count INTEGER DEFAULT 0;
ALTER TABLE phishing_campaigns ADD COLUMN opened_count INTEGER DEFAULT 0;
ALTER TABLE phishing_campaigns ADD COLUMN clicked_count INTEGER DEFAULT 0;
ALTER TABLE phishing_campaigns ADD COLUMN converted_count INTEGER DEFAULT 0;
ALTER TABLE phishing_campaigns ADD COLUMN from_name TEXT;
ALTER TABLE phishing_campaigns ADD COLUMN from_email TEXT;
ALTER TABLE phishing_campaigns ADD COLUMN reply_to TEXT;

-- Email templates
CREATE TABLE IF NOT EXISTS email_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    name TEXT,
    subject TEXT,
    body TEXT,
    attachments TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES phishing_campaigns(id) ON DELETE CASCADE
);

-- Social posts
CREATE TABLE IF NOT EXISTS social_posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    platform TEXT NOT NULL,
    content TEXT,
    image_url TEXT,
    scheduled_at DATETIME,
    status TEXT DEFAULT 'pending',
    posted_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES phishing_campaigns(id) ON DELETE CASCADE
);

-- SMS logs
CREATE TABLE IF NOT EXISTS sms_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    phone TEXT,
    message TEXT,
    status TEXT DEFAULT 'queued',
    sent_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES phishing_campaigns(id) ON DELETE CASCADE
);

-- Campaign metrics (for analytics)
CREATE TABLE IF NOT EXISTS campaign_metrics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    metric_type TEXT,
    value INTEGER DEFAULT 0,
    FOREIGN KEY (campaign_id) REFERENCES phishing_campaigns(id) ON DELETE CASCADE
);

-- Track table for opens/clicks (with device fingerprint)
CREATE TABLE IF NOT EXISTS campaign_tracks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER,
    track_type TEXT CHECK(track_type IN ('open','click','convert')),
    ip TEXT,
    user_agent TEXT,
    device_type TEXT,
    location TEXT,
    referrer TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES phishing_campaigns(id) ON DELETE CASCADE
);
