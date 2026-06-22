<?php
// Dashboard
$router->get('/', 'DashboardController@index');

// RedTeam Ops
$router->get('/redteam', 'RedTeamController@index');
$router->post('/redteam/add', 'RedTeamController@addTarget');
$router->post('/redteam/scan', 'RedTeamController@runScan');
$router->get('/redteam/findings', 'RedTeamController@getFindings');
$router->post('/redteam/delete', 'RedTeamController@deleteTarget');
$router->get('/redteam/scans', 'RedTeamController@listScans');

// MITRE Matrix
$router->get('/matrix', 'MatrixController@index');
$router->get('/matrix/data', 'MatrixController@data');

// C2 Server
$router->get('/c2', 'C2Controller@index');
$router->get('/c2/agents', 'C2Controller@listAgents');
$router->post('/c2/register', 'C2Controller@registerAgent');
$router->post('/c2/heartbeat', 'C2Controller@heartbeat');
$router->post('/c2/command', 'C2Controller@sendCommand');
$router->post('/c2/push', 'C2Controller@pushCommand');
$router->post('/c2/task/result', 'C2Controller@taskResult');

// Phishing Campaigns
$router->get('/phishing', 'PhishingController@index');
$router->get('/phishing/campaigns', 'PhishingController@listCampaigns');
$router->post('/phishing/campaign', 'PhishingController@createCampaign');
$router->get('/phishing/track', 'PhishingController@trackClick');
$router->post('/phishing/delete', 'PhishingController@deleteCampaign');

// UrlMasker
$router->get('/urlmasker', 'UrlMaskerController@index');
$router->post('/urlmasker/generate', 'UrlMaskerController@generate');
$router->get('/go/{token}', 'UrlMaskerController@redirect');

// VirtualLab
$router->get('/virtuallab', 'VirtualLabController@index');
$router->post('/virtuallab/spawn', 'VirtualLabController@spawnMachine');

// Payload Generator
$router->get('/payload', 'PayloadController@index');
$router->post('/payload/generate', 'PayloadController@generate');

// Reports (PDF/JSON/HTML)
$router->post('/report/generate', 'ReportController@generate');
$router->get('/report/download', 'ReportController@generate');
$router->get('/api/stats', 'DashboardController@stats');
$router->get('/api/search', 'SearchController@search');
// Payload Generator routes
$router->get('/payload/list', 'PayloadController@list');
$router->get('/payload/download/{filename}', 'PayloadController@download');

// UrlMasker routes
$router->get('/urlmasker/list', 'UrlMaskerController@list');
$router->get('/urlmasker/stats', 'UrlMaskerController@stats');

// VirtualLab routes
$router->post('/virtuallab/stop', 'VirtualLabController@stopMachine');
$router->post('/virtuallab/deploy', 'VirtualLabController@deployPayload');
$router->post('/virtuallab/exec', 'VirtualLabController@runCommand');
$router->get('/virtuallab/list', 'VirtualLabController@list');

// Phishing enhanced routes
$router->post('/phishing/send', 'PhishingController@sendEmails');
$router->post('/phishing/social', 'PhishingController@postSocial');

// Search
$router->get('/api/search', 'SearchController@search');
// RedTeam Ops – new endpoints
$router->post('/redteam/edit', 'RedTeamController@editTarget');
$router->get('/redteam/view', 'RedTeamController@viewTarget');
$router->post('/redteam/upload', 'RedTeamController@uploadTargets');
$router->get('/redteam/export', 'RedTeamController@exportFindings');

// C2 – new endpoints
$router->post('/c2/edit', 'C2Controller@editAgent');
$router->get('/c2/view', 'C2Controller@viewAgent');
$router->post('/c2/delete', 'C2Controller@deleteAgent');
$router->get('/c2/share', 'C2Controller@shareAgent');

// Phishing – new endpoints
$router->post('/phishing/edit', 'PhishingController@editCampaign');
$router->get('/phishing/view', 'PhishingController@viewCampaign');
$router->post('/phishing/duplicate', 'PhishingController@duplicateCampaign');
$router->get('/phishing/export', 'PhishingController@exportCampaign');

// UrlMasker – new endpoints
$router->post('/urlmasker/edit', 'UrlMaskerController@editUrl');
$router->get('/urlmasker/view', 'UrlMaskerController@viewUrl');
$router->post('/urlmasker/delete', 'UrlMaskerController@deleteUrl');
$router->get('/urlmasker/share', 'UrlMaskerController@share');

// VirtualLab – new endpoints
$router->post('/virtuallab/edit', 'VirtualLabController@editVM');
$router->get('/virtuallab/view', 'VirtualLabController@viewVM');
$router->post('/virtuallab/delete', 'VirtualLabController@deleteVM');

