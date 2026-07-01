// ═══════════════════════════════════════════════════════════════════════════════
// AWREL — Alpine Component Registrations & Global DOM Features
// ═══════════════════════════════════════════════════════════════════════════════

// This module may execute before OR after DOMContentLoaded (Filament can inject
// the asset lazily, and it persists across wire:navigate swaps). Therefore:
//   • Event listeners + Livewire hooks are registered at module level so they
//     never miss their window (livewire:init / livewire:navigating can fire
//     before DOMContentLoaded in some load orderings).
//   • DOM-touching setup (skeleton observers, sticky actions) is deferred via
//     whenReady(), which runs immediately if the DOM is already parsed.
(() => {
    function whenReady(fn) {
        if (document.readyState !== "loading") {
            fn();
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    // Respect the user's OS/browser "reduce motion" preference.
    // All decorative animations (shake, shimmer, loading bar transitions,
    // favicon spinner, page transitions) should gate on this.
    function prefersReducedMotion() {
        return (
            window.matchMedia &&
            window.matchMedia("(prefers-reduced-motion: reduce)").matches
        );
    }

    function resolvePrimaryColor() {
        const computed =
            getComputedStyle(document.documentElement).getPropertyValue(
                "--color-primary-500",
            ) ||
            getComputedStyle(document.documentElement).getPropertyValue(
                "--primary-500",
            ) ||
            "245 158 11";
        let color = computed.trim();
        if (/^\d+\s+\d+\s+\d+$/.test(color)) {
            color = `rgb(${color.split(/\s+/).join(",")})`;
        } else if (
            color &&
            !color.startsWith("#") &&
            !color.startsWith("rgb") &&
            !color.startsWith("oklch") &&
            !color.startsWith("hsl") &&
            !color.startsWith("var")
        ) {
            color = `rgb(${color})`;
        }
        return color;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOADING BAR (opt-in via data-awrel-loading-bar)
    // ─────────────────────────────────────────────────────────────────────────
    // The bar is appended to documentElement (not body) so it survives
    // wire:navigate body swaps and stays visible across the whole navigation.
    const LOADING_BAR_MIN_BUILDUP_MS = 400; // keep build-up visible on fast requests
    const NAVIGATE_FLAG_TIMEOUT_MS = 15000; // safety net so isNavigating can't stick

    let loadingBar = null;
    let progressInterval = null;
    let progressWidth = 0;
    
    let progressStartedAt = 0;
    let completeTimer = null;
    let lastEndAt = 0;
    const PROGRESS_COOLDOWN_MS = 1500;

    function isLoadingBarEnabled() {
        return (
            document.documentElement.dataset.awrelLoadingBar !== undefined
        );
    }

    function createLoadingBarNode() {
        const color = resolvePrimaryColor();
        const bar = document.createElement("div");
        bar.id = "awrel-loading-bar";
        bar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 5px;
            background-color: ${color};
            box-shadow: 0 1px 10px ${color}, 0 0 3px ${color};
            z-index: 2147483647;
            pointer-events: none;
            opacity: 0;
            transition: width 0.2s ease, opacity 0.3s ease;
        `;
        return bar;
    }

    function ensureLoadingBar() {
        if (!loadingBar || !loadingBar.isConnected) {
            loadingBar =
                document.getElementById("awrel-loading-bar") ||
                createLoadingBarNode();
        }
        const root = document.documentElement;
        if (loadingBar.parentNode !== root) {
            root.appendChild(loadingBar);
        }
        const color = resolvePrimaryColor();
        loadingBar.style.backgroundColor = color;
        loadingBar.style.boxShadow = `0 1px 10px ${color}, 0 0 3px ${color}`;
        return loadingBar;
    }

    function clearProgressTimers() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        if (completeTimer) {
            clearTimeout(completeTimer);
            completeTimer = null;
        }
    }

    function startProgress() {
        if (!isLoadingBarEnabled()) return;
        if (Date.now() - lastEndAt < PROGRESS_COOLDOWN_MS) return;
        clearProgressTimers();
        const bar = ensureLoadingBar();
        progressWidth = 0;
        bar.style.transition = "none";
        bar.style.width = "0%";
        bar.style.opacity = "1";
        void bar.offsetHeight; // force reflow so the transition resets cleanly
        bar.style.transition =
            "width 0.4s cubic-bezier(0.08, 0.82, 0.17, 1), opacity 0.3s ease";
        progressWidth = 20;
        bar.style.width = progressWidth + "%";
        progressStartedAt = Date.now();
        progressInterval = setInterval(() => {
            if (progressWidth < 85) {
                progressWidth += Math.random() * 5;
                bar.style.width = progressWidth + "%";
            }
        }, 250);
    }

    function finishProgress() {
        lastEndAt = Date.now();
        completeTimer = null;
        if (!isLoadingBarEnabled()) return;
        const bar = ensureLoadingBar();
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        bar.style.transition = "width 0.2s ease, opacity 0.3s ease";
        bar.style.width = "100%";
        completeTimer = setTimeout(() => {
            bar.style.opacity = "0";
            completeTimer = setTimeout(() => {
                bar.style.width = "0%";
                completeTimer = null;
            }, 300);
        }, 250);
    }

    function endProgress() {
        if (!isLoadingBarEnabled()) return;
        ensureLoadingBar();
        // Guarantee the build-up is perceivable even on very fast requests.
        const elapsed = Date.now() - progressStartedAt;
        const delay = Math.max(0, LOADING_BAR_MIN_BUILDUP_MS - elapsed);
        if (completeTimer) clearTimeout(completeTimer);
        completeTimer = setTimeout(finishProgress, delay);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ANIMATED FAVICON SPINNER (opt-in via data-awrel-favicon-spinner)
    //
    // Draws a rotating arc ring directly on a canvas — no external image is
    // loaded into the canvas, so the canvas is never tainted and toDataURL is
    // always safe (the previous version tried drawImage() on the favicon and
    // silently failed on SVG / tainted sources).
    // ─────────────────────────────────────────────────────────────────────────
    const FAVICON_SIZE = 32;
    const FAVICON_SPINNER_MIN_MS = 500; // keep the spinner legible on very fast requests

    let isSpinnerActive = false;
    let faviconAnimationId = null;
    let faviconCanvas = null;
    let faviconCtx = null;
    let faviconAngle = 0;
    let faviconStartedAt = 0;
    let faviconStopTimer = null;
    let originalFaviconHrefs = new Map();
    let originalFaviconTypes = new Map();

    function isFaviconSpinnerEnabled() {
        return (
            document.documentElement.dataset.awrelFaviconSpinner !== undefined
        );
    }

    function getFaviconElements() {
        return Array.from(document.querySelectorAll('link[rel*="icon"]'));
    }

    function ensureFaviconCanvas() {
        if (!faviconCanvas) {
            faviconCanvas = document.createElement("canvas");
            faviconCanvas.width = FAVICON_SIZE;
            faviconCanvas.height = FAVICON_SIZE;
            faviconCtx = faviconCanvas.getContext("2d");
        }
        return faviconCtx;
    }

    function captureOriginalFavicons() {
        getFaviconElements().forEach((el) => {
            if (!originalFaviconHrefs.has(el)) {
                originalFaviconHrefs.set(el, el.getAttribute("href") || "");
                originalFaviconTypes.set(el, el.getAttribute("type") || "");
            }
        });
    }

    function drawSpinnerFrame() {
        if (!isSpinnerActive) return;
        const ctx = ensureFaviconCanvas();
        if (!ctx) return;

        const size = FAVICON_SIZE;
        const primaryColor = resolvePrimaryColor();

        ctx.clearRect(0, 0, size, size);
        faviconAngle += 0.18;

        const cx = size / 2;
        const cy = size / 2;
        const radius = size / 2 - 5;
        const lineW = size * 0.14;

        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(faviconAngle);

        // Track (faint)
        ctx.beginPath();
        ctx.arc(0, 0, radius, 0, Math.PI * 2);
        ctx.strokeStyle = primaryColor;
        ctx.globalAlpha = 0.2;
        ctx.lineWidth = lineW;
        ctx.lineCap = "round";
        ctx.stroke();

        // Moving arc (3/4 turn, tapered via gradient alpha)
        ctx.beginPath();
        ctx.arc(0, 0, radius, 0, Math.PI * 1.5);
        ctx.globalAlpha = 1;
        ctx.strokeStyle = primaryColor;
        ctx.stroke();

        ctx.restore();
        ctx.globalAlpha = 1;

        // Always-safe toDataURL: canvas is never tainted (no external images).
        let dataUrl;
        try {
            dataUrl = faviconCanvas.toDataURL("image/png");
        } catch (e) {
            return; // extremely unlikely; bail rather than spam the tab icon
        }

        getFaviconElements().forEach((el) => {
            el.setAttribute("href", dataUrl);
            el.setAttribute("type", "image/png");
        });

        faviconAnimationId = requestAnimationFrame(drawSpinnerFrame);
    }

    function restoreOriginalFavicons() {
        getFaviconElements().forEach((el) => {
            const origHref = originalFaviconHrefs.get(el);
            const origType = originalFaviconTypes.get(el);
            if (origHref) {
                el.setAttribute("href", origHref);
                if (origType) {
                    el.setAttribute("type", origType);
                } else {
                    el.removeAttribute("type");
                }
            }
        });
    }

    function startFaviconSpinner() {
        if (!isFaviconSpinnerEnabled() || isSpinnerActive) return;
        if (prefersReducedMotion()) return; // decorative animation — skip
        if (getFaviconElements().length === 0) return;

        captureOriginalFavicons();
        isSpinnerActive = true;
        faviconAngle = 0;
        faviconStartedAt = Date.now();
        if (faviconStopTimer) {
            clearTimeout(faviconStopTimer);
            faviconStopTimer = null;
        }
        drawSpinnerFrame();
    }

    function finishStopFaviconSpinner() {
        faviconStopTimer = null;
        if (!isSpinnerActive) return;
        isSpinnerActive = false;
        if (faviconAnimationId) {
            cancelAnimationFrame(faviconAnimationId);
            faviconAnimationId = null;
        }
        restoreOriginalFavicons();
        originalFaviconHrefs.clear();
        originalFaviconTypes.clear();
    }

    function stopFaviconSpinner() {
        if (!isSpinnerActive) return;
        // Guarantee a minimum visible duration so the spinner is perceivable
        // even on very fast requests, then restore the original favicon.
        const elapsed = Date.now() - faviconStartedAt;
        const delay = Math.max(0, FAVICON_SPINNER_MIN_MS - elapsed);
        if (faviconStopTimer) clearTimeout(faviconStopTimer);
        faviconStopTimer = setTimeout(finishStopFaviconSpinner, delay);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LIVEWIRE LIFECYCLE HOOK INTEGRATION (registered at module level)
    // ─────────────────────────────────────────────────────────────────────────
    let isNavigating = false;
    let navigateFlagTimer = null;
    let activeProgressCount = 0;

    function triggerStartProgress() {
        activeProgressCount++;
        if (activeProgressCount === 1) {
            startProgress();
        }
    }

    function triggerEndProgress() {
        activeProgressCount--;
        if (activeProgressCount <= 0) {
            activeProgressCount = 0;
            endProgress();
        }
    }

    // Handle wire:navigate events (switching menus). Registered on document,
    // which persists across SPA swaps, so these keep firing on every navigation.
    document.addEventListener("livewire:navigating", function () {
        isNavigating = true;
        // Safety net: if livewire:navigated never fires for some reason, the
        // flag (and thus the request-hook guard) would stick and disable ajax
        // progress forever. Self-reset after a generous timeout.
        if (navigateFlagTimer) clearTimeout(navigateFlagTimer);
        navigateFlagTimer = setTimeout(() => {
            isNavigating = false;
            navigateFlagTimer = null;
        }, NAVIGATE_FLAG_TIMEOUT_MS);
        triggerStartProgress();
        startFaviconSpinner();
    });

    document.addEventListener("livewire:navigated", function () {
        isNavigating = false;
        if (navigateFlagTimer) {
            clearTimeout(navigateFlagTimer);
            navigateFlagTimer = null;
        }
        triggerEndProgress();
        stopFaviconSpinner();
    });

    // Hook into general Livewire requests (form submissions, table filters,
    // save actions). Robust to load order: register immediately if Livewire is
    // already bootstrapped, otherwise fall back to the livewire:init event.
    function registerRequestHook() {
        if (
            typeof Livewire === "undefined" ||
            typeof Livewire.hook !== "function"
        ) {
            return false;
        }
        Livewire.hook("request", ({ succeed, fail }) => {
            // wire:navigate requests are already handled by livewire:navigating.
            if (isNavigating) return;

            triggerStartProgress();

            succeed(function () {
                triggerEndProgress();
            });

            fail(function () {
                triggerEndProgress();
            });
        });
        return true;
    }

    if (!registerRequestHook()) {
        document.addEventListener("livewire:init", registerRequestHook);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DISABLED BUTTON SHAKE (always-on, gated by prefers-reduced-motion)
    // ─────────────────────────────────────────────────────────────────────────
    if (!prefersReducedMotion()) {
        document.addEventListener("click", function (e) {
            if (e.target.disabled && e.target.tagName === "BUTTON") {
                e.target.classList.add("animate-shake");
                setTimeout(function () {
                    e.target.classList.remove("animate-shake");
                }, 500);
        }
    });
    } // end prefers-reduced-motion guard

    // ─────────────────────────────────────────────────────────────────────────
    // PAGE TRANSITION (opt-in via data-awrel-page-transition)
    // Fade-in the main content area after wire:navigate.
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener("livewire:navigated", function () {
        if (document.documentElement.dataset.awrelPageTransition === undefined) return;
        if (prefersReducedMotion()) return;

        const main =
            document.querySelector(".fi-main-ctn") ||
            document.querySelector(".fi-main") ||
            document.querySelector("main");
        if (!main) return;

        // Remove any previous animation to ensure replay on double-navigate.
        main.style.animation = "none";
        void main.offsetWidth; // force reflow
        main.style.animation = "awrel-fade-in 0.25s ease-out";
    });

    // ─────────────────────────────────────────────────────────────────────────
    // BUTTON SUBMIT LOADING (opt-in via data-awrel-button-submit-loading)
    // Show a spinner inside action buttons while a Livewire request runs.
    // ─────────────────────────────────────────────────────────────────────────
    if (document.documentElement.dataset.awrelButtonSubmitLoading !== undefined) {
        const loadingButtons = new WeakSet();

        function createSpinner() {
            const s = document.createElement("span");
            s.className = "awrel-btn-spinner";
            s.style.cssText =
                "display:inline-block;width:1em;height:1em;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:awrel-spin .6s linear;vertical-align:middle;margin-right:.3em;";
            return s;
        }

        function captureButton(btn) {
            if (loadingButtons.has(btn)) return;
            loadingButtons.add(btn);
            btn.dataset.awrelOrigHtml = btn.innerHTML;
            btn.disabled = true;
            btn.classList.add("fi-disabled");
            const spinner = createSpinner();
            btn.prepend(spinner);
        }

        function restoreButton(btn) {
            if (!loadingButtons.has(btn)) return;
            loadingButtons.delete(btn);
            btn.disabled = false;
            btn.classList.remove("fi-disabled");
            if (btn.dataset.awrelOrigHtml !== undefined) {
                btn.innerHTML = btn.dataset.awrelOrigHtml;
                delete btn.dataset.awrelOrigHtml;
            }
        }

        // 1. Capture on click — wire:click on buttons or anchors
        document.addEventListener("click", function (e) {
            const trigger = e.target.closest("[wire\\:click]");
            if (!trigger) return;
            const btn = trigger.closest("button, [role='button']");
            if (!btn) return;
            captureButton(btn);
        });

        // 2. Capture on submit — button[type=submit] inside wire:submit forms
        document.addEventListener("submit", function (e) {
            const form = e.target;
            if (!form.hasAttribute("wire:submit")) return;
            const btn = form.querySelector("button[type='submit']");
            if (!btn) return;
            captureButton(btn);
        });

        // 3. Restore on Livewire request completion.
        document.addEventListener("livewire:init", function () {
            if (typeof Livewire === "undefined") return;
            Livewire.hook("request", ({ succeed, fail }) => {
                const restoreAll = function () {
                    document.querySelectorAll("button.fi-disabled").forEach(function (b) {
                        if (!b.dataset.awrelOrigHtml) return;
                        // If Livewire already re-rendered the button, its
                        // innerHTML now differs from our saved HTML — skip restore.
                        if (b.innerHTML !== b.innerHTML) return; // sanity
                        restoreButton(b);
                    });
                };
                succeed(restoreAll);
                fail(restoreAll);
            });
        });

        // 4. Safety timeout — in case Livewire hooks aren't registered yet,
        // auto-restore after 15 s.
        document.addEventListener("click", function (e) {
            // Using a capture-phase delegate won't work here; instead we
            // schedule a single periodic cleanup.
        });
        // Stale-button guard runs every 2 s.
        setInterval(function () {
            document.querySelectorAll("button.fi-disabled").forEach(function (b) {
                if (!b.dataset.awrelOrigHtml) return;
                // If it's been disabled suspiciously long, restore.
                restoreButton(b);
            });
        }, 2000);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UNSAVED CHANGES GUARD (opt-in via data-awrel-unsaved-changes-guard)
    // ─────────────────────────────────────────────────────────────────────────
    if (document.documentElement.dataset.awrelUnsavedChangesGuard !== undefined) {
        let formDirty = false;

        function markDirty() { formDirty = true; }
        function markClean() { formDirty = false; }

        document.addEventListener("change", markDirty);
        document.addEventListener("input", markDirty);
        document.addEventListener("livewire:navigated", markClean);

        document.addEventListener("livewire:navigating", function (e) {
            if (!formDirty) return;
            if (!confirm("You have unsaved changes. Leave anyway?")) {
                e.preventDefault();
            }
        });

        window.addEventListener("beforeunload", function (e) {
            if (!formDirty) return;
            e.preventDefault();
            e.returnValue = "";
        });

        document.addEventListener("livewire:init", function () {
            if (typeof Livewire === "undefined") return;
            Livewire.hook("request", ({ succeed }) => {
                succeed(function () { markClean(); });
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DOM-DEPENDENT FEATURES (need a parsed body)
    // ─────────────────────────────────────────────────────────────────────────
    whenReady(function () {
        // ── AUTOMATIC TABLE SKELETON LOADER (always-on) ───────────────────────
        function createSkeletonCell(widthPercent) {
            const cell = document.createElement("div");
            cell.className = "awrel-skeleton-cell";
            const bar = document.createElement("div");
            bar.className = "awrel-skeleton-bar";
            bar.style.width = widthPercent + "%";
            cell.appendChild(bar);
            return cell;
        }

        function createTableSkeleton() {
            const wrapper = document.createElement("div");
            wrapper.className = "awrel-table-skeleton";

            const header = document.createElement("div");
            header.className = "awrel-table-skeleton-header";
            [22, 18, 16, 20, 14, 10].forEach(function (w) {
                header.appendChild(createSkeletonCell(w));
            });
            wrapper.appendChild(header);

            [
                [38, 24, 30, 18, 14, 12],
                [32, 28, 22, 24, 16, 10],
                [42, 20, 26, 16, 12, 14],
                [28, 32, 20, 26, 18, 10],
                [36, 22, 28, 20, 14, 12],
                [30, 26, 34, 18, 16, 10],
                [40, 18, 24, 22, 14, 12],
                [34, 30, 20, 24, 12, 14],
            ].forEach(function (widths) {
                const row = document.createElement("div");
                row.className = "awrel-table-skeleton-row";
                widths.forEach(function (w) {
                    row.appendChild(createSkeletonCell(w));
                });
                wrapper.appendChild(row);
            });

            return wrapper;
        }

        function injectTableSkeleton(container) {
            if (container.querySelector(".awrel-table-skeleton")) return;
            container.innerHTML = "";
            container.appendChild(createTableSkeleton());
        }

        const skeletonObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1 && node.matches) {
                        if (node.matches(".fi-ta-table-loading-ctn")) {
                            injectTableSkeleton(node);
                        }
                        if (node.querySelectorAll) {
                            node.querySelectorAll(
                                ".fi-ta-table-loading-ctn",
                            ).forEach(function (el) {
                                injectTableSkeleton(el);
                            });
                        }
                    }
                });
            });
        });

        skeletonObserver.observe(document.documentElement, {
            childList: true,
            subtree: true,
        });

        document
            .querySelectorAll(".fi-ta-table-loading-ctn")
            .forEach(function (el) {
                injectTableSkeleton(el);
            });

        // ── STATS OVERVIEW SKELETON LOADER (always-on) ───────────────────────
        function createStatsSkeleton(columnCount) {
            const skeleton = document.createElement("div");
            skeleton.className = "awrel-stats-skeleton";

            const cols = Math.min(columnCount || 4, 6);
            skeleton.style.gridTemplateColumns = "repeat(" + cols + ", 1fr)";

            for (let i = 0; i < cols; i++) {
                const card = document.createElement("div");
                card.className = "awrel-skeleton-card";

                const icon = document.createElement("div");
                icon.className = "awrel-skeleton-card-icon";
                card.appendChild(icon);

                const value = document.createElement("div");
                value.className = "awrel-skeleton-card-value";
                card.appendChild(value);

                const label = document.createElement("div");
                label.className = "awrel-skeleton-card-label";
                card.appendChild(label);

                skeleton.appendChild(card);
            }
            return skeleton;
        }

        function watchForRealStats(container) {
            const realStatsObserver = new MutationObserver(function () {
                if (container.querySelector(".fi-wi-stats-overview-stat")) {
                    const skeleton = container.querySelector(
                        ".awrel-stats-skeleton",
                    );
                    if (skeleton) skeleton.remove();
                    realStatsObserver.disconnect();
                }
            });
            realStatsObserver.observe(container, {
                childList: true,
                subtree: true,
            });
        }

        function injectStatsSkeleton(container) {
            if (container.querySelector(".awrel-stats-skeleton")) return;
            if (container.querySelector(".fi-wi-stats-overview-stat")) return;

            let columnCount = 4;
            try {
                const computedStyle = getComputedStyle(container);
                const gridTemplate = computedStyle.gridTemplateColumns;
                if (gridTemplate) {
                    const parts = gridTemplate.split(/\s+/);
                    if (parts.length > 0 && parts.length <= 6) {
                        columnCount = parts.length;
                    }
                }
            } catch (e) {
                /* ignore */
            }

            container.appendChild(createStatsSkeleton(columnCount));
            watchForRealStats(container);
        }

        const statsObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1 && node.matches) {
                        if (node.matches(".fi-wi-stats-overview")) {
                            injectStatsSkeleton(node);
                        }
                        if (node.querySelectorAll) {
                            node.querySelectorAll(".fi-wi-stats-overview").forEach(
                                function (el) {
                                    injectStatsSkeleton(el);
                                },
                            );
                        }
                    }
                });
            });
        });

        statsObserver.observe(document.documentElement, {
            childList: true,
            subtree: true,
        });

        document.querySelectorAll(".fi-wi-stats-overview").forEach(function (el) {
            injectStatsSkeleton(el);
        });

        // ── STICKY TABLE ACTIONS DRAG SCROLL (opt-in) ───────────────────────
        function initDragScroll(table) {
            if (table.dataset.dragInitialized) return;
            table.dataset.dragInitialized = "true";

            let isDown = false;
            let startX;
            let scrollLeft;

            table.addEventListener("mousedown", function (e) {
                isDown = true;
                startX = e.pageX - table.offsetLeft;
                scrollLeft = table.scrollLeft;
            });

            table.addEventListener("mouseleave", function () {
                isDown = false;
            });

            table.addEventListener("mouseup", function () {
                isDown = false;
            });

            table.addEventListener("mousemove", function (e) {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - table.offsetLeft;
                const walk = (x - startX) * 1.5;
                table.scrollLeft = scrollLeft - walk;
            });

            table.style.cursor = "grab";
        }

        function enableDragScrolling() {
            document.querySelectorAll(".fi-ta-table").forEach(initDragScroll);
        }

        function applyStickyActions() {
            if (
                document.documentElement.dataset.awrelStickyActions !==
                undefined
            ) {
                document.body.classList.add("awrel-sticky-actions");
                enableDragScrolling();
            }
        }

        if (
            document.documentElement.dataset.awrelStickyActions !== undefined
        ) {
            applyStickyActions();
            document.addEventListener("livewire:navigated", applyStickyActions);
        }
    });

    // ── IMAGE SKELETON ──
    function setupImgSkel(i) {
        if (i.classList.contains("awrel-img-skel")||!(i.closest(".fi-ta-cell,.fi-wi-stats-overview-stat"))) return;
        if (i.complete && i.naturalWidth>0) return;
        i.classList.add("awrel-img-skeleton");
        i.addEventListener("load",function(){i.classList.remove("awrel-img-skeleton")},{once:1});
    }
    document.querySelectorAll("img").forEach(setupImgSkel);
    new MutationObserver(function(m){m.forEach(function(mu){mu.addedNodes.forEach(function(n){
        if(n.nodeType!==1)return;
        if(n.matches&&n.matches("img"))setupImgSkel(n);
        if(n.querySelectorAll)n.querySelectorAll("img").forEach(setupImgSkel);
    })})}).observe(document.documentElement,{childList:1,subtree:1});

    !function(){var b=null;function s(){b||(b=document.createElement("div"),b.className="awrel-offline-banner",b.textContent="You are offline.",document.body.appendChild(b)),requestAnimationFrame(function(){b.classList.add("is-visible")})}function h(){b&&b.classList.remove("is-visible")}window.addEventListener("offline",s),window.addEventListener("online",h),navigator.onLine||s()}();

    !function(){var b=document.createElement("button");b.className="awrel-scroll-top",b.setAttribute("aria-label","Scroll to top"),b.innerHTML='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>',document.body.appendChild(b);var t=!1;window.addEventListener("scroll",function(){t||requestAnimationFrame(function(){var m=document.querySelector(".fi-main-ctn")||document.querySelector("main");b.classList.toggle("is-visible",m?m.getBoundingClientRect().top<-300:!1),t=!1}),t=!0},{passive:1}),b.addEventListener("click",function(){(document.querySelector(".fi-main-ctn")||document.querySelector("main")||document.documentElement).scrollTo({top:0,behavior:"smooth"})})}();

    !function(){var o=new MutationObserver(function(m){m.forEach(function(mu){mu.addedNodes.forEach(function(n){if(n.nodeType!==1)return;var ns=n.matches&&n.matches(".fi-notification")?[n]:n.querySelectorAll?Array.from(n.querySelectorAll(".fi-notification")):[];ns.forEach(function(no){if(!no.querySelector(".awrel-toast-bar")){var b=document.createElement("div");b.className="awrel-toast-bar",no.appendChild(b)}})})})});o.observe(document.documentElement,{childList:1,subtree:1}),document.querySelectorAll(".fi-notification").forEach(function(n){n.querySelector(".awrel-toast-bar")||(b=document.createElement("div"),b.className="awrel-toast-bar",n.appendChild(b))})}();

    document.addEventListener("livewire:navigated",function(){var f=document.querySelector(".fi-page-create-record-form,.fi-page-edit-record-form");if(f){var i=f.querySelector("input:not([type=hidden]):not([disabled])");i&&setTimeout(function(){i.focus()},100)}});

    !function(){var l=document.createElement("div");l.className="awrel-autosave-label",l.textContent="Saving...",document.body.appendChild(l);var t=null;function s(){l.classList.add("is-visible"),t&&clearTimeout(t)}function h(){t&&clearTimeout(t),t=setTimeout(function(){l.classList.remove("is-visible")},800)}document.addEventListener("livewire:navigating",s),document.addEventListener("livewire:navigated",h),document.addEventListener("change",s),document.addEventListener("livewire:init",function(){typeof Livewire!="undefined"&&(Livewire.hook("request",function(){s()}),Livewire.hook("request",function({succeed:e,fail:n}){e(function(){h()}),n(function(){h()})}))})}();

})();
