import "./bootstrap";

import Alpine from "alpinejs";
import Swal from "sweetalert2";

window.Alpine = Alpine;
window.Swal = Swal;

const toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2600,
    timerProgressBar: true,
    didOpen: (el) => {
        el.addEventListener("mouseenter", Swal.stopTimer);
        el.addEventListener("mouseleave", Swal.resumeTimer);
    },
});

window.swalToast = (icon, text) => {
    const titles = {
        success: "Berjaya",
        error: "Ralat",
        warning: "Amaran",
        info: "Makluman",
        question: "Soalan",
    };
    return toast.fire({ icon, title: titles[icon] ?? "", text });
};

window.swalSuccess = (text, title = "Berjaya") =>
    Swal.fire({
        icon: "success",
        title,
        text,
        showConfirmButton: false,
        timer: 1800,
        timerProgressBar: true,
    });

window.swalShowLoading = (
    title = "Memproses...",
    text = "Sila tunggu sebentar.",
) => {
    Swal.fire({
        title,
        text,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
};

const removeLegacyFlashBanner = (message, type) => {
    if (!message) {
        return;
    }

    const greenSelectors = [
        ".rounded-md.bg-green-50.p-3.text-sm.text-green-800",
        ".rounded-lg.bg-green-50.border.border-green-200.px-4.py-3.text-sm.text-green-800",
    ];

    const redSelectors = [
        ".rounded-md.bg-red-50.p-3.text-sm.text-red-700",
        ".rounded-md.bg-red-50.p-3.text-sm.text-red-800",
        ".rounded-lg.bg-red-50.border.border-red-200.px-4.py-3.text-sm.text-red-800",
    ];

    const candidates = document.querySelectorAll(
        (type === "error" ? redSelectors : greenSelectors).join(","),
    );

    for (const node of candidates) {
        const text = node.textContent?.trim() ?? "";
        if (text.includes(message.trim())) {
            node.remove();
            break;
        }
    }
};

document.addEventListener("DOMContentLoaded", () => {
    const flashSuccess = document.body.dataset.flashSuccess || "";
    const flashStatus = document.body.dataset.flashStatus || "";
    const flashError = document.body.dataset.flashError || "";

    if (flashSuccess) {
        removeLegacyFlashBanner(flashSuccess, "success");
        window.swalToast("success", flashSuccess);
    }

    if (flashStatus) {
        removeLegacyFlashBanner(flashStatus, "success");
        window.swalToast("success", flashStatus);
    }

    if (flashError) {
        removeLegacyFlashBanner(flashError, "error");
        window.swalToast("error", flashError);
    }
});

const confirmDialogDefaults = {
    title: "Adakah anda pasti?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, teruskan",
    cancelButtonText: "Batal",
    confirmButtonColor: "#2563eb",
    cancelButtonColor: "#6b7280",
    reverseButtons: true,
    focusCancel: true,
};

document.addEventListener(
    "submit",
    (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const message = form.dataset.confirm;
        if (!message || form.dataset.confirmed === "true") {
            return;
        }

        event.preventDefault();

        Swal.fire({
            ...confirmDialogDefaults,
            text: message,
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            form.dataset.confirmed = "true";
            window.swalShowLoading();
            form.submit();
        });
    },
    true,
);

Alpine.start();
