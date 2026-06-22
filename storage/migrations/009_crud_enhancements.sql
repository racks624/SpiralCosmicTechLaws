-- Add notes to payloads
ALTER TABLE payloads ADD COLUMN notes TEXT;

-- Add share_token to masked_urls for sharing
ALTER TABLE masked_urls ADD COLUMN share_token TEXT;

-- Add description and tags to agents (optional)
ALTER TABLE agents ADD COLUMN description TEXT;
ALTER TABLE agents ADD COLUMN tags TEXT;

-- Add last_modified to campaigns
ALTER TABLE phishing_campaigns ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP;
