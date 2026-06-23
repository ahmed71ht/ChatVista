import "./bootstrap";
import Alpine from "alpinejs";

// مكون Toast
Alpine.data("toast", () => ({
    toasts: [],
    show(message, type = "success") {
        const id = Date.now();
        this.toasts.push({ id, message, type, show: true });
        setTimeout(() => {
            const index = this.toasts.findIndex((t) => t.id === id);
            if (index > -1) this.toasts[index].show = false;
            setTimeout(() => {
                this.toasts = this.toasts.filter((t) => t.id !== id);
            }, 300);
        }, 3000);
    },
}));

// متغيرات عامة
Alpine.data("mainApp", () => ({
    mobileMenuOpen: false,
}));

window.Alpine = Alpine;
Alpine.start();
