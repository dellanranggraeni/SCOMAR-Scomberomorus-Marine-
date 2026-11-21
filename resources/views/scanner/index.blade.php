@extends('layouts.app')

@section('title', 'Pemindai Kesegaran Ikan')
@section('page-title', 'Pemindai Kesegaran Ikan')
@section('page-breadcrumb', 'SCOMAR / Pemindai Kesegaran Ikan')

@push('styles')
<style>

/*SCOMAR FISH SCANNER*/

/* ---------- Page Header ---------- */
.scanner-page-header {
    background: linear-gradient(135deg, var(--deep-navy) 0%, #1a2d40 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
}

animation: scannerHeaderEnter 0.8s ease-out both;

/* ---------- Header Animation ---------- */
@keyframes scannerHeaderEnter {
    from {
        opacity: 0;
        transform: translateY(22px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Lingkaran dekoratif bergerak perlahan */
.scanner-page-header::before {
    animation: scannerCircleMove 9s ease-in-out infinite alternate;
}

.scanner-page-header::after {
    animation: scannerCircleMove 12s ease-in-out infinite alternate-reverse;
}

@keyframes scannerCircleMove {
    from {
        transform: translate(0, 0) scale(1);
    }

    to {
        transform: translate(-15px, 12px) scale(1.08);
    }
}

/* Mengikuti pengaturan reduced motion */
@media (prefers-reduced-motion: reduce) {
    .scanner-page-header,
    .scanner-page-header::before,
    .scanner-page-header::after {
        animation: none !important;
    }
}

.scanner-page-header::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(165,184,208,0.12);
}
.scanner-page-header::after {
    content: '';
    position: absolute;
    bottom: -30px; left: 60px;
    width: 120px; height: 120px;
    border-radius: 50%;
    background: rgba(115,52,58,0.18);
}
.scanner-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(165,184,208,0.2);
    border: 1px solid rgba(165,184,208,0.35);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    color: var(--cashmere-blue);
    text-transform: uppercase;
    margin-bottom: 10px;
}
.scanner-header-title {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 6px;
    color: #fff;
}
.scanner-header-sub {
    font-size: 13px;
    color: rgba(255,255,255,0.7);
    margin: 0;
}

/* ---------- Mode Toggle ---------- */
.mode-toggle-wrapper {
    display: flex;
    gap: 10px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.mode-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    border: 2px solid transparent;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--bg-card);
    color: var(--text-secondary);
    box-shadow: 0 2px 8px rgba(36,54,75,0.08);
}
.mode-btn.active {
    background: var(--merlot);
    color: #fff;
    border-color: var(--merlot);
    box-shadow: 0 4px 14px rgba(115,52,58,0.3);
}
.mode-btn:not(.active):hover {
    border-color: var(--cashmere-blue);
    color: var(--deep-navy);
}
.mode-btn svg { flex-shrink: 0; }

/* ---------- Main Grid ---------- */
.scanner-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.85fr);
    gap: 24px;
    margin-bottom: 24px;
    width: 100%;
    align-items: start;
}
.scanner-left-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
    min-width: 0;
    width: 100%;
}
.scanner-right-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
    min-width: 0;
    width: 100%;
}
.scanner-submit-card .submit-status {
    margin-bottom: 16px;
}
.scanner-submit-card .submit-status-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}
.scanner-submit-card .photo-status-dots {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.scanner-submit-card .btn-submit-full {
    width: 100%;
    justify-content: center;
    padding: 13px 20px;
    font-size: 14.5px;
}
.scanner-panel {
    width: 100%;
    min-width: 0;
}
@media (max-width: 900px) {
    .scanner-grid { grid-template-columns: 1fr; }
}

/* ---------- Panel Card ---------- */
.scanner-panel {
    background: var(--bg-card);
    border-radius: 16px;
    border: 1px solid var(--border-subtle);
    box-shadow: 0 2px 12px rgba(36,54,75,0.07);
    overflow: hidden;
    box-sizing: border-box;
}
.panel-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-subtle);
    background: var(--bg-surface);
}
.panel-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.panel-icon.merlot { background: rgba(115,52,58,0.1); color: var(--merlot); }
.panel-icon.navy   { background: rgba(36,54,75,0.1);  color: var(--deep-navy); }
.panel-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.panel-sub   { font-size: 11px; color: var(--text-secondary); margin-top: 1px; }
.panel-body  { padding: 20px; }

/* ---------- Scanner Guide Panel ---------- */
.scanner-side-panel {
    height: fit-content;
}
.scanner-side-panel .panel-body {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.scanner-guide-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    background: var(--bg-body);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
}
.scanner-guide-number {
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: rgba(115, 52, 58, 0.1);
    color: var(--merlot);
    font-size: 12px;
    font-weight: 800;
}
.scanner-guide-title {
    color: var(--text-primary);
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 5px;
}
.scanner-guide-item p,
.scanner-guide-warning p,
.scanner-guide-info p {
    font-size: 12px;
    line-height: 1.7;
    color: var(--text-secondary);
    margin: 0;
}
.scanner-guide-warning {
    padding: 15px;
    border-radius: 12px;
    background: rgba(115, 52, 58, 0.07);
    border: 1px solid rgba(115, 52, 58, 0.2);
}
.scanner-guide-warning strong {
    display: block;
    color: var(--merlot);
    font-size: 13px;
    margin-bottom: 5px;
}
.scanner-guide-info {
    padding: 15px;
    border-radius: 12px;
    background: rgba(165, 184, 208, 0.12);
    border: 1px solid rgba(165, 184, 208, 0.3);
}
.scanner-guide-info .panel-title {
    margin-bottom: 7px;
}

/* ---------- Camera Panel ---------- */
#camera-panel { display: none; }
#camera-panel.active { display: block; }

/* Step Indicator */
.step-indicator {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 18px;
    overflow-x: auto;
}
.step-item {
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    min-width: 0;
}
.step-circle {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--bg-body);
    border: 2px solid var(--border-subtle);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
    color: var(--text-secondary);
    flex-shrink: 0;
    transition: all 0.2s;
}
.step-circle.active {
    background: var(--merlot);
    border-color: var(--merlot);
    color: #fff;
    box-shadow: 0 2px 8px rgba(115,52,58,0.35);
}
.step-circle.done {
    background: var(--deep-navy);
    border-color: var(--deep-navy);
    color: #fff;
}
.step-label { font-size: 11px; font-weight: 600; color: var(--text-secondary); min-width: 0; }
.step-label.active { color: var(--merlot); }
.step-label.done   { color: var(--deep-navy); }
.step-connector {
    flex: 1;
    height: 2px;
    background: var(--border-subtle);
    margin: 0 6px;
    min-width: 10px;
    max-width: 32px;
}