// Payload – new endpoints
$router->post('/payload/edit', 'PayloadController@editPayload');
$router->get('/payload/view', 'PayloadController@viewPayload');
$router->post('/payload/delete', 'PayloadController@deletePayload');
// C2 Enterprise routes
$router->post('/c2/schedule', 'C2Controller@scheduleCommand');
$router->get('/c2/scheduled', 'C2Controller@listScheduled');
$router->post('/c2/schedule/cancel', 'C2Controller@cancelScheduled');

$router->get('/c2/groups', 'C2Controller@listGroups');
$router->post('/c2/group/create', 'C2Controller@createGroup');
$router->post('/c2/group/delete', 'C2Controller@deleteGroup');

$router->get('/c2/analytics', 'C2Controller@analytics');

$router->post('/c2/scheduler/run', 'C2Controller@processScheduler');
// Phishing Enterprise routes
$router->get('/phishing/stats', 'PhishingController@getStats');
$router->get('/phishing/track/open', 'PhishingController@trackOpen');
$router->get('/phishing/track/click', 'PhishingController@trackClick');
$router->post('/phishing/send-sms', 'PhishingController@sendSms');
$router->post('/phishing/social-post', 'PhishingController@postSocial');
$router->post('/phishing/send-email', 'PhishingController@sendEmails');
$router->get('/phishing/templates', 'PhishingController@listTemplates'); // to be added if needed
// Phishing Templates
$router->get('/phishing/templates', 'PhishingController@listTemplates');
$router->post('/phishing/template/create', 'PhishingController@createTemplate');
$router->post('/phishing/template/edit', 'PhishingController@editTemplate');
$router->post('/phishing/template/delete', 'PhishingController@deleteTemplate');

// Phishing Social
$router->get('/phishing/social', 'PhishingController@listSocial');
$router->post('/phishing/social/create', 'PhishingController@createSocial');
$router->post('/phishing/social/delete', 'PhishingController@deleteSocial');

// Phishing SMS
$router->get('/phishing/sms', 'PhishingController@listSms');
$router->post('/phishing/send-sms', 'PhishingController@sendSms');

// Phishing Tracking Events
$router->get('/phishing/tracks', 'PhishingController@listTracks');
$router->get('/phishing/track/open', 'PhishingController@trackOpen');
$router->get('/phishing/track/click', 'PhishingController@trackClick');
$router->post('/phishing/track/conversion', 'PhishingController@trackConversion');

// Phishing Analytics
$router->get('/phishing/stats', 'PhishingController@getStats');
$router->get('/phishing/metrics', 'PhishingController@getMetrics');

// Phishing Email Sending
$router->post('/phishing/send-email', 'PhishingController@sendEmails');
// Phishing Campaigns
$router->get('/phishing', 'PhishingController@index');
$router->post('/phishing/campaign', 'PhishingController@createCampaign');
$router->post('/phishing/edit', 'PhishingController@editCampaign');
$router->get('/phishing/view', 'PhishingController@viewCampaign');
$router->get('/phishing/campaigns', 'PhishingController@listCampaigns');
$router->get('/phishing/export', 'PhishingController@exportCampaign');
$router->post('/phishing/duplicate', 'PhishingController@duplicateCampaign');
$router->post('/phishing/delete', 'PhishingController@deleteCampaign');

// Templates
$router->get('/phishing/templates', 'PhishingController@listTemplates');
$router->post('/phishing/template/create', 'PhishingController@createTemplate');
$router->post('/phishing/template/edit', 'PhishingController@editTemplate');
$router->post('/phishing/template/delete', 'PhishingController@deleteTemplate');

// Social
$router->get('/phishing/social', 'PhishingController@listSocial');
$router->post('/phishing/social/create', 'PhishingController@createSocial');
$router->post('/phishing/social/delete', 'PhishingController@deleteSocial');

// SMS
$router->get('/phishing/sms', 'PhishingController@listSms');
$router->post('/phishing/send-sms', 'PhishingController@sendSms');

// Email sending
$router->post('/phishing/send-email', 'PhishingController@sendEmails');

// Tracking
$router->get('/phishing/tracks', 'PhishingController@listTracks');
$router->get('/phishing/track/open', 'PhishingController@trackOpen');
$router->get('/phishing/track/click', 'PhishingController@trackClick');
$router->post('/phishing/track/conversion', 'PhishingController@trackConversion');

// Analytics
$router->get('/phishing/stats', 'PhishingController@getStats');
$router->get('/phishing/metrics', 'PhishingController@getMetrics');
