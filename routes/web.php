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

// C2 Server
$router->get('/c2', 'C2Controller@index');
$router->get('/c2/agents', 'C2Controller@listAgents');
$router->post('/c2/register', 'C2Controller@registerAgent');
$router->post('/c2/heartbeat', 'C2Controller@heartbeat');
$router->post('/c2/command', 'C2Controller@sendCommand');
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