/* Video Container */
.video-container {
    position: relative;
    width: 100%;
    aspect-ratio: 4/3;
    background: #0d1b2a;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 14px;
}
#camera-video {
    width: 100%; height: 100%;
    object-fit: cover;
    display: none;
}
.video-placeholder {
    width: 100%; height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    color: rgba(165,184,208,0.5);
    gap: 10px;
}
.video-placeholder svg { width: 48px; height: 48px; }
.video-placeholder span { font-size: 13px; font-weight: 500; }
#canvas-capture { display: none; }

/* Camera Controls */
.camera-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.cam-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 9px 16px;
    border-radius: 9px;
    border: none;
    font-size: 12px; font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.cam-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.cam-btn-primary   { background: var(--deep-navy); color: #fff; }
.cam-btn-primary:not(:disabled):hover   { background: #1a2d40; }
.cam-btn-capture   { background: var(--merlot); color: #fff; }
.cam-btn-capture:not(:disabled):hover   { background: #5c2a2f; }
.cam-btn-secondary { background: var(--bg-body); color: var(--text-primary); border: 1px solid var(--border-subtle); }
.cam-btn-secondary:not(:disabled):hover { background: rgba(165,184,208,0.2); }
.cam-btn-danger    { background: rgba(115,52,58,0.1); color: var(--merlot); border: 1px solid rgba(115,52,58,0.2); }
.cam-btn-danger:not(:disabled):hover    { background: rgba(115,52,58,0.18); }

/* Photo Previews */
.photo-previews {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 4px;
}
.preview-item {
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 1;
    background: var(--bg-body);
    border: 2px solid var(--border-subtle);
    position: relative;
    transition: border-color 0.2s;
}
.preview-item.has-photo { border-color: var(--deep-navy); }
.preview-item.active-step { border-color: var(--merlot); box-shadow: 0 0 0 3px rgba(115,52,58,0.15); }
.preview-img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: none;
}
.preview-placeholder {
    width: 100%; height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 4px;
    color: var(--text-muted);
    font-size: 10px; font-weight: 500;
    padding: 8px;
    text-align: center;
}
.preview-label {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: rgba(36,54,75,0.75);
    color: #fff;
    font-size: 10px; font-weight: 600;
    text-align: center;
    padding: 3px 4px;
    backdrop-filter: blur(4px);
}
.preview-check {
    position: absolute;
    top: 4px; right: 4px;
    width: 20px; height: 20px;
    background: var(--deep-navy);
    border-radius: 50%;
    display: none;
    align-items: center; justify-content: center;
}
.preview-check.show { display: flex; }

/* Camera Error */
.camera-error {
    display: none;
    align-items: center; gap: 10px;
    background: rgba(115,52,58,0.08);
    border: 1px solid rgba(115,52,58,0.25);
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 12px;
    font-size: 13px;
    color: var(--merlot);
}
.camera-error.show { display: flex; }

/* ---------- Upload Panel ---------- */
.upload-fields { display: flex; flex-direction: column; gap: 18px; }
.upload-field-label {
    font-size: 12px; font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
    display: flex; align-items: center; gap: 6px;
}
.upload-area {
    border: 2px dashed var(--border-subtle);
    border-radius: 10px;
    padding: 14px;
    display: flex; align-items: center; gap: 12px;
    transition: border-color 0.2s;
    cursor: pointer;
    background: var(--bg-body);
}
.upload-area:hover { border-color: var(--cashmere-blue); }
.upload-area.has-file { border-color: var(--deep-navy); background: rgba(36,54,75,0.04); }
.upload-preview-thumb {
    width: 56px; height: 56px;
    border-radius: 8px;
    object-fit: cover;
    display: none;
    flex-shrink: 0;
}
.upload-preview-thumb.show { display: block; }
.upload-icon-placeholder {
    width: 56px; height: 56px;
    border-radius: 8px;
    background: rgba(165,184,208,0.2);
    display: flex; align-items: center; justify-content: center;
    color: var(--cashmere-blue);
    flex-shrink: 0;
}
.upload-area.has-file .upload-icon-placeholder { display: none; }
.upload-info { flex: 1; min-width: 0; }
.upload-info-name { font-size: 12px; font-weight: 600; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.upload-info-hint { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.file-input-hidden { display: none !important; }

/* ---------- Submit Section (now in right column) ---------- */
.submit-section {
    background: var(--bg-card);
    border-radius: 16px;
    border: 1px solid var(--border-subtle);
    overflow: hidden;
    box-sizing: border-box;
    margin-bottom: 0;
}
.submit-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-subtle);
    background: var(--bg-surface);
}
.submit-section-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.submit-status {
    flex: 1;
    min-width: 0;
}
.submit-status-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.photo-status-dots {
    display: flex; gap: 8px; margin-top: 6px; flex-wrap: wrap;
}
.photo-dot {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 500; color: var(--text-secondary);
}
.dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--border-subtle);
    transition: background 0.2s;
}
.dot.ready { background: var(--deep-navy); }

.btn-submit {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 28px;
    background: var(--merlot);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px; font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    box-shadow: 0 4px 14px rgba(115,52,58,0.3);
}
.btn-submit:hover { background: #5c2a2f; transform: translateY(-1px); }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.btn-submit-loading { display: none; }
.btn-submit.loading .btn-submit-text { display: none; }
.btn-submit.loading .btn-submit-loading { display: flex; align-items: center; gap: 8px; }

/* Validation Errors */
.validation-errors {
    background: rgba(115,52,58,0.07);
    border: 1px solid rgba(115,52,58,0.2);
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 20px;
}
.validation-errors-title {
    font-size: 13px; font-weight: 700; color: var(--merlot);
    margin-bottom: 8px;
    display: flex; align-items: center; gap: 6px;
}
.validation-errors ul { margin: 0; padding-left: 18px; }
.validation-errors li { font-size: 12px; color: var(--merlot); margin-bottom: 3px; }

/* ---------- AI Image Validation Result ---------- */

.validation-status-panel {
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 20px;
    border: 1px solid;
}

.validation-status-panel.error {
    background: rgba(115,52,58,0.07);
    border-color: rgba(115,52,58,0.25);
}

.validation-status-panel.success {
    background: rgba(36,54,75,0.06);
    border-color: rgba(36,54,75,0.2);
}

.validation-status-title {
    font-size: 14px;
    font-weight: 800;
    margin-bottom: 7px;
}

.validation-status-panel.error .validation-status-title {
    color: var(--merlot);
}

.validation-status-panel.success .validation-status-title {
    color: var(--deep-navy);
}

.validation-status-message {
    font-size: 12px;
    line-height: 1.7;
    color: var(--text-secondary);
    margin-bottom: 12px;
}

.validation-detail-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.validation-detail-item {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 9px;
    padding: 12px 14px;
}

.validation-detail-name {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary);
    overflow-wrap: anywhere;
    margin-bottom: 8px;
}

.validation-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.validation-detail-label {
    font-size: 10px;
    color: var(--text-secondary);
    margin-bottom: 3px;
}

.validation-detail-value {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary);
    overflow-wrap: anywhere;
}

