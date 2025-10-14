(function($) {
    'use strict';

    // Global function to open bot analysis popup
    window.userspn_open_bot_analysis_popup = function() {
        console.log('Opening bot analysis popup...');
        console.log('Chart.js available:', typeof Chart !== 'undefined');
        
        $('#userspn-bot-analysis-popup').removeClass('userspn-display-none-soft').addClass('userspn-display-block');
        $('.userspn-popup-overlay').removeClass('userspn-display-none-soft').addClass('userspn-display-block');
        loadBotAnalysisResults();
    };

    $(document).ready(function() {
        // Close popup handlers
        $(document).on('click', '.userspn-popup-close-wrapper', function() {
            $('#userspn-bot-analysis-popup').removeClass('userspn-display-block').addClass('userspn-display-none-soft');
            $('.userspn-popup-overlay').removeClass('userspn-display-block').addClass('userspn-display-none-soft');
        });
        
        // Close popup when clicking overlay
        $(document).on('click', '.userspn-popup-overlay', function() {
            $('#userspn-bot-analysis-popup').removeClass('userspn-display-block').addClass('userspn-display-none-soft');
            $('.userspn-popup-overlay').removeClass('userspn-display-block').addClass('userspn-display-none-soft');
        });

        // Start analysis button
        $(document).on('click', '#userspn-start-bot-analysis', function() {
            var $btn = $(this);
            var limit = $('#userspn-analysis-limit').val() || 100;
            
            $btn.prop('disabled', true).text('Analyzing...');
            
            $.post(userspn_ajax.ajax_url, {
                action: 'userspn_ajax',
                userspn_ajax_type: 'userspn_analyze_bots',
                userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
                limit: limit
            }, function(response) {
                if (response.success) {
                    displayAnalysisResults(response.data);
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            }).always(function() {
                $btn.prop('disabled', false).text('Start Analysis');
            });
        });

        // Mark user as bot
        $(document).on('click', '.mark-as-bot', function() {
            var userId = $(this).data('user-id');
            var $row = $(this).closest('tr');
            
            if (confirm('Are you sure you want to mark this user as a bot?')) {
                $.post(userspn_ajax.ajax_url, {
                    action: 'userspn_ajax',
                    userspn_ajax_type: 'userspn_mark_user_as_bot',
                    userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
                    user_id: userId
                }, function(response) {
                    if (response.success) {
                        $row.addClass('marked-as-bot');
                        $(this).text('Confirmed Bot').prop('disabled', true);
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                });
            }
        });

        // Mark user as human
        $(document).on('click', '.mark-as-human', function() {
            var userId = $(this).data('user-id');
            var $row = $(this).closest('tr');
            
            if (confirm('Are you sure you want to mark this user as human?')) {
                $.post(userspn_ajax.ajax_url, {
                    action: 'userspn_ajax',
                    userspn_ajax_type: 'userspn_mark_user_as_human',
                    userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
                    user_id: userId
                }, function(response) {
                    if (response.success) {
                        $row.addClass('marked-as-human');
                        $(this).text('Confirmed Human').prop('disabled', true);
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                });
            }
        });

        // Delete confirmed bots
        $(document).on('click', '#userspn-delete-confirmed-bots', function() {
            if (confirm('Are you sure you want to delete all confirmed bot users? This action cannot be undone.')) {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Deleting...');
                
                $.post(userspn_ajax.ajax_url, {
                    action: 'userspn_ajax',
                    userspn_ajax_type: 'userspn_delete_confirmed_bots',
                    userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce
                }, function(response) {
                    if (response.success) {
                        alert('Successfully deleted ' + response.data.deleted_count + ' bot users');
                        loadBotAnalysisResults(); // Reload results
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('Delete Confirmed Bots');
                });
            }
        });
    });

    function loadBotAnalysisResults() {
        $.post(userspn_ajax.ajax_url, {
            action: 'userspn_ajax',
            userspn_ajax_type: 'userspn_get_bot_analysis',
            userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce
        }, function(response) {
            if (response.success && response.data) {
                displayAnalysisResults(response.data);
            } else {
                showNoResults();
            }
        });
    }

    function displayAnalysisResults(data) {
        var $popup = $('#userspn-bot-analysis-popup');
        var $content = $popup.find('.userspn-popup-content');
        
        var html = '<div class="userspn-bot-analysis-results">';
        
        // Summary section with charts
        if (data.analysis_summary) {
            html += '<div class="userspn-analysis-summary userspn-mb-30">';
            html += '<h3>Analysis Summary</h3>';
            html += '<div class="userspn-summary-stats">';
            html += '<div class="userspn-stat"><strong>Total Users Analyzed:</strong> ' + data.analysis_summary.total_users + '</div>';
            html += '<div class="userspn-stat"><strong>Suspicious Users:</strong> ' + data.analysis_summary.suspicious_count + '</div>';
            html += '<div class="userspn-stat"><strong>Suspicion Rate:</strong> ' + data.analysis_summary.suspicion_rate + '%</div>';
            html += '<div class="userspn-stat"><strong>Analysis Date:</strong> ' + formatDate(data.analysis_summary.analysis_date) + '</div>';
            html += '</div>';
            
            // Charts section
            html += '<div class="userspn-charts-section userspn-mt-30">';
            html += '<div class="userspn-charts-grid">';
            
            // Suspicion levels chart
            html += '<div class="userspn-chart-container">';
            html += '<h4>Suspicion Levels Distribution</h4>';
            html += '<canvas id="suspicionLevelsChart" width="400" height="200"></canvas>';
            html += '</div>';
            
            // Bot patterns chart
            if (data.analysis_summary.common_patterns && Object.keys(data.analysis_summary.common_patterns).length > 0) {
                html += '<div class="userspn-chart-container">';
                html += '<h4>Bot Patterns Detected</h4>';
                html += '<canvas id="botPatternsChart" width="400" height="200"></canvas>';
                html += '</div>';
            }
            
            html += '</div>';
            html += '</div>';
            
            // Common patterns list
            if (data.analysis_summary.common_patterns && Object.keys(data.analysis_summary.common_patterns).length > 0) {
                html += '<div class="userspn-common-patterns userspn-mt-20">';
                html += '<h4>Common Bot Patterns Detected:</h4>';
                html += '<ul>';
                for (var pattern in data.analysis_summary.common_patterns) {
                    html += '<li>' + pattern + ' (' + data.analysis_summary.common_patterns[pattern] + ' occurrences)</li>';
                }
                html += '</ul>';
                html += '</div>';
            }
            html += '</div>';
        }
        
        // Suspicious users table (only show users with score >= 30)
        var highSuspicionUsers = data.suspicious_users ? data.suspicious_users.filter(function(user) {
            return user.suspicion_score >= 30;
        }) : [];
        
        if (highSuspicionUsers.length > 0) {
            html += '<div class="userspn-suspicious-users">';
            html += '<h3>High Suspicion Users (' + highSuspicionUsers.length + ')</h3>';
            html += '<div class="userspn-table-wrapper">';
            html += '<table class="userspn-table">';
            html += '<thead>';
            html += '<tr>';
            html += '<th>ID</th>';
            html += '<th>Username</th>';
            html += '<th>Email</th>';
            html += '<th>Registered</th>';
            html += '<th>Score</th>';
            html += '<th>Patterns</th>';
            html += '<th>Actions</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            
            highSuspicionUsers.forEach(function(user) {
                html += '<tr class="suspicious-user" data-user-id="' + user.id + '">';
                html += '<td>' + user.id + '</td>';
                html += '<td>' + escapeHtml(user.login) + '</td>';
                html += '<td>' + escapeHtml(user.email) + '</td>';
                html += '<td>' + formatDate(user.registered) + '</td>';
                html += '<td><span class="userspn-score userspn-score-' + getScoreClass(user.suspicion_score) + '">' + user.suspicion_score + '</span></td>';
                html += '<td>' + user.bot_patterns.join(', ') + '</td>';
                html += '<td>';
                html += '<button class="mark-as-bot userspn-btn userspn-btn-small userspn-btn-danger" data-user-id="' + user.id + '">Mark as Bot</button> ';
                html += '<button class="mark-as-human userspn-btn userspn-btn-small userspn-btn-success" data-user-id="' + user.id + '">Mark as Human</button>';
                html += '</td>';
                html += '</tr>';
            });
            
            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            html += '</div>';
            
            // Action buttons
            html += '<div class="userspn-analysis-actions userspn-mt-30">';
            html += '<button id="userspn-delete-confirmed-bots" class="userspn-btn userspn-btn-danger">Delete Confirmed Bots</button>';
            html += '</div>';
        } else {
            html += '<div class="userspn-no-suspicious-users">';
            html += '<h3>No High Suspicion Users Found</h3>';
            html += '<p>No users with high suspicion scores (≥30) were found in the analysis. Check the charts above for detailed distribution of all analyzed users.</p>';
            html += '</div>';
        }
        
        html += '</div>';
        
        $content.html(html);
    }

    function showNoResults() {
        var $popup = $('#userspn-bot-analysis-popup');
        var $content = $popup.find('.userspn-popup-content');
        
        var html = '<div class="userspn-bot-analysis-results">';
        html += '<div class="userspn-no-results">';
        html += '<h3>Bot Analysis Tool</h3>';
        html += '<p>This tool analyzes existing users to identify potential bots based on various patterns and behaviors.</p>';
        
        html += '<div class="userspn-analysis-info userspn-mb-30">';
        html += '<h4>Analysis Criteria:</h4>';
        html += '<ul>';
        html += '<li>Suspicious email patterns (sequential, temp emails)</li>';
        html += '<li>Bot-like usernames (user123, test456, etc.)</li>';
        html += '<li>Empty or generic profile data</li>';
        html += '<li>Multiple registrations from same IP</li>';
        html += '<li>Suspicious user agent strings</li>';
        html += '<li>No activity since registration</li>';
        html += '</ul>';
        html += '</div>';
        
        html += '<div class="userspn-charts-section userspn-mt-30">';
        html += '<div class="userspn-charts-grid">';
        html += '<div class="userspn-chart-container">';
        html += '<h4>Example: Suspicion Levels Distribution</h4>';
        html += '<canvas id="exampleSuspicionChart" width="400" height="200"></canvas>';
        html += '</div>';
        html += '<div class="userspn-chart-container">';
        html += '<h4>Example: Bot Patterns Detected</h4>';
        html += '<canvas id="examplePatternsChart" width="400" height="200"></canvas>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        
        html += '<div class="userspn-analysis-controls">';
        html += '<label for="userspn-analysis-limit">Users to analyze:</label>';
        html += '<input type="number" id="userspn-analysis-limit" value="100" min="1" max="1000">';
        html += '<button id="userspn-start-bot-analysis" class="userspn-btn userspn-btn-primary">Start Analysis</button>';
        html += '</div>';
        
        html += '<div class="userspn-analysis-warning userspn-mt-20">';
        html += '<p><strong>Warning:</strong> This analysis is based on patterns and may produce false positives. Always review results carefully before taking action.</p>';
        html += '</div>';
        
        html += '</div>';
        html += '</div>';
        
        $content.html(html);
        
        // Create example charts
        setTimeout(function() {
            createExampleCharts();
        }, 100);
    }
    
    function loadChartJS() {
        return new Promise(function(resolve, reject) {
            // Check if Chart.js is already loaded
            if (typeof Chart !== 'undefined') {
                resolve();
                return;
            }
            
            // Check if script is already being loaded
            if (document.querySelector('script[src*="chart.js"]')) {
                // Wait for it to load
                var checkInterval = setInterval(function() {
                    if (typeof Chart !== 'undefined') {
                        clearInterval(checkInterval);
                        resolve();
                    }
                }, 100);
                
                // Timeout after 5 seconds
                setTimeout(function() {
                    clearInterval(checkInterval);
                    reject('Chart.js loading timeout');
                }, 5000);
                return;
            }
            
            // Load Chart.js dynamically
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js';
            script.onload = function() {
                resolve();
            };
            script.onerror = function() {
                reject('Failed to load Chart.js');
            };
            document.head.appendChild(script);
        });
    }
    
    function showChartsError() {
        var $popup = $('#userspn-bot-analysis-popup');
        var $content = $popup.find('.userspn-popup-content');
        
        var errorHtml = '<div class="userspn-charts-error userspn-mb-30">';
        errorHtml += '<div class="userspn-alert userspn-alert-warning">';
        errorHtml += '<h4>Charts Not Available</h4>';
        errorHtml += '<p>Unable to load Chart.js library. Charts will not be displayed.</p>';
        errorHtml += '<button id="userspn-retry-charts" class="userspn-btn userspn-btn-primary">Retry Loading Charts</button>';
        errorHtml += '</div>';
        errorHtml += '</div>';
        
        $content.prepend(errorHtml);
        
        // Retry button handler
        $(document).on('click', '#userspn-retry-charts', function() {
            $(this).text('Loading...').prop('disabled', true);
            loadChartJS().then(function() {
                location.reload(); // Reload to recreate charts
            }).catch(function() {
                $(this).text('Retry Failed').prop('disabled', false);
            });
        });
    }

    function createExampleCharts() {
        // Load Chart.js dynamically if not available
        if (typeof Chart === 'undefined') {
            loadChartJS().then(function() {
                createExampleCharts();
            }).catch(function() {
                console.error('Failed to load Chart.js for examples');
            });
            return;
        }
        
        // Example suspicion levels chart
        var ctx1 = document.getElementById('exampleSuspicionChart');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: ['Clean Users', 'Low Suspicion', 'Medium Suspicion', 'High Suspicion'],
                    datasets: [{
                        data: [85, 8, 4, 3],
                        backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.parsed;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ' + value + ' users (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Example patterns chart
        var ctx2 = document.getElementById('examplePatternsChart');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Sequential Email', 'Bot-like Username', 'Empty Profile', 'Same IP', 'No Activity'],
                    datasets: [{
                        label: 'Occurrences',
                        data: [12, 8, 15, 6, 9],
                        backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0dcaf0'],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' occurrences';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    }
                }
            });
        }
    }
    
    function createCharts(data) {
        console.log('Creating charts...');
        console.log('Chart.js available:', typeof Chart !== 'undefined');
        console.log('Data received:', data);
        
        // Load Chart.js dynamically if not available
        if (typeof Chart === 'undefined') {
            console.log('Chart.js not available, loading dynamically...');
            loadChartJS().then(function() {
                console.log('Chart.js loaded successfully');
                createCharts(data);
            }).catch(function(error) {
                console.error('Failed to load Chart.js:', error);
                showChartsError();
            });
            return;
        }
        
        // Create suspicion levels chart
        createSuspicionLevelsChart(data);
        
        // Create bot patterns chart
        if (data.analysis_summary && data.analysis_summary.common_patterns) {
            createBotPatternsChart(data.analysis_summary.common_patterns);
        }
    }
    
    function createSuspicionLevelsChart(data) {
        var ctx = document.getElementById('suspicionLevelsChart');
        console.log('Creating suspicion levels chart, canvas found:', !!ctx);
        if (!ctx) {
            console.error('Suspicion levels chart canvas not found');
            return;
        }
        
        // Calculate suspicion levels distribution
        var suspicionLevels = {
            'Clean (0-15)': 0,
            'Low (16-30)': 0,
            'Medium (31-50)': 0,
            'High (51-70)': 0,
            'Very High (71-100)': 0
        };
        
        // Use all users from analysis_summary if available, otherwise use suspicious_users
        var usersToAnalyze = data.analysis_summary && data.analysis_summary.all_users ? 
            data.analysis_summary.all_users : 
            (data.suspicious_users || []);
        
        usersToAnalyze.forEach(function(user) {
            var score = user.suspicion_score || 0;
            if (score <= 15) {
                suspicionLevels['Clean (0-15)']++;
            } else if (score <= 30) {
                suspicionLevels['Low (16-30)']++;
            } else if (score <= 50) {
                suspicionLevels['Medium (31-50)']++;
            } else if (score <= 70) {
                suspicionLevels['High (51-70)']++;
            } else {
                suspicionLevels['Very High (71-100)']++;
            }
        });
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(suspicionLevels),
                datasets: [{
                    data: Object.values(suspicionLevels),
                    backgroundColor: [
                        '#28a745', // Green for clean users
                        '#ffc107', // Yellow for low suspicion
                        '#fd7e14', // Orange for medium suspicion
                        '#dc3545', // Red for high suspicion
                        '#6f42c1'  // Purple for very high suspicion
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' users (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    
    function createBotPatternsChart(patterns) {
        var ctx = document.getElementById('botPatternsChart');
        if (!ctx) return;
        
        var labels = Object.keys(patterns);
        var values = Object.values(patterns);
        
        // Sort by frequency
        var sortedData = labels.map(function(label, index) {
            return {
                label: label,
                value: values[index]
            };
        }).sort(function(a, b) {
            return b.value - a.value;
        });
        
        var sortedLabels = sortedData.map(function(item) {
            return item.label;
        });
        var sortedValues = sortedData.map(function(item) {
            return item.value;
        });
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: sortedLabels,
                datasets: [{
                    label: 'Occurrences',
                    data: sortedValues,
                    backgroundColor: [
                        '#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0dcaf0',
                        '#6f42c1', '#e83e8c', '#6c757d', '#198754', '#0d6efd'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' occurrences';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                }
            }
        });
    }

    function getScoreClass(score) {
        if (score >= 80) return 'high';
        if (score >= 60) return 'medium';
        return 'low';
    }

    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

})(jQuery);
