<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<!DOCTYPE html><!-- Include this CSS in your <head> or a shared stylesheet -->
<style>
    /* ══════════════════════════════════
       SIDEBAR — BETHEL PHARMACY
    ══════════════════════════════════ */
    :root {
        --sb-bg:          #0a2e4a;
        --sb-bg-deep:     #071e30;
        --sb-surface:     rgba(255,255,255,0.05);
        --sb-border:      rgba(255,255,255,0.08);
        --sb-accent:      #00c9a7;
        --sb-accent-glow: rgba(0,201,167,0.20);
        --sb-text:        #cbd5e1;
        --sb-text-bright: #f0f6ff;
        --sb-muted:       rgba(255,255,255,0.35);
        --sb-width:       260px;
        --sb-radius:      12px;
    }

    /* ── Sidebar Shell ── */
    .sidebar {
        width: var(--sb-width);
        height: 100vh;
        position: fixed;
        left: 0; top: 0;
        background: linear-gradient(180deg, var(--sb-bg) 0%, var(--sb-bg-deep) 100%);
        display: flex;
        flex-direction: column;
        z-index: 200;
        overflow: hidden;
        font-family: 'Sora', sans-serif;
    }

    /* decorative top glow */
    .sidebar::before {
        content: '';
        position: absolute;
        top: -60px; left: -60px;
        width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(0,201,167,0.18) 0%, transparent 70%);
        pointer-events: none;
    }

    /* ── Brand Area ── */
    .sb-brand {
        padding: 28px 22px 20px;
        border-bottom: 1px solid var(--sb-border);
        position: relative;
    }

    .sb-brand-inner {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .sb-logo-wrap {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        background: linear-gradient(135deg, var(--sb-accent), #0097a7);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 800;
        color: white;
        letter-spacing: -1px;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(0,201,167,0.35);
    }

    .sb-brand-text .name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--sb-text-bright);
        line-height: 1.2;
        letter-spacing: -0.3px;
    }

    .sb-brand-text .tagline {
        font-size: 0.68rem;
        color: var(--sb-accent);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2px;
    }

    /* status indicator */
    .sb-status {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 14px;
        padding: 7px 12px;
        background: var(--sb-surface);
        border: 1px solid var(--sb-border);
        border-radius: 8px;
    }

    .sb-status .dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: var(--sb-accent);
        box-shadow: 0 0 8px var(--sb-accent);
        animation: pulse 2s infinite;
        flex-shrink: 0;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.4; }
    }

    .sb-status span {
        font-size: 0.71rem;
        color: var(--sb-muted);
        font-weight: 500;
    }

    .sb-status span b {
        color: var(--sb-text);
        font-weight: 600;
    }

    /* ── Section Label ── */
    .sb-section-label {
        padding: 18px 22px 6px;
        font-size: 0.62rem;
        font-weight: 700;
        color: var(--sb-muted);
        text-transform: uppercase;
        letter-spacing: 1.2px;
    }

    /* ── Nav List ── */
    .sb-nav {
        list-style: none;
        padding: 4px 12px 0;
        margin: 0;
        flex: 1;
        overflow-y: auto;
        scrollbar-width: none;
    }

    .sb-nav::-webkit-scrollbar { display: none; }

    .sb-nav li { margin-bottom: 2px; }

    .sb-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        border-radius: 10px;
        text-decoration: none;
        color: var(--sb-text);
        font-size: 0.855rem;
        font-weight: 500;
        transition: all 0.18s ease;
        position: relative;
        overflow: hidden;
    }

    .sb-nav a:hover {
        background: var(--sb-surface);
        color: var(--sb-text-bright);
        padding-left: 18px;
    }

    /* Active state */
    .sb-nav a.active {
        background: linear-gradient(135deg, rgba(0,201,167,0.18), rgba(0,201,167,0.06));
        color: var(--sb-accent);
        font-weight: 600;
        border: 1px solid rgba(0,201,167,0.20);
    }

    .sb-nav a.active::before {
        content: '';
        position: absolute;
        left: 0; top: 15%; bottom: 15%;
        width: 3px;
        background: var(--sb-accent);
        border-radius: 0 3px 3px 0;
        box-shadow: 0 0 8px var(--sb-accent);
    }

    /* Icon wrap */
    .sb-nav a .nav-icon {
        width: 32px; height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        background: rgba(255,255,255,0.06);
        transition: all 0.18s;
        flex-shrink: 0;
    }

    .sb-nav a.active .nav-icon {
        background: var(--sb-accent-glow);
        color: var(--sb-accent);
    }

    .sb-nav a:hover .nav-icon {
        background: rgba(255,255,255,0.10);
    }

    /* Badge */
    .sb-badge {
        margin-left: auto;
        background: var(--sb-accent-glow);
        color: var(--sb-accent);
        font-size: 0.67rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
        border: 1px solid rgba(0,201,167,0.25);
        letter-spacing: 0.3px;
    }

    /* Divider */
    .sb-divider {
        height: 1px;
        background: var(--sb-border);
        margin: 10px 12px;
    }

    /* ── Logout ── */
    .sb-nav a.logout {
        color: #f87171;
    }

    .sb-nav a.logout .nav-icon {
        background: rgba(248,113,113,0.10);
        color: #f87171;
    }

    .sb-nav a.logout:hover {
        background: rgba(248,113,113,0.10);
        color: #fca5a5;
    }

    /* ── Footer ── */
    .sb-footer {
        padding: 14px 16px;
        border-top: 1px solid var(--sb-border);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sb-user-avatar {
        width: 34px; height: 34px;
        border-radius: 9px;
        background: linear-gradient(135deg, #1b6ca8, var(--sb-accent));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .sb-user-info .uname {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--sb-text-bright);
        line-height: 1.2;
    }

    .sb-user-info .urole {
        font-size: 0.68rem;
        color: var(--sb-muted);
    }

    .sb-footer-action {
        margin-left: auto;
        width: 28px; height: 28px;
        border-radius: 7px;
        background: var(--sb-surface);
        border: 1px solid var(--sb-border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--sb-muted);
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
    }

    .sb-footer-action:hover {
        background: rgba(248,113,113,0.12);
        border-color: rgba(248,113,113,0.3);
        color: #f87171;
    }

    /* ── Slide-in animation ── */
    .sb-nav li {
        animation: sbFadeIn 0.3s ease both;
    }
    .sb-nav li:nth-child(1) { animation-delay: 0.05s; }
    .sb-nav li:nth-child(2) { animation-delay: 0.08s; }
    .sb-nav li:nth-child(3) { animation-delay: 0.11s; }
    .sb-nav li:nth-child(4) { animation-delay: 0.14s; }
    .sb-nav li:nth-child(5) { animation-delay: 0.17s; }
    .sb-nav li:nth-child(6) { animation-delay: 0.20s; }

    @keyframes sbFadeIn {
        from { opacity: 0; transform: translateX(-10px); }
        to   { opacity: 1; transform: translateX(0); }
    }
</style>

<!-- ════════════════════════════════════════
     SIDEBAR HTML
════════════════════════════════════════ -->
<div class="sidebar">

    <!-- Brand -->
    <div class="sb-brand">
        <div class="sb-brand-inner">
            <div class="sb-logo-wrap">BP</div>
            <div class="sb-brand-text">
                <div class="name">HealthCare</div>
                <div class="tagline">Bethel Pharmacy</div>
            </div>
        </div>
        <div class="sb-status">
            <span class="dot"></span>
            <span>System <b>Online</b> &mdash; <?php echo date('D, d M'); ?></span>
        </div>
    </div>

    <!-- Main Navigation -->
    <div class="sb-section-label">Main Menu</div>
    <ul class="sb-nav">
        <!-- Dashboard: visible to all -->
        <li>
            <a href="dashboard.php" class="<?= $currentPage=='dashboard.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-grid-fill"></i></span>
                Dashboard
            </a>
        </li>

        <!-- Customers: visible to all -->
        <li>
            <a href="customers.php" class="<?= $currentPage=='customers.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
                Customers
            </a>
        </li>

        <!-- Items: visible to all -->
        <li>
            <a href="items_list.php" class="<?= $currentPage=='items_list.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-box-seam-fill"></i></span>
                Items
            </a>
        </li>

        <!-- Receivings: ADMIN ONLY -->
        <?php if (isAdmin()): ?>
        <li>
            <a href="receivings.php" class="<?= $currentPage=='receivings.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-truck-front-fill"></i></span>
                Receivings
            </a>
        </li>
        <?php endif; ?>

        <!-- Manage Users: ADMIN ONLY -->
        <?php if (isAdmin()): ?>
        <li>
            <a href="manage_user.php" class="<?= $currentPage=='manage_users.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-shield-lock-fill"></i></span>
                Manage Users
                <span class="sb-badge">Admin</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Finance Section -->
    <div class="sb-section-label">Finance</div>
    <ul class="sb-nav">
        <!-- Sales: visible to all -->
        <li>
            <a href="sales.php" class="<?= $currentPage=='sales.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-cart-check-fill"></i></span>
                Sales
            </a>
        </li>

        <?php if (isAdmin()): ?>
        <li>
          <a href="reports.php" class="<?= $currentPage=='reports.php' ? 'active' : '' ?>">
           <span class="nav-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></span>
            Reports
          <span class="sb-badge">PDF</span>
         </a>
        </li>
       <?php endif; ?>

        <!-- Expenses: ADMIN ONLY -->
        <?php if (isAdmin()): ?>
        <li>
            <a href="expenses.php" class="<?= $currentPage=='expenses.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-cash-stack"></i></span>
                Expenses
            </a>
        </li>
        <?php endif; ?>

        <div class="sb-divider"></div>

        <!-- <li>
            <a href="logout.php" class="logout">
                <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span>
                Logout
            </a>
        </li> -->
    </ul>

    <!-- Footer User Strip -->
    <div class="sb-footer">
        <div class="sb-user-avatar">
            <?php 
                $name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
                echo strtoupper(substr($name, 0, 1)); 
            ?>
        </div>
        <div class="sb-user-info">
            <div class="uname">
                <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User') ?>
            </div>
            <div class="urole">
                <?= ucfirst($_SESSION['role'] ?? 'Staff') ?>
            </div>
        </div>
        <a href="logout.php" class="sb-footer-action" title="Logout">
            <i class="bi bi-power"></i>
        </a>
    </div>

</div>