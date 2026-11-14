<!-- ══════════════════════════════════════════════════════════════════════
     COOL SMOOTH REFRESH — drop this block anywhere in <body>, once per page.
     Then call smoothRefresh() wherever you'd refresh the page
     (e.g. a Refresh button's onclick="smoothRefresh()").

     What it does:
       EXIT  (before reload):
         1. Blurs + slightly scales down + fades the page content.
         2. Fades in a soft veil with a modern 3-dot bounce loader card.
         3. Reloads once the transition finishes — no white-flash.
       ENTER (on the fresh page load):
         4. The .container slides up from the bottom (24px) and fades in
            over 0.3s — pure CSS, runs automatically on every load.

     IMPORTANT: any modal / overlay (Bootstrap .modal, .modal-overlay, etc.)
     must live OUTSIDE .container (as a sibling, not a descendant). A
     transform or filter on an ancestor becomes the positioning context for
     position:fixed children, and filters render the whole subtree as one
     blurred group — so a modal nested inside .container will look
     squished/misaligned/blurry no matter what CSS is applied to the modal
     itself. Keep modals as direct children of <body>.
     ══════════════════════════════════════════════════════════════════════ -->

<div id="refresh-veil">
    <div class="refresh-card">
        <div class="refresh-dots">
            <span></span><span></span><span></span>
        </div>
        <div class="refresh-label">Refreshing…</div>
    </div>
</div>

<style>
    /* ── ENTER: runs once automatically whenever the page loads ─────────
       "backwards" fill-mode locks in the 0% state during the (zero-length)
       delay so there's no flash of the un-animated container before the
       animation kicks in — but, unlike "both", it does NOT keep applying
       the end-state transform forever after the animation finishes. That
       matters because a lingering transform on .container turns it into
       the containing block for any position:fixed descendant (like a
       modal), breaking its positioning permanently. Targets .container
       (the page's main wrapper), not body. */
    .container {
        animation: page-enter .3s ease backwards;
    }
    @keyframes page-enter {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── EXIT: toggled by smoothRefresh() right before reload ────────────
       Blurs only .container (the page content), not body — filter on body
       would blur EVERY descendant, including fixed-position modals and
       overlays, which made open modals look far blurrier than intended. */
    .container {
        transition: filter .3s ease, transform .3s ease, opacity .3s ease;
        transform-origin: center top;
    }
    body.refreshing .container {
        filter: blur(6px);
        opacity: .5;
        transform: scale(.99);
    }

    /* Modals/overlays/the veil itself always stay sharp, even while
       .container is blurring out behind them. NOTE: this only works
       because modals are kept OUTSIDE .container in the markup — a filter
       cannot be "cancelled" by a descendant if the modal is nested inside
       the filtered element, since filter renders the whole subtree as one
       compositing group. */
    .modal,
    .modal-backdrop,
    .modal-overlay,
    #refresh-veil {
        filter: none !important;
    }

    #refresh-veil {
        position: fixed;
        inset: 0;
        background: color-mix(in srgb, var(--bg, #f5f6f8) 88%, transparent);
        opacity: 0;
        pointer-events: none;
        transition: opacity .4s ease;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #refresh-veil.show {
        opacity: 1;
        pointer-events: all;
    }

    .refresh-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        padding: 22px 30px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .12);
        transform: translateY(6px);
        opacity: 0;
        transition: opacity .35s ease .1s, transform .35s ease .1s;
    }
    #refresh-veil.show .refresh-card {
        opacity: 1;
        transform: translateY(0);
    }

    .refresh-dots {
        display: flex;
        gap: 7px;
    }
    .refresh-dots span {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--accent, #2563eb);
        animation: refresh-bounce 1s ease-in-out infinite;
    }
    .refresh-dots span:nth-child(2) { animation-delay: .15s; }
    .refresh-dots span:nth-child(3) { animation-delay: .3s; }

    @keyframes refresh-bounce {
        0%, 80%, 100% { transform: translateY(0); opacity: .5; }
        40% { transform: translateY(-8px); opacity: 1; }
    }

    .refresh-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted, #6b7280);
        letter-spacing: .2px;
    }
</style>

<script>
    // Blurs/scales/fades .container, fades in the loader card, then
    // reloads once the transition finishes. Adds 'refreshing' to body,
    // which the CSS above uses to target body.refreshing .container —
    // this keeps modals/overlays outside .container unaffected.
    function smoothRefresh(delay = 450) {
        document.body.classList.add('refreshing');
        document.getElementById('refresh-veil').classList.add('show');
        setTimeout(() => window.location.reload(), delay);
    }
</script>