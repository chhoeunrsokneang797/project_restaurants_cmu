<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final - Project</title>
</head>

<body>

    <style>
        /* ── Main ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Header ── */
        .header {
            height: var(--header-h);
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 50;
            gap: 16px;
        }

        .header-title {
            font-size: 16px;
            font-weight: 500;
            color: var(--text);
            flex: 1;
        }

        .header-sub {
            font-size: 12px;
            color: var(--text3);
            margin-top: 1px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 12px;
            width: 220px;
        }

        .search-box svg {
            width: 14px;
            height: 14px;
            stroke: var(--text3);
            fill: none;
            stroke-width: 2;
            flex-shrink: 0;
        }

        .search-box input {
            background: none;
            border: none;
            outline: none;
            color: var(--text2);
            font-family: var(--font);
            font-size: 13px;
            width: 100%;
        }

        .search-box input::placeholder {
            color: var(--text3);
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .15s;
            position: relative;
        }

        .icon-btn:hover {
            border-color: var(--border2);
            background: var(--bg4);
        }

        .icon-btn svg {
            width: 16px;
            height: 16px;
            stroke: var(--text2);
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .notif-dot {
            position: absolute;
            top: 7px;
            right: 7px;
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            border: 1.5px solid var(--bg2);
        }

        .period-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg3);
            color: var(--text2);
            font-family: var(--font);
            font-size: 12.5px;
            cursor: pointer;
            transition: all .15s;
        }

        .period-btn:hover {
            border-color: var(--border2);
            color: var(--text);
        }

        .period-btn svg {
            width: 12px;
            height: 12px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .content {
            padding: 10px;
        }
    </style>


    <!-- Header -->
    <div class="main">
        <header class="header">
            <div style="flex:1">
                <div class="header-title" id="header-title">Administrator</div>
                <div class="header-sub">Welcome to our Restaurant, here's what's happening today.</div>
            </div>
            <!-- <div class="search-box">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" placeholder="Search..." />
            </div> -->
            <button class="period-btn">
                <i class="fa-regular fa-calendar"></i>
                <span id="date-display"></span>
            </button>

            <button class="period-btn">
                <i class="fa-regular fa-clock"></i>    
                <span id="clock"></span>
            </button>
            <div class="user-card" style="padding:6px; cursor:pointer;">

                <!-- User Image -->
                <img src="../images/logo-ang.png" alt="Cashier" class="mini-user-avatar">
            </div>
        </header>

        <div class="content">

    <script>
        // ── CLOCK ─────────────────────────────────────────────────────────────────────
        function tick() {
            const n = new Date();
            document.getElementById("clock").textContent = n.toLocaleTimeString([], {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            });
            document.getElementById("date-display").textContent = n.toLocaleDateString([], {
                weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
            });
        }
        setInterval(tick, 1000);
        tick();
    </script>
    
</body>
</html>