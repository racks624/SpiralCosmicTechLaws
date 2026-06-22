-- Add A/B test group field to email_templates
ALTER TABLE email_templates ADD COLUMN ab_group TEXT DEFAULT 'A';
ALTER TABLE email_templates ADD COLUMN subject TEXT;
ALTER TABLE email_templates ADD COLUMN body TEXT;

-- Add conversion tracking fields
ALTER TABLE campaign_tracks ADD COLUMN conversion_value INTEGER DEFAULT 0;

-- Add campaign goals
ALTER TABLE phishing_campaigns ADD COLUMN goal INTEGER DEFAULT 0;
ALTER TABLE phishing_campaigns ADD COLUMN converted_count INTEGER DEFAULT 0;
