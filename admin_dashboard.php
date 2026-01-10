<?php
session_start();
require_once 'db.php';

// Access control
$allowed_email = "youremail@sgsf.com";
if (!isset($_SESSION['user_id'])) {
    header('Location: auth');
    exit;
}

$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['email'] !== $allowed_email) {
    http_response_code(403);
    die("Forbidden: Access denied.");
}

// Fetch stats
$total_users_stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $total_users_stmt->fetchColumn();

// Levels breakdown
$academic_data_stmt = $pdo->query("SELECT data FROM academic_data");
$level_counts = [];
while ($row = $academic_data_stmt->fetch()) {
    $data = json_decode($row['data'], true);
    if (isset($data['db'])) {
        foreach (array_keys($data['db']) as $lvl) {
            $lvl_name = $lvl . " Level";
            $level_counts[$lvl_name] = ($level_counts[$lvl_name] ?? 0) + 1;
        }
    }
}
ksort($level_counts);

// Visit stats for charts
function getVisitData($pdo, $interval)
{
    $sql = "SELECT DATE(visited_at) as date, COUNT(*) as count 
            FROM site_visits 
            WHERE visited_at >= DATE_SUB(NOW(), INTERVAL $interval) 
            GROUP BY DATE(visited_at) 
            ORDER BY date ASC";
    return $pdo->query($sql)->fetchAll();
}

$visits_30d = getVisitData($pdo, '30 DAY');
$visits_6m = getVisitData($pdo, '6 MONTH');
$visits_1y = getVisitData($pdo, '1 YEAR');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Naija Cgpa</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0056D2;
            --primary-light: #0081FF;
            --success: #00B86B;
            --bg-body: #F1F5F9;
            --text-main: #0F172A;
            --text-muted: #475569;
            --border-light: rgba(15, 23, 42, 0.08);
            --glass-white: rgba(255, 255, 255, 0.85);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        .glass {
            background: var(--glass-white);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>

<body class="min-h-screen pb-12">
    <nav class="p-6 flex justify-between items-center max-w-6xl mx-auto">
        <h1 class="text-xl font-black uppercase tracking-widest text-emerald-600">Admin Dashboard</h1>
        <a href="index" class="text-sm font-bold text-slate-400 hover:text-indigo-600 transition-colors">Back to
            Site</a>
    </nav>

    <main class="container mx-auto px-6 max-w-6xl space-y-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass rounded-[2rem] p-8 text-center">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Users</p>
                <h3 class="text-5xl font-extrabold text-slate-800">
                    <?php echo $total_users; ?>
                </h3>
            </div>
            <div class="glass rounded-[2rem] p-8 md:col-span-2">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Users by Academic Level
                </p>
                <div class="flex flex-wrap gap-4">
                    <?php if (empty($level_counts)): ?>
                        <p class="text-slate-500 italic">No academic data tracked yet.</p>
                    <?php else: ?>
                        <?php foreach ($level_counts as $lvl => $count): ?>
                            <div
                                class="bg-white border border-slate-200 rounded-2xl px-6 py-3 flex items-center gap-3 shadow-sm">
                                <span class="text-emerald-600 font-black">
                                    <?php echo $count; ?>
                                </span>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                    <?php echo $lvl; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 gap-8">
            <div class="glass rounded-[2.5rem] p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-lg font-extrabold text-slate-800">Site Visits Tracking</h2>
                    <div class="flex gap-2 bg-slate-100 p-1 rounded-xl border border-slate-200">
                        <button onclick="updateChart('30d')"
                            class="chart-btn active px-4 py-2 rounded-lg text-[10px] font-black transition-all bg-emerald-500 text-white"
                            id="btn-30d">30 DAYS</button>
                        <button onclick="updateChart('6m')"
                            class="chart-btn px-4 py-2 rounded-lg text-[10px] font-black transition-all text-slate-400 hover:text-emerald-600"
                            id="btn-6m">6 MONTHS</button>
                        <button onclick="updateChart('1y')"
                            class="chart-btn px-4 py-2 rounded-lg text-[10px] font-black transition-all text-slate-400 hover:text-emerald-600"
                            id="btn-1y">1 YEAR</button>
                    </div>
                </div>
                <div class="h-[400px]">
                    <canvas id="visitsChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('visitsChart').getContext('2d');
        let chart;

        const dataSets = {
            '30d': {
                labels: <?php echo json_encode(array_column($visits_30d, 'date')); ?>,
                data: <?php echo json_encode(array_column($visits_30d, 'count')); ?>
            },
            '6m': {
                labels: <?php echo json_encode(array_column($visits_6m, 'date')); ?>,
                data: <?php echo json_encode(array_column($visits_6m, 'count')); ?>
            },
            '1y': {
                labels: <?php echo json_encode(array_column($visits_1y, 'date')); ?>,
                data: <?php echo json_encode(array_column($visits_1y, 'count')); ?>
            }
        };

        function initChart(period) {
            const config = {
                type: 'line',
                data: {
                    labels: dataSets[period].labels,
                    datasets: [{
                        label: 'Page Visits',
                        data: dataSets[period].data,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                color: '#64748b',
                                font: {
                                    family: 'Outfit',
                                    weight: '700'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#64748b',
                                font: {
                                    family: 'Outfit',
                                    weight: '700'
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: {
                                family: 'Outfit',
                                size: 13,
                                weight: '800'
                            },
                            bodyFont: {
                                family: 'Outfit',
                                size: 12
                            },
                            padding: 12,
                            cornerRadius: 12,
                            borderWidth: 1,
                            borderColor: 'rgba(255, 255, 255, 0.1)'
                        }
                    }
                }
            };

            if (chart) chart.destroy();
            chart = new Chart(ctx, config);
        }

        function updateChart(period) {
            // Update buttons
            document.querySelectorAll('.chart-btn').forEach(btn => {
                btn.classList.remove('bg-emerald-500', 'text-white');
                btn.classList.add('text-slate-400');
            });
            const activeBtn = document.getElementById('btn-' + period);
            activeBtn.classList.add('bg-emerald-500', 'text-white');
            activeBtn.classList.remove('text-slate-400');

            initChart(period);
        }

        initChart('30d');
    </script>
</body>

</html>