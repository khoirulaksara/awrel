document.addEventListener("DOMContentLoaded", function () {
    // ═══════════════════════════════════════════════
    // LOADING BAR (always-on)
    // ═══════════════════════════════════════════════
    var loadingBar = document.createElement("div");
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

    // ═══════════════════════════════════════════════
    // ANIMATED FAVICON SPINNER (opt-in)
    // Check for data-awrel-favicon-spinner on <html>
    // ═══════════════════════════════════════════════
    if (document.documentElement.dataset.awrelFaviconSpinner !== undefined) {
        var favicon = document.querySelector('link[rel*="icon"]');
        var originalFavicon = favicon ? favicon.href : null;
        var spinnerFavicon = "/favicon-spinner.svg";

        document.addEventListener("livewire:navigating", function () {
            if (favicon) favicon.href = spinnerFavicon;
        });

        document.addEventListener("livewire:navigated", function () {
            if (favicon && originalFavicon) favicon.href = originalFavicon;
        });
    }

    // ═══════════════════════════════════════════════
    // DISABLED BUTTON SHAKE (always-on)
    // ═══════════════════════════════════════════════
    document.addEventListener("click", function (e) {
        if (e.target.disabled && e.target.tagName === "BUTTON") {
            e.target.classList.add("animate-shake");
            setTimeout(function () {
                e.target.classList.remove("animate-shake");
            }, 500);
        }
    });

    // ═══════════════════════════════════════════════
    // AUTOMATIC TABLE SKELETON LOADER (always-on)
    // Creates a realistic table skeleton inside any
    // .fi-ta-table-loading-ctn that appears in the DOM.
    // ═══════════════════════════════════════════════
    var skeletonBarWidths = [38, 24, 30, 18, 14, 12];

    function createSkeletonCell(widthPercent) {
        var cell = document.createElement("div");
        cell.className = "awrel-skeleton-cell";
        var bar = document.createElement("div");
        bar.className = "awrel-skeleton-bar";
        bar.style.width = widthPercent + "%";
        cell.appendChild(bar);
        return cell;
    }

    function createTableSkeleton() {
        var wrapper = document.createElement("div");
        wrapper.className = "awrel-table-skeleton";

        // Header row
        var header = document.createElement("div");
        header.className = "awrel-table-skeleton-header";
        var headerWidths = [22, 18, 16, 20, 14, 10];
        headerWidths.forEach(function (w) {
            header.appendChild(createSkeletonCell(w));
        });
        wrapper.appendChild(header);

        // Body rows with varied bar widths
        var bodyWidths = [
            [38, 24, 30, 18, 14, 12],
            [32, 28, 22, 24, 16, 10],
            [42, 20, 26, 16, 12, 14],
            [28, 32, 20, 26, 18, 10],
            [36, 22, 28, 20, 14, 12],
            [30, 26, 34, 18, 16, 10],
            [40, 18, 24, 22, 14, 12],
            [34, 30, 20, 24, 12, 14],
        ];

        bodyWidths.forEach(function (widths) {
            var row = document.createElement("div");
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

    // MutationObserver to detect table loading containers
    var skeletonObserver = new MutationObserver(function (mutations) {
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

    // Scan existing loading containers on page load
    document
        .querySelectorAll(".fi-ta-table-loading-ctn")
        .forEach(function (el) {
            injectTableSkeleton(el);
        });

    // ═══════════════════════════════════════════════
    // STATS OVERVIEW SKELETON LOADER (always-on)
    // Automatically injects skeleton cards into any
    // .fi-wi-stats-overview that appears without real
    // .fi-wi-stats-overview-stat children yet.
    // ═══════════════════════════════════════════════

    function createStatsSkeleton(columnCount) {
        var skeleton = document.createElement("div");
        skeleton.className = "awrel-stats-skeleton";

        var cols = Math.min(columnCount || 4, 6);
        skeleton.style.gridTemplateColumns = "repeat(" + cols + ", 1fr)";

        for (var i = 0; i < cols; i++) {
            var card = document.createElement("div");
            card.className = "awrel-skeleton-card";

            var icon = document.createElement("div");
            icon.className = "awrel-skeleton-card-icon";
            card.appendChild(icon);

            var value = document.createElement("div");
            value.className = "awrel-skeleton-card-value";
            card.appendChild(value);

            var label = document.createElement("div");
            label.className = "awrel-skeleton-card-label";
            card.appendChild(label);

            skeleton.appendChild(card);
        }
        return skeleton;
    }

    function watchForRealStats(container) {
        var realStatsObserver = new MutationObserver(function () {
            if (container.querySelector(".fi-wi-stats-overview-stat")) {
                var skeleton = container.querySelector(".awrel-stats-skeleton");
                if (skeleton) {
                    skeleton.remove();
                }
                realStatsObserver.disconnect();
            }
        });
        realStatsObserver.observe(container, {
            childList: true,
            subtree: true,
        });
    }

    function injectStatsSkeleton(container) {
        // Already have skeleton or real stat cards — skip
        if (container.querySelector(".awrel-stats-skeleton")) return;
        if (container.querySelector(".fi-wi-stats-overview-stat")) return;

        // Derive column count from computed grid-template-columns
        var columnCount = 4;
        try {
            var computedStyle = getComputedStyle(container);
            var gridTemplate = computedStyle.gridTemplateColumns;
            if (gridTemplate) {
                var parts = gridTemplate.split(/\s+/);
                // Ignore track listings like "100px 1fr 1fr" etc
                // Only count if it's a simple repeat pattern
                if (parts.length > 0 && parts.length <= 6) {
                    columnCount = parts.length;
                }
            }
        } catch (e) {
            // Fallback to default
        }

        container.appendChild(createStatsSkeleton(columnCount));
        watchForRealStats(container);
    }

    var statsObserver = new MutationObserver(function (mutations) {
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

    // Scan existing stats overview containers on page load
    document.querySelectorAll(".fi-wi-stats-overview").forEach(function (el) {
        injectStatsSkeleton(el);
    });
});

// ═══════════════════════════════════════════════
// STICKY TABLE ACTIONS DRAG SCROLL (opt-in)
// Adds drag-to-scroll behavior on .fi-ta-table when
// data-awrel-sticky-actions is present on <html>.
// The data attribute is set via HEAD_START render hook.
// ═══════════════════════════════════════════════

function initDragScroll(table) {
    if (table.dataset.dragInitialized) return;
    table.dataset.dragInitialized = "true";

    var isDown = false;
    var startX;
    var scrollLeft;

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
        var x = e.pageX - table.offsetLeft;
        var walk = (x - startX) * 1.5;
        table.scrollLeft = scrollLeft - walk;
    });

    table.style.cursor = "grab";
}

function enableDragScrolling() {
    document.querySelectorAll(".fi-ta-table").forEach(initDragScroll);
}

// Check at parse time if the data attribute is set
if (document.documentElement.dataset.awrelStickyActions !== undefined) {
    // <body> might not exist yet, queue adding the class for DOMContentLoaded
    document.addEventListener("DOMContentLoaded", function () {
        document.body.classList.add("awrel-sticky-actions");
        enableDragScrolling();
    });

    // Re-init after Livewire navigation
    document.addEventListener("livewire:navigated", enableDragScrolling);
}

// ═══════════════════════════════════════════════
// ALPINE.JS COMPONENT REGISTRATIONS
// Used by the Awrel Theme Settings page.
// ═══════════════════════════════════════════════
document.addEventListener("alpine:init", () => {
    // Color Picker component
    Alpine.data("awrelColorPicker", () => ({
        previewColor: "#f59e0b",
        init(initialColor) {
            this.previewColor = initialColor || "#f59e0b";
        },
        updatePreview(value) {
            this.previewColor = value;
        },
        setColor(value) {
            this.previewColor = value;
            this.$wire.set("data.primary_color", value);
            if (this.$refs.colorInput) {
                this.$refs.colorInput.value = value;
            }
        },
    }));

    // Range Slider component
    Alpine.data("awrelRangeSlider", () => ({
        currentWidth: 256,
        init(initialWidth) {
            this.currentWidth = parseInt(initialWidth) || 256;
        },
        updateWidth(value) {
            this.currentWidth = parseInt(value) || 256;
            this.$wire.set("data.sidebar_width", this.currentWidth);
        },
    }));
});
