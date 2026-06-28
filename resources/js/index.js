// ═══════════════════════════════════════════════════════════════════════════════
// AWREL — Alpine Component Registrations & Global DOM Features
// ═══════════════════════════════════════════════════════════════════════════════

// ─────────────────────────────────────────────────────────────────────────────
// Non-Alpine DOM Features
// ─────────────────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    // ── LOADING BAR (opt-in via dataset) ───────────────────────────────────
    let loadingBar = null;
    let progressInterval = null;
    let progressWidth = 0;

    function updateLoadingBarColor() {
        if (!loadingBar) return;
        const computed = getComputedStyle(document.documentElement).getPropertyValue('--color-primary-500') 
            || getComputedStyle(document.documentElement).getPropertyValue('--primary-500')
            || '245 158 11';
        
        let color = computed.trim();
        if (/^\d+\s+\d+\s+\d+$/.test(color)) {
            color = `rgb(${color.split(/\s+/).join(',')})`;
        } else if (!color.startsWith('#') && !color.startsWith('rgb') && !color.startsWith('oklch') && !color.startsWith('hsl') && !color.startsWith('var')) {
            color = `rgb(${color})`;
        }
        
        loadingBar.style.backgroundColor = color;
        loadingBar.style.boxShadow = `0 1px 10px ${color}, 0 0 3px ${color}`;
    }

    if (document.documentElement.dataset.awrelLoadingBar !== undefined) {
        loadingBar = document.createElement("div");
        loadingBar.id = "awrel-loading-bar";
        loadingBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 5px;
            background-color: #f59e0b;
            box-shadow: 0 1px 10px #f59e0b, 0 0 3px #f59e0b;
            z-index: 2147483647;
            pointer-events: none;
            opacity: 0;
            transition: width 0.2s ease, opacity 0.3s ease;
        `;
        document.body.appendChild(loadingBar);
        updateLoadingBarColor();
    }

    function ensureLoadingBar() {
        if (!loadingBar) return;
        if (!document.body.contains(loadingBar)) {
            document.body.appendChild(loadingBar);
        }
    }

    function startProgress() {
        if (!loadingBar) return;
        ensureLoadingBar();
        updateLoadingBarColor();
        if (progressInterval) clearInterval(progressInterval);
        progressWidth = 0;
        loadingBar.style.transition = 'none';
        loadingBar.style.width = '0%';
        loadingBar.style.opacity = '1';
        loadingBar.offsetHeight; // force reflow
        loadingBar.style.transition = 'width 0.4s cubic-bezier(0.08, 0.82, 0.17, 1), opacity 0.3s ease';
        progressWidth = 20;
        loadingBar.style.width = progressWidth + '%';
        progressInterval = setInterval(() => {
            if (progressWidth < 85) {
                progressWidth += Math.random() * 5;
                loadingBar.style.width = progressWidth + '%';
            }
        }, 250);
    }

    function endProgress() {
        if (!loadingBar) return;
        ensureLoadingBar();
        if (progressInterval) clearInterval(progressInterval);
        loadingBar.style.transition = 'width 0.2s ease, opacity 0.3s ease';
        loadingBar.style.width = '100%';
        setTimeout(() => {
            loadingBar.style.opacity = '0';
            setTimeout(() => {
                loadingBar.style.width = '0%';
            }, 300);
        }, 200);
    }

    // ── ANIMATED FAVICON SPINNER (opt-in via dataset) ─────────────────────
    let isSpinnerActive = false;
    let faviconAnimationId = null;
    let faviconCanvas = null;
    let faviconCtx = null;
    let faviconImage = null;
    let faviconAngle = 0;
    let isFaviconImageLoaded = false;
    let originalFaviconHrefs = new Map();
    let originalFaviconTypes = new Map();

    function isFaviconSpinnerEnabled() {
        return document.documentElement.dataset.awrelFaviconSpinner !== undefined;
    }

    function getFaviconElements() {
        return Array.from(document.querySelectorAll('link[rel*="icon"]'));
    }

    function getPrimaryColor() {
        const computed = getComputedStyle(document.documentElement).getPropertyValue('--color-primary-500') 
            || getComputedStyle(document.documentElement).getPropertyValue('--primary-500') 
            || '245 158 11';
        let color = computed.trim();
        if (/^\d+\s+\d+\s+\d+$/.test(color)) {
            return `rgb(${color.split(/\s+/).join(',')})`;
        } else if (!color.startsWith('#') && !color.startsWith('rgb') && !color.startsWith('oklch') && !color.startsWith('hsl') && !color.startsWith('var')) {
            return `rgb(${color})`;
        }
        return color;
    }

    function initFaviconAnimation() {
        if (!isFaviconSpinnerEnabled()) return;
        
        const elements = getFaviconElements();
        if (elements.length === 0) return;

        // Populate original attributes if not already tracked
        elements.forEach(el => {
            if (!originalFaviconHrefs.has(el)) {
                originalFaviconHrefs.set(el, el.getAttribute('href') || '');
                originalFaviconTypes.set(el, el.getAttribute('type') || '');
            }
        });

        if (faviconImage) return;

        // Use the first icon's href to load the image
        const baseHref = elements[0].getAttribute('href');
        faviconImage = new Image();
        
        // Only set crossOrigin if it is truly cross-origin
        if (baseHref && (baseHref.startsWith('http://') || baseHref.startsWith('https://'))) {
            try {
                const url = new URL(baseHref);
                if (url.origin !== window.location.origin) {
                    faviconImage.crossOrigin = "anonymous";
                }
            } catch (e) {}
        }

        faviconImage.onload = function () {
            isFaviconImageLoaded = true;
        };
        faviconImage.onerror = function () {
            isFaviconImageLoaded = false;
        };
        faviconImage.src = baseHref;

        faviconCanvas = document.createElement('canvas');
        faviconCanvas.width = 32;
        faviconCanvas.height = 32;
        faviconCtx = faviconCanvas.getContext('2d');
    }

    function animateFavicon() {
        if (!isSpinnerActive || !faviconCtx) return;

        const primaryColor = getPrimaryColor();
        faviconCtx.clearRect(0, 0, 32, 32);

        if (isFaviconImageLoaded && faviconImage) {
            // Draw coin flip animation using original favicon image
            faviconAngle += 0.12;
            const scaleX = Math.cos(faviconAngle);

            faviconCtx.save();
            faviconCtx.translate(16, 16);
            faviconCtx.scale(scaleX, 1);
            try {
                faviconCtx.drawImage(faviconImage, -16, -16, 32, 32);
            } catch (e) {
                // If drawImage fails (e.g. tainted canvas or SVG issues), fallback to drawing a solid circle
                faviconCtx.beginPath();
                faviconCtx.arc(0, 0, 12, 0, Math.PI * 2);
                faviconCtx.fillStyle = primaryColor;
                faviconCtx.fill();
            }

            // Draw primary indicator at mid-rotation
            if (Math.abs(scaleX) < 0.2) {
                faviconCtx.strokeStyle = primaryColor;
                faviconCtx.lineWidth = 4;
                faviconCtx.beginPath();
                faviconCtx.moveTo(0, -16);
                faviconCtx.lineTo(0, 16);
                faviconCtx.stroke();
            }
            faviconCtx.restore();
        } else {
            // Fallback spinner ring while image is loading or if it failed
            faviconAngle += 0.15;
            faviconCtx.save();
            faviconCtx.translate(16, 16);
            faviconCtx.rotate(faviconAngle);
            faviconCtx.strokeStyle = primaryColor;
            faviconCtx.lineWidth = 4;
            faviconCtx.beginPath();
            faviconCtx.arc(0, 0, 12, 0, Math.PI * 1.5);
            faviconCtx.stroke();
            faviconCtx.restore();
        }

        const dataUrl = faviconCanvas.toDataURL('image/png');
        const elements = getFaviconElements();
        elements.forEach(el => {
            el.setAttribute('href', dataUrl);
            el.setAttribute('type', 'image/png');
        });

        faviconAnimationId = requestAnimationFrame(animateFavicon);
    }

    function startFaviconSpinner() {
        if (!isFaviconSpinnerEnabled() || isSpinnerActive) return;
        isSpinnerActive = true;
        faviconAngle = 0;
        
        initFaviconAnimation();
        animateFavicon();
    }

    function stopFaviconSpinner() {
        if (!isSpinnerActive) return;
        isSpinnerActive = false;

        if (faviconAnimationId) {
            cancelAnimationFrame(faviconAnimationId);
            faviconAnimationId = null;
        }

        // Restore original favicons
        const elements = getFaviconElements();
        elements.forEach(el => {
            const origHref = originalFaviconHrefs.get(el);
            const origType = originalFaviconTypes.get(el);
            if (origHref) {
                el.setAttribute('href', origHref);
                if (origType) {
                    el.setAttribute('type', origType);
                } else {
                    el.removeAttribute('type');
                }
            }
        });

        // Clear tracking maps to avoid memory leaks
        originalFaviconHrefs.clear();
        originalFaviconTypes.clear();
    }

    // ── LIVEWIRE LIFECYCLE HOOK INTEGRATION ──────────────────────────────────
    let isNavigating = false;
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

    // Handle wire:navigate events (switching menus)
    document.addEventListener("livewire:navigating", function () {
        isNavigating = true;
        triggerStartProgress();
        startFaviconSpinner();
    });

    document.addEventListener("livewire:navigated", function () {
        isNavigating = false;
        triggerEndProgress();
        stopFaviconSpinner();
    });

    // Hook into general Livewire requests (form submissions, table filters, save action)
    document.addEventListener("livewire:init", function () {
        if (typeof Livewire !== 'undefined') {
            Livewire.hook("request", ({ succeed, fail }) => {
                // If it is a page/menu navigation request, the loading bar is already handled by livewire:navigating
                if (isNavigating) return;

                triggerStartProgress();

                succeed(function () {
                    triggerEndProgress();
                });

                fail(function () {
                    triggerEndProgress();
                });
            });
        }
    });

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

    skeletonObserver.observe(document.documentElement, {
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

    statsObserver.observe(document.documentElement, {
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

    function applyStickyActions() {
        if (document.documentElement.dataset.awrelStickyActions !== undefined) {
            document.body.classList.add("awrel-sticky-actions");
            enableDragScrolling();
        }
    }

    if (document.documentElement.dataset.awrelStickyActions !== undefined) {
        applyStickyActions();
        document.addEventListener("livewire:navigated", applyStickyActions);
    }
});
