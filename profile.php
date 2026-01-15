<?php
session_start();
require_once 'db.php';
require_once 'tracker.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch academic data
$stmt = $pdo->prepare("SELECT data FROM academic_data WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch();
$appState = $row ? json_decode($row['data'], true) : null;

// Calculation helper
function getGPA($db, $lvl)
{
    if (!isset($db[$lvl]))
        return null;
    $totalQP = 0;
    $totalU = 0;
    foreach ($db[$lvl] as $sem) {
        if ($sem['mode'] === 'quick') {
            if ($sem['units'] > 0) {
                $totalU += $sem['units'];
                $totalQP += ($sem['units'] * $sem['gpa']);
            }
        } elseif ($sem['mode'] === 'detail') {
            foreach ($sem['items'] as $item) {
                if ($item['u'] > 0) {
                    $gp = -1;
                    if ($sem['type'] === 'score') {
                        $s = $item['s'];
                        if ($s >= 70)
                            $gp = 5;
                        else if ($s >= 60)
                            $gp = 4;
                        else if ($s >= 50)
                            $gp = 3;
                        else if ($s >= 45)
                            $gp = 2;
                        else if ($s >= 40)
                            $gp = 1;
                        else
                            $gp = 0;
                    } else {
                        $gp = $item['g'] !== '' ? floatval($item['g']) : -1;
                    }
                    if ($gp !== -1) {
                        $totalU += $item['u'];
                        $totalQP += ($item['u'] * $gp);
                    }
                }
            }
        }
    }
    return $totalU > 0 ? ($totalQP / $totalU) : null;
}

$levels = [];
$globalQP = 0;
$globalU = 0;

if ($appState && isset($appState['db'])) {
    // Sort levels first to calculate cumulative CGPA correctly
    $sortedLevels = $appState['db'];
    ksort($sortedLevels);

    foreach ($sortedLevels as $lvl => $data) {
        $gpa = getGPA($appState['db'], $lvl);
        if ($gpa !== null) {
            // Calculate cumulative CGPA up to this level
            $cumulativeQP = 0;
            $cumulativeU = 0;

            // Iterate through all levels up to and including current level
            foreach ($sortedLevels as $currentLvl => $currentData) {
                if ($currentLvl > $lvl)
                    break; // Stop when we pass the current level

                foreach ($currentData as $sem) {
                    if ($sem['mode'] === 'quick') {
                        if ($sem['units'] > 0) {
                            $cumulativeU += $sem['units'];
                            $cumulativeQP += ($sem['units'] * $sem['gpa']);
                        }
                    } elseif ($sem['mode'] === 'detail') {
                        foreach ($sem['items'] as $item) {
                            if ($item['u'] > 0) {
                                $gp = -1;
                                if ($sem['type'] === 'score') {
                                    $s = $item['s'];
                                    if ($s >= 70)
                                        $gp = 5;
                                    else if ($s >= 60)
                                        $gp = 4;
                                    else if ($s >= 50)
                                        $gp = 3;
                                    else if ($s >= 45)
                                        $gp = 2;
                                    else if ($s >= 40)
                                        $gp = 1;
                                    else
                                        $gp = 0;
                                } else {
                                    $gp = $item['g'] !== '' ? floatval($item['g']) : -1;
                                }
                                if ($gp !== -1) {
                                    $cumulativeU += $item['u'];
                                    $cumulativeQP += ($item['u'] * $gp);
                                }
                            }
                        }
                    }
                }
            }

            $cumulativeCGPA = $cumulativeU > 0 ? ($cumulativeQP / $cumulativeU) : 0;

            $levels[$lvl] = [
                'gpa' => $gpa,
                'cgpa' => $cumulativeCGPA
            ];

            // Update global totals
            $globalQP = $cumulativeQP;
            $globalU = $cumulativeU;
        }
    }
}

$cgpa = $globalU > 0 ? ($globalQP / $globalU) : 0;
$cls = "No Data";
$color = "text-slate-400";
$bg_accent = "bg-slate-500/10";

if ($globalU > 0) {
    if ($cgpa >= 4.5) {
        $cls = "First Class Honours";
        $color = "text-emerald-600";
        $bg_accent = "bg-emerald-500/10";
    } else if ($cgpa >= 3.5) {
        $cls = "Second Class Upper (2:1)";
        $color = "text-cyan-600";
        $bg_accent = "bg-cyan-500/10";
    } else if ($cgpa >= 2.4) {
        $cls = "Second Class Lower (2:2)";
        $color = "text-amber-600";
        $bg_accent = "bg-amber-500/10";
    } else if ($cgpa >= 1.5) {
        $cls = "Third Class Honours";
        $color = "text-slate-600";
        $bg_accent = "bg-slate-200";
    } else {
        $cls = "Pass / Fail";
        $color = "text-rose-600";
        $bg_accent = "bg-rose-500/10";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Naija Cgpa</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
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
    <nav class="p-6 flex justify-between items-center max-w-2xl mx-auto">
        <a href="index" class="p-2 glass rounded-full text-slate-400 hover:text-indigo-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-sm font-black uppercase tracking-widest text-slate-500">Student Profile</h1>
        <a href="logout"
            class="text-[10px] font-black uppercase tracking-widest text-rose-500/70 hover:text-rose-600 transition-colors">Logout</a>
    </nav>

    <main class="container mx-auto px-6 max-w-2xl space-y-8">
        <!-- Profile Header -->
        <section class="glass rounded-[2.5rem] p-8 text-center relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 <?php echo $bg_accent; ?> rounded-full blur-3xl"></div>
            <div
                class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-indigo-100">
                <span class="text-3xl font-black text-indigo-600">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </span>
            </div>
            <h2 class="text-3xl font-extrabold text-slate-800 mb-2">
                <?php echo htmlspecialchars($username); ?>
            </h2>
            <div class="mt-4">
                <span
                    class="inline-block px-6 py-3 rounded-full <?php echo $bg_accent; ?> border border-white/40 text-xs font-black uppercase tracking-[0.3em] <?php echo $color; ?> shadow-sm backdrop-blur-md">
                    <?php echo $cls; ?>
                </span>
            </div>
        </section>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4">
            <div class="glass rounded-[2rem] p-6">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Final CGPA</p>
                <h3 class="text-4xl font-extrabold <?php echo $color; ?>">
                    <?php echo number_format($cgpa, 2); ?>
                </h3>
            </div>
            <div class="glass rounded-[2rem] p-6">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Levels</p>
                <h3 class="text-4xl font-extrabold text-slate-800">
                    <?php echo count($levels); ?>
                </h3>
            </div>
        </div>

        <!-- Level Breakdown -->
        <section class="space-y-4">
            <h2 class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Academic Breakdown</h2>
            <div class="space-y-3">
                <?php if (empty($levels)): ?>
                    <div class="glass rounded-[2rem] p-8 text-center">
                        <p class="text-sm font-bold text-slate-500">No data found. Start adding courses on the home page.
                        </p>
                    </div>
                <?php else: ?>
                    <?php ksort($levels);
                    foreach ($levels as $lvl => $data): ?>
                        <div class="glass rounded-3xl p-5 flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-extrabold text-slate-800">
                                    <?php echo $lvl; ?> Level
                                </h4>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">CGPA at Level</p>
                            </div>
                            <div class="text-2xl font-black text-indigo-600">
                                <?php echo number_format($data['cgpa'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Quick Help -->
        <div class="p-6 border border-slate-200 rounded-[2rem] bg-white">
            <p class="text-[10px] text-slate-500 font-medium leading-relaxed">
                This report is based on the data synced to your account. Ensure you save your latest calculations on the
                main dashboard to keep this profile up to date.
            </p>
        </div>
    </main>
</body>

</html>