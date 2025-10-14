<?php
/**
 * Bot Analysis Popup Template
 *
 * @package USERSPN
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="userspn-bot-analysis-popup" class="userspn-popup userspn-popup-size-large userspn-display-none-soft">
    <button class="userspn-popup-close-wrapper">
        <i class="material-icons-outlined">close</i>
    </button>
    
    <div class="userspn-popup-content">
        <div class="userspn-p-30">
            <h2 class="userspn-mb-30"><?php esc_html_e('Bot Analysis Tool', 'userspn'); ?></h2>
            <div class="userspn-bot-analysis-results">
                <div class="userspn-no-results">
                    <h3><?php esc_html_e('Bot Analysis Tool', 'userspn'); ?></h3>
                    <p><?php esc_html_e('This tool analyzes existing users to identify potential bots based on various patterns and behaviors.', 'userspn'); ?></p>
                    
                    <div class="userspn-analysis-info userspn-mb-30">
                        <h4><?php esc_html_e('Analysis Criteria:', 'userspn'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Suspicious email patterns (sequential, temp emails)', 'userspn'); ?></li>
                            <li><?php esc_html_e('Bot-like usernames (user123, test456, etc.)', 'userspn'); ?></li>
                            <li><?php esc_html_e('Empty or generic profile data', 'userspn'); ?></li>
                            <li><?php esc_html_e('Multiple registrations from same IP', 'userspn'); ?></li>
                            <li><?php esc_html_e('Suspicious user agent strings', 'userspn'); ?></li>
                            <li><?php esc_html_e('No activity since registration', 'userspn'); ?></li>
                        </ul>
                    </div>
                    
                    <div class="userspn-analysis-controls">
                        <label for="userspn-analysis-limit"><?php esc_html_e('Users to analyze:', 'userspn'); ?></label>
                        <input type="number" id="userspn-analysis-limit" value="100" min="1" max="1000" class="userspn-input">
                        <button id="userspn-start-bot-analysis" class="userspn-btn userspn-btn-primary"><?php esc_html_e('Start Analysis', 'userspn'); ?></button>
                    </div>
                    
                    <div class="userspn-analysis-warning userspn-mt-20">
                        <p><strong><?php esc_html_e('Warning:', 'userspn'); ?></strong> <?php esc_html_e('This analysis is based on patterns and may produce false positives. Always review results carefully before taking action.', 'userspn'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.userspn-bot-analysis-results {
    max-height: 70vh;
    overflow-y: auto;
}

.userspn-analysis-summary {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.userspn-summary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-bottom: 15px;
}

.userspn-stat {
    padding: 10px;
    background: white;
    border-radius: 3px;
    border: 1px solid #eee;
}

.userspn-common-patterns ul {
    margin: 10px 0;
    padding-left: 20px;
}

.userspn-table-wrapper {
    overflow-x: auto;
    margin: 20px 0;
}

.userspn-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.userspn-table th,
.userspn-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.userspn-table th {
    background: #f5f5f5;
    font-weight: bold;
}

.userspn-score {
    padding: 4px 8px;
    border-radius: 3px;
    font-weight: bold;
    color: white;
}

.userspn-score-high {
    background: #dc3545;
}

.userspn-score-medium {
    background: #ffc107;
    color: #000;
}

.userspn-score-low {
    background: #28a745;
}

.userspn-analysis-actions {
    text-align: center;
    padding: 20px;
    border-top: 1px solid #ddd;
}

.userspn-btn-small {
    padding: 5px 10px;
    font-size: 12px;
    margin: 2px;
}

.userspn-btn-danger {
    background: #dc3545;
    color: white;
    border: none;
}

.userspn-btn-success {
    background: #28a745;
    color: white;
    border: none;
}

.marked-as-bot {
    background: #ffe6e6 !important;
}

.marked-as-human {
    background: #e6ffe6 !important;
}

.userspn-analysis-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 20px 0;
}

.userspn-analysis-controls label {
    font-weight: bold;
}

.userspn-analysis-controls input {
    width: 100px;
}

.userspn-analysis-info {
    background: #e7f3ff;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #007cba;
}

.userspn-analysis-warning {
    background: #fff3cd;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #ffc107;
}

.userspn-no-results {
    text-align: center;
    padding: 40px 20px;
}

.userspn-no-suspicious-users {
    text-align: center;
    padding: 40px 20px;
    background: #d4edda;
    border-radius: 5px;
    border: 1px solid #c3e6cb;
}

/* Charts styles */
.userspn-charts-section {
    margin: 30px 0;
}

.userspn-charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin: 20px 0;
}

.userspn-chart-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.userspn-chart-container h4 {
    margin: 0 0 20px 0;
    color: #495057;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
}

.userspn-chart-container canvas {
    max-height: 300px;
}

/* Responsive charts */
@media (max-width: 768px) {
    .userspn-charts-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .userspn-chart-container {
        padding: 15px;
    }
    
    .userspn-chart-container canvas {
        max-height: 250px;
    }
}

/* Enhanced summary stats */
.userspn-summary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.userspn-stat {
    padding: 15px;
    background: white;
    border-radius: 6px;
    border-left: 4px solid #007cba;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.2s ease;
}

.userspn-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.userspn-stat strong {
    color: #495057;
    font-weight: 600;
}

/* Enhanced analysis info */
.userspn-analysis-info {
    background: linear-gradient(135deg, #e7f3ff 0%, #cce7ff 100%);
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #007cba;
    box-shadow: 0 2px 8px rgba(0,124,186,0.1);
}

.userspn-analysis-info h4 {
    color: #0056b3;
    margin-bottom: 15px;
    font-weight: 600;
}

.userspn-analysis-info ul {
    margin: 0;
    padding-left: 20px;
}

.userspn-analysis-info li {
    margin-bottom: 8px;
    color: #495057;
    line-height: 1.5;
}

/* Enhanced warning */
.userspn-analysis-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
    box-shadow: 0 2px 8px rgba(255,193,7,0.1);
}

.userspn-analysis-warning p {
    margin: 0;
    color: #856404;
    line-height: 1.6;
}

/* Enhanced controls */
.userspn-analysis-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 25px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.userspn-analysis-controls label {
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.userspn-analysis-controls input {
    width: 120px;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
}

.userspn-analysis-controls button {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
}

/* Charts error styles */
.userspn-charts-error {
    margin: 20px 0;
}

.userspn-alert {
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.userspn-alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-left-color: #ffc107;
    color: #856404;
}

.userspn-alert h4 {
    margin: 0 0 10px 0;
    color: #856404;
    font-weight: 600;
}

.userspn-alert p {
    margin: 0 0 15px 0;
    line-height: 1.5;
}

.userspn-alert button {
    margin-top: 10px;
}
</style>
