import "./bootstrap";

import Alpine from "alpinejs";
import Swal from "sweetalert2";
import {
    showToast,
    showSuccess,
    showSuccessAutoClose,
    showError,
    showConfirm,
    showConfirmAsync,
    showLoading,
} from "./utils/swal";

window.Alpine = Alpine;
window.Swal = Swal; // kept for any inline blade scripts that use Swal directly

// ---------------------------------------------------------------------------
// Expose helpers globally
// ---------------------------------------------------------------------------
window.showToast = showToast;
window.showSuccess = showSuccess;
window.showSuccessAutoClose = showSuccessAutoClose;
window.showError = showError;
window.showConfirm = showConfirm;
window.showConfirmAsync = showConfirmAsync;
window.showLoading = showLoading;

// Backward-compat aliases (existing blade templates may still call these)
window.swalToast = showToast;
window.swalSuccess = (text) => showSuccess(text);
window.swalShowLoading = showLoading;

// ---------------------------------------------------------------------------
// Remove legacy flash banner DOM nodes so toasts don't duplicate them
// ---------------------------------------------------------------------------
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

// ---------------------------------------------------------------------------
// Convert session flash messages to toasts on page load
// ---------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    const flashSuccess = document.body.dataset.flashSuccess || "";
    const flashStatus = document.body.dataset.flashStatus || "";
    const flashError = document.body.dataset.flashError || "";

    if (flashSuccess) {
        removeLegacyFlashBanner(flashSuccess, "success");
        showToast("success", flashSuccess);
    }

    if (flashStatus) {
        removeLegacyFlashBanner(flashStatus, "success");
        showToast("success", flashStatus);
    }

    if (flashError) {
        removeLegacyFlashBanner(flashError, "error");
        showToast("error", flashError);
    }
});

// ---------------------------------------------------------------------------
// Global async action handler
// Supports:
// - data-async-url + data-async-method
// - data-async-delete-url (backward compatible, method defaults to DELETE)
// ---------------------------------------------------------------------------
const resolveCsrfToken = (button) => {
    const formToken = button
        .closest("form")
        ?.querySelector('input[name="_token"]')?.value;
    if (formToken) {
        return formToken;
    }

    return document.querySelector('meta[name="csrf-token"]')?.content ?? "";
};

document.addEventListener("click", async (event) => {
    const button = event.target.closest(
        "[data-async-url], [data-async-delete-url]",
    );
    if (!(button instanceof HTMLButtonElement)) {
        return;
    }

    event.preventDefault();

    if (button.dataset.deleting === "true") {
        return;
    }

    const actionUrl = button.dataset.asyncUrl || button.dataset.asyncDeleteUrl;
    if (!actionUrl) {
        return;
    }

    const actionMethod = (
        button.dataset.asyncMethod ||
        (button.dataset.asyncDeleteUrl ? "DELETE" : "POST")
    ).toUpperCase();

    const confirmResult = await showConfirmAsync(
        {
            title: button.dataset.confirmTitle || "Adakah anda pasti?",
            text:
                button.dataset.confirmText ||
                "Tindakan ini tidak boleh dibatalkan.",
            confirmText: button.dataset.confirmButton || "Ya, padam",
            processingError:
                button.dataset.processingError ||
                "Ralat semasa memproses tindakan.",
        },
        async () => {
            const response = await fetch(actionUrl, {
                method: actionMethod,
                headers: {
                    "X-CSRF-TOKEN": resolveCsrfToken(button),
                    "Content-Type": "application/json",
                },
            });

            if (!response.ok) {
                throw new Error("Delete request failed");
            }

            return true;
        },
    );

    if (!confirmResult.isConfirmed) {
        return;
    }

    button.disabled = true;
    button.dataset.deleting = "true";

    try {
        Swal.close();

        const successMessage =
            button.dataset.successMessage || "Rekod berjaya dipadam.";
        const successRedirectUrl = button.dataset.successRedirectUrl || "";
        if (successRedirectUrl) {
            await showSuccessAutoClose(successMessage, {
                redirectUrl: successRedirectUrl,
                timer: Number(button.dataset.successRedirectDelay || 1400),
            });
            return;
        }

        await showToast("success", successMessage);

        const removeSelectors = (button.dataset.removeSelector || "")
            .split("|")
            .map((s) => s.trim())
            .filter(Boolean);
        for (const selector of removeSelectors) {
            const node = document.querySelector(selector);
            if (node) {
                node.remove();
            }
        }

        const showSelectors = (button.dataset.showSelector || "")
            .split("|")
            .map((s) => s.trim())
            .filter(Boolean);
        for (const selector of showSelectors) {
            const node = document.querySelector(selector);
            if (node) {
                node.style.display = "";
            }
        }

        const resetInputSelectors = (button.dataset.resetFileInputs || "")
            .split("|")
            .map((s) => s.trim())
            .filter(Boolean);
        for (const selector of resetInputSelectors) {
            const input = document.querySelector(selector);
            if (input instanceof HTMLInputElement) {
                input.value = "";
            }
        }

        const resetLabelSelector = button.dataset.resetLabelSelector;
        if (resetLabelSelector) {
            const labelNode = document.querySelector(resetLabelSelector);
            if (labelNode) {
                labelNode.textContent =
                    button.dataset.resetLabelText ||
                    labelNode.textContent ||
                    "";
            }
        }
    } catch (error) {
        if (Swal.isVisible()) {
            Swal.close();
        }

        const errorMessage =
            button.dataset.errorMessage || "Ralat semasa memadam rekod.";
        await showError(errorMessage);

        button.disabled = false;
        button.dataset.deleting = "false";
    }
});

// ---------------------------------------------------------------------------
// Global form confirm interceptor (data-confirm attribute)
// ---------------------------------------------------------------------------
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

        showConfirm({ text: message }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            form.dataset.confirmed = "true";
            showLoading();
            form.submit();
        });
    },
    true,
);

Alpine.start();
