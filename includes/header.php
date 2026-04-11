<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EQ-Math - Platform Pendaftaran Kelas Matematika">
    <meta name="author" content="EQ-Math Team">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - EQ Math</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sidebar transitions */
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }

        /* Card hover effect */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.1);
        }

        /* Smooth transitions */
        .transition-smooth {
            transition: all 0.2s ease-in-out;
        }

        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #2563eb;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Status badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased">

<!-- Mobile Menu Button -->
<?php if (isset($showMobileMenu) && $showMobileMenu): ?>
<button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-primary-600 text-white p-3 rounded-xl shadow-lg hover:bg-primary-700 transition">
    <i class="fas fa-bars"></i>
</button>
<?php endif; ?>

<!-- Overlay for mobile -->
<?php if (isset($showSidebar) && $showSidebar): ?>
<div id="overlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>
<?php endif; ?>

<div class="flex min-h-screen">

<?php if (isset($showSidebar) && $showSidebar): ?>
<aside id="sidebar" class="sidebar fixed lg:static inset-y-0 left-0 z-40 w-72 bg-gradient-to-b from-primary-700 to-primary-900 text-white transform -translate-x-full lg:translate-x-0 overflow-y-auto">
    <?php include $sidebarFile; ?>
</aside>
<?php endif; ?>

<!-- Main Content -->
<main class="flex-1 p-4 lg:p-8 <?php echo (isset($showSidebar) && $showSidebar) ? 'lg:ml-0' : ''; ?>">
