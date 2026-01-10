<?php
require_once 'db.php';

$uuid = $_GET['id'] ?? '';
$result = null;

if ($uuid) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM shared_results WHERE uuid = ?");
        $stmt->execute([$uuid]);
        $row = $stmt->fetch();
        if ($row) {
            $result = json_decode($row['data'], true);
        }
    } catch (PDOException $e) {
        // Handle DB error gracefully
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Result | Naija Cgpa</title>
    <!-- Tailwind CSS -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <!-- Font: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .slide-up {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Subtle Pattern Background */
        .bg-pattern {
            background-color: #F8FAFC;
            background-image: radial-gradient(#E2E8F0 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>

<body class="bg-pattern min-h-screen pb-32">

    <!-- Navbar -->
    <nav class="fixed top-0 w-full bg-white/90 backdrop-blur-xl border-b border-slate-200 z-50">
        <div class="container mx-auto px-6 h-20 flex items-center justify-between">
            <a href="index.php"
                class="text-2xl font-black text-indigo-900 tracking-tighter hover:opacity-80 transition">NAIJA CGPA</a>
            <a href="index.php"
                class="bg-slate-900 text-white px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-lg shadow-slate-200">
                Check Your Cgpa
            </a>
        </div>
    </nav>

    <main class="container mx-auto px-6 pt-32 max-w-2xl">
        <?php if ($result): ?>

            <!-- Main Document Card -->
            <div
                class="bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100 overflow-hidden border border-slate-100 slide-up relative">

                <!-- Report Header -->
                <div
                    class="p-8 md:p-10 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Official Document</p>
                        <h1 class="text-3xl font-black text-indigo-900 tracking-tight">Statement of Result</h1>
                    </div>
                    <div class="text-left md:text-right">
                        <div
                            class="inline-block bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-[0.1em] border border-indigo-100">
                            Academic Report
                        </div>
                    </div>
                </div>

                <!-- Student Context Grid -->
                <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100 bg-slate-50/50">
                    <div class="p-6 md:p-8 text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Level</p>
                        <p class="text-2xl font-black text-slate-800"><?php echo htmlspecialchars($result['lvl']); ?>L</p>
                    </div>
                    <div class="p-6 md:p-8 text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Semester</p>
                        <p class="text-2xl font-black text-slate-800">
                            <?php echo $result['activeIdx'] == 1 ? "1st" : "2nd"; ?>
                        </p>
                    </div>
                </div>

                <div class="p-8 md:p-10 space-y-10">

                    <!-- Course Details Table -->
                    <?php if (!empty($result['items'])): ?>
                        <div class="space-y-4">
                            <h3
                                class="text-xs font-black text-slate-800 uppercase tracking-widest pl-2 border-l-4 border-indigo-500">
                                Academic Performance
                            </h3>
                            <div class="bg-white border text-sm border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                                <table class="w-full text-left">
                                    <thead
                                        class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold tracking-widest border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 font-bold">Course</th>
                                            <th class="px-6 py-4 text-center">Unit</th>
                                            <th class="px-6 py-4 text-right">Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <?php foreach ($result['items'] as $item): ?>
                                            <?php if (isset($item['u']) && $item['u'] > 0): ?>
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="px-6 py-4 font-bold text-slate-700">
                                                        <?php echo htmlspecialchars($item['name'] ?: 'Course'); ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-center text-slate-500 font-medium">
                                                        <?php echo htmlspecialchars($item['u']); ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-right font-black text-indigo-700">
                                                        <?php echo htmlspecialchars($item['grade'] ?: '-'); ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Summary Section (The "Bottom Line") -->
                    <div class="bg-slate-900 rounded-3xl p-8 text-white relative overflow-hidden">
                        <!-- Decorative bg -->
                        <div
                            class="absolute top-0 right-0 w-64 h-64 bg-indigo-600 rounded-full blur-3xl opacity-20 -mr-20 -mt-20">
                        </div>

                        <div class="relative z-10 grid grid-cols-2 gap-8 items-center">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Semester GPA
                                </p>
                                <p class="text-3xl font-black text-indigo-300">
                                    <?php echo htmlspecialchars($result['semGPA']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Cumulative
                                    GPA</p>
                                <p class="text-4xl font-black text-emerald-400">
                                    <?php echo htmlspecialchars($result['globalCGPA']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="mt-8 pt-8 border-t border-white/10 text-center">
                            <span
                                class="inline-block bg-white/10 px-6 py-2 rounded-full text-xs font-bold uppercase tracking-widest">
                                <?php echo htmlspecialchars($result['globalClass']); ?>
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Footer area inside card -->
                <div class="bg-slate-50 p-6 text-center border-t border-slate-100 flex flex-col gap-2">
                    <p class="text-xs font-semibold text-slate-400">Verified by Naija Cgpa Calculation Engine</p>
                    <a href="https://Devmeki.xo.je" target="_blank"
                        class="text-[10px] font-bold text-indigo-400 hover:text-indigo-600 uppercase tracking-widest transition">
                        Created by DevMeki
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- 404 State -->
            <div class="text-center py-32 slide-up">
                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">🤷‍♂️</span>
                </div>
                <h2 class="text-3xl font-black text-slate-800 mb-2">Result Expired or Missing</h2>
                <p class="text-slate-500 mb-8">We couldn't find the result you are looking for.</p>
                <a href="index.php"
                    class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">Go
                    Home</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Mobile Sticky CTA -->
    <div
        class="fixed bottom-0 left-0 w-full bg-white/90 backdrop-blur-xl border-t border-slate-200 p-4 z-40 md:hidden slide-up delay-300">
        <a href="index.php"
            class="block w-full bg-indigo-700 text-white text-center py-4 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-indigo-800 transition shadow-lg shadow-indigo-200">
            Start calculating your Result
        </a>
    </div>

    <!-- Desktop Floating CTA -->
    <div class="hidden md:flex fixed bottom-10 right-10 z-50 items-center justify-center slide-up delay-300">
        <a href="index.php"
            class="group flex items-center gap-4 bg-slate-900 text-white pl-6 pr-4 py-4 rounded-full shadow-2xl hover:scale-105 transition-all hover:shadow-slate-500/30">
            <div class="text-left">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Want to check yours?</p>
                <p class="font-bold">Open Calculator</p>
            </div>
            <div
                class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center group-hover:bg-white/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </div>
        </a>
    </div>

    <!-- Smart Popup CTA -->
    <div id="cta-popup"
        class="fixed inset-0 bg-slate-900/60 z-[100] hidden flex items-center justify-center backdrop-blur-sm opacity-0 transition-opacity duration-500">
        <div class="bg-white p-8 rounded-[2.5rem] max-w-sm w-full mx-4 text-center shadow-2xl scale-90 transition-transform duration-500"
            id="cta-content">
            <div
                class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-inner rotate-3">
                <span class="text-4xl">🚀</span>
            </div>
            <h2 class="text-2xl font-black text-slate-800 mb-3 leading-tight">Take Control of Your Grades</h2>
            <p class="text-slate-500 text-sm mb-8 leading-relaxed px-2 font-medium">Join thousands of students using
                Naija Cgpa to calculate, track, and improve their academic performance.</p>
            <div class="space-y-3">
                <a href="index.php"
                    class="block w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-wider hover:bg-indigo-700 transition shadow-xl shadow-indigo-200">Calculate
                    Now</a>
                <button onclick="closePopup()"
                    class="block w-full text-slate-400 font-bold text-xs py-2 hover:text-slate-600">CONTINUE
                    READING</button>
            </div>
        </div>
    </div>

    <script>
        // Popup Logic with Animation
        setTimeout(() => {
            const popup = document.getElementById('cta-popup');
            const content = document.getElementById('cta-content');
            if (popup && content) {
                popup.classList.remove('hidden');
                // Trigger reflow
                void popup.offsetWidth;
                popup.classList.remove('opacity-0');
                content.classList.remove('scale-90');
                content.classList.add('scale-100', 'rotate-0');
            }
        }, 5000);

        function closePopup() {
            const popup = document.getElementById('cta-popup');
            if (popup) {
                popup.classList.add('opacity-0');
                setTimeout(() => popup.classList.add('hidden'), 500);
            }
        }
    </script>
</body>

</html>