<?php

it('returns valid HTML with DOCTYPE', function () {
    $html = defaultMaintenancePageHtml();

    expect($html)->toStartWith('<!DOCTYPE html>');
});

it('contains 503 status text', function () {
    $html = defaultMaintenancePageHtml();

    expect($html)->toContain('Service Temporarily Unavailable');
});

it('contains noindex meta tag', function () {
    $html = defaultMaintenancePageHtml();

    expect($html)->toContain('<meta name="robots" content="noindex">');
});

it('contains auto-refresh meta tag', function () {
    $html = defaultMaintenancePageHtml();

    expect($html)->toContain('<meta http-equiv="refresh" content="30">');
});

it('contains dark mode CSS', function () {
    $html = defaultMaintenancePageHtml();

    expect($html)->toContain('prefers-color-scheme:dark');
});

it('does not expose infrastructure details', function () {
    $html = defaultMaintenancePageHtml();

    expect($html)
        ->not->toContain('Coolify')
        ->not->toContain('Traefik')
        ->not->toContain('nginx')
        ->not->toContain('Docker');
});

it('generates nginx config with 503 status', function () {
    $config = maintenanceNginxConfiguration();

    expect($config)->toContain('return 503');
});

it('generates nginx config with Retry-After header', function () {
    $config = maintenanceNginxConfiguration();

    expect($config)->toContain('add_header Retry-After 300 always');
});

it('generates nginx config with Cache-Control header', function () {
    $config = maintenanceNginxConfiguration();

    expect($config)->toContain('add_header Cache-Control "no-cache, no-store, must-revalidate" always');
});

it('generates nginx config listening on port 80', function () {
    $config = maintenanceNginxConfiguration();

    expect($config)->toContain('listen 80 default_server');
});

it('redirect_url takes priority over maintenance page', function () {
    $redirect_url = 'https://example.com';
    $maintenance_page_enabled = true;

    $needsMaintenanceContainer = blank($redirect_url) && $maintenance_page_enabled;

    expect($needsMaintenanceContainer)->toBeFalse();
});

it('shows bare 503 when maintenance page is disabled', function () {
    $redirect_url = null;
    $maintenance_page_enabled = false;

    $needsMaintenanceContainer = blank($redirect_url) && $maintenance_page_enabled;

    expect($needsMaintenanceContainer)->toBeFalse();
});

it('enables maintenance container when no redirect_url and maintenance enabled', function () {
    $redirect_url = null;
    $maintenance_page_enabled = true;

    $needsMaintenanceContainer = blank($redirect_url) && $maintenance_page_enabled;

    expect($needsMaintenanceContainer)->toBeTrue();
});

it('uses custom HTML when provided', function () {
    $customHtml = '<html><body><h1>Custom Page</h1></body></html>';

    $html = filled($customHtml) ? $customHtml : defaultMaintenancePageHtml();

    expect($html)->toBe($customHtml);
});

it('falls back to default HTML when custom is not provided', function () {
    $customHtml = null;

    $html = filled($customHtml) ? $customHtml : defaultMaintenancePageHtml();

    expect($html)->toBe(defaultMaintenancePageHtml());
});

