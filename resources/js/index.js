// ═══════════════════════════════════════════════════════════════════════════════
// AWREL — Alpine Component Registrations & Global DOM Features
// ═══════════════════════════════════════════════════════════════════════════════

// ─────────────────────────────────────────────────────────────────────────────
// Non-Alpine DOM Features
// ─────────────────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    // ── LOADING BAR (always-on) ────────────────────────────────────────────
    const loadingBar = document.createElement("div");
    loadingBar.id = "awrel-loading-bar";
    loadingBar.style.position = "fixed";
    loadingBar.style.top = "0";
    loadingBar.style.left = "0";
    loadingBar.style.width = "0%";
    loadingBar.style.height = "3px";
    loadingBar.style.backgroundColor = "var(--color-primary-500)";
    loadingBar.style.transition = "width 0.3s ease";
    loadingBar.style.zIndex = "9999";
    document.body.appendChild(loadingBar);

    document.addEventListener("livewire:navigating", function () {
        loadingBar.style.width = "30%";
    });

    document.addEventListener("livewire:navigated", function () {
        loadingBar.style.width = "100%";
        setTimeout(function () {
            loadingBar.style.width = "0%";
        }, 300);
    });

    // ── ANIMATED FAVICON SPINNER (opt-in) ─────────────────────────────────
    if (document.documentElement.dataset.awrelFaviconSpinner !== undefined) {
        const favicon = document.querySelector('link[rel*="icon"]');
        const originalFavicon = favicon ? favicon.href : null;
        const svgSpinner =
            "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z' fill='%23e0e0e0'/%3E%3Cpath d='M12 4c-4.42 0-8 3.58-8 8' fill='none' stroke='var(--color-primary-500, %23f59e0b)' stroke-width='2' stroke-linecap='round'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 12 12' to='360 12 12' dur='1s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E";

        document.addEventListener("livewire:navigating", function () {
            if (favicon) favicon.href = svgSpinner;
        });

        document.addEventListener("livewire:navigated", function () {
            if (favicon && originalFavicon) favicon.href = originalFavicon;
        });
    }

    // ── DISABLED BUTTON SHAKE (always-on) ─────────────────────────────────
    document.addEventListener("click", function (e) {
        if (e.target.disabled && e.target.tagName === "BUTTON") {
            e.target.classList.add("animate-shake");
            setTimeout(function () {
                e.target.classList.remove("animate-shake");
            }, 500);
        }
    });

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

    skeletonObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });

    document
        .querySelectorAll(".fi-ta-table-loading-ctn")
        .forEach(function (el) {
            injectTableSkeleton(el);
        });

    // ── STATS OVERVIEW SKELETON LOADER (always-on) ─────────────────────────
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

    statsObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });

    document.querySelectorAll(".fi-wi-stats-overview").forEach(function (el) {
        injectStatsSkeleton(el);
    });

    // ── STICKY TABLE ACTIONS DRAG SCROLL (opt-in) ─────────────────────────
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

    if (document.documentElement.dataset.awrelStickyActions !== undefined) {
        document.body.classList.add("awrel-sticky-actions");
        enableDragScrolling();
        document.addEventListener("livewire:navigated", enableDragScrolling);
    }
});
