export default function (initialWidth) {
    return {
        currentWidth: 256,

        init() {
            this.currentWidth = parseInt(initialWidth) || 256;
            this.applySidebarWidth(this.currentWidth);
        },

        updateWidth(value) {
            this.currentWidth = parseInt(value) || 256;
            this.$wire.set("settings.sidebar_width", this.currentWidth);
            this.applySidebarWidth(this.currentWidth);
        },

        applySidebarWidth(width) {
            document.documentElement.style.setProperty(
                "--awrel-sidebar-width",
                width + "px",
            );
        },
    };
}
