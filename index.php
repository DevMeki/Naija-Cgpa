<?php
session_start();
require_once 'tracker.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Naijacgpa | The Ultimate CGPA Tracker</title>

    <!-- Tailwind CSS v4 -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

    <!-- Font: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- jsPDF & AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <!-- html2canvas -->
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

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
            scroll-behavior: smooth;
        }

        .glass {
            background: var(--glass-white);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
        }

        .hero-gradient {
            background: linear-gradient(135deg, #0056D2 0%, #00B4DB 100%);
        }

        .blue-gradient {
            background: linear-gradient(135deg, #0056D2 0%, #0081FF 100%);
        }

        .pop-in {
            animation: popIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes popIn {
            from {
                transform: scale(0.95);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .slide-up {
            animation: slideUp 0.5s ease-out forwards;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }

        .active-tab {
            background: #FFFFFF !important;
            color: var(--primary) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Fix for mobile keyboard */
        input:focus {
            font-size: 16px;
        }
    </style>
</head>

<body class="safe-pb">

    <!-- NAVIGATION -->
    <nav
        class="fixed top-0 left-0 right-0 z-[60] glass border-b border-black/5 px-6 py-4 flex justify-between items-center h-20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 bg-primary-gradient blue-gradient rounded-xl flex items-center justify-center font-black text-white text-sm shadow-xl shadow-blue-500/20">
                NS
            </div>
            <div>
                <h1 class="text-sm font-extrabold tracking-tight text-slate-800">NAIJA CGPA</h1>
                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Global Academic Tracker</p>
            </div>
        </div>

        <div class="hidden md:flex items-center gap-8 text-sm font-bold text-slate-500">
            <a href="index" class="hover:text-primary transition-colors">Home</a>
            <a href="#calculator" class="hover:text-primary transition-colors">Calculator</a>
            <a href="#" class="hover:text-primary transition-colors">Blog</a> <!-- Feature Coming Soon -->
            <a href="https://devmeki.xo.je" class="hover:text-primary transition-colors">About The Creator</a>
        </div>

        <div class="flex items-center gap-3">
            <!-- Help Icon -->
            <a href="help.html"
                class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-400 hover:text-primary hover:bg-slate-100 transition-all"
                title="Help Guide">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>

            <div id="auth-controls" class="hidden md:flex items-center gap-3">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="auth"
                        class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-indigo-600/20 hover:scale-105 transition-all">
                        Login
                    </a>
                <?php else: ?>
                    <button onclick="saveRemote()"
                        class="bg-blue-50 text-primary-light px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-blue-100 transition-all">
                        Save Record
                    </button>
                    <a href="profile"
                        class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:text-primary transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Toggle -->
            <button id="mobile-menu-btn" onclick="toggleMobileMenu()"
                class="md:hidden w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-600 hover:bg-slate-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu"
        class="fixed inset-0 z-[59] bg-white/95 backdrop-blur-xl transform translate-x-full transition-transform duration-300 md:hidden flex flex-col pt-24 px-6 gap-6">
        <a href="index" onclick="toggleMobileMenu()"
            class="text-xl font-bold text-slate-800 hover:text-primary">Home</a>
        <a href="#calculator" onclick="toggleMobileMenu()"
            class="text-xl font-bold text-slate-800 hover:text-primary">Calculator</a>
        <a href="#" onclick="toggleMobileMenu()" class="text-xl font-bold text-slate-800 hover:text-primary">Blog</a>
        <a href="https://devmeki.xo.je" onclick="toggleMobileMenu()"
            class="text-xl font-bold text-slate-800 hover:text-primary">About the Creator</a>
        <hr class="border-slate-100">
        <div class="flex flex-col gap-4">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="auth" class="bg-indigo-600 text-white py-4 rounded-xl font-bold text-center shadow-lg">Login / Sign
                    Up</a>
            <?php else: ?>
                <button onclick="saveRemote(); toggleMobileMenu()"
                    class="bg-blue-50 text-primary-light py-4 rounded-xl font-bold text-center">Save Record</button>
                <a href="profile" class="bg-slate-100 text-slate-600 py-4 rounded-xl font-bold text-center">My Profile</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- HERO SECTION -->
    <header class="pt-32 pb-20 hero-gradient text-white overflow-hidden relative">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                <circle cx="10" cy="10" r="1" fill="white" />
                <circle cx="90" cy="50" r="2" fill="white" />
                <circle cx="50" cy="90" r="1" fill="white" />
            </svg>
        </div>

        <div class="container mx-auto px-6 grid md:grid-cols-2 gap-12 items-center relative z-10">
            <div class="space-y-8 slide-up">
                <h2 class="text-4xl md:text-6xl font-extrabold leading-tight tracking-tight">
                    Calculate Your GPA & <br> <span class="text-cyan-200">CGPA with Ease</span>
                </h2>
                <p class="text-lg text-white/80 max-w-md leading-relaxed">
                    Trusted GPA/CGPA calculator for Nigerian universities & polytechnics. Track your academic journey
                    with precision.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="#calculator"
                        class="bg-emerald-600 text-white px-8 py-4 rounded-2xl font-bold text-sm shadow-2xl shadow-emerald-600/30 hover:bg-emerald-500 transition-all flex items-center gap-2">
                        Start Calculating
                    </a>
                    <a href="profile"
                        class="bg-white/10 backdrop-blur-md border border-white/20 text-white px-8 py-4 rounded-2xl font-bold text-sm hover:bg-white/20 transition-all">
                        Track Records
                    </a>
                </div>
                <div class="flex items-center gap-3 text-xs font-bold text-white/70">
                    <svg class="w-5 h-5 text-cyan-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    Uses NUC 5.0 Grading Standard
                </div>
            </div>

            <div class="hidden md:block slide-up" style="animation-delay: 0.2s">
                <!-- Illustration Placeholder (Real students prefer visuals) -->
                <div class="relative bg-white/5 p-4 rounded-[3rem] border border-white/10 backdrop-blur-xl">
                    <div class="bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-3xl">
                        <img src="./whisk_webp.webp" alt="Student mockup" class="opacity-80">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- TRUST BADGES -->
    <section class="container mx-auto px-6 -mt-12 relative z-20">
        <div class="glass rounded-3xl p-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-center shadow-2xl border-white/60">
            <div class="flex flex-col items-center gap-3">
                <div
                    class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500 border border-orange-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-tight">Accurate
                    <br>Results
                </p>
            </div>
            <div class="flex flex-col items-center gap-3 border-l border-slate-200/50">
                <div
                    class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 border border-emerald-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-tight">Nigerian
                    <br>Grading System
                </p>
            </div>
            <div class="flex flex-col items-center gap-3 border-l border-slate-200/50">
                <div
                    class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 border border-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-tight">Track Your
                    <br>Progress
                </p>
            </div>
            <div class="flex flex-col items-center gap-3 border-l border-slate-200/50">
                <div
                    class="w-12 h-12 bg-cyan-50 rounded-2xl flex items-center justify-center text-cyan-600 border border-cyan-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-tight">Data <br>Secure
                </p>
            </div>
        </div>
    </section>

    <!-- MAIN WORKSPACE -->
    <main id="calculator" class="container mx-auto px-6 py-20">
        <div id="level-tabs"
            class="grid grid-cols-3 md:flex md:flex-wrap gap-4 md:gap-6 mb-8 pt-6 overflow-x-auto pb-4 snap-x no-scrollbar scroll-pl-6 items-center">
            <!-- Dynamic button injection -->
            <button onclick="addNewLevelUI()"
                class="flex-shrink-0 relative group h-11 px-6 rounded-xl border-2 border-dashed border-slate-300 text-slate-500 font-bold text-xs flex items-center justify-center gap-2 hover:border-primary-light hover:text-primary hover:bg-white transition-all order-last md:min-w-[120px] snap-start">
                <span class="text-lg">+</span> Add Level
            </button>
        </div>

        <div class="grid lg:grid-cols-12 gap-10">
            <!-- Left: Workspace -->
            <div class="lg:col-span-8 space-y-8 slide-up">
                <!-- Semester Toggle -->
                <div class="flex gap-2 mb-4 p-1 bg-slate-100 rounded-2xl w-fit">
                    <button onclick="switchSemester(1)" id="sem-btn-1"
                        class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all bg-white text-primary shadow-sm">
                        1st Semester
                    </button>
                    <button onclick="switchSemester(2)" id="sem-btn-2"
                        class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600">
                        2nd Semester
                    </button>
                </div>

                <div id="semester-container" class="space-y-8">
                    <!-- Semester content injected here -->
                </div>
            </div>

            <!-- Right: Fixed Summary -->
            <aside class="lg:col-span-4 lg:sticky lg:top-32 h-fit space-y-6 slide-up" style="animation-delay: 0.1s">
                <div
                    class="blue-gradient rounded-[2.5rem] p-8 text-white shadow-2xl shadow-blue-500/30 overflow-hidden relative">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>

                    <div class="space-y-6 relative z-10">
                        <div>
                            <p class="text-[10px] font-black text-blue-100 uppercase tracking-[0.2em] mb-4">Live
                                Performance</p>
                            <div class="flex justify-between items-end border-b border-white/10 pb-6 mb-6">
                                <div>
                                    <p class="text-[10px] font-bold text-blue-200 uppercase mb-1">Semester GPA</p>
                                    <p class="text-3xl font-extrabold" id="sem-gpa-val">0.00</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-bold text-blue-200 uppercase mb-1">Total CGPA</p>
                                    <p class="text-5xl font-extrabold" id="global-cgpa">0.00</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-100 uppercase tracking-widest">Class of
                                Degree:</span>
                            <span
                                class="bg-success text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                id="global-class">---</span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 mt-8 pt-6 border-t border-white/10">
                            <button onclick="exportPDF()"
                                class="bg-white/10 p-3 rounded-xl flex flex-col items-center gap-1 hover:bg-white/20 transition-all">
                                <span class="text-[8px] font-extrabold uppercase">PDF</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                            </button>
                            <button onclick="exportImage()"
                                class="bg-white/10 p-3 rounded-xl flex flex-col items-center gap-1 hover:bg-white/20 transition-all">
                                <span class="text-[8px] font-extrabold uppercase">Image</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <button onclick="shareResult()"
                                class="bg-white/10 p-3 rounded-xl flex flex-col items-center gap-1 hover:bg-white/20 transition-all">
                                <span class="text-[8px] font-extrabold uppercase">Share</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="glass rounded-[2rem] p-6 text-center border-dashed">
                    <p class="text-xs font-bold text-slate-400 mb-1">TOTAL CREDITS</p>
                    <p id="global-units" class="text-2xl font-black text-slate-800 tracking-tight">0 Units</p>
                </div>
            </aside>
        </div>
    </main>

    <!-- HOW IT WORKS -->
    <section class="bg-indigo-50/30 py-24 border-y border-slate-200/50">
        <div class="container mx-auto px-6">
            <h3 class="text-center text-xs font-black text-primary-light uppercase tracking-[0.4em] mb-4">Workflow</h3>
            <h4 class="text-center text-3xl font-extrabold text-slate-800 mb-16">How It Works</h4>

            <div class="grid md:grid-cols-4 gap-8">
                <div
                    class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 hover:scale-105 transition-all group">
                    <div
                        class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <p class="font-extrabold text-slate-900 mb-2">01. Enter Courses</p>
                    <p class="text-xs text-slate-500 leading-relaxed font-medium">Add your course codes and names as
                        they appear on your result sheet.</p>
                </div>
                <div
                    class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 hover:scale-105 transition-all group">
                    <div
                        class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <p class="font-extrabold text-slate-900 mb-2">02. Add Units & Grades</p>
                    <p class="text-xs text-slate-400 leading-relaxed font-medium">Select the unit loads and your grades
                        (A, B, C, etc.) for each course.</p>
                </div>
                <div
                    class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 hover:scale-105 transition-all group">
                    <div
                        class="w-14 h-14 bg-cyan-50 rounded-2xl flex items-center justify-center text-cyan-600 mb-6 group-hover:bg-cyan-600 group-hover:text-white transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="font-extrabold text-slate-900 mb-2">03. Calculate GPA</p>
                    <p class="text-xs text-slate-400 leading-relaxed font-medium">Our system automatically computes your
                        GPA based on NUC 5.0 scale.</p>
                </div>
                <div
                    class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 hover:scale-105 transition-all group">
                    <div
                        class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 mb-6 group-hover:bg-orange-600 group-hover:text-white transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <p class="font-extrabold text-slate-900 mb-2">04. Save & Export</p>
                    <p class="text-xs text-slate-400 leading-relaxed font-medium">Store your results securely or
                        download as PDF for reference.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section class="py-24 container mx-auto px-6 max-w-4xl">
        <h3 class="text-center text-3xl font-extrabold text-slate-800 mb-16">Frequently Asked Questions</h3>
        <div class="space-y-4">
            <details
                class="group bg-white rounded-3xl p-6 border border-slate-100 shadow-sm open:shadow-md transition-all">
                <summary class="list-none flex justify-between items-center cursor-pointer">
                    <span class="font-bold text-slate-700">How is GPA & CGPA calculated?</span>
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">↓</span>
                </summary>
                <div class="mt-4 text-sm text-slate-500 leading-relaxed border-t pt-4">
                    The GPA is calculated by dividing the total Quality Points (Units × Grade Value) by the total Credit
                    Units. CGPA is the average of all your semester GPAs.
                </div>
            </details>
            <details
                class="group bg-white rounded-3xl p-6 border border-slate-100 shadow-sm open:shadow-md transition-all">
                <summary class="list-none flex justify-between items-center cursor-pointer">
                    <span class="font-bold text-slate-700">Does this work for polytechnics?</span>
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">↓</span>
                </summary>
                <div class="mt-4 text-sm text-slate-500 leading-relaxed border-t pt-4">
                    Yes! Although most Nigerian polytechnics use the 4.0 scale, our calculator currently supports the
                    universal 5.0 scale used by nearly all universities.
                </div>
            </details>
            <details
                class="group bg-white rounded-3xl p-6 border border-slate-100 shadow-sm open:shadow-md transition-all">
                <summary class="list-none flex justify-between items-center cursor-pointer">
                    <span class="font-bold text-slate-700">Can I save my results?</span>
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">↓</span>
                </summary>
                <div class="mt-4 text-sm text-slate-500 leading-relaxed border-t pt-4">
                    Absolutely. By creating a free account, you can store your records securely and access them from any
                    device at any time.
                </div>
            </details>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-900 pt-20 pb-10 text-white rounded-t-[3rem]">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 pb-20 border-b border-white/5">
                <div class="space-y-6">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 blue-gradient rounded-lg flex items-center justify-center font-black text-xs">
                            NS</div>
                        <span class="font-extrabold tracking-tighter">Naija Scholar</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed font-medium">The most accurate academic tracking
                        platform for Nigerian students. Simplified, secure, and reliable.</p>
                </div>
                <div>
                    <h5 class="font-bold mb-6 text-sm uppercase tracking-widest text-slate-500">Quick Links</h5>
                    <ul class="space-y-4 text-sm font-bold text-slate-300">
                        <li><a href="#calculator" class="hover:text-primary-light transition-colors">Calculator</a></li>
                        <li><a href="help.html" class="hover:text-primary-light transition-colors">Help Guide</a></li>
                        <li><a href="admin_dashboard.php" class="hover:text-primary-light transition-colors">Admin
                                Dashboard</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-bold mb-6 text-sm uppercase tracking-widest text-slate-500">Legal</h5>
                    <ul class="space-y-4 text-sm font-bold text-slate-300">
                        <li><a href="#" class="hover:text-primary-light transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-primary-light transition-colors">Terms of Service</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-bold mb-6 text-sm uppercase tracking-widest text-slate-500">Connect</h5>
                    <p class="text-sm font-bold text-slate-300 mb-4">support@naijacgpa.ng</p>
                    <div class="flex gap-4">
                        <a href="https://x.com">
                            <div
                                class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center hover:bg-white/10 transition-all cursor-pointer">
                                𝕏</div>
                        </a>
                        <!-- <div
                            class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center hover:bg-white/10 transition-all cursor-pointer">
                            in</div> -->
                    </div>
                </div>
            </div>
            <p class="text-center pt-10 text-xs font-black text-slate-600 uppercase tracking-widest">© 2026 NAiJA
                CGPA • MADE FOR NIGERIA</p>
        </div>
    </footer>

    <!-- JS TEMPLATES (Engine Integration) -->

    <!-- Empty Slot -->
    <template id="tpl-empty">
        <div class="bg-white rounded-[2rem] p-8 text-center border border-slate-100 shadow-sm pop-in">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8 sem-label">First Semester
            </h3>
            <div class="grid grid-cols-1 gap-4">
                <button onclick="setupSem(this, 'quick')"
                    class="p-6 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center gap-2 group active:scale-95 transition-all">
                    <span class="text-lg font-bold text-slate-700 group-hover:text-primary">GPA & Units Entry</span>
                    <span class="text-[9px] text-slate-400 uppercase tracking-widest font-black">Quick Mode</span>
                </button>
                <button onclick="setupSem(this, 'detail')"
                    class="p-6 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center gap-2 group active:scale-95 transition-all">
                    <span class="text-lg font-bold text-slate-700 group-hover:text-primary">Course by Course</span>
                    <span class="text-[9px] text-slate-400 uppercase tracking-widest font-black">Detailed Mode</span>
                </button>
            </div>
        </div>
    </template>

    <!-- Detailed Mode Sidebar/List Structure -->
    <template id="tpl-detail">
        <div class="bg-white rounded-[2rem] p-6 pop-in border-l-4 border-indigo-500 shadow-sm" data-sem>
            <div class="flex justify-between items-center mb-6 px-1">
                <div>
                    <h3 class="text-[10px] font-black text-slate-800 uppercase tracking-[0.2em] sem-label">First
                        Semester</h3>
                    <p class="text-[8px] font-black text-slate-400 uppercase mt-1 sem-meta">0 COURSES • 0 UNITS</p>
                </div>
                <div class="flex bg-slate-100 p-1 rounded-xl">
                    <button onclick="toggleInputType(this, 'grade')"
                        class="btn-grade px-4 py-1.5 rounded-lg text-[9px] font-black transition-all">GRADE</button>
                    <button onclick="toggleInputType(this, 'score')"
                        class="btn-score px-4 py-1.5 rounded-lg text-[9px] font-black transition-all">SCORE</button>
                </div>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="text-[8px] font-black text-slate-400 uppercase pb-3 pl-2 w-1/2">Course Code</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase pb-3 text-center w-1/4">Units</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase pb-3 text-center w-1/4 val-label">
                                Grade</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="course-list space-y-2">
                        <!-- Course rows injection -->
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col gap-3">
                <button onclick="addNewCourse(this)"
                    class="w-full py-4 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 transition-all">
                    + Add Course
                </button>
                <div class="flex justify-between items-center mt-2 px-2">
                    <button onclick="setupSem(this, 'quick')"
                        class="text-[9px] font-black text-slate-400 uppercase hover:text-indigo-500">Switch to
                        Quick</button>
                    <button onclick="resetSem(this)"
                        class="text-[9px] font-black text-rose-300 uppercase hover:text-rose-500">Reset</button>
                </div>
            </div>
        </div>
    </template>

    <template id="tpl-course">
        <tr class="course-item group border-b border-slate-50 last:border-0 slide-up">
            <td class="py-2 pr-2">
                <input type="text" placeholder="e.g. MTH101"
                    class="inp-name w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-xs font-bold text-slate-900 outline-none focus:border-indigo-500 focus:bg-white transition-all">
            </td>
            <td class="py-2 px-1">
                <input type="number" placeholder="0"
                    class="inp-u w-full bg-slate-50 border border-slate-200 rounded-xl py-3 text-center text-xs font-bold text-slate-900 outline-none focus:border-indigo-500 focus:bg-white transition-all">
            </td>
            <td class="py-2 pl-2">
                <div class="relative">
                    <select
                        class="inp-g w-full bg-slate-50 border border-slate-200 rounded-xl py-3 text-center text-xs font-bold text-indigo-600 outline-none appearance-none cursor-pointer focus:border-indigo-500 focus:bg-white transition-all">
                        <option value="">--</option>
                        <option value="5">A</option>
                        <option value="4">B</option>
                        <option value="3">C</option>
                        <option value="2">D</option>
                        <option value="1">E</option>
                        <option value="0">F</option>
                    </select>
                    <input type="number" placeholder="0-100"
                        class="inp-s hidden w-full bg-slate-50 border border-slate-200 rounded-xl py-3 text-center text-xs font-bold text-indigo-600 outline-none focus:border-indigo-500 focus:bg-white transition-all">
                </div>
            </td>
            <td class="py-2 pl-2 text-center">
                <button onclick="this.closest('.course-item').remove(); calc();"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-all font-bold">×</button>
            </td>
        </tr>
    </template>

    <!-- Quick Mode Template -->
    <template id="tpl-quick">
        <div class="bg-white rounded-[2rem] p-8 pop-in border-l-4 border-success shadow-sm">
            <div class="flex justify-between items-center mb-8 px-2">
                <h3 class="text-[10px] font-black text-slate-800 uppercase tracking-[0.2em] sem-label">First Semester
                </h3>
                <button onclick="setupSem(this, 'detail')"
                    class="text-[9px] font-black text-primary-light uppercase">Switch to Detail</button>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="text-[8px] font-black text-slate-400 uppercase mb-3 block px-2">GPA Value</label>
                    <input type="number" step="0.01" placeholder="0.00"
                        class="inp-gpa w-full bg-slate-50 border border-slate-100 rounded-2xl py-4 px-6 text-2xl font-black text-slate-800 outline-none focus:border-success/30">
                </div>
                <div>
                    <label class="text-[8px] font-black text-slate-400 uppercase mb-3 block px-2">Total Units</label>
                    <input type="number" placeholder="0"
                        class="inp-units w-full bg-slate-50 border border-slate-100 rounded-2xl py-4 px-6 text-2xl font-black text-slate-800 outline-none focus:border-success/30">
                </div>
            </div>
            <div class="mt-8 pt-4 border-t border-slate-50 flex justify-end">
                <button onclick="resetSem(this)" class="text-[9px] font-black text-rose-300 uppercase">Clear
                    All</button>
            </div>
        </div>
    </template>

    <!-- <script src="script.js?v=3.1"></script> -->
    <script src="script.js"></script>
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const btn = document.getElementById('mobile-menu-btn');

            if (menu.classList.contains('translate-x-full')) {
                // Open
                menu.classList.remove('translate-x-full');
                // Change icon to close (optional, but simple toggle is fine for now)
            } else {
                // Close
                menu.classList.add('translate-x-full');
            }
        }
    </script>

    <!-- GUEST POPUP -->
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div id="signup-popup"
            class="fixed inset-0 z-[100] hidden flex items-center justify-center px-4 bg-slate-900/60 backdrop-blur-md">
            <div
                class="max-w-md w-full glass rounded-[3rem] p-10 shadow-3xl text-center space-y-8 pop-in relative border-white/20">
                <button onclick="document.getElementById('signup-popup').remove()"
                    class="absolute top-8 right-8 w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-800 transition-all">×</button>
                <div
                    class="w-20 h-20 blue-gradient rounded-3xl flex items-center justify-center mx-auto shadow-2xl shadow-blue-500/20">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="space-y-4">
                    <h4 class="text-2xl font-black text-slate-800">Secure Your Progress</h4>
                    <p class="text-sm font-medium text-slate-500 leading-relaxed px-4">Create a free account to
                        automatically save your semester records and track your academic journey across all devices.</p>
                </div>
                <div class="space-y-3">
                    <a href="auth"
                        class="block w-full py-4 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:scale-[1.02] transition-all shadow-xl shadow-indigo-500/20">Create
                        Free Account</a>

                    <button onclick="document.getElementById('signup-popup').remove()"
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-800 transition-colors">Maybe
                        Later</button>
                </div>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const popup = document.getElementById('signup-popup');
                if (popup) { popup.classList.remove('hidden'); popup.classList.add('flex'); }
            }, 5000);
        </script>
    <?php endif; ?>

    <!-- NOTIFICATION TOAST -->
    <div id="toast"
        class="fixed top-6 left-1/2 -translate-x-1/2 z-[70] hidden opacity-0 transition-opacity duration-300">
        <div
            class="bg-indigo-900/90 backdrop-blur-md text-white px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-indigo-500/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            <span class="text-xs font-bold tracking-wide" id="toast-msg">Notification</span>
        </div>
    </div>

    <!-- SHARE MODAL -->
    <div id="share-modal"
        class="fixed inset-0 z-[110] hidden flex items-center justify-center px-4 bg-slate-900/60 backdrop-blur-md">
        <div class="max-w-sm w-full bg-white rounded-[2rem] p-8 shadow-2xl relative pop-in">
            <button onclick="document.getElementById('share-modal').classList.add('hidden')"
                class="absolute top-6 right-6 w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-800 transition">×</button>

            <div class="text-center mb-8">
                <div
                    class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
                    🚀</div>
                <h3 class="text-xl font-black text-slate-800">Share Result</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Show off your performance</p>
            </div>

            <div class="space-y-6">
                <!-- Link Box -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 flex items-center gap-3">
                    <input type="text" id="share-link" readonly
                        class="bg-transparent w-full text-xs font-bold text-slate-600 outline-none"
                        placeholder="Generating link...">
                    <button onclick="copyShareLink()"
                        class="text-indigo-600 text-xs font-black uppercase hover:text-indigo-800">Copy</button>
                </div>

                <!-- Social Icons -->
                <div class="grid grid-cols-4 gap-4">
                    <a href="#" id="share-wa" target="_blank"
                        class="h-12 bg-[#25D366]/10 text-[#25D366] rounded-xl flex items-center justify-center text-xl hover:scale-110 transition"><i
                            class="fab fa-whatsapp"></i>W</a>
                    <a href="#" id="share-tw" target="_blank"
                        class="h-12 bg-black/5 text-black rounded-xl flex items-center justify-center text-xl hover:scale-110 transition"><i
                            class="fab fa-twitter"></i>X</a>
                    <a href="#" id="share-fb" target="_blank"
                        class="h-12 bg-[#1877F2]/10 text-[#1877F2] rounded-xl flex items-center justify-center text-xl hover:scale-110 transition"><i
                            class="fab fa-facebook-f"></i>F</a>
                    <a href="#" id="share-li" target="_blank"
                        class="h-12 bg-[#0A66C2]/10 text-[#0A66C2] rounded-xl flex items-center justify-center text-xl hover:scale-110 transition"><i
                            class="fab fa-linkedin-in"></i>L</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>