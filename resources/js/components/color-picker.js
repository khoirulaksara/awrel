export default function (initialColor) {
    return {
        previewColor: "#f59e0b",

        init() {
            this.previewColor = initialColor || "#f59e0b";

            this.$watch("previewColor", (val) => {
                document.documentElement.style.setProperty(
                    "--color-primary-500",
                    this.hexToRgb(val),
                );
            });
        },

        updatePreview(value) {
            this.previewColor = value;
        },

        setColor(value) {
            this.previewColor = value;
            this.$wire.set("settings.primary_color", value);

            if (this.$refs.colorInput) {
                this.$refs.colorInput.value = value;
            }

            const rgb = this.hexToRgb(value);

            for (let i = 50; i <= 950; i += 50) {
                const shade = this.shadeRgb(rgb, i);
                document.documentElement.style.setProperty(
                    "--color-primary-" + i,
                    shade,
                );
                document.documentElement.style.setProperty(
                    "--primary-" + i,
                    "rgb(" + shade + ")",
                );
            }
        },

        hexToRgb(hex) {
            hex = hex.replace("#", "");

            if (hex.length === 3) {
                hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            }

            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);

            return r + " " + g + " " + b;
        },

        shadeRgb(rgb, shade) {
            const parts = rgb.split(" ");
            const r = parseInt(parts[0]);
            const g = parseInt(parts[1]);
            const b = parseInt(parts[2]);
            const ratio = shade / 500;

            if (ratio <= 1) {
                const t = 1 - ratio;
                const nr = Math.round(r + (255 - r) * t);
                const ng = Math.round(g + (255 - g) * t);
                const nb = Math.round(b + (255 - b) * t);

                return nr + " " + ng + " " + nb;
            }

            const t = (shade - 500) / 450;
            const nr = Math.round(r * (1 - t));
            const ng = Math.round(g * (1 - t));
            const nb = Math.round(b * (1 - t));

            return nr + " " + ng + " " + nb;
        },
    };
}
