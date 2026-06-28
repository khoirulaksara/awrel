export default function () {
    return {
        activeTab: "general",

        init() {
            const saved = localStorage.getItem("awrel_active_tab");
            if (saved) this.activeTab = saved;
        },

        switchTab(tab) {
            this.activeTab = tab;
            localStorage.setItem("awrel_active_tab", tab);
        },
    };
}