.validation-value-danger {
    color: var(--merlot);
}

.validation-value-success {
    color: #237447;
}

/* ---------- Results Section ---------- */
.results-section {
    background: var(--bg-card);
    border-radius: 16px;
    border: 1px solid var(--border-subtle);
    overflow: hidden;
    margin-bottom: 24px;
    box-sizing: border-box;
}
.results-header {
    padding: 18px 24px;
    background: linear-gradient(135deg, var(--deep-navy), #1a2d40);
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
}
.results-header-left { display: flex; align-items: center; gap: 10px; }
.results-header-title { font-size: 15px; font-weight: 800; color: #fff; }
.results-header-sub { font-size: 12px; color: rgba(255,255,255,0.65); }
.btn-scan-again {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 8px;
    color: #fff;
    font-size: 12px; font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
}
.btn-scan-again:hover { background: rgba(255,255,255,0.2); color: #fff; }
.results-body { padding: 20px 24px; }
.results-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}
@media (max-width: 700px) {
    .results-grid { grid-template-columns: 1fr; }
}
.result-card {
    border-radius: 12px;
    padding: 16px;
    border: 1px solid var(--border-subtle);
    background: var(--bg-body);
}
.result-card-label {
    font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    color: var(--text-secondary);
    margin-bottom: 10px;
    display: flex; align-items: center; gap: 5px;
}
.result-card-value { font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; }
.result-card-sub { font-size: 11px; color: var(--text-secondary); }
.result-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px; font-weight: 700;
    margin-top: 8px;
}
.badge-segar   { background: rgba(36,54,75,0.1);  color: var(--deep-navy);  }
.badge-kurang  { background: rgba(165,184,208,0.3); color: #2a4a6b; }
.badge-tidak   { background: rgba(115,52,58,0.1);  color: var(--merlot); }
.overall-score-card {
    border-radius: 14px;
    padding: 20px 24px;
    background: linear-gradient(135deg, var(--deep-navy), #1a2d40);
    color: #fff;
    display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
}
.score-circle {
    width: 72px; height: 72px;
    border-radius: 50%;
    border: 3px solid rgba(165,184,208,0.4);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    flex-shrink: 0;
}
.score-num  { font-size: 22px; font-weight: 800; color: #fff; line-height: 1; }
.score-denom { font-size: 11px; color: rgba(255,255,255,0.6); }
.score-info-title { font-size: 15px; font-weight: 800; }
.score-info-sub   { font-size: 12px; color: rgba(255,255,255,0.7); margin-top: 4px; }
.score-status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px; font-weight: 700;
    margin-top: 8px;
}
.score-status-fresh   { background: rgba(165,184,208,0.2); border: 1px solid rgba(165,184,208,0.4); color: var(--cashmere-blue); }
.score-status-kurang  { background: rgba(165,184,208,0.12); border: 1px solid rgba(165,184,208,0.25); color: rgba(165,184,208,0.8); }
.score-status-tidak   { background: rgba(115,52,58,0.3); border: 1px solid rgba(115,52,58,0.5); color: #e8a0a4; }

/* Loading Spinner */
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.spinner {
    width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

/* Info box */
.info-box {
    display: flex; align-items: flex-start; gap: 10px;
    background: rgba(165,184,208,0.12);
    border: 1px solid rgba(165,184,208,0.3);
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 12px; color: var(--text-secondary);
}
.info-box svg { flex-shrink: 0; color: var(--cashmere-blue); margin-top: 1px; }

/* â”€â”€ RESPONSIVE SCANNER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@media (max-width: 640px) {
    .scanner-grid {
        grid-template-columns: 1fr;
    }
    .panel-header {
        padding: 12px 14px;
    }
    .results-body {
        padding: 14px 14px;
    }
    .results-grid {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .step-label {
        display: none;
    }
}

@media (max-width: 480px) {
    .results-grid {
        grid-template-columns: 1fr;
    }
    .result-card-value {
        font-size: 15px;
    }
    .info-box {
        font-size: 11.5px;
        padding: 10px 12px;
    }
    .panel-title {
        font-size: 13px;
    }
}
</style>
@endpush

@section('content')

{{-- ============================================================
     PAGE HEADER
============================================================ --}}
<div class="scanner-page-header">
    <div style="position: relative; z-index: 1;">
        <div class="scanner-header-badge">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            AI Fish Scanner
        </div>
        <h1 class="scanner-header-title"> Pemindai Tingkat Kesegaran dan Identifikasi Genus Ikan</h1>
        <p class="scanner-header-sub">Identifikasi genus dan klasifikasi kesegaran ikan menggunakan kecerdasan buatan berbasis foto tubuh, mata, dan insang.</p>
    </div>
</div>


{{-- ============================================================
     VALIDATION ERRORS AND AI IMAGE VALIDATION
============================================================ --}}

@if (!empty($validationResult) && is_array($validationResult))

    @php
        $failedImages = $validationResult['gambar_tidak_sesuai'] ?? [];

        $validationMessage = $validationResult['pesan']
            ?? 'Gambar tidak sesuai dengan bagian ikan yang diminta.';

        $classLabels = [
            'bukan_ikan' => 'Bukan Ikan',
            'tubuh_ikan' => 'Tubuh Ikan',
            'mata_ikan' => 'Mata Ikan',
            'insang_ikan' => 'Insang Ikan',
        ];
    @endphp

    <div class="validation-status-panel error">

        <div class="validation-status-title">
            Gambar Tidak Sesuai
        </div>

        <div class="validation-status-message">
            {{ $validationMessage }}
            Silakan periksa gambar dan unggah ulang.
        </div>

        @if (!empty($failedImages))

            <div class="validation-detail-list">

                @foreach ($failedImages as $item)

                    @php
                        $expected = $item['kelas_yang_diharapkan'] ?? '-';
                        $predicted = $item['prediksi'] ?? '-';

                        $confidence = $item['confidence_persen']
                            ?? (isset($item['confidence'])
                                ? (float) $item['confidence'] * 100
                                : null);
                    @endphp

                    <div class="validation-detail-item">

                        <div class="validation-detail-name">
                            Nama file:
                            {{ $item['nama_file'] ?? 'Tidak diketahui' }}
                        </div>

                        <div class="validation-detail-grid">

                            <div>
                                <div class="validation-detail-label">
                                    Bagian yang diminta
                                </div>
                                <div class="validation-detail-value">
                                    {{ $classLabels[$expected] ?? $expected }}
                                </div>
                            </div>

                            <div>
                                <div class="validation-detail-label">
                                    Hasil deteksi AI
                                </div>
                                <div class="validation-detail-value validation-value-danger">
                                    {{ $classLabels[$predicted] ?? $predicted }}
                                </div>
                            </div>

                            <div>
                                <div class="validation-detail-label">
                                    Confidence
                                </div>
                                <div class="validation-detail-value">
                                    {{ $confidence !== null
                                        ? number_format((float) $confidence, 2, ',', '.') . '%'
                                        : 'Tidak tersedia' }}
                                </div>
                            </div>

                            <div>
                                <div class="validation-detail-label">
                                    Status
                                </div>
                                <div class="validation-detail-value validation-value-danger">
                                    Tidak Sesuai
                                </div>
                            </div>

                        </div>

                        <div class="validation-status-message" style="margin:10px 0 0;">
                            {{ $item['alasan'] ?? 'Gambar tidak sesuai dengan bagian yang diminta.' }}
                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

@elseif ($errors->any())

    <div class="validation-errors">

        <div class="validation-errors-title">
            Terdapat masalah pada pengiriman foto:
        </div>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

    </div>

@endif


{{-- SCAN RESULTS --}}
@if ($scanResult)
<div class="results-section">
    <div class="results-header">
        <div class="results-header-left">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(165,184,208,0.15);display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#A5B8D0" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <div class="results-header-title">Hasil Pemindaian AI</div>
                <div class="results-header-sub">Analisis selesai â€” lihat detail di bawah</div>
            </div>
        </div>
        <a href="{{ route('scanner.index') }}" class="btn-scan-again">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
            Pindai Lagi
        </a>
    </div>
    <div class="results-body">

    {{-- 3 Result Cards --}}

{{-- ============================================================
     AI VALIDATION SUCCESS
============================================================ --}}

@if (!empty($scanResult['validasi']['detail']))

    @php
        $validationDetails = $scanResult['validasi']['detail'];

        $validationLabels = [
            'tubuh_ikan' => 'Tubuh Ikan',
            'mata_ikan' => 'Mata Ikan',
            'insang_ikan' => 'Insang Ikan',
        ];
    @endphp

    <div class="validation-status-panel success">

        <div class="validation-status-title">
            Seluruh Gambar Berhasil Divalidasi
        </div>

        <div class="validation-status-message">
            Ketiga gambar telah sesuai dengan bagian ikan yang diminta
            dan berhasil diteruskan ke model identifikasi genus
            serta kesegaran ikan.
        </div>

        <div class="validation-detail-list">

            @foreach ($validationDetails as $key => $item)

                <div class="validation-detail-item">

                    <div class="validation-detail-grid">

                        <div>
                            <div class="validation-detail-label">
                                Bagian Gambar
                            </div>

                            <div class="validation-detail-value">
                                {{ $validationLabels[$key] ?? $key }}
                            </div>
                        </div>

                        <div>
                            <div class="validation-detail-label">
                                Hasil Validasi
                            </div>

                            <div class="validation-detail-value validation-value-success">
                                Sesuai
                            </div>
                        </div>

                        <div>
                            <div class="validation-detail-label">
                                Prediksi AI
                            </div>

                            <div class="validation-detail-value">
                                {{ $item['prediksi'] ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="validation-detail-label">
                                Confidence
                            </div>

                            <div class="validation-detail-value">
                                {{ number_format(
                                    (float) ($item['confidence_persen'] ?? 0),
                                    2,
                                    ',',
                                    '.'
                                ) }}%
                            </div>
                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

@endif
      
{{-- 3 Result Cards --}}
@php
    // Membaca struktur respons FastAPI
    $hasilScan = $scanResult['hasil'] ?? $scanResult;

    $genusData = $hasilScan['identifikasi_genus'] ?? $hasilScan['genus'] ?? null;
    $mataData = $hasilScan['kesegaran_mata'] ?? $hasilScan['mata'] ?? null;
    $insangData = $hasilScan['kesegaran_insang'] ?? $hasilScan['insang'] ?? null;

    // Mengambil hasil prediksi
    $getPrediction = function ($data) {
        if (is_array($data)) {
            return $data['prediksi']
                ?? $data['label']
                ?? $data['class']
                ?? $data['status']
                ?? null;
        }

        return is_string($data) ? $data : null;
    };

    // Mengambil confidence dalam persen
    $getConfidence = function ($data) {
        if (!is_array($data)) {
            return null;
        }

        if (isset($data['confidence_persen']) && is_numeric($data['confidence_persen'])) {
            return (float) $data['confidence_persen'];
        }

        if (isset($data['confidence']) && is_numeric($data['confidence'])) {
            return (float) $data['confidence'] * 100;
        }

        return null;
    };

    // Mengidentifikasi kelas kesegaran
    $getFreshnessState = function ($label) {
        $value = strtolower(str_replace(
            [' ', '-'],
            '_',
            trim((string) $label)
        ));

        if (
            str_contains($value, 'non_fresh') ||
            str_contains($value, 'not_fresh') ||
            str_contains($value, 'nonfresh') ||
            str_contains($value, 'notfresh') ||
            str_contains($value, 'tidak') ||
            str_contains($value, 'busuk')
        ) {
            return 'not_fresh';
        }

        if (
            str_contains($value, 'less_fresh') ||
            str_contains($value, 'kurang') ||
            str_contains($value, 'moderate')
        ) {
            return 'less_fresh';
        }

        if (
            str_contains($value, 'fresh') ||
            str_contains($value, 'segar')
        ) {
            return 'fresh';
        }

        return 'unknown';
    };

    $genusLabel = $getPrediction($genusData);
    $mataLabel = $getPrediction($mataData);
    $insangLabel = $getPrediction($insangData);

    $genusConfidence = $getConfidence($genusData);
    $mataConfidence = $getConfidence($mataData);
    $insangConfidence = $getConfidence($insangData);

    $mataState = $getFreshnessState($mataLabel);
    $insangState = $getFreshnessState($insangLabel);

    // Label tampilan
    $getDisplayLabel = function ($state, $original) {
        if ($state === 'fresh') return 'Segar';
        if ($state === 'not_fresh') return 'Tidak Segar';
        if ($state === 'less_fresh') return 'Kurang Segar';

        return $original ?? 'Tidak Terdeteksi';
    };

    $mataDisplayLabel = $getDisplayLabel($mataState, $mataLabel);
    $insangDisplayLabel = $getDisplayLabel($insangState, $insangLabel);

    $mataBadge = $mataState === 'fresh'
        ? 'badge-segar'
        : ($mataState === 'not_fresh' ? 'badge-tidak' : 'badge-kurang');

    $insangBadge = $insangState === 'fresh'
        ? 'badge-segar'
        : ($insangState === 'not_fresh' ? 'badge-tidak' : 'badge-kurang');
@endphp

<div class="results-grid">

    {{-- Genus --}}
    <div class="result-card">
        <div class="result-card-label">
            Identifikasi Genus
        </div>

        @if($genusLabel)
            <div class="result-card-value">
                {{ $genusLabel }}
            </div>

            <div class="result-card-sub">
                @if($genusConfidence !== null)
                    Kepercayaan:
                    {{ number_format($genusConfidence, 2, ',', '.') }}%
                @else
                    Jenis ikan teridentifikasi
                @endif
            </div>

            <div class="result-badge badge-segar">
                Teridentifikasi
            </div>
        @else
            <div class="result-card-value" style="color:var(--text-muted);">â€”</div>
            <div class="result-card-sub">Data tidak tersedia</div>
            <div class="result-badge badge-tidak">Tidak Terdeteksi</div>
        @endif
    </div>

    {{-- Mata --}}
    <div class="result-card">
        <div class="result-card-label">
            Kesegaran Mata
        </div>

        @if($mataLabel)
            <div class="result-card-value">
                {{ $mataDisplayLabel }}
            </div>

            <div class="result-card-sub">
                @if($mataConfidence !== null)
                    Kepercayaan:
                    {{ number_format($mataConfidence, 2, ',', '.') }}%
                @else
                    Klasifikasi kesegaran mata
                @endif
            </div>

            <div class="result-badge {{ $mataBadge }}">
                {{ $mataDisplayLabel }}
            </div>
        @else
            <div class="result-card-value" style="color:var(--text-muted);">â€”</div>
            <div class="result-card-sub">Data tidak tersedia</div>
            <div class="result-badge badge-tidak">Tidak Terdeteksi</div>
        @endif
    </div>

    {{-- Insang --}}
    <div class="result-card">
        <div class="result-card-label">
            Kesegaran Insang
        </div>

        @if($insangLabel)
            <div class="result-card-value">
                {{ $insangDisplayLabel }}
            </div>

            <div class="result-card-sub">
                @if($insangConfidence !== null)
                    Kepercayaan:
                    {{ number_format($insangConfidence, 2, ',', '.') }}%
                @else
                    Klasifikasi kesegaran insang
                @endif
            </div>

            <div class="result-badge {{ $insangBadge }}">
                {{ $insangDisplayLabel }}
            </div>
        @else
            <div class="result-card-value" style="color:var(--text-muted);">â€”</div>
            <div class="result-card-sub">Data tidak tersedia</div>
            <div class="result-badge badge-tidak">Tidak Terdeteksi</div>
        @endif
    </div>

</div>

{{-- Overall Score --}}
@php
    $overall = $hasilScan['overall']
        ?? $hasilScan['skor_keseluruhan']
        ?? $hasilScan['kesegaran_keseluruhan']
        ?? null;

    $overallLabel = null;
    $overallScore = null;

    if ($overall) {
        if (is_array($overall)) {
            $overallLabel = $overall['label']
                ?? $overall['status']
                ?? $overall['class']
                ?? null;

            $overallScore = $overall['score']
                ?? $overall['confidence']
                ?? $overall['nilai']
                ?? null;
        } else {
            $overallLabel = $overall;
        }
    }

    // Penilaian berdasarkan hasil mata dan insang
    if (!$overallLabel) {
        if ($mataState === 'fresh' && $insangState === 'fresh') {
            $overallLabel = 'Sangat Segar';
        } elseif (
            $mataState === 'not_fresh' &&
            $insangState === 'not_fresh'
        ) {
            $overallLabel = 'Tidak Segar';
        } elseif (
            in_array('not_fresh', [$mataState, $insangState], true) &&
            in_array('fresh', [$mataState, $insangState], true)
        ) {
            $overallLabel = 'Perlu Pemeriksaan Lanjutan';
        } elseif (
            $mataState === 'not_fresh' ||
            $insangState === 'not_fresh'
        ) {
            $overallLabel = 'Tidak Segar';
        } elseif (
            $mataState === 'less_fresh' ||
            $insangState === 'less_fresh'
        ) {
            $overallLabel = 'Kurang Segar';
        } else {
            $overallLabel = 'Tidak dapat dinilai';
        }
    }

    $overallStatusClass = 'score-status-kurang';

    if (
        $overallLabel === 'Sangat Segar' ||
        $overallLabel === 'Segar'
    ) {
        $overallStatusClass = 'score-status-fresh';
    } elseif ($overallLabel === 'Tidak Segar') {
        $overallStatusClass = 'score-status-tidak';
    }

    $scoreNum = '-';

    if ($overallScore !== null && is_numeric($overallScore)) {
        $scoreValue = (float) $overallScore;

        $scoreNum = number_format(
            $scoreValue <= 1 ? $scoreValue * 100 : $scoreValue,
            0
        );
    }
@endphp

<div class="overall-score-card">
    <div class="score-circle">
        <div class="score-num">{{ $scoreNum }}</div>

        @if($overallScore !== null)
            <div class="score-denom">/100</div>
        @endif
    </div>

    <div>
        <div class="score-info-title">
            Kesegaran Keseluruhan
        </div>

        <div class="score-info-sub">
            Penilaian berdasarkan kondisi mata dan insang ikan
        </div>

        <div class="score-status-badge {{ $overallStatusClass }}">
            {{ $overallLabel }}
        </div>
    </div>
</div>


    </div>
</div>
@endif

{{-- ============================================================
     MODE TOGGLE
============================================================ --}}
@if (!$scanResult)
<div class="mode-toggle-wrapper">
    <button class="mode-btn active" id="btn-mode-camera" onclick="setMode('camera')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        Gunakan Kamera
    </button>
    <button class="mode-btn" id="btn-mode-upload" onclick="setMode('upload')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Unggah Foto
    </button>
</div>
@endif

{{-- ============================================================
     FORM
============================================================ --}}
<form id="scanner-form" method="POST" action="{{ route('scanner.predict') }}" enctype="multipart/form-data">
    @csrf

    {{-- Hidden file inputs (shared between camera and upload modes) --}}
    <input type="file" id="genus_image_input" name="genus_image" accept="image/jpeg,image/png,image/webp" class="file-input-hidden">
    <input type="file" id="mata_image_input" name="mata_image" accept="image/jpeg,image/png,image/webp" class="file-input-hidden">
    <input type="file" id="insang_image_input" name="insang_image" accept="image/jpeg,image/png,image/webp" class="file-input-hidden">

    @if (!$scanResult)
    <div class="scanner-grid">

        {{-- ======================================================
             CAMERA PANEL
        ====================================================== --}}
        <div class="scanner-left-column">
        <div class="scanner-panel" id="camera-panel">
            <div class="panel-header">
                <div class="panel-icon merlot">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                </div>
                <div>
                    <div class="panel-title">Ambil Foto via Kamera</div>
                    <div class="panel-sub">Ambil 3 foto secara berurutan: Tubuh â†’ Mata â†’ Insang</div>
                </div>
            </div>
            <div class="panel-body">

                {{-- Step Indicator --}}
                <div class="step-indicator">
                    <div class="step-item">
                        <div class="step-circle active" id="step-circle-0">1</div>
                        <div class="step-label active" id="step-label-0">Tubuh Ikan</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step-item">
                        <div class="step-circle" id="step-circle-1">2</div>
                        <div class="step-label" id="step-label-1">Mata Ikan</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step-item">
                        <div class="step-circle" id="step-circle-2">3</div>
                        <div class="step-label" id="step-label-2">Insang Ikan</div>
                    </div>
                </div>

                {{-- Camera Error --}}
                <div class="camera-error" id="camera-error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span id="camera-error-msg">Kamera tidak dapat diakses.</span>
                </div>

                {{-- Video --}}
                <div class="video-container">
                    <video id="camera-video" autoplay playsinline muted></video>
                    <div class="video-placeholder" id="video-placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                        <span id="video-placeholder-text">Tekan "Aktifkan Kamera" untuk memulai</span>
                    </div>
                </div>
                <canvas id="canvas-capture"></canvas>

                {{-- Controls --}}
                <div class="camera-controls">
                    <button type="button" class="cam-btn cam-btn-primary" id="btn-start-cam" onclick="startCamera()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                        Aktifkan Kamera
                    </button>
                    <button type="button" class="cam-btn cam-btn-capture" id="btn-capture" onclick="capturePhoto()" disabled>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M20.94 11A8.994 8.994 0 1 1 3.06 13"/></svg>
                        Ambil Foto
                    </button>
                    <button type="button" class="cam-btn cam-btn-secondary" id="btn-retake" onclick="retakePhoto()" disabled>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
                        Ulangi Foto
                    </button>
                    <button type="button" class="cam-btn cam-btn-danger" id="btn-stop-cam" onclick="stopCamera()" disabled>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                        Matikan Kamera
                    </button>
                </div>

                {{-- Step hint --}}
                <div class="info-box" id="cam-step-hint">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span id="cam-hint-text">Aktifkan kamera, arahkan ke <strong>tubuh ikan</strong>, lalu tekan "Ambil Foto".</span>
                </div>

                {{-- Photo Previews --}}
                <div class="photo-previews">
                    <div class="preview-item active-step" id="preview-box-0">
                        <img class="preview-img" id="preview-img-0" src="" alt="Tubuh Ikan">
                        <div class="preview-placeholder" id="preview-ph-0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            Tubuh
                        </div>
                        <div class="preview-label">Tubuh</div>
                        <div class="preview-check" id="check-0">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </div>
                    <div class="preview-item" id="preview-box-1">
                        <img class="preview-img" id="preview-img-1" src="" alt="Mata Ikan">
                        <div class="preview-placeholder" id="preview-ph-1">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Mata
                        </div>
                        <div class="preview-label">Mata</div>
                        <div class="preview-check" id="check-1">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </div>
                    <div class="preview-item" id="preview-box-2">
                        <img class="preview-img" id="preview-img-2" src="" alt="Insang Ikan">
                        <div class="preview-placeholder" id="preview-ph-2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg>
                            Insang
                        </div>
                        <div class="preview-label">Insang</div>
                        <div class="preview-check" id="check-2">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ======================================================
             UPLOAD PANEL
        ====================================================== --}}
        <div class="scanner-panel" id="upload-panel" style="display:none;">
            <div class="panel-header">
                <div class="panel-icon navy">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <div>
                    <div class="panel-title">Unggah Foto dari Perangkat</div>
                    <div class="panel-sub">Pilih 3 foto dari galeri atau folder perangkat Anda</div>
                </div>
            </div>
            <div class="panel-body">

                <div class="info-box">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Format yang diterima: JPG, PNG, WEBP. Ukuran maksimal 10 MB per foto.
                </div>

                <div class="upload-fields">

                    {{-- Genus Upload --}}
                    <div>
                        <div class="upload-field-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/></svg>
                            Foto Tubuh Ikan <span style="color:var(--merlot);">*</span>
                        </div>
                        <div class="upload-area" id="upload-area-0" onclick="triggerUpload('genus_image_input')">
                            <div class="upload-icon-placeholder">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                            <img class="upload-preview-thumb" id="upload-thumb-0" src="" alt="">
                            <div class="upload-info">
                                <div class="upload-info-name" id="upload-name-0">Belum ada foto dipilih</div>
                                <div class="upload-info-hint">Klik untuk memilih foto tubuh ikan</div>
                            </div>
                        </div>
                    </div>

                    {{-- Mata Upload --}}
                    <div>
                        <div class="upload-field-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Foto Mata Ikan <span style="color:var(--merlot);">*</span>
                        </div>
                        <div class="upload-area" id="upload-area-1" onclick="triggerUpload('mata_image_input')">
                            <div class="upload-icon-placeholder">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                            <img class="upload-preview-thumb" id="upload-thumb-1" src="" alt="">
                            <div class="upload-info">
                                <div class="upload-info-name" id="upload-name-1">Belum ada foto dipilih</div>
                                <div class="upload-info-hint">Klik untuk memilih foto mata ikan</div>
                            </div>
                        </div>
                    </div>

                    {{-- Insang Upload --}}
                    <div>
                        <div class="upload-field-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg>
                            Foto Insang Ikan <span style="color:var(--merlot);">*</span>
                        </div>
                                                <div class="upload-area" id="upload-area-2" onclick="triggerUpload('insang_image_input')">
                            <div class="upload-icon-placeholder">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg>
                            </div>
                            <img class="upload-preview-thumb" id="upload-thumb-2" src="" alt="">
                            <div class="upload-info">
                                <div class="upload-info-name" id="upload-name-2">Belum ada foto dipilih</div>
                                <div class="upload-info-hint">Klik untuk memilih foto insang ikan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- /scanner-left-column --}}

    {{-- ======================================================
         KOLOM KANAN: STATUS AKSI & PANDUAN
         (Layout di sisi kanan diisi dengan bagian bawahnya)
    ====================================================== --}}
    <div class="scanner-right-column">

        {{-- KARTU STATUS & AKSI PEMINDAIAN --}}
        <div class="scanner-panel scanner-submit-card">
            <div class="panel-header">
                <div class="panel-icon merlot">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </div>
                <div>
                    <div class="panel-title">Aksi &amp; Status Pemindaian</div>
                    <div class="panel-sub">Lengkapi 3 foto untuk memulai analisis AI</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="submit-status">
                    <div class="submit-status-title">Status Kelengkapan Foto</div>
                    <div class="photo-status-dots">
                        <div class="photo-dot"><div class="dot" id="dot-0"></div>Tubuh Ikan</div>
                        <div class="photo-dot"><div class="dot" id="dot-1"></div>Mata Ikan</div>
                        <div class="photo-dot"><div class="dot" id="dot-2"></div>Insang Ikan</div>
                    </div>
                </div>
                <button type="submit" class="btn-submit btn-submit-full" id="btn-submit" disabled>
                    <span class="btn-submit-text" style="display:flex;align-items:center;justify-content:center;gap:8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Mulai Pemindaian AI
                    </span>
                    <span class="btn-submit-loading">
                        <div class="spinner"></div>
                        Memindai 3 Model AI...
                    </span>
                </button>
            </div>
        </div>

        {{-- PANDUAN PEMINDAIAN AI --}}
        <aside class="scanner-panel scanner-side-panel">
            <div class="panel-header">
                <div class="panel-icon navy">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4"/>
                        <path d="M12 8h.01"/>
                    </svg>
                </div>
                <div>
                    <div class="panel-title">Panduan Pemindaian AI</div>
                    <div class="panel-sub">Petunjuk pengambilan gambar ikan</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="scanner-guide-item">
                    <div class="scanner-guide-number">01</div>
                    <div>
                        <div class="scanner-guide-title">Identifikasi Genus</div>
                        <p>Arahkan kamera ke seluruh tubuh ikan. Pastikan bentuk tubuh terlihat jelas.</p>
                    </div>
                </div>
                <div class="scanner-guide-item">
                    <div class="scanner-guide-number">02</div>
                    <div>
                        <div class="scanner-guide-title">Kesegaran Mata</div>
                        <p>Arahkan kamera mendekati mata ikan. Pastikan bagian mata terlihat jelas dan tidak buram.</p>
                    </div>
                </div>
                <div class="scanner-guide-item">
                    <div class="scanner-guide-number">03</div>
                    <div>
                        <div class="scanner-guide-title">Kesegaran Insang</div>
                        <p>Arahkan kamera ke bagian insang. Pastikan struktur insang terlihat dengan jelas.</p>
                    </div>
                </div>
                <div class="scanner-guide-warning">
                    <strong>Perhatian</strong>
                    <p>Gunakan tiga foto dari ikan yang sama. Hindari gambar yang buram, terlalu gelap, atau tertutup objek lain.</p>
                </div>
                <div class="scanner-guide-info">
                    <div class="panel-title">Informasi Analisis AI</div>
                    <p>Ketiga model AI akan memproses foto setelah seluruh gambar berhasil diambil dan tombol Mulai Pemindaian ditekan.</p>
                </div>
            </div>
        </aside>

    </div>
    {{-- /scanner-right-column --}}

</div>
{{-- /scanner-grid --}}

@endif {{-- !scanResult --}}

</form>

@endsection

@push('scripts')
<script>
// ================================================================
//  SCOMAR FISH SCANNER â€” JavaScript
// ================================================================

// --- State ---
let cameraStream = null;
let currentMode = 'camera'; // 'camera' | 'upload'
let currentStep = 0;        // 0=genus, 1=mata, 2=insang
let photoDone = [false, false, false];
const inputIds  = ['genus_image_input', 'mata_image_input', 'insang_image_input'];
const stepNames = ['tubuh ikan', 'mata ikan', 'insang ikan'];
const stepHints = [
    'Aktifkan kamera, arahkan ke <strong>tubuh ikan</strong>, lalu tekan "Ambil Foto".',
    'Arahkan kamera ke bagian <strong>mata ikan</strong>, lalu tekan "Ambil Foto".',
    'Arahkan kamera ke bagian <strong>insang ikan</strong>, lalu tekan "Ambil Foto".',
    'Semua foto sudah diambil. Tekan <strong>Mulai Pemindaian</strong> untuk menganalisis.'
];

// --- Mode Toggle ---
function setMode(mode) {
    currentMode = mode;
    const camPanel    = document.getElementById('camera-panel');
    const uploadPanel = document.getElementById('upload-panel');
    const btnCam      = document.getElementById('btn-mode-camera');
    const btnUpload   = document.getElementById('btn-mode-upload');

    if (mode === 'camera') {
        camPanel.classList.add('active');
        uploadPanel.style.display = 'none';
        btnCam.classList.add('active');
        btnUpload.classList.remove('active');
    } else {
        camPanel.classList.remove('active');
        uploadPanel.style.display = 'block';
        btnCam.classList.remove('active');
        btnUpload.classList.add('active');
        stopCamera();
    }
}

// Initialize camera panel as active
document.addEventListener('DOMContentLoaded', function() {
    const cp = document.getElementById('camera-panel');
    if (cp) {
        cp.classList.add('active');
        cp.style.display = '';
    }
    updateSubmitButton();
});

// --- Camera Functions ---
async function startCamera() {
    clearCameraError();
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: false
        });
        const video = document.getElementById('camera-video');
        video.srcObject = cameraStream;
        video.style.display = 'block';
        document.getElementById('video-placeholder').style.display = 'none';

        // Update controls
        setBtn('btn-start-cam',  true);
        setBtn('btn-capture',    false);
        setBtn('btn-stop-cam',   false);
        setBtn('btn-retake',     false);

    } catch (e) {
        let msg = 'Kamera tidak dapat diakses: ' + e.message;
        if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
            msg = 'Izin kamera ditolak. Silakan aktifkan izin kamera di pengaturan browser Anda.';
        } else if (e.name === 'NotFoundError' || e.name === 'DevicesNotFoundError') {
            msg = 'Kamera tidak ditemukan pada perangkat ini.';
        } else if (e.name === 'NotReadableError') {
            msg = 'Kamera sedang digunakan oleh aplikasi lain.';
        } else if (e.name === 'OverconstrainedError') {
            // Fallback: try without constraints
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                const video = document.getElementById('camera-video');
                video.srcObject = cameraStream;
                video.style.display = 'block';
                document.getElementById('video-placeholder').style.display = 'none';
                setBtn('btn-start-cam', true); setBtn('btn-capture', false);
                setBtn('btn-stop-cam', false); setBtn('btn-retake', false);
                return;
            } catch(e2) { msg = 'Tidak dapat mengakses kamera: ' + e2.message; }
        }
        showCameraError(msg);
    }
}

function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(t => t.stop());
        cameraStream = null;
    }
    const video = document.getElementById('camera-video');
    if (video) {
        video.srcObject = null;
        video.style.display = 'none';
    }
    const ph = document.getElementById('video-placeholder');
    if (ph) {
        ph.style.display = 'flex';
        document.getElementById('video-placeholder-text').textContent = 'Kamera dimatikan.';
    }
    setBtn('btn-start-cam', false);
    setBtn('btn-capture',   true);
    setBtn('btn-stop-cam',  true);
    setBtn('btn-retake',    true);
}

function capturePhoto() {
    if (!cameraStream) return;
    const video  = document.getElementById('camera-video');
    const canvas = document.getElementById('canvas-capture');
    canvas.width  = video.videoWidth  || 1280;
    canvas.height = video.videoHeight || 720;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);

    canvas.toBlob(function(blob) {
        if (!blob) return;
        const fname = `foto_${inputIds[currentStep].replace('_input','')}.jpg`;
        const file  = new File([blob], fname, { type: 'image/jpeg' });
        const dt    = new DataTransfer();
        dt.items.add(file);
        document.getElementById(inputIds[currentStep]).files = dt.files;

        // Show preview
        const url = URL.createObjectURL(blob);
        showPreview(currentStep, url);
        photoDone[currentStep] = true;
        updateStatusDot(currentStep, true);

        // Advance step or finish
        if (currentStep < 2) {
            const prevStep = currentStep;
            currentStep++;
            updateStepIndicator();
            updateHint();
            setActivePreviewBox(currentStep);
            // Enable retake for previous step
            setBtn('btn-retake', false);
        } else {
            // All done
            updateHint(3);
            setBtn('btn-capture', true);
            setBtn('btn-retake', false);
        }
        updateSubmitButton();
    }, 'image/jpeg', 0.92);
}

function retakePhoto() {
    // Retake the last completed step (or current)
    const stepToRetake = currentStep > 0 && photoDone[currentStep] ? currentStep :
                         (currentStep > 0 && !photoDone[currentStep] ? currentStep - 1 : currentStep);
    // Reset that step
    photoDone[stepToRetake] = false;
    document.getElementById(inputIds[stepToRetake]).value = '';
    hidePreview(stepToRetake);
    updateStatusDot(stepToRetake, false);
    currentStep = stepToRetake;
    updateStepIndicator();
    updateHint();
    setActivePreviewBox(currentStep);
    setBtn('btn-capture', false);
    if (!cameraStream) startCamera();
    updateSubmitButton();
}

// --- Step UI ---
function updateStepIndicator() {
    for (let i = 0; i < 3; i++) {
        const circle = document.getElementById('step-circle-' + i);
        const label  = document.getElementById('step-label-' + i);
        if (!circle || !label) continue;
        circle.classList.remove('active', 'done');
        label.classList.remove('active', 'done');
        if (photoDone[i]) {
            circle.classList.add('done');
            circle.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
            label.classList.add('done');
        } else if (i === currentStep) {
            circle.classList.add('active');
            circle.textContent = i + 1;
            label.classList.add('active');
        } else {
            circle.textContent = i + 1;
        }
    }
}

function updateHint(override) {
    const hint = document.getElementById('cam-hint-text');
    if (!hint) return;
    const idx = (override !== undefined) ? override : currentStep;
    hint.innerHTML = stepHints[Math.min(idx, 3)];
}

function setActivePreviewBox(step) {
    for (let i = 0; i < 3; i++) {
        const box = document.getElementById('preview-box-' + i);
        if (!box) continue;
        box.classList.toggle('active-step', i === step && !photoDone[i]);
    }
}

// --- Preview ---
function showPreview(step, url) {
    const img = document.getElementById('preview-img-' + step);
    const ph  = document.getElementById('preview-ph-' + step);
    const chk = document.getElementById('check-' + step);
    const box = document.getElementById('preview-box-' + step);
    if (img)  { img.src = url; img.style.display = 'block'; }
    if (ph)   { ph.style.display = 'none'; }
    if (chk)  { chk.classList.add('show'); }
    if (box)  { box.classList.add('has-photo'); box.classList.remove('active-step'); }
}

function hidePreview(step) {
    const img = document.getElementById('preview-img-' + step);
    const ph  = document.getElementById('preview-ph-' + step);
    const chk = document.getElementById('check-' + step);
    const box = document.getElementById('preview-box-' + step);
    if (img)  { img.src = ''; img.style.display = 'none'; }
    if (ph)   { ph.style.display = 'flex'; }
    if (chk)  { chk.classList.remove('show'); }
    if (box)  { box.classList.remove('has-photo'); }
}

// --- Status Dots ---
function updateStatusDot(step, ready) {
    const dot = document.getElementById('dot-' + step);
    if (dot) dot.classList.toggle('ready', ready);
}

// --- Submit Button ---
function updateSubmitButton() {
    const btn = document.getElementById('btn-submit');
    if (!btn) return;
    const allReady = photoDone.every(d => d) ||
        [0,1,2].every(i => document.getElementById(inputIds[i]).files.length > 0);
    btn.disabled = !allReady;
}

// --- Camera Error ---
function showCameraError(msg) {
    const el = document.getElementById('camera-error');
    const msgEl = document.getElementById('camera-error-msg');
    if (el) el.classList.add('show');
    if (msgEl) msgEl.textContent = msg;
}
function clearCameraError() {
    const el = document.getElementById('camera-error');
    if (el) el.classList.remove('show');
}

// --- Helpers ---
function setBtn(id, disabled) {
    const el = document.getElementById(id);
    if (el) el.disabled = disabled;
}

// --- Upload Mode ---
function triggerUpload(inputId) {
    document.getElementById(inputId).click();
}

// Listen to file input changes (upload mode)
['genus_image_input', 'mata_image_input', 'insang_image_input'].forEach(function(id, idx) {
    const inp = document.getElementById(id);
    if (!inp) return;
    inp.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            const url  = URL.createObjectURL(file);
            // Update upload area UI
            const area  = document.getElementById('upload-area-' + idx);
            const thumb = document.getElementById('upload-thumb-' + idx);
            const name  = document.getElementById('upload-name-' + idx);
            if (area)  area.classList.add('has-file');
            if (thumb) { thumb.src = url; thumb.classList.add('show'); }
            if (name)  name.textContent = file.name;
            updateStatusDot(idx, true);
            photoDone[idx] = true;
        } else {
            const area  = document.getElementById('upload-area-' + idx);
            const thumb = document.getElementById('upload-thumb-' + idx);
            const name  = document.getElementById('upload-name-' + idx);
            if (area)  area.classList.remove('has-file');
            if (thumb) { thumb.src = ''; thumb.classList.remove('show'); }
            if (name)  name.textContent = 'Belum ada foto dipilih';
            updateStatusDot(idx, false);
            photoDone[idx] = false;
        }
        updateSubmitButton();
    });
});

// --- Form Submit ---
const scannerForm = document.getElementById('scanner-form');
if (scannerForm) {
    scannerForm.addEventListener('submit', function(e) {
        // Validate all 3 inputs have files
        const missing = [];
        const labels  = ['Foto Tubuh Ikan', 'Foto Mata Ikan', 'Foto Insang Ikan'];
        inputIds.forEach(function(id, i) {
            const inp = document.getElementById(id);
            if (!inp || inp.files.length === 0) missing.push(labels[i]);
        });
        if (missing.length > 0) {
            e.preventDefault();
            alert('Foto berikut belum diunggah:\nâ€¢ ' + missing.join('\nâ€¢ '));
            return;
        }
        // Show loading
        const btn = document.getElementById('btn-submit');
        if (btn) btn.classList.add('loading');
        // Stop camera
        stopCamera();
    });
}

// --- Stop camera on page leave ---
window.addEventListener('beforeunload', stopCamera);
window.addEventListener('pagehide',     stopCamera);

// --- Visibility change (tab switch) ---
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') stopCamera();
});
</script>
@endpush


